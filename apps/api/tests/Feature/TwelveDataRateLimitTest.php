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
}
