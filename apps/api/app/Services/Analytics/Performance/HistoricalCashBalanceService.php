<?php

namespace App\Services\Analytics\Performance;

use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use Carbon\CarbonInterface;

class HistoricalCashBalanceService
{
    /**
     * Reconstruct an account's non-security cash balance on a historical date.
     *
     * Start with the account's current stored cash balance and reverse every
     * transaction that occurred after the requested date.
     *
     * This uses the transaction's signed net amount because internal activity
     * such as buys, sells, dividends, fees, deposits, and withdrawals all
     * affect account cash even though only deposits/withdrawals are external
     * cash flows for TWR purposes.
     */
    public function balanceOnDate(
        InvestmentAccount $account,
        CarbonInterface $date,
    ): float {
        $currentCash =
            $this->currentCashBalance($account);

        $futureNetCashMovement =
            InvestmentTransaction::query()
                ->where(
                    'investment_account_id',
                    $account->id,
                )
                ->whereDate(
                    'transaction_date',
                    '>',
                    $date->toDateString(),
                )
                ->get()
                ->sum(
                    fn (
                        InvestmentTransaction $transaction
                    ): float =>
                        $this->signedTransactionAmount(
                            $transaction,
                        ),
                );

        return round(
            $currentCash
            - $futureNetCashMovement,
            2,
        );
    }

    private function currentCashBalance(
        InvestmentAccount $account,
    ): float {
        foreach (
            [
                'cash_balance',
                'cash_value',
                'available_cash',
            ] as $attribute
        ) {
            $value =
                $account->getAttribute(
                    $attribute,
                );

            if (
                $value !== null
                && is_numeric($value)
            ) {
                return (float) $value;
            }
        }

        return 0.0;
    }

    /**
     * Use the transaction ledger's signed amount.
     *
     * Expected normalized convention:
     * - deposits / sales / dividends: positive cash
     * - withdrawals / buys / fees: negative cash
     */
    private function signedTransactionAmount(
        InvestmentTransaction $transaction,
    ): float {
        foreach (
            [
                'net_amount',
                'gross_amount',
                'amount',
            ] as $attribute
        ) {
            $value =
                $transaction->getAttribute(
                    $attribute,
                );

            if (
                $value !== null
                && is_numeric($value)
            ) {
                return (float) $value;
            }
        }

        return 0.0;
    }
}