<?php

namespace App\Services\Analytics\Performance;

use App\Models\Holding;
use App\Models\InvestmentTransaction;
use Carbon\CarbonInterface;

class HistoricalQuantityService
{
    /**
     * Reconstruct the quantity owned for a holding on a given date.
     *
     * This starts with the current holding quantity and reverses
     * transactions that occurred after the requested date.
     */
    public function quantityOnDate(
        Holding $holding,
        CarbonInterface $date
    ): float {
        $quantity = (float) $holding->quantity;

        $transactions = InvestmentTransaction::query()
            ->where(
                'investment_account_id',
                $holding->investment_account_id
            )
            ->where(
                'security_id',
                $holding->security_id
            )
            ->whereDate(
                'transaction_date',
                '>',
                $date->toDateString()
            )
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        foreach ($transactions as $transaction) {
            $quantity = $this->reverseTransaction(
                currentQuantity: $quantity,
                transaction: $transaction,
            );
        }

        return max(0, round($quantity, 8));
    }

    private function reverseTransaction(
        float $currentQuantity,
        InvestmentTransaction $transaction
    ): float {
        $type = $this->normalizeType(
            $transaction->transaction_type
        );

        $quantity = abs(
            (float) ($transaction->quantity ?? 0)
        );

        return match ($type) {
            /*
             * A buy increased quantity after the historical date,
             * so reverse it by subtracting shares.
             */
            'buy',
            'purchase',
            'reinvestment',
            'dividend_reinvestment',
            'transfer_in',
            'security_transfer_in'
                => $currentQuantity - $quantity,

            /*
             * A sale reduced quantity after the historical date,
             * so reverse it by adding shares back.
             */
            'sell',
            'sale',
            'transfer_out',
            'security_transfer_out'
                => $currentQuantity + $quantity,

            /*
             * Stock splits require reversing the split ratio.
             * The ratio should be stored in metadata as split_ratio.
             *
             * Example:
             * 4-for-1 split => split_ratio = 4
             */
            'stock_split'
                => $this->reverseSplit(
                    currentQuantity: $currentQuantity,
                    transaction: $transaction,
                ),

            default => $currentQuantity,
        };
    }

    private function reverseSplit(
        float $currentQuantity,
        InvestmentTransaction $transaction
    ): float {
        $ratio = (float) data_get(
            $transaction->metadata,
            'split_ratio',
            1
        );

        if ($ratio <= 0) {
            return $currentQuantity;
        }

        return $currentQuantity / $ratio;
    }

    private function normalizeType(
        ?string $type
    ): string {
        return strtolower(
            trim(
                str_replace(
                    ['-', ' '],
                    '_',
                    $type ?? ''
                )
            )
        );
    }
}
