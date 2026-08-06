<?php

namespace App\Services\Analytics\Performance;

use App\Data\Analytics\AnalyticsResult;
use App\Models\Benchmark;
use App\Models\PortfolioValuation;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PerformanceAnalyticsService
{
    public const FORMULA_VERSION = 'performance-0.3.0';

    public function __construct(
        private readonly ReturnCalculator $returnCalculator,
        private readonly TimeWeightedReturnService $timeWeightedReturnService,
        private readonly BenchmarkSeriesService $benchmarkSeriesService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
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

        if ($valuations->count() < 2) {
            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message: 'At least two portfolio valuations are required.',

                    metrics: $this->emptyMetrics(),

                    warnings: [
                        [
                            'code' => 'insufficient_portfolio_history',
                            'message' => 'At least two portfolio valuations are required.',
                        ],
                    ],

                    data: [
                        'period' => null,
                        'portfolio' => null,
                        'benchmark' => null,
                        'comparison' => null,
                        'chart' => [],
                        'daily_returns' => [],
                        'data_quality' => [
                            'has_sufficient_portfolio_history' => false,
                            'has_benchmark_data' => false,
                            'warnings' => [
                                [
                                    'code' => 'insufficient_portfolio_history',
                                    'message' => 'At least two portfolio valuations are required.',
                                ],
                            ],
                        ],
                    ],

                    formulaVersion: self::FORMULA_VERSION,
                )
            );
        }

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

        $period = [
            'start_date' =>
                $firstValuation
                    ->valuation_date
                    ->toDateString(),

            'end_date' =>
                $lastValuation
                    ->valuation_date
                    ->toDateString(),

            'days' => $actualDays,
        ];

        $portfolio = [
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
        ];

        $benchmarkData = [
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
        ];

        $comparison = [
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
        ];

        $chart = $this->buildComparisonChart(
            valuations: $valuations,
            benchmarkSeries:
                $benchmarkSeriesResult[
                    'series'
                ],
        );

        $warnings = array_merge(
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
        );

        $flags = $this->buildFlags(
            portfolioReturn: $portfolioReturn,
            annualizedPortfolioReturn:
                $annualizedPortfolioReturn,
            alpha: $alpha,
            opportunityCost: $opportunityCost,
        );

        $metrics = [
            'portfolio_return' =>
                $this->roundRate(
                    $portfolioReturn
                ),

            'annualized_portfolio_return' =>
                $this->roundRate(
                    $annualizedPortfolioReturn
                ),

            'benchmark_return' =>
                $this->roundRate(
                    $benchmarkReturn
                ),

            'annualized_benchmark_return' =>
                $this->roundRate(
                    $annualizedBenchmarkReturn
                ),

            'alpha' =>
                $this->roundRate($alpha),

            'opportunity_cost' =>
                $opportunityCost === null
                    ? null
                    : round(
                        $opportunityCost,
                        2
                    ),

            'beginning_value' =>
                $portfolio[
                    'beginning_value'
                ],

            'ending_value' =>
                $portfolio[
                    'ending_value'
                ],

            'net_cash_flow' =>
                $portfolio[
                    'net_cash_flow'
                ],

            'valuation_count' =>
                $portfolio[
                    'valuation_count'
                ],

            'subperiod_count' =>
                $portfolio[
                    'subperiod_count'
                ],

            'skipped_subperiod_count' =>
                $portfolio[
                    'skipped_subperiod_count'
                ],

            'benchmark_data_points' =>
                $benchmarkData[
                    'data_points'
                ],

            'missing_benchmark_price_count' =>
                $benchmarkData[
                    'missing_price_count'
                ],

            'stale_benchmark_price_count' =>
                $benchmarkData[
                    'stale_price_count'
                ],
        ];

        $score = $this->calculateScore(
            portfolioReturn:
                $portfolioReturn,

            annualizedPortfolioReturn:
                $annualizedPortfolioReturn,

            alpha:
                $alpha,

            benchmark:
                $benchmark,

            valuations:
                $valuations,

            benchmarkSeriesResult:
                $benchmarkSeriesResult,

            timeWeightedResult:
                $timeWeightedResult,
        );

        $dataQuality = [
            'has_sufficient_portfolio_history' =>
                true,

            'has_benchmark_data' =>
                $benchmarkSeriesResult[
                    'data_points'
                ] >= 2,

            'warnings' =>
                $warnings,
        ];

        $result = AnalyticsResult::complete(
            metrics: $metrics,
            flags: $flags,
            warnings: $warnings,

            data: [
                'period' => $period,
                'portfolio' => $portfolio,
                'benchmark' => $benchmarkData,
                'comparison' => $comparison,
                'chart' => $chart,
                'daily_returns' =>
                    $timeWeightedResult[
                        'subperiods'
                    ],
                'data_quality' => $dataQuality,
            ],

            score: $score,

            label:
                $score === null
                    ? null
                    : $this->scoreLabel($score),

            formulaVersion:
                self::FORMULA_VERSION,
        );

        return $this->legacyCompatibleResult(
            $result
        );
    }

    private function calculateScore(
        ?float $portfolioReturn,
        ?float $annualizedPortfolioReturn,
        ?float $alpha,
        ?Benchmark $benchmark,
        Collection $valuations,
        array $benchmarkSeriesResult,
        array $timeWeightedResult
    ): ?int {
        if ($portfolioReturn === null) {
            return null;
        }

        $score = 70;

        if ($alpha !== null) {
            if ($alpha >= 0.05) {
                $score += 20;
            } elseif ($alpha >= 0.02) {
                $score += 15;
            } elseif ($alpha >= 0) {
                $score += 10;
            } elseif ($alpha <= -0.05) {
                $score -= 25;
            } elseif ($alpha <= -0.02) {
                $score -= 15;
            } else {
                $score -= 8;
            }
        } elseif ($benchmark === null) {
            $score -= 20;
        }

        if (
            $annualizedPortfolioReturn !== null
            && $annualizedPortfolioReturn > 0
        ) {
            $score += min(
                10,
                (int) round(
                    $annualizedPortfolioReturn
                    * 100
                )
            );
        } elseif (
            $annualizedPortfolioReturn !== null
            && $annualizedPortfolioReturn < 0
        ) {
            $score -= 15;
        }

        if ($valuations->count() < 12) {
            $score -= 10;
        }

        if (
            (
                $benchmarkSeriesResult[
                    'missing_price_count'
                ] ?? 0
            ) > 0
        ) {
            $score -= 5;
        }

        if (
            (
                $benchmarkSeriesResult[
                    'stale_price_count'
                ] ?? 0
            ) > 0
        ) {
            $score -= 5;
        }

        if (
            (
                $timeWeightedResult[
                    'skipped_subperiod_count'
                ] ?? 0
            ) > 0
        ) {
            $score -= 5;
        }

        return max(
            0,
            min(100, $score)
        );
    }

    private function buildFlags(
        ?float $portfolioReturn,
        ?float $annualizedPortfolioReturn,
        ?float $alpha,
        ?float $opportunityCost
    ): array {
        $flags = [];

        if ($alpha !== null && $alpha <= -0.05) {
            $flags[] = [
                'code' =>
                    'significant_benchmark_underperformance',

                'severity' => 'high',

                'title' =>
                    'Significant benchmark underperformance',

                'message' =>
                    'The portfolio underperformed its benchmark by at least five percentage points.',
            ];
        } elseif ($alpha !== null && $alpha < 0) {
            $flags[] = [
                'code' =>
                    'benchmark_underperformance',

                'severity' => 'moderate',

                'title' =>
                    'Portfolio underperformed its benchmark',

                'message' =>
                    'Portfolio performance lagged the selected benchmark during the period.',
            ];
        }

        if (
            $annualizedPortfolioReturn !== null
            && $annualizedPortfolioReturn < 0
        ) {
            $flags[] = [
                'code' =>
                    'negative_annualized_return',

                'severity' => 'high',

                'title' =>
                    'Negative annualized return',

                'message' =>
                    'The portfolio produced a negative annualized return over the selected period.',
            ];
        }

        if (
            $opportunityCost !== null
            && $opportunityCost >= 1000
        ) {
            $flags[] = [
                'code' =>
                    'meaningful_performance_opportunity_cost',

                'severity' => 'high',

                'title' =>
                    'Meaningful performance opportunity cost',

                'message' =>
                    sprintf(
                        'Benchmark underperformance may represent approximately $%s in missed growth.',
                        number_format(
                            $opportunityCost,
                            2
                        )
                    ),
            ];
        }

        if (
            $alpha !== null
            && $alpha > 0
        ) {
            $flags[] = [
                'code' =>
                    'positive_alpha',

                'severity' =>
                    'informational',

                'title' =>
                    'Portfolio outperformed its benchmark',

                'message' =>
                    'The portfolio generated positive relative performance during the selected period.',
            ];
        }

        if (
            $flags === []
            && $portfolioReturn !== null
        ) {
            $flags[] = [
                'code' =>
                    'no_major_performance_flags',

                'severity' =>
                    'informational',

                'title' =>
                    'No major performance concerns detected',

                'message' =>
                    'Available performance results did not exceed Helmio’s current warning thresholds.',
            ];
        }

        return $flags;
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
            $benchmarkSeriesResult[
                'data_points'
            ] < 2
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

    private function emptyMetrics(): array
    {
        return [
            'portfolio_return' => null,
            'annualized_portfolio_return' => null,
            'benchmark_return' => null,
            'annualized_benchmark_return' => null,
            'alpha' => null,
            'opportunity_cost' => null,
            'beginning_value' => null,
            'ending_value' => null,
            'net_cash_flow' => null,
            'valuation_count' => 0,
            'subperiod_count' => 0,
            'skipped_subperiod_count' => 0,
            'benchmark_data_points' => 0,
            'missing_benchmark_price_count' => 0,
            'stale_benchmark_price_count' => 0,
        ];
    }

    /**
     * Preserve the existing controller and Blade response while exposing
     * the standardized AnalyticsResult contract.
     *
     * @return array<string, mixed>
     */
    private function legacyCompatibleResult(
        AnalyticsResult $result
    ): array {
        return array_merge(
            $result->toArray(),
            $result->data
        );
    }

    private function roundRate(
        ?float $value
    ): ?float {
        return $value === null
            ? null
            : round($value, 10);
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
}