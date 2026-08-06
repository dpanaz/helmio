<?php

namespace App\Services\Analytics;

use App\Models\Benchmark;
use App\Models\InvestmentAccount;
use App\Models\User;
use App\Services\Analytics\Cash\CashDragAnalyticsService;
use App\Services\Analytics\Performance\PerformanceAnalyticsService;
use App\Services\Analytics\Risk\RiskAnalyticsService;
use App\Services\Analytics\Tax\TaxAnalyticsService;
use App\Services\Analytics\Trading\TradingAnalyticsService;
use Illuminate\Support\Collection;

class HelmScoreService
{
    public const FORMULA_VERSION = 'helm-score-0.3.0';

    public function __construct(
        private readonly CostAnalyticsService $costAnalytics,
        private readonly FundExpenseAnalyticsService $fundAnalytics,
        private readonly DiversificationAnalyticsService $diversificationAnalytics,
        private readonly PerformanceAnalyticsService $performanceAnalytics,
        private readonly RiskAnalyticsService $riskAnalytics,
        private readonly TradingAnalyticsService $tradingAnalytics,
        private readonly CashDragAnalyticsService $cashAnalytics,
        private readonly TaxAnalyticsService $taxAnalytics,
    ) {
    }

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(Collection $accounts): array
    {
        $costs = $this->costAnalytics->calculate($accounts);
        $funds = $this->fundAnalytics->calculate($accounts);

        $diversification =
            $this->diversificationAnalytics->calculate($accounts);

        $user = $this->resolveUser($accounts);

        $startDate = now()
            ->subYear()
            ->startOfDay();

        $endDate = now()
            ->endOfDay();

        $benchmark = Benchmark::query()
            ->where('is_active', true)
            ->where('symbol', 'SPY')
            ->first();

        $performance = $user
            ? $this->performanceAnalytics->analyze(
                user: $user,
                startDate: $startDate,
                endDate: $endDate,
                benchmark: $benchmark,
            )
            : $this->missingCategoryResult(
                'The account owner could not be identified.'
            );

        $risk = $user
            ? $this->riskAnalytics->analyze(
                user: $user,
                startDate: $startDate,
                endDate: $endDate,
                benchmark: $benchmark,
            )
            : $this->missingCategoryResult(
                'The account owner could not be identified.'
            );

        $trading = $user
            ? $this->tradingAnalytics->analyze(
                user: $user,
                startDate: $startDate,
                endDate: $endDate,
            )
            : $this->missingCategoryResult(
                'The account owner could not be identified.'
            );

        $cash = $user
            ? $this->cashAnalytics->analyze(
                user: $user,
                startDate: $startDate,
                endDate: $endDate,
                benchmark: $benchmark,
            )
            : $this->missingCategoryResult(
                'The account owner could not be identified.'
            );

        $tax = $user
            ? $this->taxAnalytics->analyze(
                user: $user,
                startDate: $startDate,
                endDate: $endDate,
            )
            : $this->missingCategoryResult(
                'The account owner could not be identified.'
            );

        $categories = [
            'cost' => $this->calculateCostScore(
                costs: $costs,
                funds: $funds,
            ),

            'diversification' =>
                $this->normalizeLegacyCategory(
                    $diversification
                ),

            'performance' =>
                $this->normalizeSharedCategory(
                    $performance
                ),

            'risk' =>
                $this->normalizeSharedCategory(
                    $risk
                ),

            'trading' =>
                $this->normalizeSharedCategory(
                    $trading
                ),

            'cash' =>
                $this->normalizeSharedCategory(
                    $cash
                ),

            'tax' =>
                $this->normalizeSharedCategory(
                    $tax
                ),
        ];

        $completedCategories = collect($categories)
            ->filter(
                fn (array $category): bool =>
                    $category['score'] !== null
            );

        $dataCompleteness = count($categories) > 0
            ? $completedCategories->count()
                / count($categories)
            : 0.0;

        $overallScore =
            $completedCategories->count() >= 4
                ? (int) round(
                    $completedCategories->avg('score')
                )
                : null;

        return [
            'overall_score' =>
                $overallScore,

            'overall_label' =>
                $overallScore !== null
                    ? $this->scoreLabel($overallScore)
                    : 'Building your score',

            'data_completeness' =>
                round($dataCompleteness, 8),

            'categories' =>
                $categories,

            'cost_analytics' =>
                $costs,

            'fund_analytics' =>
                $funds,

            'diversification_analytics' =>
                $diversification,

            'performance_analytics' =>
                $performance,

            'risk_analytics' =>
                $risk,

            'trading_analytics' =>
                $trading,

            'cash_analytics' =>
                $cash,

            'tax_analytics' =>
                $tax,

            'benchmark' => [
                'id' => $benchmark?->id,
                'name' => $benchmark?->name,
                'symbol' => $benchmark?->symbol,
            ],

            'analysis_period' => [
                'start_date' =>
                    $startDate->toDateString(),

                'end_date' =>
                    $endDate->toDateString(),
            ],

            'formula_version' =>
                self::FORMULA_VERSION,

            'calculated_for_date' =>
                now()->toDateString(),
        ];
    }

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     */
    private function resolveUser(
        Collection $accounts
    ): ?User {
        /** @var InvestmentAccount|null $firstAccount */
        $firstAccount = $accounts->first();

        if (
            $firstAccount === null
            || $firstAccount->user_id === null
        ) {
            return null;
        }

        return User::query()->find(
            $firstAccount->user_id
        );
    }

    /**
     * Normalize services using the shared AnalyticsResult contract.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function normalizeSharedCategory(
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

        $reasons = collect($flags)
            ->pluck('message')
            ->filter()
            ->values()
            ->all();

        if ($reasons === []) {
            $reasons = collect($warnings)
                ->pluck('message')
                ->filter()
                ->values()
                ->all();
        }

        if (
            $reasons === []
            && isset($result['message'])
            && $result['message']
        ) {
            $reasons[] = $result['message'];
        }

        $recommendations = $this->recommendationsFromFlags(
            $flags
        );

        if (
            $recommendations === []
            && $score !== null
        ) {
            $recommendations[] =
                'Continue monitoring this category for material changes.';
        }

        return [
            'score' => $score,

            'label' =>
                $result['label']
                ?? (
                    $score !== null
                        ? $this->scoreLabel($score)
                        : 'Insufficient data'
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

            'status' =>
                $result['status']
                ?? 'unknown',

            'formula_version' =>
                $result['formula_version']
                ?? null,
        ];
    }

    /**
     * Normalize Cost and Diversification services that still use
     * the older score/reasons/recommendations structure.
     *
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function normalizeLegacyCategory(
        array $result
    ): array {
        $score = isset($result['score'])
            && is_numeric($result['score'])
                ? (int) $result['score']
                : null;

        return [
            'score' => $score,

            'label' =>
                $result['label']
                ?? (
                    $score !== null
                        ? $this->scoreLabel($score)
                        : 'Insufficient data'
                ),

            'reasons' =>
                array_values(
                    $result['reasons'] ?? []
                ),

            'recommendations' =>
                array_values(
                    $result['recommendations'] ?? []
                ),

            'metrics' =>
                $result['metrics'] ?? [],

            'flags' =>
                $result['flags'] ?? [],

            'warnings' =>
                $result['warnings'] ?? [],

            'status' =>
                $score === null
                    ? 'insufficient_data'
                    : 'complete',

            'formula_version' =>
                $result['formula_version']
                ?? null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $flags
     * @return array<int, string>
     */
    private function recommendationsFromFlags(
        array $flags
    ): array {
        $recommendations = [];

        foreach ($flags as $flag) {
            $code = $flag['code'] ?? null;

            $recommendation = match ($code) {
                'significant_benchmark_underperformance',
                'benchmark_underperformance' =>
                    'Review performance relative to the benchmark and ask what decisions drove the difference.',

                'negative_annualized_return' =>
                    'Review whether the current strategy remains appropriate for the investor’s goals and time horizon.',

                'high_volatility',
                'severe_drawdown',
                'high_market_beta' =>
                    'Review whether portfolio risk is consistent with the investor’s stated tolerance and objectives.',

                'weak_risk_adjusted_return',
                'weak_downside_adjusted_return' =>
                    'Evaluate whether the return earned adequately compensates for the risk taken.',

                'high_portfolio_turnover',
                'high_trade_frequency',
                'possible_churning_pattern',
                'repeated_very_short_round_trips',
                'frequent_short_term_round_trips' =>
                    'Request a clear explanation of trading frequency, purpose, costs, and client benefit.',

                'high_trading_costs' =>
                    'Review transaction costs and whether lower-cost execution or reduced activity is appropriate.',

                'high_average_cash',
                'elevated_average_cash',
                'high_current_cash',
                'meaningful_cash_opportunity_cost' =>
                    'Review the purpose of the cash allocation and whether excess cash should be invested or reserved for a stated need.',

                'possible_wash_sales_detected' =>
                    'Review potential wash sales with a qualified tax professional before relying on the related loss deductions.',

                'tax_loss_harvesting_opportunity' =>
                    'Review tax-loss harvesting opportunities and suitable replacement investments with a tax professional.',

                'harvesting_wash_sale_risk' =>
                    'Review recent purchases before harvesting losses to reduce potential wash-sale exposure.',

                'short_term_gains_dominate' =>
                    'Review whether taxable sales can be delayed until long-term holding-period treatment applies.',

                'non_qualified_dividend_heavy' =>
                    'Review whether tax-inefficient income-producing assets belong in a tax-advantaged account.',

                default => null,
            };

            if ($recommendation !== null) {
                $recommendations[] = $recommendation;
            }
        }

        return array_values(
            array_unique($recommendations)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function missingCategoryResult(
        string $message
    ): array {
        return [
            'status' => 'insufficient_data',
            'message' => $message,
            'score' => null,
            'label' => 'Insufficient data',
            'metrics' => [],
            'flags' => [],
            'warnings' => [
                [
                    'code' =>
                        'account_owner_unavailable',

                    'message' =>
                        $message,
                ],
            ],
            'data' => [],
            'formula_version' => null,
        ];
    }

    /**
     * @param array<string, mixed> $costs
     * @param array<string, mixed> $funds
     * @return array<string, mixed>
     */
    private function calculateCostScore(
        array $costs,
        array $funds,
    ): array {
        $allInCostRate =
            $costs['all_in_cost_rate'] ?? null;

        $coverageRate =
            $funds['expense_data_coverage_rate']
            ?? null;

        if (
            ($costs['portfolio_value'] ?? 0) <= 0
            || $allInCostRate === null
        ) {
            return [
                'score' => null,
                'label' => 'Insufficient data',

                'reasons' => [
                    'Portfolio value or account-cost data is missing.',
                ],

                'recommendations' => [
                    'Add account values, advisory fees and fund expense ratios.',
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

                'flags' => [],
                'warnings' => [],
                'status' => 'insufficient_data',
                'formula_version' => null,
            ];
        }

        $score = match (true) {
            $allInCostRate <= 0.0025 => 100,

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
                $allInCostRate * 100,
            ),

            sprintf(
                'Estimated annual investment cost is $%s.',
                number_format(
                    $costs[
                        'total_annual_cost'
                    ] ?? 0,
                    2
                ),
            ),
        ];

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
        }

        if (
            ($funds['estimated_savings'] ?? 0) > 0
        ) {
            $reasons[] = sprintf(
                'Lower-cost comparison candidates indicate approximately $%s in potential annual savings.',
                number_format(
                    $funds[
                        'estimated_savings'
                    ],
                    2
                ),
            );
        }

        $recommendations = [];

        if ($allInCostRate > 0.0150) {
            $recommendations[] =
                'Ask for an itemized explanation of all advisory, fund and account costs.';
        }

        if (
            (
                $funds[
                    'missing_expense_ratio_count'
                ] ?? 0
            ) > 0
        ) {
            $recommendations[] =
                'Complete missing mutual fund and ETF expense-ratio data.';
        }

        if (
            ($funds['estimated_savings'] ?? 0) > 0
        ) {
            $recommendations[] =
                'Review lower-cost candidates with similar comparison-group exposure.';
        }

        if ($recommendations === []) {
            $recommendations[] =
                'Continue monitoring costs for changes and new account fees.';
        }

        return [
            'score' => $score,
            'label' => $this->scoreLabel($score),
            'reasons' => $reasons,
            'recommendations' => $recommendations,

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

            'flags' => [],
            'warnings' => [],
            'status' => 'complete',
            'formula_version' => null,
        ];
    }

    private function interpolate(
        float $value,
        float $minimumValue,
        float $maximumValue,
        int $maximumScore,
        int $minimumScore,
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
                ),
            )
        );
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