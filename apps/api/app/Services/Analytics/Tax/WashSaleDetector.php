<?php

namespace App\Services\Analytics\Tax;

use App\Models\InvestmentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WashSaleDetector
{
    private const WINDOW_DAYS = 30;

    /**
     * Detect possible wash sales by matching loss sales with purchases
     * of the same security within 30 days before or after the sale.
     *
     * @param Collection<int, InvestmentTransaction> $transactions
     */
    public function analyze(Collection $transactions): array
    {
        $transactions = $transactions
            ->filter(
                fn (InvestmentTransaction $transaction): bool =>
                    $transaction->security_id !== null
                    && $transaction->transaction_date !== null
            )
            ->sortBy([
                ['transaction_date', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        if ($transactions->isEmpty()) {
            return $this->emptyResult();
        }

        $lossSales = $transactions
            ->filter(
                fn (InvestmentTransaction $transaction): bool =>
                    $this->isSale($transaction)
                    && (float) ($transaction->realized_gain_loss ?? 0) < 0
            )
            ->values();

        if ($lossSales->isEmpty()) {
            return $this->emptyResult(
                message: 'No realized loss sales were found.'
            );
        }

        $purchases = $transactions
            ->filter(
                fn (InvestmentTransaction $transaction): bool =>
                    $this->isPurchase($transaction)
            )
            ->values();

        $washSales = [];

        foreach ($lossSales as $lossSale) {
            $saleDate = Carbon::parse(
                $lossSale->transaction_date
            );

            $saleQuantity = abs(
                (float) ($lossSale->quantity ?? 0)
            );

            $lossAmount = abs(
                (float) $lossSale->realized_gain_loss
            );

            $matchingPurchases = $purchases
                ->filter(function (
                    InvestmentTransaction $purchase
                ) use (
                    $lossSale,
                    $saleDate
                ): bool {
                    if (
                        $purchase->security_id
                        !== $lossSale->security_id
                    ) {
                        return false;
                    }

                    $purchaseDate = Carbon::parse(
                        $purchase->transaction_date
                    );

                    return $purchaseDate->betweenIncluded(
                        $saleDate->copy()->subDays(
                            self::WINDOW_DAYS
                        ),
                        $saleDate->copy()->addDays(
                            self::WINDOW_DAYS
                        )
                    );
                })
                ->sortBy('transaction_date')
                ->values();

            if ($matchingPurchases->isEmpty()) {
                continue;
            }

            $remainingSaleQuantity = $saleQuantity;

            foreach ($matchingPurchases as $purchase) {
                if ($remainingSaleQuantity <= 0) {
                    break;
                }

                $purchaseQuantity = abs(
                    (float) ($purchase->quantity ?? 0)
                );

                if ($purchaseQuantity <= 0) {
                    continue;
                }

                $matchedQuantity = min(
                    $remainingSaleQuantity,
                    $purchaseQuantity
                );

                $disallowedLoss = $saleQuantity > 0
                    ? (
                        $matchedQuantity
                        / $saleQuantity
                    ) * $lossAmount
                    : 0;

                $purchaseDate = Carbon::parse(
                    $purchase->transaction_date
                );

                $daysBetween = $saleDate->diffInDays(
                    $purchaseDate
                );

                $washSales[] = [
                    'security_id' =>
                        $lossSale->security_id,

                    'investment_account_id' =>
                        $lossSale->investment_account_id,

                    'loss_sale_transaction_id' =>
                        $lossSale->id,

                    'repurchase_transaction_id' =>
                        $purchase->id,

                    'loss_sale_date' =>
                        $saleDate->toDateString(),

                    'repurchase_date' =>
                        $purchaseDate->toDateString(),

                    'days_between' =>
                        $daysBetween,

                    'sale_quantity' =>
                        round($saleQuantity, 8),

                    'repurchase_quantity' =>
                        round($purchaseQuantity, 8),

                    'matched_quantity' =>
                        round($matchedQuantity, 8),

                    'realized_loss' =>
                        round(-$lossAmount, 2),

                    'estimated_disallowed_loss' =>
                        round(
                            $disallowedLoss,
                            2
                        ),

                    'confidence' =>
                        $this->confidence(
                            lossSale: $lossSale,
                            purchase: $purchase,
                            matchedQuantity:
                                $matchedQuantity,
                            saleQuantity:
                                $saleQuantity,
                        ),

                    'purchase_before_sale' =>
                        $purchaseDate->lessThan(
                            $saleDate
                        ),
                ];

                $remainingSaleQuantity -=
                    $matchedQuantity;
            }
        }

        $washSaleCollection = collect(
            $washSales
        );

        $estimatedDisallowedLoss =
            $washSaleCollection->sum(
                fn (array $washSale): float =>
                    (float) $washSale[
                        'estimated_disallowed_loss'
                    ]
            );

        $likelyCount = $washSaleCollection
            ->where('confidence', 'likely')
            ->count();

        $possibleCount = $washSaleCollection
            ->where('confidence', 'possible')
            ->count();

        return [
            'status' => $washSales === []
                ? 'complete'
                : 'complete',

            'metrics' => [
                'wash_sale_count' =>
                    count($washSales),

                'likely_wash_sale_count' =>
                    $likelyCount,

                'possible_wash_sale_count' =>
                    $possibleCount,

                'estimated_disallowed_loss' =>
                    round(
                        $estimatedDisallowedLoss,
                        2
                    ),
            ],

            'wash_sales' => $washSales,

            'flags' => $this->buildFlags(
                washSaleCount:
                    count($washSales),

                estimatedDisallowedLoss:
                    $estimatedDisallowedLoss,

                likelyCount:
                    $likelyCount,
            ),

            'formula_version' =>
                'wash-sale-0.1.0',
        ];
    }

    private function isSale(
        InvestmentTransaction $transaction
    ): bool {
        return in_array(
            $this->normalizeType(
                $transaction->transaction_type
            ),
            [
                'sell',
                'sale',
            ],
            true
        );
    }

    private function isPurchase(
        InvestmentTransaction $transaction
    ): bool {
        return in_array(
            $this->normalizeType(
                $transaction->transaction_type
            ),
            [
                'buy',
                'purchase',
                'reinvestment',
                'dividend_reinvestment',
            ],
            true
        );
    }

    private function confidence(
        InvestmentTransaction $lossSale,
        InvestmentTransaction $purchase,
        float $matchedQuantity,
        float $saleQuantity
    ): string {
        $sameAccount =
            $lossSale->investment_account_id
            === $purchase->investment_account_id;

        $fullyMatched =
            $saleQuantity > 0
            && $matchedQuantity >= $saleQuantity;

        return $sameAccount || $fullyMatched
            ? 'likely'
            : 'possible';
    }

    private function buildFlags(
        int $washSaleCount,
        float $estimatedDisallowedLoss,
        int $likelyCount
    ): array {
        if ($washSaleCount === 0) {
            return [
                [
                    'code' =>
                        'no_wash_sales_detected',

                    'severity' =>
                        'informational',

                    'title' =>
                        'No wash sales detected',

                    'message' =>
                        'No realized loss sales were matched with same-security purchases inside the 30-day window.',
                ],
            ];
        }

        $severity =
            $estimatedDisallowedLoss >= 1000
            || $likelyCount >= 2
                ? 'high'
                : 'moderate';

        return [
            [
                'code' =>
                    'possible_wash_sales_detected',

                'severity' =>
                    $severity,

                'title' =>
                    'Possible wash sales detected',

                'message' =>
                    "{$washSaleCount} potential wash sale match(es) may have deferred approximately $"
                    . number_format(
                        $estimatedDisallowedLoss,
                        2
                    )
                    . ' in deductible losses.',
            ],
        ];
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

    private function emptyResult(
        ?string $message = null
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                $message
                ?? 'No qualifying transactions were found.',

            'metrics' => [
                'wash_sale_count' => 0,
                'likely_wash_sale_count' => 0,
                'possible_wash_sale_count' => 0,
                'estimated_disallowed_loss' => 0,
            ],

            'wash_sales' => [],
            'flags' => [],

            'formula_version' =>
                'wash-sale-0.1.0',
        ];
    }
}