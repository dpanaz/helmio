<?php

namespace App\Services\Analytics\Trading;

use App\Models\InvestmentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RoundTripTradeDetector
{
    public const FORMULA_VERSION = 'round-trip-0.2.0';

    /**
     * Match buys and sells using FIFO lots.
     *
     * Cash and cash-equivalent securities are excluded because sweep and
     * money-market movements should not be interpreted as speculative
     * round-trip trading or churning behavior.
     *
     * @param Collection<int, InvestmentTransaction> $transactions
     */
    public function analyze(
        Collection $transactions
    ): array {
        $transactions->each(
            fn (
                InvestmentTransaction $transaction
            ) =>
                $transaction->loadMissing(
                    'security'
                )
        );

        $excludedCashEquivalentCount =
            $transactions
                ->filter(
                    fn (
                        InvestmentTransaction $transaction
                    ): bool =>
                        $this->isCashEquivalent(
                            $transaction
                        )
                )
                ->count();

        $transactions = $transactions
            ->filter(
                fn (
                    InvestmentTransaction $transaction
                ): bool =>
                    $transaction->security_id !== null
                    && ! $this->isCashEquivalent(
                        $transaction
                    )
                    && in_array(
                        $this->normalizeType(
                            $transaction
                                ->transaction_type
                        ),
                        [
                            'buy',
                            'purchase',
                            'sell',
                            'sale',
                        ],
                        true
                    )
            )
            ->sortBy([
                [
                    'transaction_date',
                    'asc',
                ],
                [
                    'id',
                    'asc',
                ],
            ])
            ->values();

        if ($transactions->isEmpty()) {
            return $this->emptyResult(
                excludedCashEquivalentCount:
                    $excludedCashEquivalentCount,
            );
        }

        $transactionsByPosition =
            $transactions->groupBy(
                fn (
                    InvestmentTransaction $transaction
                ): string =>
                    $transaction
                        ->investment_account_id
                    . ':'
                    . $transaction
                        ->security_id
            );

        $roundTrips = [];

        foreach (
            $transactionsByPosition
                as $positionTransactions
        ) {
            $roundTrips = array_merge(
                $roundTrips,
                $this->matchPosition(
                    $positionTransactions
                )
            );
        }

        $roundTripsCollection =
            collect($roundTrips);

        $shortTermRoundTrips =
            $roundTripsCollection
                ->where(
                    'holding_period_days',
                    '<=',
                    30
                )
                ->count();

        $veryShortRoundTrips =
            $roundTripsCollection
                ->where(
                    'holding_period_days',
                    '<=',
                    7
                )
                ->count();

        $totalFees =
            $roundTripsCollection->sum(
                fn (
                    array $roundTrip
                ): float =>
                    (float) $roundTrip[
                        'allocated_fees'
                    ]
            );

        $totalRealizedGainLoss =
            $roundTripsCollection->sum(
                fn (
                    array $roundTrip
                ): float =>
                    (float) $roundTrip[
                        'realized_gain_loss'
                    ]
            );

        $averageHoldingPeriod =
            $roundTripsCollection
                ->isEmpty()
                    ? null
                    : $roundTripsCollection
                        ->avg(
                            'holding_period_days'
                        );

        return [
            'status' =>
                $roundTrips === []
                    ? 'insufficient_data'
                    : 'complete',

            'metrics' => [
                'round_trip_count' =>
                    count($roundTrips),

                'short_term_round_trip_count' =>
                    $shortTermRoundTrips,

                'very_short_round_trip_count' =>
                    $veryShortRoundTrips,

                'average_holding_period_days' =>
                    $averageHoldingPeriod === null
                        ? null
                        : round(
                            $averageHoldingPeriod,
                            2
                        ),

                'total_round_trip_fees' =>
                    round(
                        $totalFees,
                        2
                    ),

                'total_realized_gain_loss' =>
                    round(
                        $totalRealizedGainLoss,
                        2
                    ),

                'excluded_cash_equivalent_transaction_count' =>
                    $excludedCashEquivalentCount,
            ],

            'round_trips' =>
                $roundTrips,

            'flags' =>
                $this->buildFlags(
                    roundTripCount:
                        count($roundTrips),

                    shortTermRoundTripCount:
                        $shortTermRoundTrips,

                    veryShortRoundTripCount:
                        $veryShortRoundTrips,

                    totalFees:
                        $totalFees,
                ),

            'warnings' =>
                $this->buildWarnings(
                    excludedCashEquivalentCount:
                        $excludedCashEquivalentCount,
                ),

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    /**
     * @param Collection<int, InvestmentTransaction> $transactions
     */
    private function matchPosition(
        Collection $transactions
    ): array {
        $openLots = [];
        $roundTrips = [];

        foreach (
            $transactions as $transaction
        ) {
            $type =
                $this->normalizeType(
                    $transaction
                        ->transaction_type
                );

            $quantity = abs(
                (float) (
                    $transaction->quantity
                    ?? 0
                )
            );

            if ($quantity <= 0) {
                continue;
            }

            if (
                in_array(
                    $type,
                    [
                        'buy',
                        'purchase',
                    ],
                    true
                )
            ) {
                $openLots[] = [
                    'transaction_id' =>
                        $transaction->id,

                    'investment_account_id' =>
                        $transaction
                            ->investment_account_id,

                    'security_id' =>
                        $transaction->security_id,

                    'transaction_date' =>
                        $transaction
                            ->transaction_date
                            ->toDateString(),

                    'remaining_quantity' =>
                        $quantity,

                    'price' =>
                        $this->transactionPrice(
                            $transaction
                        ),

                    'fees' =>
                        abs(
                            (float) (
                                $transaction->fees
                                ?? 0
                            )
                        ),

                    'original_quantity' =>
                        $quantity,
                ];

                continue;
            }

            if (
                ! in_array(
                    $type,
                    [
                        'sell',
                        'sale',
                    ],
                    true
                )
            ) {
                continue;
            }

            $remainingSellQuantity =
                $quantity;

            while (
                $remainingSellQuantity > 0
                && $openLots !== []
            ) {
                $lot =
                    &$openLots[0];

                $matchedQuantity =
                    min(
                        $remainingSellQuantity,
                        $lot[
                            'remaining_quantity'
                        ]
                    );

                $buyFeeAllocation =
                    $lot['original_quantity'] > 0
                        ? (
                            $matchedQuantity
                            / $lot[
                                'original_quantity'
                            ]
                        ) * $lot['fees']
                        : 0;

                $sellFeeAllocation =
                    $quantity > 0
                        ? (
                            $matchedQuantity
                            / $quantity
                        ) * abs(
                            (float) (
                                $transaction->fees
                                ?? 0
                            )
                        )
                        : 0;

                $buyPrice =
                    (float) $lot['price'];

                $sellPrice =
                    $this->transactionPrice(
                        $transaction
                    );

                $grossGainLoss =
                    (
                        $sellPrice
                        - $buyPrice
                    )
                    * $matchedQuantity;

                $allocatedFees =
                    $buyFeeAllocation
                    + $sellFeeAllocation;

                $holdingPeriodDays =
                    Carbon::parse(
                        $lot[
                            'transaction_date'
                        ]
                    )->diffInDays(
                        $transaction
                            ->transaction_date
                    );

                $roundTrips[] = [
                    'investment_account_id' =>
                        $transaction
                            ->investment_account_id,

                    'security_id' =>
                        $transaction->security_id,

                    'buy_transaction_id' =>
                        $lot[
                            'transaction_id'
                        ],

                    'sell_transaction_id' =>
                        $transaction->id,

                    'buy_date' =>
                        $lot[
                            'transaction_date'
                        ],

                    'sell_date' =>
                        $transaction
                            ->transaction_date
                            ->toDateString(),

                    'quantity' =>
                        round(
                            $matchedQuantity,
                            8
                        ),

                    'buy_price' =>
                        round(
                            $buyPrice,
                            8
                        ),

                    'sell_price' =>
                        round(
                            $sellPrice,
                            8
                        ),

                    'holding_period_days' =>
                        $holdingPeriodDays,

                    'allocated_fees' =>
                        round(
                            $allocatedFees,
                            2
                        ),

                    'realized_gain_loss' =>
                        round(
                            $grossGainLoss
                            - $allocatedFees,
                            2
                        ),

                    'is_short_term' =>
                        $holdingPeriodDays <= 30,

                    'is_very_short_term' =>
                        $holdingPeriodDays <= 7,
                ];

                $remainingSellQuantity -=
                    $matchedQuantity;

                $lot[
                    'remaining_quantity'
                ] -= $matchedQuantity;

                if (
                    $lot[
                        'remaining_quantity'
                    ] <= 0.00000001
                ) {
                    array_shift(
                        $openLots
                    );
                }

                unset($lot);
            }
        }

        return $roundTrips;
    }

    private function transactionPrice(
        InvestmentTransaction $transaction
    ): float {
        if (
            $transaction->price !== null
        ) {
            return abs(
                (float) $transaction->price
            );
        }

        $quantity = abs(
            (float) (
                $transaction->quantity
                ?? 0
            )
        );

        $grossAmount = abs(
            (float) (
                $transaction->gross_amount
                ?? $transaction->net_amount
                ?? 0
            )
        );

        if ($quantity <= 0) {
            return 0.0;
        }

        return $grossAmount
            / $quantity;
    }

    private function isCashEquivalent(
        InvestmentTransaction $transaction
    ): bool {
        $security =
            $transaction->security;

        if ($security === null) {
            return false;
        }

        $securityType =
            strtolower(
                trim(
                    (string) (
                        $security
                            ->security_type
                        ?? ''
                    )
                )
            );

        return in_array(
            $securityType,
            [
                'cash',
                'cash_equivalent',
                'money_market',
                'money_market_fund',
            ],
            true
        );
    }

    private function buildFlags(
        int $roundTripCount,
        int $shortTermRoundTripCount,
        int $veryShortRoundTripCount,
        float $totalFees
    ): array {
        $flags = [];

        if (
            $veryShortRoundTripCount >= 3
        ) {
            $flags[] = [
                'code' =>
                    'repeated_very_short_round_trips',

                'severity' =>
                    'high',

                'title' =>
                    'Repeated rapid trading detected',

                'message' =>
                    "{$veryShortRoundTripCount} positions were bought and sold within seven days.",
            ];
        }

        if (
            $shortTermRoundTripCount >= 5
        ) {
            $flags[] = [
                'code' =>
                    'frequent_short_term_round_trips',

                'severity' =>
                    'high',

                'title' =>
                    'Frequent short-term round trips',

                'message' =>
                    "{$shortTermRoundTripCount} round trips were completed within 30 days.",
            ];
        }

        if (
            $roundTripCount >= 10
            && $totalFees >= 500
        ) {
            $flags[] = [
                'code' =>
                    'possible_churning_pattern',

                'severity' =>
                    'high',

                'title' =>
                    'Possible churning pattern',

                'message' =>
                    'The portfolio had repeated round-trip trading combined with meaningful transaction costs.',
            ];
        }

        if (
            $roundTripCount > 0
            && $flags === []
        ) {
            $flags[] = [
                'code' =>
                    'round_trips_detected',

                'severity' =>
                    'informational',

                'title' =>
                    'Round-trip trades detected',

                'message' =>
                    "{$roundTripCount} completed non-cash buy-and-sell sequences were identified.",
            ];
        }

        return $flags;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWarnings(
        int $excludedCashEquivalentCount
    ): array {
        if (
            $excludedCashEquivalentCount <= 0
        ) {
            return [];
        }

        return [
            [
                'code' =>
                    'cash_equivalent_transactions_excluded',

                'message' =>
                    sprintf(
                        '%d cash or cash-equivalent transaction(s) were excluded from round-trip trading analysis.',
                        $excludedCashEquivalentCount
                    ),
            ],
        ];
    }

    private function normalizeType(
        ?string $type
    ): string {
        return strtolower(
            trim(
                str_replace(
                    [
                        '-',
                        ' ',
                    ],
                    '_',
                    $type
                    ?? ''
                )
            )
        );
    }

    private function emptyResult(
        int $excludedCashEquivalentCount = 0
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'metrics' => [
                'round_trip_count' => 0,
                'short_term_round_trip_count' => 0,
                'very_short_round_trip_count' => 0,
                'average_holding_period_days' => null,
                'total_round_trip_fees' => 0,
                'total_realized_gain_loss' => 0,

                'excluded_cash_equivalent_transaction_count' =>
                    $excludedCashEquivalentCount,
            ],

            'round_trips' =>
                [],

            'flags' =>
                [],

            'warnings' =>
                $this->buildWarnings(
                    excludedCashEquivalentCount:
                        $excludedCashEquivalentCount,
                ),

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }
}