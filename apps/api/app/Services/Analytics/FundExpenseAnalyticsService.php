<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use App\Models\Security;
use Illuminate\Support\Collection;

class FundExpenseAnalyticsService
{
    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(Collection $accounts): array
    {
        $holdings = $accounts
            ->flatMap(
                fn (InvestmentAccount $account): Collection =>
                    $account->holdings->map(
                        function ($holding) use ($account): array {
                            return [
                                'account_id' => $account->id,
                                'account_name' => $account->name,
                                'holding' => $holding,
                                'security' => $holding->security,
                            ];
                        },
                    ),
            )
            ->filter(
                fn (array $item): bool =>
                    in_array(
                        $item['security']?->security_type,
                        ['mutual_fund', 'etf'],
                        true,
                    ),
            )
            ->values();

        $totalFundValue = (float) $holdings->sum(
            fn (array $item): float =>
                (float) $item['holding']->market_value,
        );

        $knownExpenseValue = (float) $holdings
            ->filter(
                fn (array $item): bool =>
                    $item['security']?->expense_ratio !== null,
            )
            ->sum(
                fn (array $item): float =>
                    (float) $item['holding']->market_value,
            );

        $annualExpenseCost = (float) $holdings->sum(
            function (array $item): float {
                $expenseRatio = $item['security']?->expense_ratio;

                if ($expenseRatio === null) {
                    return 0;
                }

                return (float) $item['holding']->market_value
                    * (float) $expenseRatio;
            },
        );

        $weightedExpenseRatio = $knownExpenseValue > 0
            ? $annualExpenseCost / $knownExpenseValue
            : null;

        $holdingRows = $holdings->map(
            function (array $item): array {
                $security = $item['security'];
                $holding = $item['holding'];
                $marketValue = (float) $holding->market_value;

                $expenseRatio = $security?->expense_ratio !== null
                    ? (float) $security->expense_ratio
                    : null;

                $alternatives = $this->findAlternatives(
                    $security,
                    $marketValue,
                );

                return [
                    'account_id' => $item['account_id'],
                    'account_name' => $item['account_name'],
                    'security_id' => $security?->id,
                    'symbol' => $security?->symbol,
                    'name' => $security?->name,
                    'security_type' => $security?->security_type,
                    'category' => $security?->category,
                    'comparison_group' => $security?->comparison_group,
                    'market_value' => round($marketValue, 2),
                    'expense_ratio' => $expenseRatio,
                    'annual_expense_cost' => $expenseRatio !== null
                        ? round($marketValue * $expenseRatio, 2)
                        : null,
                    'alternatives' => $alternatives,
                    'best_estimated_savings' =>
                        $alternatives->first()['estimated_annual_savings']
                        ?? null,
                ];
            },
        );

        return [
            'total_fund_value' => round($totalFundValue, 2),
            'known_expense_value' => round($knownExpenseValue, 2),
            'expense_data_coverage_rate' => $totalFundValue > 0
                ? $knownExpenseValue / $totalFundValue
                : null,
            'weighted_expense_ratio' => $weightedExpenseRatio,
            'annual_expense_cost' => round($annualExpenseCost, 2),
            'fund_count' => $holdingRows->count(),
            'missing_expense_ratio_count' => $holdingRows
                ->whereNull('expense_ratio')
                ->count(),
            'estimated_savings' => round(
                (float) $holdingRows->sum(
                    fn (array $row): float =>
                        (float) ($row['best_estimated_savings'] ?? 0),
                ),
                2,
            ),
            'holdings' => $holdingRows
                ->sortByDesc(
                    fn (array $row): float =>
                        (float) ($row['annual_expense_cost'] ?? 0),
                )
                ->values(),
            'formula_version' => 'fund-expense-1.0.0',
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function findAlternatives(
        ?Security $security,
        float $marketValue,
    ): Collection {
        if (
            $security === null
            || $security->expense_ratio === null
            || blank($security->comparison_group)
        ) {
            return collect();
        }

        $currentExpenseRatio = (float) $security->expense_ratio;

        return Security::query()
            ->whereKeyNot($security->id)
            ->where('comparison_group', $security->comparison_group)
            ->whereIn('security_type', ['mutual_fund', 'etf'])
            ->whereNotNull('expense_ratio')
            ->where('expense_ratio', '<', $currentExpenseRatio)
            ->orderBy('expense_ratio')
            ->limit(3)
            ->get()
            ->map(
                function (Security $candidate) use (
                    $security,
                    $marketValue,
                    $currentExpenseRatio,
                ): array {
                    $candidateExpenseRatio =
                        (float) $candidate->expense_ratio;

                    return [
                        'security_id' => $candidate->id,
                        'symbol' => $candidate->symbol,
                        'name' => $candidate->name,
                        'expense_ratio' => $candidateExpenseRatio,
                        'expense_ratio_difference' =>
                            $currentExpenseRatio - $candidateExpenseRatio,
                        'estimated_annual_savings' => round(
                            $marketValue
                            * (
                                $currentExpenseRatio
                                - $candidateExpenseRatio
                            ),
                            2,
                        ),
                        'one_year_return_difference' =>
                            $this->returnDifference(
                                $candidate->trailing_1y_return,
                                $security->trailing_1y_return,
                            ),
                        'three_year_return_difference' =>
                            $this->returnDifference(
                                $candidate->trailing_3y_annualized_return,
                                $security->trailing_3y_annualized_return,
                            ),
                        'five_year_return_difference' =>
                            $this->returnDifference(
                                $candidate->trailing_5y_annualized_return,
                                $security->trailing_5y_annualized_return,
                            ),
                    ];
                },
            );
    }

    private function returnDifference(
        mixed $candidateReturn,
        mixed $currentReturn,
    ): ?float {
        if (
            $candidateReturn === null
            || $currentReturn === null
        ) {
            return null;
        }

        return (float) $candidateReturn
            - (float) $currentReturn;
    }
}
