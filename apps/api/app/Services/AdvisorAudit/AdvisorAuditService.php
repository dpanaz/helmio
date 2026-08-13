<?php

namespace App\Services\AdvisorAudit;

use App\Models\Benchmark;
use App\Models\InvestmentAccount;
use App\Models\User;
use App\Services\Analytics\Cash\CashDragAnalyticsService;
use App\Services\Analytics\CostAnalyticsService;
use App\Services\Analytics\DiversificationAnalyticsService;
use App\Services\Analytics\FundExpenseAnalyticsService;
use App\Services\Analytics\PerformanceAnalyticsService;
use App\Services\Analytics\RiskAnalyticsService;
use App\Services\Analytics\Risk\SuitabilityRiskService;
use App\Services\Analytics\Tax\TaxAnalyticsService;
use App\Services\Analytics\Trading\TradingAnalyticsService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Throwable;

class AdvisorAuditService
{
    public const FORMULA_VERSION =
        'advisor-audit-0.2.0';

    public function __construct(
        private readonly CostAnalyticsService $costAnalytics,
        private readonly FundExpenseAnalyticsService $fundAnalytics,
        private readonly DiversificationAnalyticsService $diversificationAnalytics,
        private readonly PerformanceAnalyticsService $performanceAnalytics,
        private readonly RiskAnalyticsService $riskAnalytics,
        private readonly SuitabilityRiskService $suitabilityRiskAnalytics,
        private readonly TradingAnalyticsService $tradingAnalytics,
        private readonly CashDragAnalyticsService $cashAnalytics,
        private readonly TaxAnalyticsService $taxAnalytics,
        private readonly AdvisorAuditScoringService $scoringService,
        private readonly AdvisorAuditFindingBuilder $findingBuilder,
    ) {
    }

    /**
     * Run the complete advisor audit.
     *
     * @return array<string, mixed>
     */
    public function analyze(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?Benchmark $benchmark = null
    ): array {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with([
                'holdings.security',
                'transactions.security',
                'institution',
            ])
            ->orderBy('name')
            ->get();

        if ($accounts->isEmpty()) {
            return $this->insufficientDataResult(
                message:
                    'No investment accounts were found.'
            );
        }

        $benchmark ??=
            $this->defaultBenchmark();

        $rawAnalytics = [
            'cost' =>
                $this->calculateCostCategory(
                    $accounts
                ),

            'diversification' =>
                $this->safelyAnalyze(
                    category: 'diversification',

                    callback: fn (): array =>
                        $this
                            ->diversificationAnalytics
                            ->calculate($accounts),
                ),

            'performance' =>
                $this->safelyAnalyze(
                    category: 'performance',

                    callback: fn (): array =>
                        $this
                            ->performanceAnalytics
                            ->analyze(
                                user: $user,
                                startDate: $startDate,
                                endDate: $endDate,
                                benchmark: $benchmark,
                            ),
                ),

            'risk' =>
                $this->safelyAnalyze(
                    category: 'risk',

                    callback: fn (): array =>
                        $this
                            ->riskAnalytics
                            ->analyze(
                                user: $user,
                                startDate: $startDate,
                                endDate: $endDate,
                                benchmark: $benchmark,
                            ),
                ),

            'suitability' =>
                $this->safelyAnalyze(
                    category: 'suitability',

                    callback: fn (): array =>
                        $this
                            ->suitabilityRiskAnalytics
                            ->analyze(
                                user: $user,
                                startDate: $startDate,
                                endDate: $endDate,
                                benchmark: $benchmark,
                            ),
                ),

            'trading' =>
                $this->safelyAnalyze(
                    category: 'trading',

                    callback: fn (): array =>
                        $this
                            ->tradingAnalytics
                            ->analyze(
                                user: $user,
                                startDate: $startDate,
                                endDate: $endDate,
                            ),
                ),

            'cash' =>
                $this->safelyAnalyze(
                    category: 'cash',

                    callback: fn (): array =>
                        $this
                            ->cashAnalytics
                            ->analyze(
                                user: $user,
                                startDate: $startDate,
                                endDate: $endDate,
                                benchmark: $benchmark,
                            ),
                ),

            'tax' =>
                $this->safelyAnalyze(
                    category: 'tax',

                    callback: fn (): array =>
                        $this
                            ->taxAnalytics
                            ->analyze(
                                user: $user,
                                startDate: $startDate,
                                endDate: $endDate,
                            ),
                ),
        ];

        $categories = [
            'cost' =>
                $this->normalizeCategory(
                    category: 'cost',
                    result:
                        $rawAnalytics['cost'],
                ),

            'diversification' =>
                $this->normalizeCategory(
                    category:
                        'diversification',

                    result:
                        $rawAnalytics[
                            'diversification'
                        ],
                ),

            'performance' =>
                $this->normalizeCategory(
                    category: 'performance',
                    result:
                        $rawAnalytics[
                            'performance'
                        ],
                ),

            'risk' =>
                $this->normalizeCategory(
                    category: 'risk',
                    result:
                        $rawAnalytics['risk'],
                ),

            'suitability' =>
                $this->normalizeCategory(
                    category: 'suitability',
                    result:
                        $rawAnalytics[
                            'suitability'
                        ],
                ),

            'trading' =>
                $this->normalizeCategory(
                    category: 'trading',
                    result:
                        $rawAnalytics['trading'],
                ),

            'cash' =>
                $this->normalizeCategory(
                    category: 'cash',
                    result:
                        $rawAnalytics['cash'],
                ),

            'tax' =>
                $this->normalizeCategory(
                    category: 'tax',
                    result:
                        $rawAnalytics['tax'],
                ),
        ];

        $scoreResult =
            $this->scoringService->calculate(
                $categories
            );

        $findings =
            $this->findingBuilder->build(
                $scoreResult['categories']
            );

        $executiveSummary =
            $this->buildExecutiveSummary(
                scoreResult: $scoreResult,
                findings: $findings,
            );

        return [
            'status' =>
                $scoreResult['status'],

            'message' =>
                $scoreResult['status']
                    === 'complete'
                        ? null
                        : 'More complete account data is required before Helmio can present a full advisor score.',

            'overall_score' =>
                $scoreResult[
                    'overall_score'
                ],

            'overall_label' =>
                $scoreResult[
                    'overall_label'
                ],

            'advisor_rating' =>
                $scoreResult[
                    'advisor_rating'
                ],

            'data_completeness' =>
                $scoreResult[
                    'data_completeness'
                ],

            'available_weight' =>
                $scoreResult[
                    'available_weight'
                ],

            'available_category_count' =>
                $scoreResult[
                    'available_category_count'
                ],

            'total_category_count' =>
                $scoreResult[
                    'total_category_count'
                ],

            'period' => [
                'start_date' =>
                    $startDate->toDateString(),

                'end_date' =>
                    $endDate->toDateString(),

                'account_count' =>
                    $accounts->count(),
            ],

            'benchmark' => [
                'id' => $benchmark?->id,
                'name' => $benchmark?->name,
                'symbol' => $benchmark?->symbol,
            ],

            'categories' =>
                $scoreResult['categories'],

            'findings' =>
                $findings,

            'executive_summary' =>
                $executiveSummary,

            'raw_analytics' =>
                $rawAnalytics,

            'formula_version' =>
                self::FORMULA_VERSION,

            'scoring_formula_version' =>
                $scoreResult[
                    'formula_version'
                ],

            'finding_formula_version' =>
                $findings[
                    'formula_version'
                ],

            'calculated_at' =>
                now()->toIso8601String(),
        ];
    }

    /**
     * Cost still uses the older account-based analytics services.
     *
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    private function calculateCostCategory(
        Collection $accounts
    ): array {
        try {
            $costs =
                $this->costAnalytics
                    ->calculate($accounts);

            $funds =
                $this->fundAnalytics
                    ->calculate($accounts);

            $category =
                $this->calculateCostScore(
                    costs: $costs,
                    funds: $funds,
                );

            return [
                ...$category,

                'status' =>
                    $category['score'] === null
                        ? 'insufficient_data'
                        : 'complete',

                'flags' =>
                    $this->costFlags(
                        category: $category,
                    ),

                'warnings' =>
                    $this->costWarnings(
                        costs: $costs,
                        funds: $funds,
                    ),

                'data' => [
                    'cost_analytics' =>
                        $costs,

                    'fund_analytics' =>
                        $funds,
                ],

                'formula_version' =>
                    'cost-adapter-0.1.0',
            ];
        } catch (Throwable $exception) {
            return $this->failedCategoryResult(
                category: 'cost',
                exception: $exception,
            );
        }
    }

    /**
     * @param callable(): array<string, mixed> $callback
     * @return array<string, mixed>
     */
    private function safelyAnalyze(
        string $category,
        callable $callback
    ): array {
        try {
            return $callback();
        } catch (Throwable $exception) {
            return $this->failedCategoryResult(
                category: $category,
                exception: $exception,
            );
        }
    }

    /**
     * Convert shared and legacy analytics into the category
     * shape expected by AdvisorAuditScoringService.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function normalizeCategory(
        string $category,
        array $result
    ): array {
        $score = isset($result['score'])
            && is_numeric($result['score'])
                ? (int) $result['score']
                : null;

        $flags = array_values(
            $result['flags'] ?? []
        );

        $warnings = array_values(
            $result['warnings'] ?? []
        );

        $reasons = array_values(
            $result['reasons'] ?? []
        );

        if ($reasons === []) {
            $reasons = collect($flags)
                ->pluck('message')
                ->filter(
                    fn ($message): bool =>
                        is_string($message)
                        && $message !== ''
                )
                ->values()
                ->all();
        }

        if (
            $reasons === []
            && ! empty($result['message'])
        ) {
            $reasons[] =
                (string) $result['message'];
        }

        $recommendations =
            array_values(
                $result[
                    'recommendations'
                ] ?? []
            );

        if ($recommendations === []) {
            $recommendations =
                $this->recommendationsFromFlags(
                    category: $category,
                    flags: $flags,
                );
        }

        return [
            'score' => $score,

            'label' =>
                $result['label']
                ?? (
                    $score !== null
                        ? $this->scoreLabel(
                            $score
                        )
                        : 'Insufficient data'
                ),

            'status' =>
                $result['status']
                ?? (
                    $score !== null
                        ? 'complete'
                        : 'insufficient_data'
                ),

            'reasons' =>
                $reasons,

            'recommendations' =>
                $recommendations,

            'metrics' =>
                $result['metrics'] ?? [],

            'flags' =>
                $flags,

            'warnings' =>
                $warnings,

            'formula_version' =>
                $result[
                    'formula_version'
                ] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildExecutiveSummary(
        array $scoreResult,
        array $findings
    ): array {
        $topConcerns = collect(
            array_merge(
                $findings['critical'],
                $findings['important'],
            )
        )
            ->sortByDesc('priority')
            ->take(3)
            ->values()
            ->all();

        $topOpportunities = collect(
            $findings['opportunities']
        )
            ->sortByDesc('priority')
            ->take(3)
            ->values()
            ->all();

        $topRecommendations = collect(
            $findings['recommendations']
        )
            ->sortByDesc('priority')
            ->take(5)
            ->values()
            ->all();

        $headline =
            $this->summaryHeadline(
                overallScore:
                    $scoreResult[
                        'overall_score'
                    ],

                advisorRating:
                    $scoreResult[
                        'advisor_rating'
                    ],
            );

        return [
            'headline' => $headline,

            'summary' =>
                $this->summaryText(
                    scoreResult:
                        $scoreResult,

                    findings:
                        $findings,
                ),

            'top_concerns' =>
                $topConcerns,

            'top_opportunities' =>
                $topOpportunities,

            'top_recommendations' =>
                $topRecommendations,
        ];
    }

    private function summaryHeadline(
        ?int $overallScore,
        ?string $advisorRating
    ): string {
        if ($overallScore === null) {
            return 'Building your advisor audit';
        }

        return match ($advisorRating) {
            'strong' =>
                'The available evidence indicates strong portfolio oversight.',

            'generally_sound' =>
                'The portfolio appears generally sound, with some areas worth reviewing.',

            'mixed' =>
                'The advisor audit found a mixed set of strengths and concerns.',

            'concerning' =>
                'Several advisor and portfolio decisions need closer review.',

            'high_concern' =>
                'The audit identified substantial concerns requiring prompt review.',

            default =>
                'Advisor audit complete.',
        };
    }

    private function summaryText(
        array $scoreResult,
        array $findings
    ): string {
        $overallScore =
            $scoreResult['overall_score'];

        if ($overallScore === null) {
            return sprintf(
                '%d of %d audit categories currently have enough data for analysis.',
                $scoreResult[
                    'available_category_count'
                ],
                $scoreResult[
                    'total_category_count'
                ],
            );
        }

        return sprintf(
            'Helmio scored the available advisor and portfolio evidence at %d out of 100. The audit identified %d critical concern(s), %d important finding(s), and %d opportunity or opportunities.',
            $overallScore,
            $findings['summary'][
                'critical_count'
            ],
            $findings['summary'][
                'important_count'
            ],
            $findings['summary'][
                'opportunity_count'
            ],
        );
    }

    /**
     * @param array<int, array<string, mixed>> $flags
     * @return array<int, string>
     */
    private function recommendationsFromFlags(
        string $category,
        array $flags
    ): array {
        $recommendations = [];

        foreach ($flags as $flag) {
            $code = $flag['code'] ?? '';

            $recommendation = match ($code) {
                'significant_benchmark_underperformance',
                'benchmark_underperformance' =>
                    'Ask for a benchmark-relative performance review that explains the causes of underperformance.',

                'negative_annualized_return' =>
                    'Review whether the current investment strategy remains aligned with the investor’s time horizon and goals.',

                'high_volatility',
                'severe_drawdown',
                'high_market_beta' =>
                    'Review the portfolio’s volatility, drawdown, and market sensitivity.',

                'portfolio_materially_too_aggressive',
                'portfolio_somewhat_too_aggressive' =>
                    'Ask the advisor to explain why the portfolio risk exceeds the effective investor risk tolerance.',

                'portfolio_materially_too_conservative',
                'portfolio_somewhat_too_conservative' =>
                    'Review whether the portfolio is too conservative for the investor’s time horizon and growth objective.',

                'portfolio_risk_aligned' =>
                    'Continue monitoring suitability as the investor’s age, goals, and account purposes change.',

                'weak_risk_adjusted_return',
                'weak_downside_adjusted_return' =>
                    'Evaluate whether the return earned justifies the level of risk taken.',

                'high_portfolio_turnover',
                'high_trade_frequency',
                'possible_churning_pattern',
                'repeated_very_short_round_trips',
                'frequent_short_term_round_trips' =>
                    'Request a trade-by-trade explanation of purpose, client benefit, costs, and tax impact.',

                'high_trading_costs' =>
                    'Review whether trading costs could be reduced through lower activity or lower-cost execution.',

                'high_average_cash',
                'elevated_average_cash',
                'high_current_cash',
                'meaningful_cash_opportunity_cost' =>
                    'Document the purpose of the cash position and review whether excess cash should be invested.',

                'possible_wash_sales_detected' =>
                    'Review potential wash sales with a qualified tax professional.',

                'tax_loss_harvesting_opportunity' =>
                    'Review available tax-loss harvesting opportunities and suitable replacement investments.',

                'harvesting_wash_sale_risk' =>
                    'Review recent purchases before realizing losses to reduce wash-sale exposure.',

                default =>
                    $this->defaultCategoryRecommendation(
                        $category
                    ),
            };

            if ($recommendation !== null) {
                $recommendations[] =
                    $recommendation;
            }
        }

        return array_values(
            array_unique($recommendations)
        );
    }

    private function defaultCategoryRecommendation(
        string $category
    ): ?string {
        return match ($category) {
            'cost' =>
                'Review advisory, fund, transaction, and account costs.',

            'diversification' =>
                'Review concentration and allocation risks.',

            'performance' =>
                'Review performance relative to an appropriate benchmark.',

            'risk' =>
                'Review the portfolio’s measured volatility, drawdown, and market sensitivity.',

            'suitability' =>
                'Review whether the portfolio’s actual risk matches the investor’s age, time horizon, objectives, liquidity needs, and stated tolerance.',

            'trading' =>
                'Review the purpose and cost of trading activity.',

            'cash' =>
                'Review whether current cash levels are intentional.',

            'tax' =>
                'Review taxable activity with a qualified tax professional.',

            default => null,
        };
    }

    /**
     * @param array<string, mixed> $costs
     * @param array<string, mixed> $funds
     * @return array<string, mixed>
     */
    private function calculateCostScore(
        array $costs,
        array $funds
    ): array {
        $allInCostRate =
            $costs['all_in_cost_rate']
            ?? null;

        $coverageRate =
            $funds[
                'expense_data_coverage_rate'
            ] ?? null;

        if (
            ($costs['portfolio_value'] ?? 0)
                <= 0
            || $allInCostRate === null
        ) {
            return [
                'score' => null,

                'label' =>
                    'Insufficient data',

                'reasons' => [
                    'Portfolio value or account-cost data is missing.',
                ],

                'recommendations' => [
                    'Add account values, advisory fees, and fund expense ratios.',
                ],

                'metrics' => [
                    'all_in_cost_rate' =>
                        $allInCostRate,

                    'annual_cost' =>
                        $costs[
                            'total_annual_cost'
                        ] ?? null,

                    'expense_data_coverage_rate' =>
                        $coverageRate,
                ],
            ];
        }

        $score = match (true) {
            $allInCostRate <= 0.0025 =>
                100,

            $allInCostRate <= 0.0050 =>
                $this->interpolate(
                    $allInCostRate,
                    0.0025,
                    0.0050,
                    100,
                    90,
                ),

            $allInCostRate <= 0.0075 =>
                $this->interpolate(
                    $allInCostRate,
                    0.0050,
                    0.0075,
                    90,
                    80,
                ),

            $allInCostRate <= 0.0100 =>
                $this->interpolate(
                    $allInCostRate,
                    0.0075,
                    0.0100,
                    80,
                    70,
                ),

            $allInCostRate <= 0.0150 =>
                $this->interpolate(
                    $allInCostRate,
                    0.0100,
                    0.0150,
                    70,
                    50,
                ),

            $allInCostRate <= 0.0200 =>
                $this->interpolate(
                    $allInCostRate,
                    0.0150,
                    0.0200,
                    50,
                    30,
                ),

            $allInCostRate <= 0.0250 =>
                $this->interpolate(
                    $allInCostRate,
                    0.0200,
                    0.0250,
                    30,
                    10,
                ),

            default => 10,
        };

        $reasons = [
            sprintf(
                'Estimated all-in annual cost is %.2f%%.',
                $allInCostRate * 100
            ),

            sprintf(
                'Estimated annual investment cost is $%s.',
                number_format(
                    $costs[
                        'total_annual_cost'
                    ] ?? 0,
                    2
                )
            ),
        ];

        $recommendations = [];

        if (
            $coverageRate !== null
            && $coverageRate < 0.80
        ) {
            $score = max(
                0,
                $score - 10
            );

            $reasons[] =
                'Expense-ratio data covers less than 80% of fund assets.';

            $recommendations[] =
                'Complete missing fund expense-ratio data.';
        }

        if (
            ($funds['estimated_savings'] ?? 0)
            > 0
        ) {
            $reasons[] = sprintf(
                'Lower-cost comparison candidates indicate approximately $%s in potential annual savings.',
                number_format(
                    $funds[
                        'estimated_savings'
                    ],
                    2
                )
            );

            $recommendations[] =
                'Review lower-cost investments with similar exposure.';
        }

        if ($allInCostRate > 0.0150) {
            $recommendations[] =
                'Request an itemized explanation of all advisory, fund, transaction, and account costs.';
        }

        if ($recommendations === []) {
            $recommendations[] =
                'Continue monitoring investment costs and fee changes.';
        }

        return [
            'score' => $score,

            'label' =>
                $this->scoreLabel($score),

            'reasons' =>
                $reasons,

            'recommendations' =>
                array_values(
                    array_unique(
                        $recommendations
                    )
                ),

            'metrics' => [
                'all_in_cost_rate' =>
                    $allInCostRate,

                'annual_cost' =>
                    $costs[
                        'total_annual_cost'
                    ] ?? null,

                'fund_expense_cost' =>
                    $funds[
                        'annual_expense_cost'
                    ] ?? null,

                'potential_savings' =>
                    $funds[
                        'estimated_savings'
                    ] ?? null,

                'expense_data_coverage_rate' =>
                    $coverageRate,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function costFlags(
        array $category
    ): array {
        $flags = [];

        $costRate = data_get(
            $category,
            'metrics.all_in_cost_rate'
        );

        if (
            $costRate !== null
            && $costRate >= 0.02
        ) {
            $flags[] = [
                'code' =>
                    'very_high_investment_cost',

                'severity' => 'high',

                'title' =>
                    'Investment costs are very high',

                'message' =>
                    'Estimated all-in annual investment costs are at least 2%.',
            ];
        } elseif (
            $costRate !== null
            && $costRate >= 0.015
        ) {
            $flags[] = [
                'code' =>
                    'high_investment_cost',

                'severity' =>
                    'moderate',

                'title' =>
                    'Investment costs are elevated',

                'message' =>
                    'Estimated all-in annual investment costs are at least 1.5%.',
            ];
        }

        $potentialSavings = data_get(
            $category,
            'metrics.potential_savings'
        );

        if (
            $potentialSavings !== null
            && $potentialSavings >= 500
        ) {
            $flags[] = [
                'code' =>
                    'lower_cost_opportunity',

                'severity' =>
                    'informational',

                'title' =>
                    'Lower-cost alternatives may exist',

                'message' =>
                    sprintf(
                        'Lower-cost comparison candidates indicate approximately $%s in potential annual savings.',
                        number_format(
                            (float) $potentialSavings,
                            2
                        )
                    ),
            ];
        }

        return $flags;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function costWarnings(
        array $costs,
        array $funds
    ): array {
        $warnings = [];

        if (
            (
                $funds[
                    'expense_data_coverage_rate'
                ] ?? 1
            ) < 0.80
        ) {
            $warnings[] = [
                'code' =>
                    'limited_expense_ratio_coverage',

                'message' =>
                    'Expense-ratio data covers less than 80% of fund assets.',
            ];
        }

        if (
            (
                $costs[
                    'portfolio_value'
                ] ?? 0
            ) <= 0
        ) {
            $warnings[] = [
                'code' =>
                    'portfolio_value_missing',

                'message' =>
                    'Portfolio value was unavailable for cost-rate calculations.',
            ];
        }

        return $warnings;
    }

    private function defaultBenchmark(): ?Benchmark
    {
        return Benchmark::query()
            ->where('is_active', true)
            ->where('symbol', 'SPY')
            ->first();
    }

    private function failedCategoryResult(
        string $category,
        Throwable $exception
    ): array {
        report($exception);

        return [
            'status' => 'failed',

            'message' =>
                sprintf(
                    '%s analytics could not be calculated.',
                    ucfirst($category)
                ),

            'score' => null,

            'label' =>
                'Calculation failed',

            'reasons' => [
                'This category could not be calculated because an internal analytics error occurred.',
            ],

            'recommendations' => [
                'Review the category data and application logs before recalculating the audit.',
            ],

            'metrics' => [],

            'flags' => [],

            'warnings' => [
                [
                    'code' =>
                        'category_calculation_failed',

                    'message' =>
                        sprintf(
                            '%s analytics failed during the advisor audit.',
                            ucfirst($category)
                        ),
                ],
            ],

            'data' => [],

            'formula_version' => null,
        ];
    }

    private function insufficientDataResult(
        string $message
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                $message,

            'overall_score' =>
                null,

            'overall_label' =>
                'Building advisor audit',

            'advisor_rating' =>
                null,

            'data_completeness' =>
                0.0,

            'available_weight' =>
                0.0,

            'available_category_count' =>
                0,

            'total_category_count' =>
                8,

            'period' =>
                null,

            'benchmark' =>
                null,

            'categories' =>
                [],

            'findings' => [
                'critical' => [],
                'important' => [],
                'opportunities' => [],
                'recommendations' => [],
                'all' => [],

                'summary' => [
                    'critical_count' => 0,
                    'important_count' => 0,
                    'opportunity_count' => 0,
                    'recommendation_count' => 0,
                    'total_finding_count' => 0,
                ],
            ],

            'executive_summary' => [
                'headline' =>
                    'Building your advisor audit',

                'summary' =>
                    $message,

                'top_concerns' => [],
                'top_opportunities' => [],
                'top_recommendations' => [],
            ],

            'raw_analytics' =>
                [],

            'formula_version' =>
                self::FORMULA_VERSION,

            'calculated_at' =>
                now()->toIso8601String(),
        ];
    }

    private function interpolate(
        float $value,
        float $minimumValue,
        float $maximumValue,
        int $maximumScore,
        int $minimumScore
    ): int {
        $position = (
            $value - $minimumValue
        ) / (
            $maximumValue - $minimumValue
        );

        $score = $maximumScore
            - (
                $position
                * (
                    $maximumScore
                    - $minimumScore
                )
            );

        return (int) round(
            max(
                $minimumScore,
                min(
                    $maximumScore,
                    $score
                )
            )
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