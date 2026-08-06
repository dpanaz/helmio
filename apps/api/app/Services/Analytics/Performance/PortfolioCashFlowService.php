<?php

namespace App\Services\Analytics\Performance;

use App\Models\InvestmentTransaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PortfolioCashFlowService
{
    public function __construct(
        private readonly CashFlowClassifier $classifier
    ) {
    }

    /**
     * Calculate the user's net external cash flow for one date.
     */
    public function forUserOnDate(
        User $user,
        CarbonInterface $date
    ): array {
        $transactions = InvestmentTransaction::query()
            ->whereHas(
                'investmentAccount',
                fn ($query) => $query->where(
                    'user_id',
                    $user->id
                )
            )
            ->whereDate(
                'transaction_date',
                $date->toDateString()
            )
            ->get();

        return $this->summarize($transactions);
    }

    /**
     * Calculate an account's external cash flow for one date.
     */
    public function forAccountOnDate(
        int $investmentAccountId,
        CarbonInterface $date
    ): array {
        $transactions = InvestmentTransaction::query()
            ->where(
                'investment_account_id',
                $investmentAccountId
            )
            ->whereDate(
                'transaction_date',
                $date->toDateString()
            )
            ->get();

        return $this->summarize($transactions);
    }

    /**
     * Calculate external cash flows over a date range.
     */
    public function forUserBetween(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        $transactions = InvestmentTransaction::query()
            ->whereHas(
                'investmentAccount',
                fn ($query) => $query->where(
                    'user_id',
                    $user->id
                )
            )
            ->whereBetween(
                'transaction_date',
                [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ]
            )
            ->orderBy('transaction_date')
            ->get();

        return $this->summarize($transactions);
    }

    private function summarize(
        Collection $transactions
    ): array {
        $externalInflows = 0.0;
        $externalOutflows = 0.0;
        $unknownTransactions = [];

        foreach ($transactions as $transaction) {
            $classification = $this->classifier->classify(
                $transaction
            );

            $signedAmount =
                $this->classifier->signedExternalCashFlow(
                    $transaction
                );

            if (
                $classification
                === CashFlowClassifier::EXTERNAL_INFLOW
            ) {
                $externalInflows += $signedAmount;
            }

            if (
                $classification
                === CashFlowClassifier::EXTERNAL_OUTFLOW
            ) {
                $externalOutflows += abs($signedAmount);
            }

            if (
                $classification
                === CashFlowClassifier::UNKNOWN
            ) {
                $unknownTransactions[] = [
                    'id' => $transaction->id,
                    'transaction_type' =>
                        $transaction->transaction_type,

                    'transaction_date' =>
                        $transaction->transaction_date
                            ?->toDateString(),

                    'net_amount' =>
                        (float) ($transaction->net_amount ?? 0),
                ];
            }
        }

        return [
            'external_inflows' => round(
                $externalInflows,
                2
            ),

            'external_outflows' => round(
                $externalOutflows,
                2
            ),

            'net_external_cash_flow' => round(
                $externalInflows - $externalOutflows,
                2
            ),

            'transaction_count' => $transactions->count(),

            'external_transaction_count' =>
                $transactions
                    ->filter(
                        fn ($transaction) =>
                            $this->classifier
                                ->isExternalCashFlow($transaction)
                    )
                    ->count(),

            'unknown_transaction_count' =>
                count($unknownTransactions),

            'unknown_transactions' =>
                $unknownTransactions,
        ];
    }
}