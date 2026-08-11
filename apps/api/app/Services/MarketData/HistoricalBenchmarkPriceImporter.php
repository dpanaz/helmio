<?php

namespace App\Services\MarketData;

use App\Models\Benchmark;
use App\Models\BenchmarkPrice;
use Carbon\CarbonInterface;

class HistoricalBenchmarkPriceImporter
{
    public function __construct(
        private readonly TwelveDataMarketDataService $marketData,
    ) {
    }

    public function import(
        Benchmark $benchmark,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
    ): array {
        $symbol = trim(
            (string) $benchmark->symbol,
        );

        if ($symbol === '') {
            return [
                'benchmark_id' =>
                    $benchmark->id,

                'symbol' =>
                    null,

                'imported' =>
                    0,

                'status' =>
                    'skipped',

                'reason' =>
                    'Benchmark has no symbol.',
            ];
        }

        /*
         * Synthetic/blended benchmarks such as HELMIO-60-40
         * cannot be fetched directly from Twelve Data.
         */
        if ($benchmark->benchmark_type === 'blended') {
            return [
                'benchmark_id' =>
                    $benchmark->id,

                'symbol' =>
                    $symbol,

                'imported' =>
                    0,

                'status' =>
                    'skipped',

                'reason' =>
                    'Blended benchmarks require calculated price history.',
            ];
        }

        $prices =
            $this->marketData
                ->historicalDailyPrices(
                    symbol:
                        $symbol,

                    startDate:
                        $startDate,

                    endDate:
                        $endDate,
                );

        foreach ($prices as $price) {
            BenchmarkPrice::updateOrCreate(
                [
                    'benchmark_id' =>
                        $benchmark->id,

                    'price_date' =>
                        $price['date'],
                ],
                [
                    'close_price' =>
                        $price['close'],

                    'adjusted_close_price' =>
                        $price['close'],

                    'metadata' => [
                        'symbol' =>
                            $symbol,

                        'provider' =>
                            'twelve_data',
                    ],
                ],
            );
        }

        return [
            'benchmark_id' =>
                $benchmark->id,

            'symbol' =>
                $symbol,

            'imported' =>
                $prices->count(),

            'status' =>
                'complete',
        ];
    }
}