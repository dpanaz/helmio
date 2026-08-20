<?php

namespace App\Services\Analytics\Performance;

use App\Models\Benchmark;
use App\Models\PortfolioValuation;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PerformanceAnalyticsService
{
    public function __construct(
        private readonly ReturnCalculator $returnCalculator,
        private readonly TimeWeightedReturnService $timeWeightedReturnService,
        private readonly BenchmarkSeriesService $benchmarkSeriesService,
        private readonly PortfolioCashFlowService $portfolioCashFlowService,
    ) {
    }

    public function analyze(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?Benchmark $benchmark = null
    ): array {
        $valuations = PortfolioValuation::query()
            ->where('user_id', $user->id)
            ->whereNull('investment_account_id')
            ->whereBetween('valuation_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->orderBy('valuation_date')
            ->get();

        /*
         * Do not treat cash-only / pre-investment history as investment
         * performance. This prevents a transition such as $0.10 -> $681
         * from being interpreted as a six-thousand-percent return.
         */
        $valuations = $valuations
            ->filter(
                fn (PortfolioValuation $valuation): bool =>
                    (bool) data_get(
                        $valuation->metadata,
                        'has_invested_positions',
                        false,
                    ),
            )
            ->values();

        /*
         * Hard data-quality guard:
         *
         * Never calculate investment performance from a valuation series
         * that contains missing historical security prices.
         *
         * A missing historical price can make a holding disappear from a
         * historical valuation and then reappear at today's live value,
         * creating a fake gain or loss. Treat that as insufficient data
         * instead of publishing a misleading return.
         */
        $missingHistoricalPriceCount =
            $valuations->sum(
                function (
                    PortfolioValuation $valuation
                ): int {
                    return (int) data_get(
                        $valuation->metadata,
                        'missing_historical_price_count',
                        0,
                    );
                },
            );

        if ($missingHistoricalPriceCount > 0) {
            return $this->insufficientDataResult(
                sprintf(
                    'Performance cannot be calculated because %d holding valuation%s %s missing historical security prices. Backfill the missing price history and regenerate portfolio valuations.',
                    $missingHistoricalPriceCount,
                    $missingHistoricalPriceCount === 1 ? '' : 's',
                    $missingHistoricalPriceCount === 1 ? 'is' : 'are',
                ),
                warnings: [
                    [
                        'code' =>
                            'missing_security_prices',

                        'message' =>
                            sprintf(
                                '%d holding valuation%s %s missing historical security prices.',
                                $missingHistoricalPriceCount,
                                $missingHistoricalPriceCount === 1 ? '' : 's',
                                $missingHistoricalPriceCount === 1 ? 'is' : 'are',
                            ),
                    ],
                ],
            );
        }

        if ($valuations->count() < 2) {
            return $this->insufficientDataResult(
                'At least two invested portfolio valuations are required.'
            );
        }

        /*
         * Rebuild each valuation's external cash flow directly from the
         * transaction ledger for that exact valuation date.
         *
         * Older/generated PortfolioValuation rows can contain stale cash-flow
         * values. TWR should never trust a persisted duplicate when the
         * authoritative transaction ledger is available.
         *
         * This modifies only the in-memory models used for this calculation;
         * it does not write to the database.
         */
        $valuations = $valuations
            ->map(
                function (
                    PortfolioValuation $valuation
                ) use ($user): PortfolioValuation {
                    $cashFlow =
                        $this->portfolioCashFlowService
                            ->forUserOnDate(
                                user: $user,
                                date:
                                    $valuation
                                        ->valuation_date,
                            );

                    $valuation->setAttribute(
                        'net_cash_flow',
                        (float) (
                            $cashFlow[
                                'net_external_cash_flow'
                            ]
                            ?? 0
                        ),
                    );

                    return $valuation;
                },
            )
            ->values();

        /** @var PortfolioValuation $firstValuation */
        $firstValuation = $valuations->first();

        /** @var PortfolioValuation $lastValuation */
        $lastValuation = $valuations->last();

        $netCashFlow = $valuations
            ->skip(1)
            ->sum(
                fn (
                    PortfolioValuation $valuation
                ): float =>
                    (float) $valuation->net_cash_flow
            );

        $timeWeightedResult =
            $this->timeWeightedReturnService
                ->calculate($valuations);

        $portfolioReturn =
            $timeWeightedResult['return'];

        $actualDays = max(
            1,
            $firstValuation->valuation_date->diffInDays(
                $lastValuation->valuation_date
            )
        );

        $annualizedPortfolioReturn =
            $portfolioReturn === null
                ? null
                : $this->returnCalculator->annualizeReturn(
                    $portfolioReturn,
                    $actualDays
                );

        $benchmarkSeriesResult = $benchmark
            ? $this->benchmarkSeriesService->build(
                benchmark: $benchmark,
                portfolioValuations: $valuations,
            )
            : $this->emptyBenchmarkSeriesResult();

        $benchmarkReturn =
            $benchmarkSeriesResult['return'];

        $annualizedBenchmarkReturn =
            $benchmarkReturn === null
                ? null
                : $this->returnCalculator->annualizeReturn(
                    $benchmarkReturn,
                    $actualDays
                );

        $alpha = $this->returnCalculator->alpha(
            $portfolioReturn,
            $benchmarkReturn
        );

        $opportunityCost =
            $this->returnCalculator->opportunityCost(
                $firstValuation->total_value,
                $portfolioReturn,
                $benchmarkReturn
            );

        /*
         * Performance is scored primarily relative to the selected
         * benchmark. With no benchmark, keep the category unscored
         * rather than implying that absolute return alone is "good."
         */
        $score = $this->performanceScore(
            portfolioReturn: $portfolioReturn,
            benchmarkReturn: $benchmarkReturn,
        );

        return [
            'status' => 'complete',

            'score' => $score,

            'label' =>
                $score !== null
                    ? $this->scoreLabel($score)
                    : 'Insufficient data',

            'period' => [
                'start_date' =>
                    $firstValuation
                        ->valuation_date
                        ->toDateString(),

                'end_date' =>
                    $lastValuation
                        ->valuation_date
                        ->toDateString(),

                'days' => $actualDays,
            ],

            'portfolio' => [
                'beginning_value' => round(
                    $firstValuation->total_value,
                    2
                ),

                'ending_value' => round(
                    $lastValuation->total_value,
                    2
                ),

                'net_cash_flow' => round(
                    $netCashFlow,
                    2
                ),

                'return' =>
                    $this->roundRate(
                        $portfolioReturn
                    ),

                'annualized_return' =>
                    $this->roundRate(
                        $annualizedPortfolioReturn
                    ),

                'return_method' =>
                    'time_weighted',

                'valuation_count' =>
                    $valuations->count(),

                'subperiod_count' =>
                    $timeWeightedResult[
                        'subperiod_count'
                    ],

                'skipped_subperiod_count' =>
                    $timeWeightedResult[
                        'skipped_subperiod_count'
                    ],
            ],

            'benchmark' => [
                'id' => $benchmark?->id,
                'name' => $benchmark?->name,
                'symbol' => $benchmark?->symbol,

                'return' =>
                    $this->roundRate(
                        $benchmarkReturn
                    ),

                'annualized_return' =>
                    $this->roundRate(
                        $annualizedBenchmarkReturn
                    ),

                'data_points' =>
                    $benchmarkSeriesResult[
                        'data_points'
                    ],

                'missing_price_count' =>
                    $benchmarkSeriesResult[
                        'missing_price_count'
                    ],

                'stale_price_count' =>
                    $benchmarkSeriesResult[
                        'stale_price_count'
                    ],
            ],

            'comparison' => [
                'alpha' =>
                    $this->roundRate($alpha),

                'opportunity_cost' =>
                    $opportunityCost === null
                        ? null
                        : round(
                            $opportunityCost,
                            2
                        ),

                'outperformed_benchmark' =>
                    $alpha === null
                        ? null
                        : $alpha > 0,
            ],

            'chart' =>
                $this->buildComparisonChart(
                    valuations: $valuations,
                    benchmarkSeries:
                        $benchmarkSeriesResult[
                            'series'
                        ],
                ),

            'daily_returns' =>
                $timeWeightedResult['subperiods'],

            'data_quality' => [
                'has_sufficient_portfolio_history' =>
                    true,

                'has_benchmark_data' =>
                    $benchmarkSeriesResult[
                        'data_points'
                    ] >= 2,

                'warnings' =>
                    array_merge(
                        $this->buildWarnings(
                            valuations: $valuations,
                            benchmark: $benchmark,
                            benchmarkSeriesResult:
                                $benchmarkSeriesResult,
                            timeWeightedResult:
                                $timeWeightedResult,
                        ),
                        $benchmarkSeriesResult[
                            'warnings'
                        ],
                    ),
            ],

            'formula_version' =>
                'performance-0.3.0',
        ];
    }

    private function buildComparisonChart(
        Collection $valuations,
        array $benchmarkSeries
    ): array {
        if ($valuations->isEmpty()) {
            return [];
        }

        $firstPortfolioValue =
            $valuations->first()->total_value;

        $benchmarkByDate =
            collect($benchmarkSeries)
                ->keyBy('date');

        return $valuations
            ->map(function (
                PortfolioValuation $valuation
            ) use (
                $firstPortfolioValue,
                $benchmarkByDate
            ): array {
                $portfolioIndex =
                    $firstPortfolioValue > 0
                        ? (
                            $valuation->total_value
                            / $firstPortfolioValue
                        ) * 100
                        : null;

                $benchmarkPoint =
                    $benchmarkByDate->get(
                        $valuation
                            ->valuation_date
                            ->toDateString()
                    );

                return [
                    'date' =>
                        $valuation
                            ->valuation_date
                            ->toDateString(),

                    'portfolio_value' =>
                        round(
                            $valuation->total_value,
                            2
                        ),

                    'portfolio_index' =>
                        $portfolioIndex === null
                            ? null
                            : round(
                                $portfolioIndex,
                                6
                            ),

                    'benchmark_index' =>
                        data_get(
                            $benchmarkPoint,
                            'indexed_value'
                        ),

                    'benchmark_price' =>
                        data_get(
                            $benchmarkPoint,
                            'price'
                        ),

                    'benchmark_price_date' =>
                        data_get(
                            $benchmarkPoint,
                            'price_date'
                        ),

                    'benchmark_price_age_days' =>
                        data_get(
                            $benchmarkPoint,
                            'price_age_days'
                        ),
                ];
            })
            ->values()
            ->all();
    }

    private function buildWarnings(
        Collection $valuations,
        ?Benchmark $benchmark,
        array $benchmarkSeriesResult,
        array $timeWeightedResult
    ): array {
        $warnings = [];

        if ($valuations->count() < 12) {
            $warnings[] = [
                'code' =>
                    'limited_portfolio_history',

                'message' =>
                    'Performance history contains fewer than 12 valuation points.',
            ];
        }

        if ($benchmark === null) {
            $warnings[] = [
                'code' =>
                    'benchmark_not_selected',

                'message' =>
                    'Select a benchmark to calculate relative performance.',
            ];
        } elseif (
            $benchmarkSeriesResult['data_points'] < 2
        ) {
            $warnings[] = [
                'code' =>
                    'insufficient_benchmark_history',

                'message' =>
                    'The selected benchmark does not have enough price history.',
            ];
        }

        if (
            $timeWeightedResult[
                'skipped_subperiod_count'
            ] > 0
        ) {
            $count =
                $timeWeightedResult[
                    'skipped_subperiod_count'
                ];

            $warnings[] = [
                'code' =>
                    'skipped_return_subperiods',

                'message' =>
                    "{$count} return subperiod(s) were skipped because the beginning portfolio value was invalid.",
            ];
        }

        $missingHistoricalPriceCount =
            $valuations->sum(
                function (
                    PortfolioValuation $valuation
                ): int {
                    return (int) data_get(
                        $valuation->metadata,
                        'missing_historical_price_count',
                        0
                    );
                }
            );

        if ($missingHistoricalPriceCount > 0) {
            $warnings[] = [
                'code' =>
                    'missing_security_prices',

                'message' =>
                    "{$missingHistoricalPriceCount} holding valuation(s) used fallback values because historical security prices were unavailable.",
            ];
        }

        $unknownTransactionCount =
            $valuations->sum(
                function (
                    PortfolioValuation $valuation
                ): int {
                    return (int) data_get(
                        $valuation->metadata,
                        'unknown_transaction_count',
                        0
                    );
                }
            );

        if ($unknownTransactionCount > 0) {
            $warnings[] = [
                'code' =>
                    'unknown_transaction_types',

                'message' =>
                    "{$unknownTransactionCount} transaction(s) could not be classified for cash-flow analysis.",
            ];
        }

        return $warnings;
    }

    private function emptyBenchmarkSeriesResult(): array
    {
        return [
            'status' =>
                'insufficient_data',

            'series' => [],

            'return' => null,

            'data_points' => 0,

            'missing_price_count' => 0,

            'stale_price_count' => 0,

            'warnings' => [],
        ];
    }

    private function insufficientDataResult(
        string $message,
        array $warnings = [],
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'message' => $message,

            'period' => null,
            'portfolio' => null,
            'benchmark' => null,
            'comparison' => null,
            'chart' => [],
            'daily_returns' => [],

            'data_quality' => [
                'has_sufficient_portfolio_history' =>
                    false,

                'has_benchmark_data' =>
                    false,

                'warnings' =>
                    $warnings !== []
                        ? $warnings
                        : [
                            [
                                'code' =>
                                    'insufficient_portfolio_history',

                                'message' =>
                                    $message,
                            ],
                        ],
            ],

            'formula_version' =>
                'performance-0.3.0',
        ];
    }

    private function performanceScore(
        ?float $portfolioReturn,
        ?float $benchmarkReturn
    ): ?int {
        if (
            $portfolioReturn === null
            || $benchmarkReturn === null
        ) {
            return null;
        }

        $alpha = $portfolioReturn
            - $benchmarkReturn;

        return match (true) {
            $alpha >= 0.05 => 95,
            $alpha >= 0.02 => 90,
            $alpha >= 0.00 => 82,
            $alpha >= -0.02 => 72,
            $alpha >= -0.05 => 60,
            $alpha >= -0.10 => 45,
            default => 30,
        };
    }

    private function scoreLabel(
        int $score
    ): string {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Very good',
            $score >= 70 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Needs attention',
            default => 'Action recommended',
        };
    }

    private function roundRate(
        ?float $value
    ): ?float {
        return $value === null
            ? null
            : round($value, 10);
    }
}