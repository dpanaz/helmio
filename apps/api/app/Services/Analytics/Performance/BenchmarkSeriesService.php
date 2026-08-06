<?php

namespace App\Services\Analytics\Performance;

use App\Models\Benchmark;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BenchmarkSeriesService
{
    /**
     * Build an indexed benchmark series aligned to portfolio dates.
     *
     * The first valid benchmark price is normalized to 100.
     */
    public function build(
        Benchmark $benchmark,
        Collection $portfolioValuations
    ): array {
        $portfolioValuations = $portfolioValuations
            ->sortBy('valuation_date')
            ->values();

        if ($portfolioValuations->isEmpty()) {
            return $this->emptyResult();
        }

        $startDate = $portfolioValuations
            ->first()
            ->valuation_date;

        $endDate = $portfolioValuations
            ->last()
            ->valuation_date;

        $prices = $benchmark->prices()
            ->whereDate(
                'price_date',
                '<=',
                $endDate->toDateString()
            )
            ->orderBy('price_date')
            ->get();

        if ($prices->isEmpty()) {
            return $this->emptyResult(
                warning: 'No benchmark prices were found.'
            );
        }

        $series = [];
        $firstPrice = null;
        $stalePriceCount = 0;
        $missingPriceCount = 0;

        foreach ($portfolioValuations as $valuation) {
            $price = $prices
                ->where(
                    'price_date',
                    '<=',
                    $valuation->valuation_date
                )
                ->last();

            if ($price === null) {
                $missingPriceCount++;

                $series[] = [
                    'date' =>
                        $valuation->valuation_date
                            ->toDateString(),

                    'price' => null,
                    'indexed_value' => null,
                    'price_date' => null,
                    'price_age_days' => null,
                ];

                continue;
            }

            $performancePrice =
                $price->performance_price;

            if ($firstPrice === null) {
                $firstPrice = $performancePrice;
            }

            $priceAgeDays =
                $price->price_date->diffInDays(
                    $valuation->valuation_date
                );

            if ($priceAgeDays > 7) {
                $stalePriceCount++;
            }

            $indexedValue =
                $firstPrice > 0
                    ? ($performancePrice / $firstPrice) * 100
                    : null;

            $series[] = [
                'date' =>
                    $valuation->valuation_date
                        ->toDateString(),

                'price' =>
                    round($performancePrice, 6),

                'indexed_value' =>
                    $indexedValue === null
                        ? null
                        : round($indexedValue, 6),

                'price_date' =>
                    $price->price_date
                        ->toDateString(),

                'price_age_days' =>
                    $priceAgeDays,
            ];
        }

        $validSeries = collect($series)
            ->whereNotNull('indexed_value')
            ->values();

        if ($validSeries->count() < 2) {
            return [
                'status' => 'insufficient_data',
                'series' => $series,
                'return' => null,
                'data_points' => $validSeries->count(),
                'missing_price_count' => $missingPriceCount,
                'stale_price_count' => $stalePriceCount,
                'warnings' => [
                    [
                        'code' =>
                            'insufficient_benchmark_series',

                        'message' =>
                            'At least two benchmark prices are required.',
                    ],
                ],
            ];
        }

        $firstIndexed =
            (float) $validSeries->first()['indexed_value'];

        $lastIndexed =
            (float) $validSeries->last()['indexed_value'];

        $return = $firstIndexed > 0
            ? ($lastIndexed / $firstIndexed) - 1
            : null;

        $warnings = [];

        if ($missingPriceCount > 0) {
            $warnings[] = [
                'code' =>
                    'missing_benchmark_prices',

                'message' =>
                    "{$missingPriceCount} portfolio dates had no benchmark price.",
            ];
        }

        if ($stalePriceCount > 0) {
            $warnings[] = [
                'code' =>
                    'stale_benchmark_prices',

                'message' =>
                    "{$stalePriceCount} portfolio dates used benchmark prices older than seven days.",
            ];
        }

        return [
            'status' => 'complete',
            'series' => $series,
            'return' =>
                $return === null
                    ? null
                    : round($return, 10),

            'data_points' =>
                $validSeries->count(),

            'missing_price_count' =>
                $missingPriceCount,

            'stale_price_count' =>
                $stalePriceCount,

            'warnings' => $warnings,
        ];
    }

    private function emptyResult(
        ?string $warning = null
    ): array {
        return [
            'status' => 'insufficient_data',
            'series' => [],
            'return' => null,
            'data_points' => 0,
            'missing_price_count' => 0,
            'stale_price_count' => 0,
            'warnings' => $warning
                ? [
                    [
                        'code' =>
                            'missing_benchmark_data',

                        'message' => $warning,
                    ],
                ]
                : [],
        ];
    }
}