<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class TaxEfficiencyAnalyticsService
{
    public const FORMULA_VERSION = 'tax-efficiency-1.0.0';

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(
        Collection $accounts,
        ?CarbonInterface $asOf = null,
    ): array {
        $asOf ??= now();

        $periodStart = $asOf
            ->copy()
            ->subYear()
            ->startOfDay();

        $taxableAccounts = $accounts
            ->filter(
                fn (InvestmentAccount $account): bool =>
                    $this->isTaxableAccount($account),
            )
            ->values();

        if ($taxableAccounts->isEmpty()) {
            return $this->emptyResult();
        }

        $transactions = $taxableAccounts
            ->flatMap(
                fn (InvestmentAccount $account): Collection =>
                    $account->transactions->map(
                        fn (InvestmentTransaction $transaction): array => [
                            'account' => $account,
                            'transaction' => $transaction,
                        ],
                    ),
            )
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->transaction_date !== null
                    && $item['transaction']->transaction_date->gte($periodStart)
                    && $item['transaction']->transaction_date->lte($asOf),
            )
            ->values();

        $sales = $transactions->filter(
            fn (array $item): bool =>
                $item['transaction']->transaction_type === 'sell',
        );

        $shortTermGains = (float) $sales
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->realized_gain_loss !== null
                    && (float) $item['transaction']->realized_gain_loss > 0
                    && (
                        $item['transaction']->holding_period_days === null
                        || $item['transaction']->holding_period_days <= 365
                    ),
            )
            ->sum(
                fn (array $item): float =>
                    (float) $item['transaction']->realized_gain_loss,
            );

        $longTermGains = (float) $sales
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->realized_gain_loss !== null
                    && (float) $item['transaction']->realized_gain_loss > 0
                    && $item['transaction']->holding_period_days !== null
                    && $item['transaction']->holding_period_days > 365,
            )
            ->sum(
                fn (array $item): float =>
                    (float) $item['transaction']->realized_gain_loss,
            );

        $realizedLosses = abs(
            (float) $sales
                ->filter(
                    fn (array $item): bool =>
                        $item['transaction']->realized_gain_loss !== null
                        && (float) $item['transaction']->realized_gain_loss < 0,
                )
                ->sum(
                    fn (array $item): float =>
                        (float) $item['transaction']->realized_gain_loss,
                ),
        );

        $ordinaryDividends = (float) $transactions
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->transaction_type === 'dividend'
                    && ! $item['transaction']->is_qualified_dividend
                    && ! $item['transaction']->is_tax_exempt,
            )
            ->sum(
                fn (array $item): float =>
                    max(0, (float) $item['transaction']->gross_amount),
            );

        $qualifiedDividends = (float) $transactions
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->transaction_type === 'dividend'
                    && $item['transaction']->is_qualified_dividend,
            )
            ->sum(
                fn (array $item): float =>
                    max(0, (float) $item['transaction']->gross_amount),
            );

        $taxableInterest = (float) $transactions
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->transaction_type === 'interest'
                    && ! $item['transaction']->is_tax_exempt,
            )
            ->sum(
                fn (array $item): float =>
                    max(0, (float) $item['transaction']->gross_amount),
            );

        $taxExemptIncome = (float) $transactions
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->is_tax_exempt,
            )
            ->sum(
                fn (array $item): float =>
                    max(0, (float) $item['transaction']->gross_amount),
            );

        $taxWithheld = (float) $transactions->sum(
            fn (array $item): float =>
                (float) $item['transaction']->tax_withheld,
        );

        $taxablePortfolioValue = (float) $taxableAccounts->sum(
            fn (InvestmentAccount $account): float =>
                (float) $account->current_value,
        );

        $shortTermGainRate = $taxablePortfolioValue > 0
            ? $shortTermGains / $taxablePortfolioValue
            : null;

        $ordinaryIncome = $ordinaryDividends + $taxableInterest;

        $ordinaryIncomeRate = $taxablePortfolioValue > 0
            ? $ordinaryIncome / $taxablePortfolioValue
            : null;

        $washSaleIndicators = $this->detectWashSaleIndicators(
            $transactions,
        );

        $highTurnoverAccounts = $this->findHighTurnoverAccounts(
            $taxableAccounts,
            $periodStart,
            $asOf,
        );

        $scoreResult = $this->calculateScore(
            shortTermGains: $shortTermGains,
            longTermGains: $longTermGains,
            realizedLosses: $realizedLosses,
            ordinaryIncome: $ordinaryIncome,
            shortTermGainRate: $shortTermGainRate,
            ordinaryIncomeRate: $ordinaryIncomeRate,
            washSaleCount: $washSaleIndicators->count(),
            highTurnoverAccountCount: $highTurnoverAccounts->count(),
            taxablePortfolioValue: $taxablePortfolioValue,
        );

        return [
            'score' => $scoreResult['score'],
            'label' => $scoreResult['label'],
            'reasons' => $scoreResult['reasons'],
            'recommendations' => $scoreResult['recommendations'],

            'metrics' => [
                'taxable_account_count' => $taxableAccounts->count(),
                'taxable_portfolio_value' =>
                    round($taxablePortfolioValue, 2),
                'short_term_gains' =>
                    round($shortTermGains, 2),
                'long_term_gains' =>
                    round($longTermGains, 2),
                'realized_losses' =>
                    round($realizedLosses, 2),
                'ordinary_dividends' =>
                    round($ordinaryDividends, 2),
                'qualified_dividends' =>
                    round($qualifiedDividends, 2),
                'taxable_interest' =>
                    round($taxableInterest, 2),
                'tax_exempt_income' =>
                    round($taxExemptIncome, 2),
                'tax_withheld' =>
                    round($taxWithheld, 2),
                'short_term_gain_rate' =>
                    $shortTermGainRate,
                'ordinary_income_rate' =>
                    $ordinaryIncomeRate,
                'wash_sale_indicator_count' =>
                    $washSaleIndicators->count(),
                'high_turnover_account_count' =>
                    $highTurnoverAccounts->count(),
            ],

            'wash_sale_indicators' =>
                $washSaleIndicators,

            'high_turnover_accounts' =>
                $highTurnoverAccounts,

            'account_summary' =>
                $this->buildAccountSummary(
                    $taxableAccounts,
                    $periodStart,
                    $asOf,
                ),

            'period_start' =>
                $periodStart->toDateString(),

            'period_end' =>
                $asOf->toDateString(),

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function isTaxableAccount(
        InvestmentAccount $account,
    ): bool {
        return in_array(
            $account->account_type,
            [
                'individual',
                'joint',
                'trust',
                'other',
            ],
            true,
        );
    }

    /**
     * Heuristic only:
     * a purchase of the same security within 30 days before or
     * after a sale recorded with a realized loss.
     *
     * @param Collection<int, array<string, mixed>> $transactions
     * @return Collection<int, array<string, mixed>>
     */
    private function detectWashSaleIndicators(
        Collection $transactions,
    ): Collection {
        $indicators = collect();

        $grouped = $transactions
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->security_id !== null,
            )
            ->groupBy(
                fn (array $item): string =>
                    $item['account']->id
                    .'-'
                    .$item['transaction']->security_id,
            );

        foreach ($grouped as $items) {
            $buys = $items->filter(
                fn (array $item): bool =>
                    $item['transaction']->transaction_type === 'buy',
            );

            $lossSales = $items->filter(
                fn (array $item): bool =>
                    $item['transaction']->transaction_type === 'sell'
                    && $item['transaction']->realized_gain_loss !== null
                    && (float) $item['transaction']->realized_gain_loss < 0,
            );

            foreach ($lossSales as $saleItem) {
                $sale = $saleItem['transaction'];

                $matchingBuy = $buys
                    ->filter(
                        function (array $buyItem) use ($sale): bool {
                            $difference = abs(
                                $buyItem['transaction']
                                    ->transaction_date
                                    ->diffInDays($sale->transaction_date),
                            );

                            return $difference <= 30;
                        },
                    )
                    ->sortBy(
                        fn (array $buyItem): int =>
                            abs(
                                $buyItem['transaction']
                                    ->transaction_date
                                    ->diffInDays($sale->transaction_date),
                            ),
                    )
                    ->first();

                if ($matchingBuy === null) {
                    continue;
                }

                $buy = $matchingBuy['transaction'];

                $indicators->push([
                    'account_id' => $saleItem['account']->id,
                    'account_name' => $saleItem['account']->name,
                    'security_id' => $sale->security_id,
                    'symbol' => $sale->security?->symbol,
                    'name' => $sale->security?->name
                        ?? 'Unclassified security',
                    'sale_date' =>
                        $sale->transaction_date->toDateString(),
                    'purchase_date' =>
                        $buy->transaction_date->toDateString(),
                    'realized_loss' =>
                        round(abs((float) $sale->realized_gain_loss), 2),
                    'days_between' =>
                        abs(
                            $buy->transaction_date
                                ->diffInDays($sale->transaction_date),
                        ),
                ]);
            }
        }

        return $indicators->values();
    }

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return Collection<int, array<string, mixed>>
     */
    private function findHighTurnoverAccounts(
        Collection $accounts,
        CarbonInterface $periodStart,
        CarbonInterface $asOf,
    ): Collection {
        return $accounts
            ->map(
                function (
                    InvestmentAccount $account,
                ) use ($periodStart, $asOf): array {
                    $transactions = $account->transactions
                        ->filter(
                            fn (InvestmentTransaction $transaction): bool =>
                                $transaction->transaction_date !== null
                                && $transaction->transaction_date->gte($periodStart)
                                && $transaction->transaction_date->lte($asOf),
                        );

                    $purchases = (float) $transactions
                        ->where('transaction_type', 'buy')
                        ->sum(
                            fn (InvestmentTransaction $transaction): float =>
                                abs((float) $transaction->gross_amount),
                        );

                    $sales = (float) $transactions
                        ->where('transaction_type', 'sell')
                        ->sum(
                            fn (InvestmentTransaction $transaction): float =>
                                abs((float) $transaction->gross_amount),
                        );

                    $value = (float) $account->current_value;

                    $turnover = $value > 0
                        ? min($purchases, $sales) / $value
                        : null;

                    return [
                        'account_id' => $account->id,
                        'account_name' => $account->name,
                        'turnover_rate' => $turnover,
                        'purchase_value' => round($purchases, 2),
                        'sales_value' => round($sales, 2),
                    ];
                },
            )
            ->filter(
                fn (array $account): bool =>
                    $account['turnover_rate'] !== null
                    && $account['turnover_rate'] > 0.50,
            )
            ->sortByDesc('turnover_rate')
            ->values();
    }

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return Collection<int, array<string, mixed>>
     */
    private function buildAccountSummary(
        Collection $accounts,
        CarbonInterface $periodStart,
        CarbonInterface $asOf,
    ): Collection {
        return $accounts
            ->map(
                function (
                    InvestmentAccount $account,
                ) use ($periodStart, $asOf): array {
                    $transactions = $account->transactions
                        ->filter(
                            fn (InvestmentTransaction $transaction): bool =>
                                $transaction->transaction_date !== null
                                && $transaction->transaction_date->gte($periodStart)
                                && $transaction->transaction_date->lte($asOf),
                        );

                    $realizedGainLoss = (float) $transactions
                        ->whereNotNull('realized_gain_loss')
                        ->sum(
                            fn (InvestmentTransaction $transaction): float =>
                                (float) $transaction->realized_gain_loss,
                        );

                    $taxableIncome = (float) $transactions
                        ->filter(
                            fn (InvestmentTransaction $transaction): bool =>
                                in_array(
                                    $transaction->transaction_type,
                                    ['dividend', 'interest', 'distribution'],
                                    true,
                                )
                                && ! $transaction->is_tax_exempt,
                        )
                        ->sum(
                            fn (InvestmentTransaction $transaction): float =>
                                max(0, (float) $transaction->gross_amount),
                        );

                    return [
                        'account_id' => $account->id,
                        'account_name' => $account->name,
                        'account_type' => $account->account_type,
                        'current_value' =>
                            round((float) $account->current_value, 2),
                        'realized_gain_loss' =>
                            round($realizedGainLoss, 2),
                        'taxable_income' =>
                            round($taxableIncome, 2),
                        'tax_withheld' =>
                            round(
                                (float) $transactions->sum(
                                    fn (InvestmentTransaction $transaction): float =>
                                        (float) $transaction->tax_withheld,
                                ),
                                2,
                            ),
                    ];
                },
            )
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateScore(
        float $shortTermGains,
        float $longTermGains,
        float $realizedLosses,
        float $ordinaryIncome,
        ?float $shortTermGainRate,
        ?float $ordinaryIncomeRate,
        int $washSaleCount,
        int $highTurnoverAccountCount,
        float $taxablePortfolioValue,
    ): array {
        if ($taxablePortfolioValue <= 0) {
            return [
                'score' => null,
                'label' => 'Insufficient data',
                'reasons' => [
                    'Taxable portfolio value is required.',
                ],
                'recommendations' => [
                    'Add taxable accounts and transaction history.',
                ],
            ];
        }

        $score = 100;
        $reasons = [];
        $recommendations = [];

        if ($shortTermGainRate !== null) {
            if ($shortTermGainRate > 0.10) {
                $score -= 30;
            } elseif ($shortTermGainRate > 0.05) {
                $score -= 20;
            } elseif ($shortTermGainRate > 0.02) {
                $score -= 10;
            }

            if ($shortTermGains > 0) {
                $reasons[] = sprintf(
                    'Short-term realized gains totaled $%s.',
                    number_format($shortTermGains, 2),
                );
            }
        }

        if ($ordinaryIncomeRate !== null) {
            if ($ordinaryIncomeRate > 0.05) {
                $score -= 15;
            } elseif ($ordinaryIncomeRate > 0.025) {
                $score -= 8;
            }

            if ($ordinaryIncome > 0) {
                $reasons[] = sprintf(
                    'Ordinary taxable dividends and interest totaled $%s.',
                    number_format($ordinaryIncome, 2),
                );
            }
        }

        if ($washSaleCount >= 3) {
            $score -= 25;
            $recommendations[] =
                'Review possible wash-sale activity with a qualified tax professional.';
        } elseif ($washSaleCount > 0) {
            $score -= min(15, $washSaleCount * 5);
            $recommendations[] =
                'Review possible wash-sale indicators before relying on realized losses.';
        }

        if ($washSaleCount > 0) {
            $reasons[] = sprintf(
                '%d possible wash-sale indicator%s detected.',
                $washSaleCount,
                $washSaleCount === 1 ? '' : 's',
            );
        }

        if ($highTurnoverAccountCount > 0) {
            $score -= min(20, $highTurnoverAccountCount * 8);

            $reasons[] = sprintf(
                '%d taxable account%s had estimated turnover above 50%%.',
                $highTurnoverAccountCount,
                $highTurnoverAccountCount === 1 ? '' : 's',
            );

            $recommendations[] =
                'Review whether trading in taxable accounts is creating avoidable short-term gains.';
        }

        if ($shortTermGains > $longTermGains && $shortTermGains > 0) {
            $score -= 10;

            $reasons[] =
                'Short-term gains exceeded long-term gains during the review period.';
        }

        if ($realizedLosses > 0) {
            $reasons[] = sprintf(
                'Realized losses totaled $%s.',
                number_format($realizedLosses, 2),
            );
        }

        $score = max(0, min(100, $score));

        if ($reasons === []) {
            $reasons[] =
                'No material tax-efficiency indicators were identified using the available transaction data.';
        }

        if ($recommendations === []) {
            $recommendations[] =
                'Continue monitoring short-term gains, taxable income and turnover in taxable accounts.';
        }

        return [
            'score' => $score,
            'label' => $this->scoreLabel($score),
            'reasons' => array_values(array_unique($reasons)),
            'recommendations' =>
                array_values(array_unique($recommendations)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(): array
    {
        return [
            'score' => null,
            'label' => 'Not applicable',
            'reasons' => [
                'No taxable investment accounts were identified.',
            ],
            'recommendations' => [],
            'metrics' => [
                'taxable_account_count' => 0,
            ],
            'wash_sale_indicators' => collect(),
            'high_turnover_accounts' => collect(),
            'account_summary' => collect(),
            'period_start' => now()->subYear()->toDateString(),
            'period_end' => now()->toDateString(),
            'formula_version' => self::FORMULA_VERSION,
        ];
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
