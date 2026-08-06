<?php

namespace App\Services\Analytics\Cash;

use App\Data\Analytics\AnalyticsResult;
use App\Models\Benchmark;
use App\Models\PortfolioValuation;
use App\Models\User;
use App\Services\Analytics\Performance\BenchmarkSeriesService;
use Carbon\CarbonInterface;

class CashDragAnalyticsService
{
    public const FORMULA_VERSION = 'cash-drag-0.2.0';

    public function __construct(
        private readonly CashAllocationAnalyzer $cashAllocationAnalyzer,
        private readonly CashOpportunityCalculator $cashOpportunityCalculator,
        private readonly CashDragScoringService $cashDragScoringService,
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
        ?Benchmark $benchmark = null,
        float $targetCashPercent = 0.05
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

        $period = [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'valuation_count' => $valuations->count(),
        ];

        $benchmarkData = [
            'id' => $benchmark?->id,
            'name' => $benchmark?->name,
            'symbol' => $benchmark?->symbol,
            'return' => null,
        ];

        if ($valuations->isEmpty()) {
            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message: 'No portfolio valuations were found.',

                    metrics: [
                        'current_cash' => null,
                        'current_cash_percent' => null,
                        'average_cash' => null,
                        'average_cash_percent' => null,
                        'minimum_cash_percent' => null,
                        'maximum_cash_percent' => null,
                        'estimated_opportunity_cost' => null,
                        'excess_cash' => null,
                    ],

                    warnings: [
                        [
                            'code' => 'insufficient_cash_history',
                            'message' => 'No portfolio valuation history was available for cash-drag analysis.',
                        ],
                    ],

                    data: [
                        'period' => $period,
                        'benchmark' => $benchmarkData,
                        'allocation' => null,
                        'opportunity' => null,
                        'rating' => null,
                    ],

                    formulaVersion: self::FORMULA_VERSION,
                )
            );
        }

        $allocationResult =
            $this->cashAllocationAnalyzer
                ->analyze($valuations);

        $benchmarkSeriesResult = $benchmark
            ? $this->benchmarkSeriesService->build(
                benchmark: $benchmark,
                portfolioValuations: $valuations,
            )
            : $this->emptyBenchmarkResult();

        $benchmarkData['return'] =
            $benchmarkSeriesResult['return'] ?? null;

        $averagePortfolioValue = (float) $valuations->avg(
            fn (
                PortfolioValuation $valuation
            ): float => $valuation->total_value
        );

        $opportunityResult =
            $this->cashOpportunityCalculator
                ->calculate(
                    averageCash: (float) data_get(
                        $allocationResult,
                        'metrics.average_cash',
                        0
                    ),

                    averagePortfolioValue:
                        $averagePortfolioValue,

                    benchmarkReturn:
                        $benchmarkSeriesResult['return']
                        ?? null,

                    targetCashPercent:
                        $targetCashPercent,
                );

        $estimatedOpportunityCost = data_get(
            $opportunityResult,
            'metrics.estimated_opportunity_cost'
        );

        $opportunityCostRate =
            $averagePortfolioValue > 0
            && $estimatedOpportunityCost !== null
                ? (
                    (float) $estimatedOpportunityCost
                    / $averagePortfolioValue
                )
                : null;

        $scoreResult =
            $this->cashDragScoringService
                ->score(
                    averageCashPercent: data_get(
                        $allocationResult,
                        'metrics.average_cash_percent'
                    ),

                    opportunityCostRate:
                        $opportunityCostRate,
                );

        $flags = $this->buildFlags(
            allocationMetrics:
                $allocationResult['metrics'] ?? [],

            opportunityMetrics:
                $opportunityResult['metrics'] ?? [],

            score:
                $scoreResult['score'] ?? null,
        );

        $warnings = array_values(
            array_merge(
                $benchmarkSeriesResult['warnings']
                ?? [],

                $this->buildWarnings(
                    benchmark: $benchmark,
                    opportunityResult:
                        $opportunityResult,
                )
            )
        );

        $metrics = [
            ...($allocationResult['metrics'] ?? []),

            'average_portfolio_value' =>
                round($averagePortfolioValue, 2),

            'target_cash_percent' =>
                data_get(
                    $opportunityResult,
                    'metrics.target_cash_percent'
                ),

            'target_cash_amount' =>
                data_get(
                    $opportunityResult,
                    'metrics.target_cash_amount'
                ),

            'excess_cash' =>
                data_get(
                    $opportunityResult,
                    'metrics.excess_cash'
                ),

            'benchmark_return' =>
                data_get(
                    $opportunityResult,
                    'metrics.benchmark_return'
                ),

            'estimated_opportunity_cost' =>
                $estimatedOpportunityCost,

            'opportunity_cost_rate' =>
                $opportunityCostRate === null
                    ? null
                    : round(
                        $opportunityCostRate,
                        10
                    ),
        ];

        $result = AnalyticsResult::complete(
            metrics: $metrics,
            flags: $flags,
            warnings: $warnings,

            data: [
                'period' => $period,
                'benchmark' => $benchmarkData,
                'allocation' => $allocationResult,
                'opportunity' => $opportunityResult,

                /*
                 * Keep the legacy score structure available to
                 * the existing Blade page.
                 */
                'rating' =>
                    $scoreResult['rating']
                    ?? null,
            ],

            score:
                $scoreResult['score'] ?? null,

            label:
                $scoreResult['rating'] !== null
                    ? $this->ratingLabel(
                        $scoreResult['rating']
                    )
                    : null,

            formulaVersion:
                self::FORMULA_VERSION,
        );

        return $this->legacyCompatibleResult(
            $result
        );
    }

    /**
     * @param array<string, mixed> $allocationMetrics
     * @param array<string, mixed> $opportunityMetrics
     * @return array<int, array<string, mixed>>
     */
    private function buildFlags(
        array $allocationMetrics,
        array $opportunityMetrics,
        ?int $score
    ): array {
        $flags = [];

        $averageCashPercent =
            $allocationMetrics[
                'average_cash_percent'
            ] ?? null;

        $currentCashPercent =
            $allocationMetrics[
                'current_cash_percent'
            ] ?? null;

        $opportunityCost =
            $opportunityMetrics[
                'estimated_opportunity_cost'
            ] ?? null;

        if (
            $averageCashPercent !== null
            && $averageCashPercent >= 0.20
        ) {
            $flags[] = [
                'code' => 'high_average_cash',
                'severity' => 'high',
                'title' => 'High average cash allocation',
                'message' => 'The portfolio held at least 20% in cash on average during the selected period.',
            ];
        } elseif (
            $averageCashPercent !== null
            && $averageCashPercent >= 0.10
        ) {
            $flags[] = [
                'code' => 'elevated_average_cash',
                'severity' => 'moderate',
                'title' => 'Elevated average cash allocation',
                'message' => 'The portfolio held at least 10% in cash on average during the selected period.',
            ];
        }

        if (
            $currentCashPercent !== null
            && $currentCashPercent >= 0.25
        ) {
            $flags[] = [
                'code' => 'high_current_cash',
                'severity' => 'high',
                'title' => 'Current cash allocation is high',
                'message' => 'At least one quarter of the current portfolio value is held in cash.',
            ];
        }

        if (
            $opportunityCost !== null
            && $opportunityCost >= 1000
        ) {
            $flags[] = [
                'code' => 'meaningful_cash_opportunity_cost',
                'severity' => 'high',
                'title' => 'Meaningful estimated cash drag',
                'message' => sprintf(
                    'Excess cash may have missed approximately $%s in benchmark growth.',
                    number_format(
                        (float) $opportunityCost,
                        2
                    )
                ),
            ];
        }

        if (
            $score !== null
            && $score <= 40
        ) {
            $flags[] = [
                'code' => 'poor_cash_drag_score',
                'severity' => 'high',
                'title' => 'Cash management needs attention',
                'message' => 'The combination of cash allocation and estimated opportunity cost produced a low cash-drag score.',
            ];
        }

        if ($flags === []) {
            $flags[] = [
                'code' => 'no_major_cash_drag',
                'severity' => 'informational',
                'title' => 'No major cash drag detected',
                'message' => 'Cash levels did not exceed Helmio’s current warning thresholds.',
            ];
        }

        return $flags;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWarnings(
        ?Benchmark $benchmark,
        array $opportunityResult
    ): array {
        $warnings = [];

        if ($benchmark === null) {
            $warnings[] = [
                'code' => 'benchmark_not_selected',
                'message' => 'Select a benchmark to estimate cash opportunity cost.',
            ];
        }

        if (
            ($opportunityResult['status'] ?? null)
            !== 'complete'
        ) {
            $warnings[] = [
                'code' => 'opportunity_cost_unavailable',
                'message' =>
                    $opportunityResult['message']
                    ?? 'Cash opportunity cost could not be calculated.',
            ];
        }

        return $warnings;
    }

    /**
     * Keep the existing cash-drag Blade page working while adding
     * the standardized AnalyticsResult structure.
     *
     * @return array<string, mixed>
     */
    private function legacyCompatibleResult(
        AnalyticsResult $result
    ): array {
        $shared = $result->toArray();

        $legacyScore = [
            'score' => $result->score,
            'rating' => data_get(
                $result->data,
                'rating'
            ),
        ];

        return array_merge(
            $shared,
            $result->data,
            [
                'score' => $legacyScore,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyBenchmarkResult(): array
    {
        return [
            'status' => 'insufficient_data',
            'series' => [],
            'return' => null,
            'data_points' => 0,
            'missing_price_count' => 0,
            'stale_price_count' => 0,
            'warnings' => [],
        ];
    }

    private function ratingLabel(
        string $rating
    ): string {
        return match ($rating) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            'moderate' => 'Moderate',
            'poor' => 'Poor',
            'critical' => 'Critical',
            default => 'Unknown',
        };
    }
}