<?php

namespace App\Services\Analytics\Tax;

use App\Models\InvestmentTransaction;
use Illuminate\Support\Collection;

class TaxLotAnalyticsService
{
    /**
     * Analyze realized gains, dividends, tax withholding,
     * and holding-period classifications.
     *
     * @param Collection<int, InvestmentTransaction> $transactions
     */
    public function analyze(Collection $transactions): array
    {
        if ($transactions->isEmpty()) {
            return $this->emptyResult();
        }

        $realizedShortTermGainLoss = 0.0;
        $realizedLongTermGainLoss = 0.0;

        $qualifiedDividends = 0.0;
        $nonQualifiedDividends = 0.0;
        $taxExemptIncome = 0.0;

        $taxWithheld = 0.0;

        $realizedTransactionCount = 0;
        $shortTermTransactionCount = 0;
        $longTermTransactionCount = 0;

        $dividendTransactionCount = 0;

        $unknownHoldingPeriodCount = 0;

        foreach ($transactions as $transaction) {
            $type = $this->normalizeType(
                $transaction->transaction_type
            );

            $taxWithheld += abs(
                (float) ($transaction->tax_withheld ?? 0)
            );

            if ($this->isRealizedGainTransaction($type)) {
                $realizedTransactionCount++;

                $gainLoss = (float) (
                    $transaction->realized_gain_loss
                    ?? 0
                );

                $holdingPeriodDays =
                    $transaction->holding_period_days;

                if ($holdingPeriodDays === null) {
                    $unknownHoldingPeriodCount++;

                    continue;
                }

                if ((int) $holdingPeriodDays >= 365) {
                    $realizedLongTermGainLoss +=
                        $gainLoss;

                    $longTermTransactionCount++;
                } else {
                    $realizedShortTermGainLoss +=
                        $gainLoss;

                    $shortTermTransactionCount++;
                }
            }

            if ($this->isDividendTransaction($type)) {
                $dividendTransactionCount++;

                $amount = abs(
                    $this->transactionIncomeAmount(
                        $transaction
                    )
                );

                if (
                    (bool) $transaction->is_tax_exempt
                ) {
                    $taxExemptIncome += $amount;

                    continue;
                }

                if (
                    (bool) $transaction
                        ->is_qualified_dividend
                ) {
                    $qualifiedDividends +=
                        $amount;
                } else {
                    $nonQualifiedDividends +=
                        $amount;
                }
            }
        }

        $totalRealizedGainLoss =
            $realizedShortTermGainLoss
            + $realizedLongTermGainLoss;

        $totalTaxableDividends =
            $qualifiedDividends
            + $nonQualifiedDividends;

        $totalIncome =
            $totalTaxableDividends
            + $taxExemptIncome;

        return [
            'status' => 'complete',

            'metrics' => [
                'realized_short_term_gain_loss' =>
                    round(
                        $realizedShortTermGainLoss,
                        2
                    ),

                'realized_long_term_gain_loss' =>
                    round(
                        $realizedLongTermGainLoss,
                        2
                    ),

                'total_realized_gain_loss' =>
                    round(
                        $totalRealizedGainLoss,
                        2
                    ),

                'qualified_dividends' =>
                    round(
                        $qualifiedDividends,
                        2
                    ),

                'non_qualified_dividends' =>
                    round(
                        $nonQualifiedDividends,
                        2
                    ),

                'tax_exempt_income' =>
                    round(
                        $taxExemptIncome,
                        2
                    ),

                'total_taxable_dividends' =>
                    round(
                        $totalTaxableDividends,
                        2
                    ),

                'total_income' =>
                    round(
                        $totalIncome,
                        2
                    ),

                'tax_withheld' =>
                    round(
                        $taxWithheld,
                        2
                    ),
            ],

            'counts' => [
                'transaction_count' =>
                    $transactions->count(),

                'realized_transaction_count' =>
                    $realizedTransactionCount,

                'short_term_transaction_count' =>
                    $shortTermTransactionCount,

                'long_term_transaction_count' =>
                    $longTermTransactionCount,

                'dividend_transaction_count' =>
                    $dividendTransactionCount,

                'unknown_holding_period_count' =>
                    $unknownHoldingPeriodCount,
            ],

            'flags' => $this->buildFlags(
                realizedShortTermGainLoss:
                    $realizedShortTermGainLoss,

                realizedLongTermGainLoss:
                    $realizedLongTermGainLoss,

                qualifiedDividends:
                    $qualifiedDividends,

                nonQualifiedDividends:
                    $nonQualifiedDividends,

                taxWithheld:
                    $taxWithheld,

                unknownHoldingPeriodCount:
                    $unknownHoldingPeriodCount,
            ),

            'formula_version' =>
                'tax-lot-0.1.0',
        ];
    }

    private function isRealizedGainTransaction(
        string $type
    ): bool {
        return in_array(
            $type,
            [
                'sell',
                'sale',
                'capital_gain',
                'capital_gain_distribution',
            ],
            true
        );
    }

    private function isDividendTransaction(
        string $type
    ): bool {
        return in_array(
            $type,
            [
                'dividend',
                'qualified_dividend',
                'non_qualified_dividend',
            ],
            true
        );
    }

    private function transactionIncomeAmount(
        InvestmentTransaction $transaction
    ): float {
        foreach (
            [
                'net_amount',
                'gross_amount',
            ] as $attribute
        ) {
            $value =
                $transaction->getAttribute(
                    $attribute
                );

            if ($value !== null) {
                return (float) $value;
            }
        }

        return 0.0;
    }

    private function buildFlags(
        float $realizedShortTermGainLoss,
        float $realizedLongTermGainLoss,
        float $qualifiedDividends,
        float $nonQualifiedDividends,
        float $taxWithheld,
        int $unknownHoldingPeriodCount
    ): array {
        $flags = [];

        if (
            $realizedShortTermGainLoss
            > $realizedLongTermGainLoss
            && $realizedShortTermGainLoss > 0
        ) {
            $flags[] = [
                'code' =>
                    'short_term_gains_dominate',

                'severity' => 'moderate',

                'title' =>
                    'Short-term gains dominate',

                'message' =>
                    'More realized gains were classified as short-term than long-term.',
            ];
        }

        if (
            $nonQualifiedDividends
            > $qualifiedDividends
            && $nonQualifiedDividends > 0
        ) {
            $flags[] = [
                'code' =>
                    'non_qualified_dividend_heavy',

                'severity' => 'moderate',

                'title' =>
                    'Non-qualified dividends are elevated',

                'message' =>
                    'A larger share of dividend income was not marked as qualified.',
            ];
        }

        if (
            $taxWithheld > 0
        ) {
            $flags[] = [
                'code' =>
                    'tax_withholding_detected',

                'severity' =>
                    'informational',

                'title' =>
                    'Tax withholding detected',

                'message' =>
                    'Investment transactions included tax withholding during the selected period.',
            ];
        }

        if ($unknownHoldingPeriodCount > 0) {
            $flags[] = [
                'code' =>
                    'missing_holding_period_data',

                'severity' => 'moderate',

                'title' =>
                    'Holding-period data is incomplete',

                'message' =>
                    "{$unknownHoldingPeriodCount} realized transaction(s) could not be classified as short-term or long-term.",
            ];
        }

        if ($flags === []) {
            $flags[] = [
                'code' =>
                    'no_major_tax_flags',

                'severity' =>
                    'informational',

                'title' =>
                    'No major tax concerns detected',

                'message' =>
                    'The available transaction data did not exceed Helmio’s current tax-warning thresholds.',
            ];
        }

        return $flags;
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

    private function emptyResult(): array
    {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                'No investment transactions were found.',

            'metrics' => [
                'realized_short_term_gain_loss' => 0,
                'realized_long_term_gain_loss' => 0,
                'total_realized_gain_loss' => 0,
                'qualified_dividends' => 0,
                'non_qualified_dividends' => 0,
                'tax_exempt_income' => 0,
                'total_taxable_dividends' => 0,
                'total_income' => 0,
                'tax_withheld' => 0,
            ],

            'counts' => [
                'transaction_count' => 0,
                'realized_transaction_count' => 0,
                'short_term_transaction_count' => 0,
                'long_term_transaction_count' => 0,
                'dividend_transaction_count' => 0,
                'unknown_holding_period_count' => 0,
            ],

            'flags' => [],

            'formula_version' =>
                'tax-lot-0.1.0',
        ];
    }
}