<?php

namespace App\Services\Analytics\Risk;

use App\Models\Benchmark;
use App\Models\PortfolioValuation;
use App\Models\User;
use Carbon\CarbonInterface;

class RiskAnalyticsService
{
    public function __construct(
        private readonly DailyReturnBuilder $dailyReturnBuilder,
        private readonly BenchmarkReturnBuilder $benchmarkReturnBuilder,
        private readonly RiskMetricsService $riskMetricsService
    ) {
    }

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

        /*
         * Risk history begins only after the portfolio contains at
         * least one genuine non-cash invested position.
         *
         * Cash-only/pre-investment valuations remain stored for audit
         * history, but they must not create artificial daily returns.
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

        if ($valuations->count() < 2) {
            return $this->insufficientDataResult(
                'At least two invested portfolio valuations are required.'
            );
        }

        $portfolioSeries = $this->dailyReturnBuilder
            ->build($valuations);

        if (count($portfolioSeries) < 2) {
            return $this->insufficientDataResult(
                'At least two valid portfolio return periods are required.'
            );
        }

        $returnPeriodCount = count($portfolioSeries);

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

        $alignedSeries = $this->alignReturns(
            portfolioSeries: $portfolioSeries,
            benchmarkSeries: $benchmarkSeries,
        );

        $portfolioReturns = collect($alignedSeries)
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

        $pairedSeries = collect($alignedSeries)
            ->filter(
                fn (array $row): bool =>
                    $row['portfolio_return'] !== null
                    && $row['benchmark_return'] !== null
            )
            ->values();

        $benchmarkReturns = $pairedSeries
            ->pluck('benchmark_return')
            ->map(
                fn ($value): float =>
                    (float) $value
            )
            ->values()
            ->all();

        $pairedPortfolioReturns = $pairedSeries
            ->pluck('portfolio_return')
            ->map(
                fn ($value): float =>
                    (float) $value
            )
            ->values()
            ->all();

        /*
         * Beta must use paired observations only.
         * Other portfolio metrics can use every valid portfolio return.
         */
        $metricsResult = $this->riskMetricsService->analyze(
            portfolioReturns:
                $benchmarkReturns !== []
                    ? $pairedPortfolioReturns
                    : $portfolioReturns,

            benchmarkReturns:
                $benchmarkReturns,

            annualRiskFreeRate:
                $annualRiskFreeRate,

            minimumAcceptableAnnualReturn:
                $minimumAcceptableAnnualReturn,
        );

        if (($metricsResult['status'] ?? null) !== 'complete') {
            return [
                ...$metricsResult,

                'score' => null,
                'label' => 'Insufficient data',

                'history_confidence' =>
                    $this->historyConfidence(
                        $returnPeriodCount
                    ),

                'history_score_cap' =>
                    $this->historyScoreCap(
                        $returnPeriodCount
                    ),

                'period' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],

                'benchmark' => [
                    'id' => $benchmark?->id,
                    'name' => $benchmark?->name,
                    'symbol' => $benchmark?->symbol,
                ],

                'series' => $alignedSeries,

                'formula_version' => 'risk-0.2.2',
            ];
        }

        $metrics =
            $metricsResult['metrics']
            ?? [];

        $riskLevel =
            $metricsResult['risk_level']
            ?? null;

        $flags = $this->buildRiskFlags(
            metrics: $metrics,
            riskLevel: $riskLevel,
        );

        $warnings = array_values(
            array_merge(
                $metricsResult['warnings'] ?? [],
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

        $historyConfidence =
            $this->historyConfidence(
                $returnPeriodCount
            );

        $historyScoreCap =
            $this->historyScoreCap(
                $returnPeriodCount
            );

        $score = $this->riskScore(
            metrics: $metrics,
            riskLevel: $riskLevel,
            warnings: $warnings,
            returnPeriodCount: $returnPeriodCount,
        );

        return [
            'status' => 'complete',

            'score' => $score,

            /*
             * When history is still very limited, say so explicitly.
             * A numeric score can still be useful, but it should not be
             * presented with false precision or excessive confidence.
             */
            'label' =>
                $returnPeriodCount < 10
                    ? 'Limited history'
                    : (
                        $score !== null
                            ? $this->scoreLabel($score)
                            : 'Insufficient data'
                    ),

            'history_confidence' =>
                $historyConfidence,

            'history_score_cap' =>
                $historyScoreCap,

            'period' => [
                'start_date' => $valuations
                    ->first()
                    ->valuation_date
                    ->toDateString(),

                'end_date' => $valuations
                    ->last()
                    ->valuation_date
                    ->toDateString(),

                'valuation_count' => $valuations->count(),

                'return_period_count' =>
                    $returnPeriodCount,

                'aligned_return_count' =>
                    count($benchmarkReturns),
            ],

            'benchmark' => [
                'id' => $benchmark?->id,
                'name' => $benchmark?->name,
                'symbol' => $benchmark?->symbol,
            ],

            'metrics' => $metrics,

            'observations' =>
                $metricsResult['observations']
                ?? [],

            'assumptions' =>
                $metricsResult['assumptions']
                ?? [],

            'risk_level' =>
                $riskLevel,

            'flags' => $flags,

            'series' => $alignedSeries,

            'warnings' => $warnings,

            'formula_version' => 'risk-0.2.2',
        ];
    }

    private function riskScore(
        array $metrics,
        ?string $riskLevel,
        array $warnings,
        int $returnPeriodCount
    ): ?int {
        $volatility =
            $metrics['annualized_volatility']
            ?? null;

        $drawdown =
            $metrics['maximum_drawdown']
            ?? null;

        if (
            $volatility === null
            || $drawdown === null
        ) {
            return null;
        }

        /*
         * Start with the observed historical risk level.
         */
        $score = match ($riskLevel) {
            'very_low' => 95,
            'low' => 88,
            'moderate' => 72,
            'high' => 50,
            'very_high' => 25,
            default => 70,
        };

        $sharpe =
            $metrics['sharpe_ratio']
            ?? null;

        $sortino =
            $metrics['sortino_ratio']
            ?? null;

        $beta =
            $metrics['beta']
            ?? null;

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

        if (count($warnings) >= 3) {
            $score -= 8;
        } elseif (count($warnings) >= 1) {
            $score -= 3;
        }

        $score = max(
            0,
            min(
                100,
                $score
            )
        );

        /*
         * Historical risk statistics become more trustworthy as the
         * number of return observations grows.
         *
         * A very short history must never receive a near-perfect score,
         * even if the observed volatility/drawdown happened to be low.
         *
         * < 10 periods:  max 70
         * 10-29 periods: max 80
         * 30-59 periods: max 90
         * 60+ periods:   max 100
         */
        return min(
            $score,
            $this->historyScoreCap(
                $returnPeriodCount
            )
        );
    }

    private function historyScoreCap(
        int $returnPeriodCount
    ): int {
        return match (true) {
            $returnPeriodCount < 10 => 70,
            $returnPeriodCount < 30 => 80,
            $returnPeriodCount < 60 => 90,
            default => 100,
        };
    }

    private function historyConfidence(
        int $returnPeriodCount
    ): string {
        return match (true) {
            $returnPeriodCount < 10 => 'very_low',
            $returnPeriodCount < 30 => 'low',
            $returnPeriodCount < 60 => 'moderate',
            default => 'high',
        };
    }

    private function alignReturns(
        array $portfolioSeries,
        array $benchmarkSeries
    ): array {
        $benchmarkByDate = collect($benchmarkSeries)
            ->keyBy('date');

        return collect($portfolioSeries)
            ->map(function (
                array $portfolioPoint
            ) use ($benchmarkByDate): array {
                $benchmarkPoint = $benchmarkByDate->get(
                    $portfolioPoint['date']
                );

                return [
                    'date' =>
                        $portfolioPoint['date'],

                    'start_date' =>
                        $portfolioPoint['start_date'],

                    'portfolio_return' =>
                        $portfolioPoint['return'],

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
            })
            ->values()
            ->all();
    }

    private function buildRiskFlags(
        array $metrics,
        ?string $riskLevel
    ): array {
        $flags = [];

        $volatility =
            $metrics['annualized_volatility']
            ?? null;

        $drawdown =
            $metrics['maximum_drawdown']
            ?? null;

        $sharpe =
            $metrics['sharpe_ratio']
            ?? null;

        $sortino =
            $metrics['sortino_ratio']
            ?? null;

        $beta =
            $metrics['beta']
            ?? null;

        if (
            $volatility !== null
            && $volatility >= 0.25
        ) {
            $flags[] = [
                'code' => 'high_volatility',
                'severity' => 'high',
                'title' => 'High portfolio volatility',
                'message' => 'The portfolio has experienced substantial variation in daily returns.',
            ];
        }

        if (
            $drawdown !== null
            && $drawdown <= -0.25
        ) {
            $flags[] = [
                'code' => 'severe_drawdown',
                'severity' => 'high',
                'title' => 'Severe historical drawdown',
                'message' => 'The portfolio experienced a peak-to-trough decline of at least 25%.',
            ];
        }

        if (
            $sharpe !== null
            && $sharpe < 0.5
        ) {
            $flags[] = [
                'code' => 'weak_risk_adjusted_return',
                'severity' => 'moderate',
                'title' => 'Weak risk-adjusted performance',
                'message' => 'Portfolio returns have been low relative to the volatility taken.',
            ];
        }

        if (
            $sortino !== null
            && $sortino < 0.5
        ) {
            $flags[] = [
                'code' => 'weak_downside_adjusted_return',
                'severity' => 'moderate',
                'title' => 'Weak downside-adjusted performance',
                'message' => 'Portfolio returns have been low relative to harmful downside volatility.',
            ];
        }

        if (
            $beta !== null
            && $beta >= 1.5
        ) {
            $flags[] = [
                'code' => 'high_market_beta',
                'severity' => 'high',
                'title' => 'High sensitivity to market moves',
                'message' => 'The portfolio has historically moved substantially more than its benchmark.',
            ];
        }

        if (
            $beta !== null
            && $beta <= 0
        ) {
            $flags[] = [
                'code' => 'non_positive_beta',
                'severity' => 'informational',
                'title' => 'Unusual benchmark relationship',
                'message' => 'The portfolio has shown little or negative movement relative to its benchmark.',
            ];
        }

        if ($flags === []) {
            $flags[] = [
                'code' => 'no_major_risk_flags',
                'severity' => 'informational',
                'title' => 'No major risk flags detected',
                'message' => "The portfolio's available risk metrics do not currently exceed Helmio's warning thresholds.",
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

        if ($returnPeriodCount < 10) {
            $warnings[] = [
                'code' =>
                    'very_limited_portfolio_return_history',

                'message' =>
                    'Risk results are based on fewer than 10 portfolio return periods. The Risk score is capped at 70 until more history is available.',
            ];
        } elseif ($returnPeriodCount < 30) {
            $warnings[] = [
                'code' =>
                    'limited_portfolio_return_history',

                'message' =>
                    'Risk results are based on fewer than 30 portfolio return periods. The Risk score is capped at 80 until more history is available.',
            ];
        } elseif ($returnPeriodCount < 60) {
            $warnings[] = [
                'code' =>
                    'developing_portfolio_return_history',

                'message' =>
                    'Risk history is still developing. The Risk score is capped at 90 until at least 60 return periods are available.',
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

        $alignedCount = collect($alignedSeries)
            ->filter(
                fn (array $row): bool =>
                    $row['portfolio_return'] !== null
                    && $row['benchmark_return'] !== null
            )
            ->count();

        if ($alignedCount < count($portfolioSeries)) {
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

    private function insufficientDataResult(
        string $message
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                $message,

            'score' =>
                null,

            'label' =>
                'Insufficient data',

            'history_confidence' =>
                'very_low',

            'history_score_cap' =>
                70,

            'period' =>
                null,

            'benchmark' =>
                null,

            'metrics' =>
                [],

            'observations' =>
                [],

            'assumptions' =>
                [],

            'risk_level' =>
                null,

            'flags' =>
                [],

            'series' =>
                [],

            'warnings' => [
                [
                    'code' =>
                        'insufficient_risk_history',

                    'message' =>
                        $message,
                ],
            ],

            'formula_version' =>
                'risk-0.2.2',
        ];
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