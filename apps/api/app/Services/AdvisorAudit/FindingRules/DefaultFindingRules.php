<?php

namespace App\Services\AdvisorAudit\FindingRules;

class DefaultFindingRules implements CategoryFindingRules
{
    public function supports(string $category): bool
    {
        return true;
    }

    public function categoryLabel(
        string $category
    ): string {
        return match ($category) {
            'cost' => 'Cost',
            'diversification' => 'Diversification',
            'performance' => 'Performance',
            'risk' => 'Risk',
            'suitability' => 'Suitability',
            'trading' => 'Trading Discipline',
            'cash' => 'Cash Drag',
            'tax' => 'Tax Efficiency',

            default => ucwords(
                str_replace(
                    '_',
                    ' ',
                    $category
                )
            ),
        };
    }

    public function categoryWeight(
        string $category
    ): int {
        return match ($category) {
            'suitability' => 18,
            'performance' => 15,
            'cost' => 14,
            'trading' => 14,
            'risk' => 12,
            'tax' => 11,
            'diversification' => 10,
            'cash' => 7,
            default => 5,
        };
    }

    public function codeWeight(
        string $code
    ): int {
        return match ($code) {
            'possible_churning_pattern' => 25,
            'repeated_very_short_round_trips' => 22,
            'possible_wash_sales_detected' => 22,
            'significant_benchmark_underperformance' => 20,
            'severe_drawdown' => 20,
            'high_portfolio_turnover' => 18,
            'high_trading_costs' => 18,
            'high_volatility' => 16,
            'meaningful_performance_opportunity_cost' => 16,
            'meaningful_cash_opportunity_cost' => 14,
            'high_average_cash' => 12,
            'tax_loss_harvesting_opportunity' => 10,
            'positive_alpha' => 5,
            default => 0,
        };
    }

    public function normalizeSeverity(
        string $code,
        ?string $severity
    ): string {
        return match ($severity) {
            'critical' => 'critical',
            'high' => 'high',
            'medium',
            'moderate' => 'moderate',
            'information',
            'informational',
            'positive' => 'informational',
            default => 'moderate',
        };
    }

    public function typeForCode(
        string $code,
        string $severity
    ): string {
        if (
            in_array(
                $code,
                [
                    'positive_alpha',
                    'tax_loss_harvesting_opportunity',
                    'no_major_risk_flags',
                    'no_major_cash_drag',
                    'no_major_tax_flags',
                    'no_major_trading_flags',
                    'no_major_performance_flags',
                ],
                true
            )
        ) {
            return 'opportunity';
        }

        return $severity === 'informational'
            ? 'opportunity'
            : 'concern';
    }

    public function title(
        string $code,
        string $category,
        ?string $providedTitle = null
    ): string {
        return $providedTitle
            ?: $this->categoryLabel($category)
                .' finding';
    }

    public function message(
        string $code,
        string $category,
        ?string $providedMessage = null
    ): string {
        return $providedMessage
            ?: 'An analytics finding was detected.';
    }

    public function recommendation(
        string $code,
        string $category
    ): ?string {
        return match ($code) {
            'significant_benchmark_underperformance',
            'benchmark_underperformance' =>
                'Ask for a benchmark-relative performance review that explains the causes of underperformance.',

            'negative_annualized_return' =>
                'Review whether the investment strategy remains aligned with the investor’s time horizon and goals.',

            'high_volatility',
            'severe_drawdown',
            'high_market_beta' =>
                'Review the portfolio’s volatility, drawdown, and market sensitivity.',

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
                'Review the portfolio’s measured risk characteristics.',

            'suitability' =>
                'Review whether the portfolio fits the investor’s profile.',

            'trading' =>
                'Review the purpose and cost of trading activity.',

            'cash' =>
                'Review whether current cash levels are intentional.',

            'tax' =>
                'Review taxable activity with a qualified tax professional.',

            default => null,
        };
    }
}