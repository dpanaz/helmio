<?php

namespace App\Services\Analytics\Performance;

use App\Models\InvestmentTransaction;

class CashFlowClassifier
{
    public const EXTERNAL_INFLOW = 'external_inflow';

    public const EXTERNAL_OUTFLOW = 'external_outflow';

    public const INTERNAL_ACTIVITY = 'internal_activity';

    public const UNKNOWN = 'unknown';

    /**
     * Classify a transaction based on its normalized transaction type.
     */
    public function classify(
        InvestmentTransaction $transaction
    ): string {
        $type = $this->normalizeType(
            $transaction->transaction_type
        );

        if (in_array($type, $this->externalInflows(), true)) {
            return self::EXTERNAL_INFLOW;
        }

        if (in_array($type, $this->externalOutflows(), true)) {
            return self::EXTERNAL_OUTFLOW;
        }

        if (in_array($type, $this->internalActivity(), true)) {
            return self::INTERNAL_ACTIVITY;
        }

        return self::UNKNOWN;
    }

    /**
     * Return the signed external cash-flow amount.
     *
     * Positive = money entering the portfolio.
     * Negative = money leaving the portfolio.
     * Internal activity = zero.
     */
    public function signedExternalCashFlow(
        InvestmentTransaction $transaction
    ): float {
        $classification = $this->classify($transaction);

        $amount = abs(
            $this->transactionAmount($transaction)
        );

        return match ($classification) {
            self::EXTERNAL_INFLOW => $amount,
            self::EXTERNAL_OUTFLOW => -$amount,
            default => 0.0,
        };
    }

    public function isExternalCashFlow(
        InvestmentTransaction $transaction
    ): bool {
        return in_array(
            $this->classify($transaction),
            [
                self::EXTERNAL_INFLOW,
                self::EXTERNAL_OUTFLOW,
            ],
            true
        );
    }

    private function transactionAmount(
        InvestmentTransaction $transaction
    ): float {
        foreach ([
            'net_amount',
            'gross_amount',
            'amount',
        ] as $attribute) {
            $value = $transaction->getAttribute($attribute);

            if ($value !== null) {
                return (float) $value;
            }
        }

        return 0.0;
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

    private function externalInflows(): array
    {
        return [
            'deposit',
            'cash_deposit',
            'contribution',
            'employee_contribution',
            'employer_contribution',
            'rollover_in',
            'transfer_in',
            'wire_in',
            'ach_in',
            'journal_in',
            'cash_in',
            'external_transfer_in',
        ];
    }

    private function externalOutflows(): array
    {
        return [
            'withdrawal',
            'cash_withdrawal',
            'distribution',
            'required_minimum_distribution',
            'rmd',
            'rollover_out',
            'transfer_out',
            'wire_out',
            'ach_out',
            'journal_out',
            'cash_out',
            'external_transfer_out',
        ];
    }

    private function internalActivity(): array
    {
        return [
            'buy',
            'purchase',
            'sell',
            'sale',
            'dividend',
            'qualified_dividend',
            'non_qualified_dividend',
            'interest',
            'capital_gain',
            'capital_gain_distribution',
            'reinvestment',
            'dividend_reinvestment',
            'fee',
            'advisory_fee',
            'management_fee',
            'account_fee',
            'transaction_fee',
            'commission',
            'tax',
            'tax_withholding',
            'tax_withheld',
            'return_of_capital',
            'stock_split',
            'merger',
            'spin_off',
            'security_transfer',
        ];
    }
}