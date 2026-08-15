<?php

namespace App\Services\Analytics\Performance;

use App\Models\Benchmark;
use Illuminate\Support\Collection;

class BenchmarkSeriesService
{
    /**
     * Build an indexed benchmark series aligned to portfolio dates.
     *
     * Single benchmarks use their own BenchmarkPrice history.
     *
     * Composite benchmarks calculate a periodically rebalanced
     * weighted return from their component benchmark series.
     *
     * The first valid benchmark value is normalized to 100.
     *
     * @return array<string, mixed>
     */
    public function build(
        Benchmark $benchmark,
        Collection $portfolioValuations
    ): array {
        $portfolioValuations =
            $portfolioValuations
                ->sortBy('valuation_date')
                ->values();

        if ($portfolioValuations->isEmpty()) {
            return $this->emptyResult();
        }

        if ($benchmark->isComposite()) {
            return $this->buildCompositeSeries(
                benchmark: $benchmark,
                portfolioValuations:
                    $portfolioValuations,
            );
        }

        return $this->buildSingleSeries(
            benchmark: $benchmark,
            portfolioValuations:
                $portfolioValuations,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSingleSeries(
        Benchmark $benchmark,
        Collection $portfolioValuations
    ): array {
        $endDate = $portfolioValuations
            ->last()
            ->valuation_date;

        $prices = $benchmark
            ->prices()
            ->whereDate(
                'price_date',
                '<=',
                $endDate->toDateString()
            )
            ->orderBy('price_date')
            ->get();

        if ($prices->isEmpty()) {
            return $this->emptyResult(
                warning:
                    "No benchmark prices were found for {$benchmark->symbol}."
            );
        }

        $series = [];

        $firstPrice = null;

        $stalePriceCount = 0;

        $missingPriceCount = 0;

        foreach (
            $portfolioValuations
            as $valuation
        ) {
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
                        $valuation
                            ->valuation_date
                            ->toDateString(),

                    'price' => null,

                    'indexed_value' => null,

                    'price_date' => null,

                    'price_age_days' => null,
                ];

                continue;
            }

            $performancePrice =
                (float) $price
                    ->performance_price;

            if (
                $firstPrice === null
                && $performancePrice > 0
            ) {
                $firstPrice =
                    $performancePrice;
            }

            $priceAgeDays =
                $price
                    ->price_date
                    ->diffInDays(
                        $valuation
                            ->valuation_date
                    );

            if ($priceAgeDays > 7) {
                $stalePriceCount++;
            }

            $indexedValue =
                $firstPrice !== null
                && $firstPrice > 0
                    ? (
                        $performancePrice
                        / $firstPrice
                    ) * 100
                    : null;

            $series[] = [
                'date' =>
                    $valuation
                        ->valuation_date
                        ->toDateString(),

                'price' =>
                    round(
                        $performancePrice,
                        6
                    ),

                'indexed_value' =>
                    $indexedValue === null
                        ? null
                        : round(
                            $indexedValue,
                            6
                        ),

                'price_date' =>
                    $price
                        ->price_date
                        ->toDateString(),

                'price_age_days' =>
                    $priceAgeDays,
            ];
        }

        return $this->finishResult(
            series: $series,
            missingPriceCount:
                $missingPriceCount,
            stalePriceCount:
                $stalePriceCount,
        );
    }

    /**
     * Build a composite benchmark.
     *
     * Example:
     *
     * 60% VTI
     * 40% BND
     *
     * Each portfolio valuation date receives a weighted
     * return from the component benchmarks.
     *
     * This effectively models a portfolio rebalanced to
     * the target weights on each valuation interval.
     *
     * @return array<string, mixed>
     */
    private function buildCompositeSeries(
        Benchmark $benchmark,
        Collection $portfolioValuations
    ): array {
        $componentDefinitions =
            $benchmark->compositeComponents();

        if (count($componentDefinitions) < 2) {
            return $this->emptyResult(
                warning:
                    'Composite benchmark requires at least two components.'
            );
        }

        $components = [];

        $weightTotal = 0.0;

        foreach (
            $componentDefinitions
            as $definition
        ) {
            $symbol = strtoupper(
                trim(
                    (string) (
                        $definition['symbol']
                        ?? ''
                    )
                )
            );

            $weight = isset(
                $definition['weight']
            )
                && is_numeric(
                    $definition['weight']
                )
                    ? (float) $definition[
                        'weight'
                    ]
                    : 0.0;

            if (
                $symbol === ''
                || $weight <= 0
            ) {
                continue;
            }

            /*
             * Prevent direct self-reference.
             */
            if (
                strtoupper(
                    (string) $benchmark->symbol
                ) === $symbol
            ) {
                return $this->emptyResult(
                    warning:
                        'Composite benchmark cannot reference itself.'
                );
            }

            $component =
                Benchmark::query()
                    ->where(
                        'symbol',
                        $symbol
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

            if ($component === null) {
                return $this->emptyResult(
                    warning:
                        "Composite benchmark component {$symbol} was not found."
                );
            }

            /*
             * Keep version 0.1 simple and deterministic:
             * components must be direct market benchmarks,
             * not other composites.
             */
            if ($component->isComposite()) {
                return $this->emptyResult(
                    warning:
                        "Nested composite benchmark {$symbol} is not supported."
                );
            }

            $components[] = [
                'benchmark' =>
                    $component,

                'symbol' =>
                    $symbol,

                'weight' =>
                    $weight,
            ];

            $weightTotal +=
                $weight;
        }

        if (
            count($components) < 2
            || $weightTotal <= 0
        ) {
            return $this->emptyResult(
                warning:
                    'Composite benchmark has insufficient valid components.'
            );
        }

        /*
         * Require weights to equal 100%.
         *
         * We allow tiny floating-point differences,
         * but do not silently normalize bad metadata.
         */
        if (
            abs(
                $weightTotal - 1.0
            ) > 0.0001
        ) {
            return $this->emptyResult(
                warning:
                    sprintf(
                        'Composite benchmark weights total %.4f instead of 1.0000.',
                        $weightTotal
                    )
            );
        }

        /*
         * Build each underlying benchmark on exactly
         * the same portfolio valuation dates.
         */
        $componentSeries = [];

        foreach (
            $components
            as $component
        ) {
            $result =
                $this->buildSingleSeries(
                    benchmark:
                        $component[
                            'benchmark'
                        ],

                    portfolioValuations:
                        $portfolioValuations,
                );

            if (
                ($result['status'] ?? null)
                !== 'complete'
            ) {
                return $this->emptyResult(
                    warning:
                        "Insufficient price history for composite component {$component['symbol']}."
                );
            }

            $componentSeries[
                $component['symbol']
            ] = collect(
                $result['series']
            )->keyBy('date');
        }

        $series = [];

        $compositeIndex =
            100.0;

        $previousValues = [];

        $started = false;

        $missingPriceCount = 0;

        $stalePriceCount = 0;

        foreach (
            $portfolioValuations
            as $valuation
        ) {
            $date =
                $valuation
                    ->valuation_date
                    ->toDateString();

            $currentValues = [];

            $componentMetadata = [];

            $missingComponent =
                false;

            $staleComponent =
                false;

            foreach (
                $components
                as $component
            ) {
                $symbol =
                    $component['symbol'];

                $point =
                    $componentSeries[
                        $symbol
                    ]->get($date);

                $indexedValue =
                    data_get(
                        $point,
                        'indexed_value'
                    );

                if (
                    $indexedValue === null
                    || ! is_numeric(
                        $indexedValue
                    )
                ) {
                    $missingComponent =
                        true;

                    break;
                }

                $currentValues[
                    $symbol
                ] = (float) $indexedValue;

                $priceAge =
                    data_get(
                        $point,
                        'price_age_days'
                    );

                if (
                    is_numeric(
                        $priceAge
                    )
                    && (int) $priceAge > 7
                ) {
                    $staleComponent =
                        true;
                }

                $componentMetadata[
                    $symbol
                ] = [
                    'weight' =>
                        $component[
                            'weight'
                        ],

                    'indexed_value' =>
                        round(
                            (float) $indexedValue,
                            6
                        ),

                    'price_date' =>
                        data_get(
                            $point,
                            'price_date'
                        ),

                    'price_age_days' =>
                        $priceAge,
                ];
            }

            if ($missingComponent) {
                $missingPriceCount++;

                $series[] = [
                    'date' => $date,

                    'price' => null,

                    'indexed_value' =>
                        null,

                    'price_date' =>
                        null,

                    'price_age_days' =>
                        null,

                    'components' =>
                        $componentMetadata,
                ];

                continue;
            }

            if ($staleComponent) {
                $stalePriceCount++;
            }

            /*
             * First fully valid point begins at 100.
             */
            if (! $started) {
                $started = true;

                $previousValues =
                    $currentValues;

                $series[] = [
                    'date' => $date,

                    'price' =>
                        round(
                            $compositeIndex,
                            6
                        ),

                    'indexed_value' =>
                        round(
                            $compositeIndex,
                            6
                        ),

                    'price_date' =>
                        $date,

                    'price_age_days' =>
                        0,

                    'components' =>
                        $componentMetadata,
                ];

                continue;
            }

            $weightedReturn =
                0.0;

            foreach (
                $components
                as $component
            ) {
                $symbol =
                    $component['symbol'];

                $previous =
                    $previousValues[
                        $symbol
                    ] ?? null;

                $current =
                    $currentValues[
                        $symbol
                    ] ?? null;

                if (
                    $previous === null
                    || $previous <= 0
                    || $current === null
                ) {
                    $missingComponent =
                        true;

                    break;
                }

                $componentReturn =
                    (
                        $current
                        / $previous
                    ) - 1;

                $weightedReturn +=
                    $componentReturn
                    * $component[
                        'weight'
                    ];
            }

            if ($missingComponent) {
                $missingPriceCount++;

                $series[] = [
                    'date' => $date,

                    'price' => null,

                    'indexed_value' =>
                        null,

                    'price_date' =>
                        null,

                    'price_age_days' =>
                        null,

                    'components' =>
                        $componentMetadata,
                ];

                continue;
            }

            $compositeIndex *=
                1 + $weightedReturn;

            $previousValues =
                $currentValues;

            $series[] = [
                'date' =>
                    $date,

                /*
                 * Composite benchmarks do not have
                 * an actual market price, so the
                 * synthetic index is used here.
                 */
                'price' =>
                    round(
                        $compositeIndex,
                        6
                    ),

                'indexed_value' =>
                    round(
                        $compositeIndex,
                        6
                    ),

                'price_date' =>
                    $date,

                'price_age_days' =>
                    0,

                'components' =>
                    $componentMetadata,

                'period_return' =>
                    round(
                        $weightedReturn,
                        10
                    ),
            ];
        }

        $result =
            $this->finishResult(
                series:
                    $series,

                missingPriceCount:
                    $missingPriceCount,

                stalePriceCount:
                    $stalePriceCount,
            );

        $result['composite'] =
            true;

        $result['components'] =
            collect(
                $components
            )->map(
                fn (
                    array $component
                ): array => [
                    'benchmark_id' =>
                        $component[
                            'benchmark'
                        ]->id,

                    'name' =>
                        $component[
                            'benchmark'
                        ]->name,

                    'symbol' =>
                        $component[
                            'symbol'
                        ],

                    'weight' =>
                        $component[
                            'weight'
                        ],
                ]
            )->values()->all();

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $series
     * @return array<string, mixed>
     */
    private function finishResult(
        array $series,
        int $missingPriceCount,
        int $stalePriceCount
    ): array {
        $validSeries =
            collect($series)
                ->whereNotNull(
                    'indexed_value'
                )
                ->values();

        if ($validSeries->count() < 2) {
            return [
                'status' =>
                    'insufficient_data',

                'series' =>
                    $series,

                'return' =>
                    null,

                'data_points' =>
                    $validSeries->count(),

                'missing_price_count' =>
                    $missingPriceCount,

                'stale_price_count' =>
                    $stalePriceCount,

                'warnings' => [
                    [
                        'code' =>
                            'insufficient_benchmark_series',

                        'message' =>
                            'At least two benchmark values are required.',
                    ],
                ],
            ];
        }

        $firstIndexed =
            (float) $validSeries
                ->first()[
                    'indexed_value'
                ];

        $lastIndexed =
            (float) $validSeries
                ->last()[
                    'indexed_value'
                ];

        $return =
            $firstIndexed > 0
                ? (
                    $lastIndexed
                    / $firstIndexed
                ) - 1
                : null;

        $warnings = [];

        if ($missingPriceCount > 0) {
            $warnings[] = [
                'code' =>
                    'missing_benchmark_prices',

                'message' =>
                    "{$missingPriceCount} portfolio dates had incomplete benchmark price data.",
            ];
        }

        if ($stalePriceCount > 0) {
            $warnings[] = [
                'code' =>
                    'stale_benchmark_prices',

                'message' =>
                    "{$stalePriceCount} portfolio dates used benchmark component prices older than seven days.",
            ];
        }

        return [
            'status' =>
                'complete',

            'series' =>
                $series,

            'return' =>
                $return === null
                    ? null
                    : round(
                        $return,
                        10
                    ),

            'data_points' =>
                $validSeries->count(),

            'missing_price_count' =>
                $missingPriceCount,

            'stale_price_count' =>
                $stalePriceCount,

            'warnings' =>
                $warnings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(
        ?string $warning = null
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'series' =>
                [],

            'return' =>
                null,

            'data_points' =>
                0,

            'missing_price_count' =>
                0,

            'stale_price_count' =>
                0,

            'warnings' =>
                $warning
                    ? [
                        [
                            'code' =>
                                'missing_benchmark_data',

                            'message' =>
                                $warning,
                        ],
                    ]
                    : [],
        ];
    }
}