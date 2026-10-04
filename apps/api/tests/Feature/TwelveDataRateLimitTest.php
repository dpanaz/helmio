<?php

namespace Tests\Feature;

use App\Services\MarketData\TwelveDataMarketDataService;
use App\Services\MarketData\TwelveDataRateLimited;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwelveDataRateLimitTest extends TestCase
{
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
