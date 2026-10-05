<?php

namespace Tests\Feature;

use App\Services\MarketData\TwelveDataMarketDataService;
use App\Services\MarketData\TwelveDataRateLimited;
use App\Services\MarketData\TwelveDataInvalidSymbol;
use Illuminate\Http\Client\RequestException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwelveDataRateLimitTest extends TestCase
{
    public function test_invalid_symbols_are_requested_once_across_date_ranges_and_service_instances(): void
    {
        $this->assertInvalidSymbolIsCached(404);
    }

    public function test_invalid_symbols_in_successful_http_responses_are_cached(): void
    {
        $this->assertInvalidSymbolIsCached(200);
    }

    private function assertInvalidSymbolIsCached(int $httpStatus): void
    {
        config()->set('services.twelve_data.key', 'invalid-symbol-test-key');
        Cache::flush();
        Http::fake(['*' => Http::response([
            'status' => 'error', 'code' => 404, 'message' => 'Symbol is invalid.',
        ], $httpStatus)]);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                (new TwelveDataMarketDataService)->historicalDailyPrices(
                    symbol: $attempt === 0 ? 'INVALID' : ' invalid ',
                    startDate: CarbonImmutable::parse('2026-09-01')->addDays($attempt),
                    endDate: CarbonImmutable::parse('2026-09-26'),
                );
                $this->fail('Expected an invalid-symbol result.');
            } catch (TwelveDataInvalidSymbol $exception) {
                $this->assertStringNotContainsString('invalid-symbol-test-key', $exception->getMessage());
            }
        }

        Http::assertSentCount(1);
    }

    public function test_invalid_symbol_cache_expires_and_other_symbols_can_still_import(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-05 10:00:00', 'UTC'));
        config()->set('services.twelve_data.key', 'expiry-test-key');
        Cache::flush();
        Http::fake(['*' => Http::sequence()
            ->push(['status' => 'error', 'code' => 404], 404)
            ->push(['values' => [['datetime' => '2026-09-01', 'close' => '100']]])
            ->push(['values' => [['datetime' => '2026-09-01', 'close' => '101']]])]);

        $service = new TwelveDataMarketDataService;
        $start = CarbonImmutable::parse('2026-09-01');
        $end = CarbonImmutable::parse('2026-09-26');
        try {
            $service->historicalDailyPrices('INVALID', $start, $end);
            $this->fail('Expected an invalid-symbol result.');
        } catch (TwelveDataInvalidSymbol) {
        }

        $this->assertCount(1, $service->historicalDailyPrices('VALID', $start, $end));
        $this->travel(25)->hours();
        $this->assertCount(1, $service->historicalDailyPrices('INVALID', $start, $end));
        Http::assertSentCount(3);
    }

    public function test_transient_server_errors_still_retry(): void
    {
        config()->set('services.twelve_data.key', 'server-test-key');
        Cache::flush();
        Http::fake(['*' => Http::sequence()->push([], 500)->push(['values' => []])]);

        (new TwelveDataMarketDataService)->historicalDailyPrices(
            'TEST', CarbonImmutable::parse('2026-09-01'), CarbonImmutable::parse('2026-09-26'),
        );

        Http::assertSentCount(2);
    }

    public function test_authentication_errors_are_not_cached_as_invalid_symbols(): void
    {
        config()->set('services.twelve_data.key', 'auth-test-key');
        Cache::flush();
        Http::fake(['*' => Http::response([], 401)]);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                (new TwelveDataMarketDataService)->historicalDailyPrices(
                    'TEST', CarbonImmutable::parse('2026-09-01'), CarbonImmutable::parse('2026-09-26'),
                );
                $this->fail('Expected the authentication error.');
            } catch (RequestException $exception) {
                $this->assertSame(401, $exception->response->status());
            }
        }

        Http::assertSentCount(2);
    }

    public function test_a_429_stops_immediate_retries_and_cools_down_subsequent_requests(): void
    {
        config()->set('services.twelve_data.key', 'test-key');
        Cache::flush();
        Http::fake([
            '*' => Http::response([
                'code' => 429,
                'message' => 'Minute credits exhausted',
            ], 429),
        ]);

        $service = app(TwelveDataMarketDataService::class);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $service->historicalDailyPrices(
                    symbol: 'TEST',
                    startDate: CarbonImmutable::parse('2026-09-01'),
                    endDate: CarbonImmutable::parse('2026-09-26'),
                );

                $this->fail('Expected Twelve Data to pause after a 429.');
            } catch (TwelveDataRateLimited $exception) {
                $this->assertStringContainsString('retry later', $exception->getMessage());
            }
        }

        Http::assertSentCount(1);
    }

    public function test_daily_credit_limit_waits_until_after_midnight_utc(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-03 10:00:00', 'UTC'));
        config()->set('services.twelve_data.key', 'daily-test-key');
        Cache::flush();
        Http::fake(['*' => Http::response([
            'code' => 429,
            'message' => 'You have run out of API credits for the day.',
        ], 429)]);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                app(TwelveDataMarketDataService::class)->historicalDailyPrices(
                    symbol: 'TEST',
                    startDate: CarbonImmutable::parse('2026-09-01'),
                    endDate: CarbonImmutable::parse('2026-10-03'),
                );

                $this->fail('Expected the daily credit limit to pause imports.');
            } catch (TwelveDataRateLimited $exception) {
                $this->assertSame(50460, $exception->retryAfterSeconds);
            }
        }

        Http::assertSentCount(1);
    }
}
