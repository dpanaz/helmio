<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CostAnalyticsService
{
    /**
     * Calculate cost analytics for a collection of accounts.
     *
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(
        Collection $accounts,
        ?CarbonInterface $asOf = null,
    ): array {
        $asOf ??= now();

        $accountResults = $accounts->map(
            fn (InvestmentAccount $account): array =>
                $this->calculateAccount($account, $asOf),
        );

        $portfolioValue = (float) $accountResults->sum('portfolio_value');
        $advisoryFees = (float) $accountResults->sum('advisory_fee');
        $accountFees = (float) $accountResults->sum('account_fee');
        $fundExpenses = (float) $accountResults->sum('fund_expense_cost');
        $transactionFees = (float) $accountResults->sum('transaction_fees');

        $totalAnnualCost =
            $advisoryFees
            + $accountFees
            + $fundExpenses
            + $transactionFees;

        $allInCostRate = $portfolioValue > 0
            ? $totalAnnualCost / $portfolioValue
            : null;

        return [
            'as_of' => $asOf->toDateString(),
            'portfolio_value' => round($portfolioValue, 2),
            'advisory_fees' => round($advisoryFees, 2),
            'account_fees' => round($accountFees, 2),
            'fund_expenses' => round($fundExpenses, 2),
            'transaction_fees' => round($transactionFees, 2),
            'total_annual_cost' => round($totalAnnualCost, 2),
            'all_in_cost_rate' => $allInCostRate,
            'accounts' => $accountResults->values(),
            'data_warnings' => $this->buildWarnings($accountResults),
            'formula_version' => 'cost-1.0.0',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateAccount(
        InvestmentAccount $account,
        CarbonInterface $asOf,
    ): array {
        $account->loadMissing([
            'holdings.security',
            'transactions',
            'institution',
        ]);

        $portfolioValue = (float) $account->current_value;
        $cashValue = (float) $account->cash_value;

        $advisoryFeeBase = $account->advisory_fee_applies_to_cash
            ? $portfolioValue
            : max(0, $portfolioValue - $cashValue);

        $advisoryFeeRate = $account->annual_advisory_fee_rate !== null
            ? (float) $account->annual_advisory_fee_rate
            : 0;

        $advisoryFee = $advisoryFeeBase * $advisoryFeeRate;

        $fundExpenseCost = $account->holdings->sum(
            function ($holding): float {
                $expenseRatio = $holding->security?->expense_ratio;

                if ($expenseRatio === null) {
                    return 0;
                }

                return (float) $holding->market_value
                    * (float) $expenseRatio;
            },
        );

        $transactionFeeStart = $asOf->copy()->subYear()->startOfDay();

        $transactionFees = $account->transactions
            ->filter(
                fn ($transaction): bool =>
                    $transaction->transaction_date !== null
                    && $transaction->transaction_date->gte(
                        $transactionFeeStart,
                    )
                    && $transaction->transaction_date->lte($asOf),
            )
            ->sum(
                fn ($transaction): float =>
                    (float) $transaction->fees,
            );

        $knownFundValue = $account->holdings
            ->filter(
                fn ($holding): bool =>
                    $holding->security?->expense_ratio !== null,
            )
            ->sum(
                fn ($holding): float =>
                    (float) $holding->market_value,
            );

        $totalHoldingValue = (float) $account->holdings
            ->sum('market_value');

        $fundExpenseCoverageRate = $totalHoldingValue > 0
            ? $knownFundValue / $totalHoldingValue
            : null;

        $totalCost =
            $advisoryFee
            + (float) $account->annual_account_fee
            + $fundExpenseCost
            + $transactionFees;

        return [
            'account_id' => $account->id,
            'account_name' => $account->name,
            'institution_name' =>
                $account->institution?->name ?? 'Manual account',
            'portfolio_value' => round($portfolioValue, 2),
            'advisory_fee_base' => round($advisoryFeeBase, 2),
            'advisory_fee_rate' => $advisoryFeeRate,
            'advisory_fee' => round($advisoryFee, 2),
            'account_fee' => round(
                (float) $account->annual_account_fee,
                2,
            ),
            'fund_expense_cost' => round($fundExpenseCost, 2),
            'transaction_fees' => round(
                (float) $transactionFees,
                2,
            ),
            'total_cost' => round($totalCost, 2),
            'cost_rate' => $portfolioValue > 0
                ? $totalCost / $portfolioValue
                : null,
            'fund_expense_coverage_rate' =>
                $fundExpenseCoverageRate,
            'missing_advisory_fee' =>
                $account->annual_advisory_fee_rate === null,
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $accountResults
     * @return array<int, string>
     */
    private function buildWarnings(Collection $accountResults): array
    {
        $warnings = [];

        if ($accountResults->contains('missing_advisory_fee', true)) {
            $warnings[] =
                'One or more accounts do not have an advisory fee entered.';
        }

        if (
            $accountResults->contains(
                fn (array $account): bool =>
                    $account['fund_expense_coverage_rate'] !== null
                    && $account['fund_expense_coverage_rate'] < 0.80,
            )
        ) {
            $warnings[] =
                'Expense-ratio data covers less than 80% of at least one account.';
        }

        return $warnings;
    }
}
