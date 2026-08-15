<?php

namespace App\Services\Analytics\Value;

use App\Models\Benchmark;
use App\Models\InvestmentAccount;
use App\Models\User;
use App\Services\Analytics\CostAnalyticsService;
use App\Services\Analytics\Performance\PerformanceAnalyticsService;
use Carbon\CarbonInterface;

class CostAdjustedPerformanceService
{
    public const FORMULA_VERSION =
        'cost-adjusted-performance-0.2.0';

    public function __construct(
        private readonly CostAnalyticsService $costAnalytics,
        private readonly PerformanceAnalyticsService $performanceAnalytics,
    ) {
    }

    /**
     * Compare what the investor pays with what the portfolio delivers
     * relative to an investable benchmark.
     *
     * Important:
     * Portfolio and ETF/fund market returns already reflect embedded
     * fund operating expenses in their NAV/price history. Therefore,
     * fund expense ratios are DISCLOSED here as costs but are not
     * subtracted a second time from observed investment returns.
     *
     * @return array<string, mixed>
     */
    public function analyze(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?Benchmark $benchmark = null,
    ): array {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with([
                'institution',
                'holdings.security',
                'transactions',
            ])
            ->orderBy('name')
            ->get();

        if ($accounts->isEmpty()) {
            return $this->insufficientDataResult(
                'No investment accounts were found.'
            );
        }

        $cost = $this->costAnalytics->calculate(
            $accounts
        );

        $performance =
            $this->performanceAnalytics->analyze(
                user: $user,
                startDate: $startDate,
                endDate: $endDate,
                benchmark: $benchmark,
            );

        if (
            ($performance['status'] ?? null)
            !== 'complete'
        ) {
            return $this->insufficientDataResult(
                $performance['message']
                    ?? 'Performance data is insufficient.',
                cost: $cost,
                performance: $performance,
            );
        }

        $portfolioValue = (float) (
            $cost['portfolio_value']
            ?? $performance['portfolio']['ending_value']
            ?? 0
        );

        $portfolioAnnualCost = (float) (
            $cost['total_annual_cost']
            ?? 0
        );

        $portfolioCostRate = $this->nullableFloat(
            $cost['all_in_cost_rate']
            ?? null
        );

        $portfolioReturn = $this->nullableFloat(
            data_get(
                $performance,
                'portfolio.return'
            )
        );

        $portfolioAnnualizedReturn =
            $this->nullableFloat(
                data_get(
                    $performance,
                    'portfolio.annualized_return'
                )
            );

        $benchmarkReturn = $this->nullableFloat(
            data_get(
                $performance,
                'benchmark.return'
            )
        );

        $benchmarkAnnualizedReturn =
            $this->nullableFloat(
                data_get(
                    $performance,
                    'benchmark.annualized_return'
                )
            );

        /*
         * Benchmark implementation cost.
         *
         * Add an expense_ratio field to benchmarks. Until populated,
         * the benchmark cost remains unknown rather than silently
         * assuming zero.
         */
        $benchmarkExpenseRatio =
            $this->benchmarkExpenseRatio(
                $benchmark
            );

        $benchmarkAnnualCost =
            $benchmarkExpenseRatio !== null
            && $portfolioValue > 0
                ? $portfolioValue
                    * $benchmarkExpenseRatio
                : null;

        $relativeReturn =
            $portfolioReturn !== null
            && $benchmarkReturn !== null
                ? $portfolioReturn
                    - $benchmarkReturn
                : null;

        $annualizedRelativeReturn =
            $portfolioAnnualizedReturn !== null
            && $benchmarkAnnualizedReturn !== null
                ? $portfolioAnnualizedReturn
                    - $benchmarkAnnualizedReturn
                : null;

        $incrementalCostRate =
            $portfolioCostRate !== null
            && $benchmarkExpenseRatio !== null
                ? $portfolioCostRate
                    - $benchmarkExpenseRatio
                : null;

        $incrementalAnnualCost =
            $benchmarkAnnualCost !== null
                ? $portfolioAnnualCost
                    - $benchmarkAnnualCost
                : null;

        /*
         * Translate benchmark-relative performance into dollars.
         *
         * Positive:
         *     Portfolio outperformed the benchmark.
         *
         * Negative:
         *     Portfolio underperformed the benchmark.
         *
         * Keep Helmio's existing opportunity-cost calculation
         * separately for backward compatibility.
         */
        $performanceValueDifference =
            $relativeReturn !== null
            && $portfolioValue > 0
                ? $portfolioValue
                    * $relativeReturn
                : null;

        $outperformanceValue =
            $performanceValueDifference !== null
            && $performanceValueDifference > 0
                ? $performanceValueDifference
                : 0.0;

        $underperformanceValue =
            $performanceValueDifference !== null
            && $performanceValueDifference < 0
                ? abs(
                    $performanceValueDifference
                )
                : 0.0;

        $performanceValueGap =
            $this->nullableFloat(
                data_get(
                    $performance,
                    'comparison.opportunity_cost'
                )
            );

        $valueAssessment =
            $this->valueAssessment(
                relativeReturn:
                    $relativeReturn,
                incrementalCostRate:
                    $incrementalCostRate,
            );

        return [
            'status' => 'complete',

            'period' =>
                $performance['period'] ?? [
                    'start_date' =>
                        $startDate->toDateString(),

                    'end_date' =>
                        $endDate->toDateString(),
                ],

            'portfolio' => [
                'value' =>
                    round(
                        $portfolioValue,
                        2
                    ),

                'return' =>
                    $this->roundRate(
                        $portfolioReturn
                    ),

                'annualized_return' =>
                    $this->roundRate(
                        $portfolioAnnualizedReturn
                    ),

                'annual_cost' =>
                    round(
                        $portfolioAnnualCost,
                        2
                    ),

                'cost_rate' =>
                    $this->roundRate(
                        $portfolioCostRate
                    ),

                'advisory_fees' =>
                    round(
                        (float) (
                            $cost['advisory_fees']
                            ?? 0
                        ),
                        2
                    ),

                'fund_expenses' =>
                    round(
                        (float) (
                            $cost['fund_expenses']
                            ?? 0
                        ),
                        2
                    ),

                'transaction_fees' =>
                    round(
                        (float) (
                            $cost['transaction_fees']
                            ?? 0
                        ),
                        2
                    ),

                'account_fees' =>
                    round(
                        (float) (
                            $cost['account_fees']
                            ?? 0
                        ),
                        2
                    ),
            ],

            'benchmark' => [
                'id' =>
                    $benchmark?->id,

                'name' =>
                    $benchmark?->name,

                'symbol' =>
                    $benchmark?->symbol,

                'return' =>
                    $this->roundRate(
                        $benchmarkReturn
                    ),

                'annualized_return' =>
                    $this->roundRate(
                        $benchmarkAnnualizedReturn
                    ),

                'expense_ratio' =>
                    $this->roundRate(
                        $benchmarkExpenseRatio
                    ),

                'estimated_annual_cost' =>
                    $benchmarkAnnualCost === null
                        ? null
                        : round(
                            $benchmarkAnnualCost,
                            2
                        ),
            ],

            'comparison' => [
                /*
                 * Positive means portfolio beat benchmark.
                 * Negative means portfolio trailed benchmark.
                 */
                'relative_return' =>
                    $this->roundRate(
                        $relativeReturn
                    ),

                'annualized_relative_return' =>
                    $this->roundRate(
                        $annualizedRelativeReturn
                    ),

                /*
                 * Positive means the portfolio costs more
                 * than the benchmark implementation.
                 */
                'incremental_cost_rate' =>
                    $this->roundRate(
                        $incrementalCostRate
                    ),

                'incremental_annual_cost' =>
                    $incrementalAnnualCost === null
                        ? null
                        : round(
                            $incrementalAnnualCost,
                            2
                        ),

                /*
                 * Signed benchmark-relative value difference.
                 *
                 * Positive = estimated value from outperformance.
                 * Negative = estimated value gap from underperformance.
                 */
                'performance_value_difference' =>
                    $performanceValueDifference === null
                        ? null
                        : round(
                            $performanceValueDifference,
                            2
                        ),

                'performance_value_status' =>
                    $performanceValueDifference === null
                        ? null
                        : (
                            $performanceValueDifference > 0
                                ? 'outperformance'
                                : (
                                    $performanceValueDifference < 0
                                        ? 'underperformance'
                                        : 'matched'
                                )
                        ),

                'outperformance_value' =>
                    $performanceValueDifference === null
                        ? null
                        : round(
                            $outperformanceValue,
                            2
                        ),

                'underperformance_value' =>
                    $performanceValueDifference === null
                        ? null
                        : round(
                            $underperformanceValue,
                            2
                        ),

                /*
                 * Existing Helmio Performance-engine opportunity cost.
                 * Retained for compatibility with existing consumers.
                 */
                'performance_value_gap' =>
                    $performanceValueGap === null
                        ? null
                        : round(
                            $performanceValueGap,
                            2
                        ),

                'outperformed_benchmark' =>
                    $relativeReturn === null
                        ? null
                        : $relativeReturn > 0,
            ],

            'assessment' =>
                $valueAssessment,

            'methodology' => [
                'portfolio_return_basis' =>
                    'time_weighted_observed',

                'benchmark_return_basis' =>
                    'benchmark_price_series',

                'embedded_expenses_note' =>
                    'Observed portfolio and ETF/fund benchmark returns already reflect embedded fund operating expenses. Expense ratios are disclosed as costs and are not subtracted again from observed returns.',

                'benchmark_cost_note' =>
                    $benchmarkExpenseRatio === null
                        ? 'Benchmark implementation cost is unavailable because the benchmark expense ratio has not been populated.'
                        : 'Benchmark implementation cost is estimated using the benchmark expense ratio and current portfolio value.',

                'advice_cost_note' =>
                    'Portfolio all-in cost includes the cost components identified by Helmio. It is displayed beside observed performance rather than automatically subtracted again from return.',
            ],

            'data_quality' => [
                'cost_warnings' =>
                    $cost['data_warnings']
                    ?? [],

                'performance_warnings' =>
                    data_get(
                        $performance,
                        'data_quality.warnings',
                        []
                    ),

                'benchmark_cost_available' =>
                    $benchmarkExpenseRatio !== null,
            ],

            'raw' => [
                'cost' => $cost,
                'performance' =>
                    $performance,
            ],

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function benchmarkExpenseRatio(
        ?Benchmark $benchmark
    ): ?float {
        if ($benchmark === null) {
            return null;
        }

        $value = data_get(
            $benchmark,
            'expense_ratio'
        );

        if (
            $value === null
            || $value === ''
            || ! is_numeric($value)
        ) {
            return null;
        }

        return max(
            0,
            (float) $value
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function valueAssessment(
        ?float $relativeReturn,
        ?float $incrementalCostRate,
    ): array {
        if (
            $relativeReturn === null
            || $incrementalCostRate === null
        ) {
            return [
                'status' =>
                    'insufficient_data',

                'label' =>
                    'More data needed',

                'message' =>
                    'Helmio needs both benchmark performance and benchmark cost data to evaluate cost versus value.',
            ];
        }

        if (
            $relativeReturn >= 0
            && $incrementalCostRate <= 0
        ) {
            return [
                'status' =>
                    'strong_value',

                'label' =>
                    'Strong value',

                'message' =>
                    'The portfolio outperformed the benchmark while costing no more than the benchmark implementation.',
            ];
        }

        if (
            $relativeReturn >=
                $incrementalCostRate
        ) {
            return [
                'status' =>
                    'cost_justified_by_return',

                'label' =>
                    'Cost supported by return',

                'message' =>
                    'The portfolio outperformance exceeded its incremental cost over the benchmark.',
            ];
        }

        if (
            $relativeReturn >= 0
        ) {
            return [
                'status' =>
                    'mixed_value',

                'label' =>
                    'Mixed value',

                'message' =>
                    'The portfolio outperformed the benchmark, but the incremental return did not exceed the incremental cost.',
            ];
        }

        if (
            $incrementalCostRate > 0
        ) {
            return [
                'status' =>
                    'value_gap',

                'label' =>
                    'Value gap identified',

                'message' =>
                    'The portfolio cost more than the benchmark while also underperforming it during the selected period.',
            ];
        }

        return [
            'status' =>
                'underperformance',

            'label' =>
                'Performance review recommended',

            'message' =>
                'The portfolio underperformed the benchmark during the selected period.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function insufficientDataResult(
        string $message,
        array $cost = [],
        array $performance = [],
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                $message,

            'portfolio' =>
                null,

            'benchmark' =>
                null,

            'comparison' =>
                null,

            'assessment' => [
                'status' =>
                    'insufficient_data',

                'label' =>
                    'More data needed',

                'message' =>
                    $message,
            ],

            'raw' => [
                'cost' =>
                    $cost,

                'performance' =>
                    $performance,
            ],

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function nullableFloat(
        mixed $value
    ): ?float {
        return is_numeric($value)
            ? (float) $value
            : null;
    }

    private function roundRate(
        ?float $value
    ): ?float {
        return $value === null
            ? null
            : round(
                $value,
                8
            );
    }
}