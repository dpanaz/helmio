<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use Illuminate\Support\Collection;

class HelmScoreService
{
    public const FORMULA_VERSION = 'helm-score-0.1.0';

public function __construct(
    private readonly CostAnalyticsService $costAnalytics,
    private readonly FundExpenseAnalyticsService $fundAnalytics,
    private readonly DiversificationAnalyticsService $diversificationAnalytics,
    private readonly TradingDisciplineAnalyticsService $tradingAnalytics,
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
        $trading =
    $this->tradingAnalytics->calculate($accounts);
            $costResult = $this->calculateCostScore($costs, $funds);

        $categories = [
            'cost' => $costResult,
            'diversification' => [
                'score' => $diversification['score'],
                'label' => $diversification['label'],
                'reasons' => $diversification['reasons'],
                'recommendations' => $diversification['recommendations'],
                'metrics' => $diversification['metrics'],
            ],
            'performance' => $this->pendingCategory(
                'Performance analysis has not been calculated yet.',
            ),
            'risk' => $this->pendingCategory(
                'Risk analysis has not been calculated yet.',
            ),
            'trading' => [
    'score' => $trading['score'],
    'label' => $trading['label'],
    'reasons' => $trading['reasons'],
    'recommendations' => $trading['recommendations'],
    'metrics' => $trading['metrics'],
],
            'tax' => $this->pendingCategory(
                'Tax-efficiency analysis has not been calculated yet.',
            ),
        ];

        $completedCategories = collect($categories)
            ->filter(fn (array $category): bool => $category['score'] !== null);

        $dataCompleteness = count($categories) > 0
            ? $completedCategories->count() / count($categories)
            : 0;

        /*
         * We deliberately avoid showing an overall score until at least
         * four categories have valid calculations.
         */
        $overallScore = $completedCategories->count() >= 4
            ? (int) round($completedCategories->avg('score'))
            : null;

        return [
            'overall_score' => $overallScore,
            'overall_label' => $overallScore !== null
                ? $this->scoreLabel($overallScore)
                : 'Building your score',
            'data_completeness' => $dataCompleteness,
            'categories' => $categories,
            'cost_analytics' => $costs,
            'fund_analytics' => $funds,
            'formula_version' => self::FORMULA_VERSION,
            'calculated_for_date' => now()->toDateString(),
            'diversification_analytics' => $diversification,
            'trading_analytics' => $trading,
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
        $allInCostRate = $costs['all_in_cost_rate'];
        $coverageRate = $funds['expense_data_coverage_rate'];

        if (
            $costs['portfolio_value'] <= 0
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
                    'all_in_cost_rate' => $allInCostRate,
                    'annual_cost' => $costs['total_annual_cost'],
                    'expense_data_coverage_rate' => $coverageRate,
                ],
            ];
        }

        /*
         * Initial consumer-facing scoring bands.
         *
         * <= 0.25%  = 100
         * 0.50%     = 90
         * 0.75%     = 80
         * 1.00%     = 70
         * 1.50%     = 50
         * 2.00%     = 30
         * >= 2.50%  = 10
         */
        $score = match (true) {
            $allInCostRate <= 0.0025 => 100,
            $allInCostRate <= 0.0050 => $this->interpolate(
                $allInCostRate,
                0.0025,
                0.0050,
                100,
                90,
            ),
            $allInCostRate <= 0.0075 => $this->interpolate(
                $allInCostRate,
                0.0050,
                0.0075,
                90,
                80,
            ),
            $allInCostRate <= 0.0100 => $this->interpolate(
                $allInCostRate,
                0.0075,
                0.0100,
                80,
                70,
            ),
            $allInCostRate <= 0.0150 => $this->interpolate(
                $allInCostRate,
                0.0100,
                0.0150,
                70,
                50,
            ),
            $allInCostRate <= 0.0200 => $this->interpolate(
                $allInCostRate,
                0.0150,
                0.0200,
                50,
                30,
            ),
            $allInCostRate <= 0.0250 => $this->interpolate(
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
                number_format($costs['total_annual_cost'], 2),
            ),
        ];

        if (
            $coverageRate !== null
            && $coverageRate < 0.80
        ) {
            $score = max(0, $score - 10);

            $reasons[] =
                'Expense-ratio data covers less than 80% of fund assets.';
        }

        if ($funds['estimated_savings'] > 0) {
            $reasons[] = sprintf(
                'Lower-cost comparison candidates indicate approximately $%s in potential annual savings.',
                number_format($funds['estimated_savings'], 2),
            );
        }

        $recommendations = [];

        if ($allInCostRate > 0.0150) {
            $recommendations[] =
                'Ask for an itemized explanation of all advisory, fund and account costs.';
        }

        if ($funds['missing_expense_ratio_count'] > 0) {
            $recommendations[] =
                'Complete missing mutual fund and ETF expense-ratio data.';
        }

        if ($funds['estimated_savings'] > 0) {
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
                'all_in_cost_rate' => $allInCostRate,
                'annual_cost' => $costs['total_annual_cost'],
                'fund_expense_cost' => $funds['annual_expense_cost'],
                'potential_savings' => $funds['estimated_savings'],
                'expense_data_coverage_rate' => $coverageRate,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pendingCategory(string $reason): array
    {
        return [
            'score' => null,
            'label' => 'Not calculated',
            'reasons' => [$reason],
            'recommendations' => [],
            'metrics' => [],
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
            - ($position * ($maximumScore - $minimumScore));

        return (int) round(max(
            $minimumScore,
            min($maximumScore, $score),
        ));
    }

    private function scoreLabel(int $score): string
    {
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
