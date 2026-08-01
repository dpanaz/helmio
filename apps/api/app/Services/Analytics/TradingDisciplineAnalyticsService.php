<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class TradingDisciplineAnalyticsService
{
    public const FORMULA_VERSION = 'trading-discipline-1.0.0';

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

        $transactions = $accounts
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
                    && $item['transaction']->transaction_date->gte(
                        $periodStart,
                    )
                    && $item['transaction']->transaction_date->lte(
                        $asOf,
                    ),
            )
            ->values();

        $tradeTransactions = $transactions
            ->filter(
                fn (array $item): bool =>
                    in_array(
                        $item['transaction']->transaction_type,
                        ['buy', 'sell'],
                        true,
                    ),
            )
            ->values();

        $purchases = $tradeTransactions
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->transaction_type === 'buy',
            );

        $sales = $tradeTransactions
            ->filter(
                fn (array $item): bool =>
                    $item['transaction']->transaction_type === 'sell',
            );

        $purchaseValue = (float) $purchases->sum(
            fn (array $item): float =>
                abs((float) $item['transaction']->gross_amount),
        );

        $salesValue = (float) $sales->sum(
            fn (array $item): float =>
                abs((float) $item['transaction']->gross_amount),
        );

        $portfolioValue = (float) $accounts->sum(
            fn (InvestmentAccount $account): float =>
                (float) $account->current_value,
        );

        /*
         * Initial turnover methodology:
         *
         * lesser of total purchases or total sales
         * divided by current portfolio value.
         *
         * This is an indicator, not a regulatory conclusion.
         */
        $turnoverRate = $portfolioValue > 0
            ? min($purchaseValue, $salesValue) / $portfolioValue
            : null;

        $tradingFees = (float) $tradeTransactions->sum(
            fn (array $item): float =>
                (float) $item['transaction']->fees,
        );

        $allTransactionFees = (float) $transactions->sum(
            fn (array $item): float =>
                (float) $item['transaction']->fees,
        );

        $tradeCount = $tradeTransactions->count();
        $tradesPerMonth = $tradeCount / 12;

        $securityActivity = $this->buildSecurityActivity(
            $tradeTransactions,
        );

        $roundTripIndicators = $securityActivity
            ->filter(
                fn (array $activity): bool =>
                    $activity['buy_count'] > 0
                    && $activity['sell_count'] > 0,
            )
            ->values();

        $shortHoldingIndicators = $this->detectShortHoldingIndicators(
            $tradeTransactions,
        );

        $sameSecurityRepeatCount = $securityActivity
            ->filter(
                fn (array $activity): bool =>
                    $activity['trade_count'] >= 4,
            )
            ->count();

        $scoreResult = $this->calculateScore(
            portfolioValue: $portfolioValue,
            turnoverRate: $turnoverRate,
            tradeCount: $tradeCount,
            tradesPerMonth: $tradesPerMonth,
            tradingFees: $tradingFees,
            roundTripCount: $roundTripIndicators->count(),
            shortHoldingCount: $shortHoldingIndicators->count(),
            repeatedSecurityCount: $sameSecurityRepeatCount,
        );

        return [
            'score' => $scoreResult['score'],
            'label' => $scoreResult['label'],
            'reasons' => $scoreResult['reasons'],
            'recommendations' =>
                $scoreResult['recommendations'],

            'metrics' => [
                'portfolio_value' => round($portfolioValue, 2),
                'purchase_value' => round($purchaseValue, 2),
                'sales_value' => round($salesValue, 2),
                'turnover_rate' => $turnoverRate,
                'trade_count' => $tradeCount,
                'buy_count' => $purchases->count(),
                'sell_count' => $sales->count(),
                'trades_per_month' => $tradesPerMonth,
                'trading_fees' => round($tradingFees, 2),
                'all_transaction_fees' =>
                    round($allTransactionFees, 2),
                'round_trip_indicator_count' =>
                    $roundTripIndicators->count(),
                'short_holding_indicator_count' =>
                    $shortHoldingIndicators->count(),
                'repeated_security_count' =>
                    $sameSecurityRepeatCount,
            ],

            'security_activity' => $securityActivity,
            'round_trip_indicators' => $roundTripIndicators,
            'short_holding_indicators' =>
                $shortHoldingIndicators,

            'period_start' => $periodStart->toDateString(),
            'period_end' => $asOf->toDateString(),
            'formula_version' => self::FORMULA_VERSION,
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $tradeTransactions
     * @return Collection<int, array<string, mixed>>
     */
    private function buildSecurityActivity(
        Collection $tradeTransactions,
    ): Collection {
        return $tradeTransactions
            ->groupBy(
                fn (array $item): string =>
                    (string) (
                        $item['transaction']->security_id
                        ?? 'unclassified'
                    ),
            )
            ->map(
                function (
                    Collection $items,
                    string $securityKey,
                ): array {
                    $first = $items->first();
                    $security = $first['transaction']->security;

                    $buys = $items->filter(
                        fn (array $item): bool =>
                            $item['transaction']->transaction_type
                            === 'buy',
                    );

                    $sells = $items->filter(
                        fn (array $item): bool =>
                            $item['transaction']->transaction_type
                            === 'sell',
                    );

                    return [
                        'security_id' =>
                            $securityKey === 'unclassified'
                                ? null
                                : (int) $securityKey,

                        'symbol' => $security?->symbol,
                        'name' =>
                            $security?->name
                            ?? 'Unclassified security',

                        'trade_count' => $items->count(),
                        'buy_count' => $buys->count(),
                        'sell_count' => $sells->count(),

                        'purchase_value' => round(
                            (float) $buys->sum(
                                fn (array $item): float =>
                                    abs(
                                        (float) $item[
                                            'transaction'
                                        ]->gross_amount,
                                    ),
                            ),
                            2,
                        ),

                        'sales_value' => round(
                            (float) $sells->sum(
                                fn (array $item): float =>
                                    abs(
                                        (float) $item[
                                            'transaction'
                                        ]->gross_amount,
                                    ),
                            ),
                            2,
                        ),

                        'fees' => round(
                            (float) $items->sum(
                                fn (array $item): float =>
                                    (float) $item[
                                        'transaction'
                                    ]->fees,
                            ),
                            2,
                        ),

                        'first_trade_date' => $items
                            ->min(
                                fn (array $item) =>
                                    $item[
                                        'transaction'
                                    ]->transaction_date,
                            )
                            ?->toDateString(),

                        'last_trade_date' => $items
                            ->max(
                                fn (array $item) =>
                                    $item[
                                        'transaction'
                                    ]->transaction_date,
                            )
                            ?->toDateString(),
                    ];
                },
            )
            ->sortByDesc('trade_count')
            ->values();
    }

    /**
     * This is a heuristic, not tax-lot accounting.
     *
     * A short-holding indicator is generated when a sale occurs
     * within 30 calendar days after an earlier purchase of the
     * same security in the same account.
     *
     * @param Collection<int, array<string, mixed>> $tradeTransactions
     * @return Collection<int, array<string, mixed>>
     */
    private function detectShortHoldingIndicators(
        Collection $tradeTransactions,
    ): Collection {
        $indicators = collect();

        $grouped = $tradeTransactions->groupBy(
            fn (array $item): string =>
                $item['account']->id
                .'-'
                .($item['transaction']->security_id ?? 'none'),
        );

        foreach ($grouped as $items) {
            $sorted = $items
                ->sortBy(
                    fn (array $item) =>
                        $item[
                            'transaction'
                        ]->transaction_date->timestamp,
                )
                ->values();

            $buys = collect();

            foreach ($sorted as $item) {
                $transaction = $item['transaction'];

                if ($transaction->transaction_type === 'buy') {
                    $buys->push($item);

                    continue;
                }

                if ($transaction->transaction_type !== 'sell') {
                    continue;
                }

                $recentBuy = $buys
                    ->filter(
                        fn (array $buy): bool =>
                            $buy[
                                'transaction'
                            ]->transaction_date->lte(
                                $transaction->transaction_date,
                            ),
                    )
                    ->sortByDesc(
                        fn (array $buy) =>
                            $buy[
                                'transaction'
                            ]->transaction_date->timestamp,
                    )
                    ->first();

                if ($recentBuy === null) {
                    continue;
                }

                $daysHeld = $recentBuy['transaction']
                    ->transaction_date
                    ->diffInDays(
                        $transaction->transaction_date,
                    );

                if ($daysHeld > 30) {
                    continue;
                }

                $indicators->push([
                    'account_id' => $item['account']->id,
                    'account_name' => $item['account']->name,
                    'security_id' =>
                        $transaction->security_id,
                    'symbol' =>
                        $transaction->security?->symbol,
                    'name' =>
                        $transaction->security?->name
                        ?? 'Unclassified security',
                    'buy_date' => $recentBuy['transaction']
                        ->transaction_date
                        ->toDateString(),
                    'sell_date' =>
                        $transaction->transaction_date
                            ->toDateString(),
                    'days_between' => $daysHeld,
                    'sale_amount' => round(
                        abs((float) $transaction->gross_amount),
                        2,
                    ),
                ]);
            }
        }

        return $indicators->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateScore(
        float $portfolioValue,
        ?float $turnoverRate,
        int $tradeCount,
        float $tradesPerMonth,
        float $tradingFees,
        int $roundTripCount,
        int $shortHoldingCount,
        int $repeatedSecurityCount,
    ): array {
        if ($portfolioValue <= 0) {
            return [
                'score' => null,
                'label' => 'Insufficient data',
                'reasons' => [
                    'Portfolio value is required to calculate turnover.',
                ],
                'recommendations' => [
                    'Add current account values and transaction history.',
                ],
            ];
        }

        $score = 100;
        $reasons = [];
        $recommendations = [];

        if ($turnoverRate !== null) {
            if ($turnoverRate > 2.00) {
                $score -= 45;
                $reasons[] = sprintf(
                    'Estimated annual turnover is %.0f%%.',
                    $turnoverRate * 100,
                );
                $recommendations[] =
                    'Request a written explanation of the strategy, expected benefits and costs supporting the trading level.';
            } elseif ($turnoverRate > 1.00) {
                $score -= 30;
                $reasons[] = sprintf(
                    'Estimated annual turnover is %.0f%%.',
                    $turnoverRate * 100,
                );
                $recommendations[] =
                    'Review whether the level of trading is consistent with the stated investment strategy.';
            } elseif ($turnoverRate > 0.50) {
                $score -= 15;
                $reasons[] = sprintf(
                    'Estimated annual turnover is %.0f%%.',
                    $turnoverRate * 100,
                );
            } elseif ($turnoverRate > 0.25) {
                $score -= 5;
                $reasons[] = sprintf(
                    'Estimated annual turnover is %.0f%%.',
                    $turnoverRate * 100,
                );
            }
        }

        if ($tradesPerMonth > 15) {
            $score -= 20;
            $reasons[] = sprintf(
                'The account averaged %.1f trades per month.',
                $tradesPerMonth,
            );
            $recommendations[] =
                'Compare trading frequency with the portfolio mandate and investor objectives.';
        } elseif ($tradesPerMonth > 8) {
            $score -= 12;
            $reasons[] = sprintf(
                'The account averaged %.1f trades per month.',
                $tradesPerMonth,
            );
        } elseif ($tradesPerMonth > 4) {
            $score -= 5;
            $reasons[] = sprintf(
                'The account averaged %.1f trades per month.',
                $tradesPerMonth,
            );
        }

        if ($shortHoldingCount >= 5) {
            $score -= 20;
            $reasons[] = sprintf(
                '%d sales occurred within 30 days of an earlier purchase of the same security.',
                $shortHoldingCount,
            );
            $recommendations[] =
                'Review the economic purpose and transaction costs associated with short holding periods.';
        } elseif ($shortHoldingCount > 0) {
            $score -= min(15, $shortHoldingCount * 3);
            $reasons[] = sprintf(
                '%d possible short-holding indicator%s detected.',
                $shortHoldingCount,
                $shortHoldingCount === 1 ? '' : 's',
            );
        }

        if ($roundTripCount >= 5) {
            $score -= 12;
            $reasons[] = sprintf(
                '%d securities had both purchases and sales during the review period.',
                $roundTripCount,
            );
        } elseif ($roundTripCount > 0) {
            $score -= min(8, $roundTripCount * 2);
            $reasons[] = sprintf(
                '%d security round-trip indicator%s detected.',
                $roundTripCount,
                $roundTripCount === 1 ? '' : 's',
            );
        }

        if ($repeatedSecurityCount >= 3) {
            $score -= 10;
            $reasons[] = sprintf(
                '%d securities were traded four or more times.',
                $repeatedSecurityCount,
            );
        }

        $feeRate = $portfolioValue > 0
            ? $tradingFees / $portfolioValue
            : 0;

        if ($feeRate > 0.01) {
            $score -= 15;
            $reasons[] = sprintf(
                'Recorded trading fees equal %.2f%% of portfolio value.',
                $feeRate * 100,
            );
            $recommendations[] =
                'Request an itemized review of commissions, ticket charges and other transaction costs.';
        } elseif ($feeRate > 0.005) {
            $score -= 8;
            $reasons[] = sprintf(
                'Recorded trading fees equal %.2f%% of portfolio value.',
                $feeRate * 100,
            );
        }

        $score = max(0, min(100, $score));

        if ($reasons === []) {
            $reasons[] = sprintf(
                '%d trades were recorded during the trailing 12 months, with no material trading-discipline indicators under the current methodology.',
                $tradeCount,
            );
        }

        if ($recommendations === []) {
            $recommendations[] =
                'Continue monitoring turnover, fees and changes in trading frequency.';
        }

        return [
            'score' => $score,
            'label' => $this->scoreLabel($score),
            'reasons' => array_values(array_unique($reasons)),
            'recommendations' =>
                array_values(array_unique($recommendations)),
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
