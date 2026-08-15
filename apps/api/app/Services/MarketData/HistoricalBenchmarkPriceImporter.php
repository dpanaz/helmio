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
         * Synthetic, blended, and composite benchmarks such as
         * HELMIO-60-40 cannot be fetched directly from Twelve Data.
         *
         * Their history must be calculated from their component
         * benchmark series instead.
         */
        if (
            in_array(
                $benchmark->benchmark_type,
                [
                    'blended',
                    'composite',
                ],
                true,
            )
        ) {
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
                    'Synthetic or composite benchmarks require calculated price history.',
            ];
        }

        /*
         * Fetch historical daily market prices from the configured
         * market-data provider.
         */
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

        /*
         * Persist each daily benchmark price.
         *
         * updateOrCreate keeps the importer idempotent, so rerunning
         * a historical backfill does not create duplicate rows.
         */
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

                    /*
                     * Twelve Data currently supplies the price series
                     * Helmio uses as its benchmark performance price.
                     *
                     * If the provider later supplies a distinct adjusted
                     * close, this can be updated without changing the
                     * importer contract.
                     */
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