<?php

namespace App\Services\Analytics;

use App\Data\Analytics\AnalyticsResult;
use App\Models\Benchmark;
use App\Models\PortfolioValuation;
use App\Models\User;
use App\Services\Analytics\Risk\BenchmarkReturnBuilder;
use App\Services\Analytics\Risk\DailyReturnBuilder;
use App\Services\Analytics\Risk\RiskMetricsService;
use Carbon\CarbonInterface;

class RiskAnalyticsService
{
    public const FORMULA_VERSION = 'risk-0.4.0';

    private const MINIMUM_SCORABLE_RETURN_PERIODS = 20;
    private const LIMITED_HISTORY_RETURN_PERIODS = 60;

    public function __construct(
        private readonly DailyReturnBuilder $dailyReturnBuilder,
        private readonly BenchmarkReturnBuilder $benchmarkReturnBuilder,
        private readonly RiskMetricsService $riskMetricsService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function analyze(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?Benchmark $benchmark = null,
        float $annualRiskFreeRate = 0.0,
        float $minimumAcceptableAnnualReturn = 0.0
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
                    message:
                        'At least two portfolio valuations are required.',

                    metrics:
                        $this->emptyMetrics(),

                    warnings: [
                        [
                            'code' =>
                                'insufficient_risk_history',

                            'message' =>
                                'At least two portfolio valuations are required.',
                        ],
                    ],

                    data: [
                        'period' => null,
                        'benchmark' => null,
                        'observations' => null,
                        'assumptions' => null,
                        'risk_level' => null,
                        'series' => [],
                        'confidence' =>
                            $this->confidenceData(
                                returnPeriodCount: 0,
                                alignedReturnCount: 0,
                            ),
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        $portfolioSeries =
            $this->dailyReturnBuilder
                ->build($valuations);

        if (count($portfolioSeries) < 2) {
            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message:
                        'At least two valid portfolio return periods are required.',

                    metrics:
                        $this->emptyMetrics(),

                    warnings: [
                        [
                            'code' =>
                                'insufficient_return_periods',

                            'message' =>
                                'At least two valid portfolio return periods are required.',
                        ],
                    ],

                    data: [
                        'period' => [
                            'start_date' =>
                                $startDate->toDateString(),

                            'end_date' =>
                                $endDate->toDateString(),

                            'valuation_count' =>
                                $valuations->count(),

                            'return_period_count' =>
                                count($portfolioSeries),

                            'aligned_return_count' =>
                                0,
                        ],

                        'benchmark' => [
                            'id' =>
                                $benchmark?->id,

                            'name' =>
                                $benchmark?->name,

                            'symbol' =>
                                $benchmark?->symbol,
                        ],

                        'observations' => null,
                        'assumptions' => null,
                        'risk_level' => null,
                        'series' => [],

                        'confidence' =>
                            $this->confidenceData(
                                returnPeriodCount:
                                    count($portfolioSeries),

                                alignedReturnCount:
                                    0,
                            ),
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        $portfolioDates = collect($portfolioSeries)
            ->pluck('date')
            ->values()
            ->all();

        $benchmarkSeries = $benchmark
            ? $this->benchmarkReturnBuilder->build(
                benchmark: $benchmark,
                dates: $portfolioDates,
                startDate: $startDate,
                endDate: $endDate,
            )
            : [];

        $alignedSeries =
            $this->alignReturns(
                portfolioSeries:
                    $portfolioSeries,

                benchmarkSeries:
                    $benchmarkSeries,
            );

        $allPortfolioReturns =
            collect($alignedSeries)
                ->pluck('portfolio_return')
                ->filter(
                    fn ($value): bool =>
                        $value !== null
                )
                ->map(
                    fn ($value): float =>
                        (float) $value
                )
                ->values()
                ->all();

        $pairedSeries =
            collect($alignedSeries)
                ->filter(
                    fn (array $row): bool =>
                        $row['portfolio_return'] !== null
                        && $row['benchmark_return'] !== null
                )
                ->values();

        $pairedPortfolioReturns =
            $pairedSeries
                ->pluck('portfolio_return')
                ->map(
                    fn ($value): float =>
                        (float) $value
                )
                ->all();

        $benchmarkReturns =
            $pairedSeries
                ->pluck('benchmark_return')
                ->map(
                    fn ($value): float =>
                        (float) $value
                )
                ->all();

        $metricsResult =
            $this->riskMetricsService
                ->analyze(
                    portfolioReturns:
                        $benchmarkReturns !== []
                            ? $pairedPortfolioReturns
                            : $allPortfolioReturns,

                    benchmarkReturns:
                        $benchmarkReturns,

                    annualRiskFreeRate:
                        $annualRiskFreeRate,

                    minimumAcceptableAnnualReturn:
                        $minimumAcceptableAnnualReturn,
                );

        $returnPeriodCount =
            count($portfolioSeries);

        $alignedReturnCount =
            count($benchmarkReturns);

        if (
            ($metricsResult['status'] ?? null)
            !== 'complete'
        ) {
            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message:
                        $metricsResult['message']
                        ?? 'Risk metrics could not be calculated.',

                    metrics:
                        $metricsResult['metrics']
                        ?? $this->emptyMetrics(),

                    warnings:
                        $metricsResult['warnings']
                        ?? [],

                    data: [
                        'period' => [
                            'start_date' =>
                                $startDate->toDateString(),

                            'end_date' =>
                                $endDate->toDateString(),

                            'valuation_count' =>
                                $valuations->count(),

                            'return_period_count' =>
                                $returnPeriodCount,

                            'aligned_return_count' =>
                                $alignedReturnCount,
                        ],

                        'benchmark' => [
                            'id' =>
                                $benchmark?->id,

                            'name' =>
                                $benchmark?->name,

                            'symbol' =>
                                $benchmark?->symbol,
                        ],

                        'observations' =>
                            $metricsResult[
                                'observations'
                            ] ?? null,

                        'assumptions' =>
                            $metricsResult[
                                'assumptions'
                            ] ?? null,

                        'risk_level' =>
                            null,

                        'provisional_risk_level' =>
                            $metricsResult[
                                'risk_level'
                            ] ?? null,

                        'series' =>
                            $alignedSeries,

                        'confidence' =>
                            $this->confidenceData(
                                returnPeriodCount:
                                    $returnPeriodCount,

                                alignedReturnCount:
                                    $alignedReturnCount,
                            ),
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        $metrics =
            $metricsResult['metrics'] ?? [];

        $calculatedRiskLevel =
            $metricsResult['risk_level'] ?? null;

        $warnings = array_values(
            array_merge(
                $metricsResult['warnings']
                    ?? [],

                $this->buildDataWarnings(
                    portfolioSeries:
                        $portfolioSeries,

                    benchmarkSeries:
                        $benchmarkSeries,

                    alignedSeries:
                        $alignedSeries,

                    benchmark:
                        $benchmark,
                )
            )
        );

        $period = [
            'start_date' =>
                $valuations
                    ->first()
                    ->valuation_date
                    ->toDateString(),

            'end_date' =>
                $valuations
                    ->last()
                    ->valuation_date
                    ->toDateString(),

            'valuation_count' =>
                $valuations->count(),

            'return_period_count' =>
                $returnPeriodCount,

            'aligned_return_count' =>
                $alignedReturnCount,
        ];

        $benchmarkData = [
            'id' =>
                $benchmark?->id,

            'name' =>
                $benchmark?->name,

            'symbol' =>
                $benchmark?->symbol,
        ];

        /*
         * Risk metrics can be useful diagnostically with a very short
         * history, but they must not be promoted into an established
         * consumer-facing score or suitability risk classification until
         * enough return periods exist.
         */
        if (
            $returnPeriodCount
            < self::MINIMUM_SCORABLE_RETURN_PERIODS
        ) {
            $warnings =
                $this->ensureWarning(
                    warnings:
                        $warnings,

                    code:
                        'insufficient_risk_history',

                    message:
                        sprintf(
                            'At least %d valid portfolio return periods are required before Helmio assigns a risk score.',
                            self::MINIMUM_SCORABLE_RETURN_PERIODS
                        ),
                );

            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message:
                        sprintf(
                            'Helmio is still building risk history. At least %d valid portfolio return periods are required before a risk score is assigned.',
                            self::MINIMUM_SCORABLE_RETURN_PERIODS
                        ),

                    metrics:
                        $metrics,

                    warnings:
                        $warnings,

                    data: [
                        'period' =>
                            $period,

                        'benchmark' =>
                            $benchmarkData,

                        'observations' =>
                            $metricsResult[
                                'observations'
                            ] ?? [],

                        'assumptions' =>
                            $metricsResult[
                                'assumptions'
                            ] ?? [],

                        /*
                         * Keep the calculated level available for internal
                         * diagnostics/UI display, but do not expose it as the
                         * canonical risk_level consumed by Suitability.
                         */
                        'risk_level' =>
                            null,

                        'provisional_risk_level' =>
                            $calculatedRiskLevel,

                        'series' =>
                            $alignedSeries,

                        'confidence' =>
                            $this->confidenceData(
                                returnPeriodCount:
                                    $returnPeriodCount,

                                alignedReturnCount:
                                    $alignedReturnCount,
                            ),
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        /*
         * Once the minimum history threshold is satisfied, the calculated
         * risk level becomes eligible for scoring and downstream use.
         */
        $riskLevel =
            $calculatedRiskLevel;

        $flags =
            $this->buildRiskFlags(
                metrics:
                    $metrics,

                riskLevel:
                    $riskLevel,
            );

        $score =
            $this->calculateScore(
                metrics:
                    $metrics,

                riskLevel:
                    $riskLevel,

                warnings:
                    $warnings,
            );

        $result =
            AnalyticsResult::complete(
                metrics:
                    $metrics,

                flags:
                    $flags,

                warnings:
                    $warnings,

                data: [
                    'period' =>
                        $period,

                    'benchmark' =>
                        $benchmarkData,

                    'observations' =>
                        $metricsResult[
                            'observations'
                        ] ?? [],

                    'assumptions' =>
                        $metricsResult[
                            'assumptions'
                        ] ?? [],

                    'risk_level' =>
                        $riskLevel,

                    'provisional_risk_level' =>
                        null,

                    'series' =>
                        $alignedSeries,

                    'confidence' =>
                        $this->confidenceData(
                            returnPeriodCount:
                                $returnPeriodCount,

                            alignedReturnCount:
                                $alignedReturnCount,
                        ),
                ],

                score:
                    $score,

                label:
                    $score === null
                        ? null
                        : $this->scoreLabel(
                            $score
                        ),

                formulaVersion:
                    self::FORMULA_VERSION,
            );

        return $this->legacyCompatibleResult(
            $result
        );
    }

    /**
     * Produce a consumer-facing risk score.
     *
     * Higher scores represent more favorable risk characteristics.
     *
     * @param array<string, mixed> $metrics
     * @param array<int, array<string, mixed>> $warnings
     */
    private function calculateScore(
        array $metrics,
        ?string $riskLevel,
        array $warnings
    ): ?int {
        $volatility =
            $metrics[
                'annualized_volatility'
            ] ?? null;

        $drawdown =
            $metrics[
                'maximum_drawdown'
            ] ?? null;

        if (
            $volatility === null
            || $drawdown === null
            || $riskLevel === null
        ) {
            return null;
        }

        $score = match ($riskLevel) {
            'very_low' => 95,
            'low' => 88,
            'moderate' => 72,
            'high' => 50,
            'very_high' => 25,
            default => 70,
        };

        $sharpe =
            $metrics[
                'sharpe_ratio'
            ] ?? null;

        $sortino =
            $metrics[
                'sortino_ratio'
            ] ?? null;

        $beta =
            $metrics[
                'beta'
            ] ?? null;

        if ($sharpe !== null) {
            if ($sharpe >= 1.5) {
                $score += 10;
            } elseif ($sharpe >= 1.0) {
                $score += 6;
            } elseif ($sharpe < 0) {
                $score -= 15;
            } elseif ($sharpe < 0.5) {
                $score -= 8;
            }
        }

        if ($sortino !== null) {
            if ($sortino >= 1.5) {
                $score += 6;
            } elseif ($sortino < 0) {
                $score -= 10;
            } elseif ($sortino < 0.5) {
                $score -= 5;
            }
        }

        if ($beta !== null) {
            if ($beta >= 1.5) {
                $score -= 12;
            } elseif ($beta >= 1.2) {
                $score -= 6;
            } elseif ($beta < 0) {
                $score -= 5;
            }
        }

        if ($drawdown <= -0.35) {
            $score -= 15;
        } elseif ($drawdown <= -0.25) {
            $score -= 10;
        } elseif ($drawdown <= -0.15) {
            $score -= 5;
        }

        /*
         * Short-but-scorable histories remain lower confidence.
         * Apply a modest penalty until at least 60 valid return periods
         * are available.
         */
        if (
            $this->hasWarning(
                warnings:
                    $warnings,

                code:
                    'limited_risk_history',
            )
        ) {
            $score -= 5;
        }

        if (count($warnings) >= 3) {
            $score -= 8;
        } elseif (count($warnings) >= 1) {
            $score -= 3;
        }

        return max(
            0,
            min(100, $score)
        );
    }

    private function alignReturns(
        array $portfolioSeries,
        array $benchmarkSeries
    ): array {
        $benchmarkByDate =
            collect($benchmarkSeries)
                ->keyBy('date');

        return collect($portfolioSeries)
            ->map(
                function (
                    array $portfolioPoint
                ) use (
                    $benchmarkByDate
                ): array {
                    $benchmarkPoint =
                        $benchmarkByDate->get(
                            $portfolioPoint[
                                'date'
                            ]
                        );

                    return [
                        'date' =>
                            $portfolioPoint[
                                'date'
                            ],

                        'start_date' =>
                            $portfolioPoint[
                                'start_date'
                            ],

                        'portfolio_return' =>
                            $portfolioPoint[
                                'return'
                            ],

                        'benchmark_return' =>
                            data_get(
                                $benchmarkPoint,
                                'return'
                            ),

                        'portfolio_beginning_value' =>
                            $portfolioPoint[
                                'beginning_value'
                            ],

                        'portfolio_ending_value' =>
                            $portfolioPoint[
                                'ending_value'
                            ],

                        'net_cash_flow' =>
                            $portfolioPoint[
                                'net_cash_flow'
                            ],
                    ];
                }
            )
            ->values()
            ->all();
    }

    private function buildRiskFlags(
        array $metrics,
        ?string $riskLevel
    ): array {
        $flags = [];

        $volatility =
            $metrics[
                'annualized_volatility'
            ] ?? null;

        $drawdown =
            $metrics[
                'maximum_drawdown'
            ] ?? null;

        $sharpe =
            $metrics[
                'sharpe_ratio'
            ] ?? null;

        $sortino =
            $metrics[
                'sortino_ratio'
            ] ?? null;

        $beta =
            $metrics[
                'beta'
            ] ?? null;

        if (
            $volatility !== null
            && $volatility >= 0.25
        ) {
            $flags[] = [
                'code' =>
                    'high_volatility',

                'severity' =>
                    'high',

                'title' =>
                    'High portfolio volatility',

                'message' =>
                    'The portfolio has experienced substantial variation in daily returns.',
            ];
        }

        if (
            $drawdown !== null
            && $drawdown <= -0.25
        ) {
            $flags[] = [
                'code' =>
                    'severe_drawdown',

                'severity' =>
                    'high',

                'title' =>
                    'Severe historical drawdown',

                'message' =>
                    'The portfolio experienced a peak-to-trough decline of at least 25%.',
            ];
        }

        if (
            $sharpe !== null
            && $sharpe < 0.5
        ) {
            $flags[] = [
                'code' =>
                    'weak_risk_adjusted_return',

                'severity' =>
                    'moderate',

                'title' =>
                    'Weak risk-adjusted performance',

                'message' =>
                    'Portfolio returns have been low relative to the volatility taken.',
            ];
        }

        if (
            $sortino !== null
            && $sortino < 0.5
        ) {
            $flags[] = [
                'code' =>
                    'weak_downside_adjusted_return',

                'severity' =>
                    'moderate',

                'title' =>
                    'Weak downside-adjusted performance',

                'message' =>
                    'Portfolio returns have been low relative to harmful downside volatility.',
            ];
        }

        if (
            $beta !== null
            && $beta >= 1.5
        ) {
            $flags[] = [
                'code' =>
                    'high_market_beta',

                'severity' =>
                    'high',

                'title' =>
                    'High sensitivity to market moves',

                'message' =>
                    'The portfolio has historically moved substantially more than its benchmark.',
            ];
        }

        if (
            $beta !== null
            && $beta <= 0
        ) {
            $flags[] = [
                'code' =>
                    'non_positive_beta',

                'severity' =>
                    'informational',

                'title' =>
                    'Unusual benchmark relationship',

                'message' =>
                    'The portfolio has shown little or negative movement relative to its benchmark.',
            ];
        }

        if (
            $flags === []
            && $riskLevel !== null
        ) {
            $flags[] = [
                'code' =>
                    'no_major_risk_flags',

                'severity' =>
                    'informational',

                'title' =>
                    'No major risk flags detected',

                'message' =>
                    "The portfolio's available risk metrics do not currently exceed Helmio's warning thresholds.",
            ];
        }

        return $flags;
    }

    private function buildDataWarnings(
        array $portfolioSeries,
        array $benchmarkSeries,
        array $alignedSeries,
        ?Benchmark $benchmark
    ): array {
        $warnings = [];

        $returnPeriodCount =
            count($portfolioSeries);

        if (
            $returnPeriodCount
            < self::MINIMUM_SCORABLE_RETURN_PERIODS
        ) {
            $warnings[] = [
                'code' =>
                    'insufficient_risk_history',

                'message' =>
                    sprintf(
                        'Risk metrics are provisional because fewer than %d valid portfolio return periods are available.',
                        self::MINIMUM_SCORABLE_RETURN_PERIODS
                    ),
            ];
        } elseif (
            $returnPeriodCount
            < self::LIMITED_HISTORY_RETURN_PERIODS
        ) {
            $warnings[] = [
                'code' =>
                    'limited_risk_history',

                'message' =>
                    sprintf(
                        'Risk results are based on fewer than %d valid portfolio return periods and should be interpreted with additional caution.',
                        self::LIMITED_HISTORY_RETURN_PERIODS
                    ),
            ];
        }

        if ($benchmark === null) {
            $warnings[] = [
                'code' =>
                    'benchmark_not_selected',

                'message' =>
                    'Select a benchmark to calculate beta.',
            ];

            return $warnings;
        }

        if ($benchmarkSeries === []) {
            $warnings[] = [
                'code' =>
                    'benchmark_return_history_missing',

                'message' =>
                    'No usable benchmark return history was available.',
            ];

            return $warnings;
        }

        $alignedCount =
            collect($alignedSeries)
                ->filter(
                    fn (array $row): bool =>
                        $row[
                            'portfolio_return'
                        ] !== null
                        && $row[
                            'benchmark_return'
                        ] !== null
                )
                ->count();

        if (
            $alignedCount
            < count($portfolioSeries)
        ) {
            $missingCount =
                count($portfolioSeries)
                - $alignedCount;

            $warnings[] = [
                'code' =>
                    'unaligned_benchmark_dates',

                'message' =>
                    "{$missingCount} portfolio return period(s) did not have a matching benchmark return.",
            ];
        }

        return $warnings;
    }

    /**
     * @return array<string, mixed>
     */
    private function confidenceData(
        int $returnPeriodCount,
        int $alignedReturnCount
    ): array {
        $level = match (true) {
            $returnPeriodCount
                < self::MINIMUM_SCORABLE_RETURN_PERIODS =>
                    'insufficient',

            $returnPeriodCount
                < self::LIMITED_HISTORY_RETURN_PERIODS =>
                    'limited',

            default =>
                'established',
        };

        return [
            'level' =>
                $level,

            'return_period_count' =>
                $returnPeriodCount,

            'aligned_return_count' =>
                $alignedReturnCount,

            'minimum_scorable_return_periods' =>
                self::MINIMUM_SCORABLE_RETURN_PERIODS,

            'established_history_return_periods' =>
                self::LIMITED_HISTORY_RETURN_PERIODS,

            'is_scorable' =>
                $returnPeriodCount
                >= self::MINIMUM_SCORABLE_RETURN_PERIODS,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $warnings
     * @return array<int, array<string, mixed>>
     */
    private function ensureWarning(
        array $warnings,
        string $code,
        string $message
    ): array {
        if (
            $this->hasWarning(
                warnings:
                    $warnings,

                code:
                    $code,
            )
        ) {
            return $warnings;
        }

        $warnings[] = [
            'code' =>
                $code,

            'message' =>
                $message,
        ];

        return array_values($warnings);
    }

    /**
     * @param array<int, array<string, mixed>> $warnings
     */
    private function hasWarning(
        array $warnings,
        string $code
    ): bool {
        return collect($warnings)
            ->contains(
                fn (array $warning): bool =>
                    ($warning['code'] ?? null)
                    === $code
            );
    }

    private function emptyMetrics(): array
    {
        return [
            'annualized_return' =>
                null,

            'annualized_volatility' =>
                null,

            'maximum_drawdown' =>
                null,

            'downside_deviation' =>
                null,

            'sharpe_ratio' =>
                null,

            'sortino_ratio' =>
                null,

            'beta' =>
                null,

            'average_daily_return' =>
                null,

            'daily_standard_deviation' =>
                null,
        ];
    }

    /**
     * Preserve the existing risk dashboard response while exposing
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

    private function scoreLabel(
        int $score
    ): string {
        return match (true) {
            $score >= 90 =>
                'Excellent',

            $score >= 80 =>
                'Very good',

            $score >= 70 =>
                'Good',

            $score >= 60 =>
                'Fair',

            $score >= 40 =>
                'Needs attention',

            default =>
                'Action recommended',
        };
    }
}