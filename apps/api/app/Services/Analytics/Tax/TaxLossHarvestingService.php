<?php

namespace App\Services\Analytics\Tax;

use App\Models\Holding;
use App\Models\InvestmentTransaction;
use App\Models\Security;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class TaxLossHarvestingService
{
    private const WASH_SALE_WINDOW_DAYS = 30;

    /**
     * Analyze open holdings for unrealized tax-loss harvesting opportunities.
     *
     * @param Collection<int, Holding> $holdings
     * @param Collection<int, InvestmentTransaction> $transactions
     */
    public function analyze(
        Collection $holdings,
        Collection $transactions,
        CarbonInterface $asOfDate,
        float $minimumLossAmount = 500,
        float $minimumLossPercent = 0.05
    ): array {
        if ($holdings->isEmpty()) {
            return $this->emptyResult(
                'No open holdings were found.'
            );
        }

        $securitySymbols = Security::query()
            ->whereIn(
                'id',
                $holdings
                    ->pluck('security_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all()
            )
            ->pluck('symbol', 'id');

        $opportunities = [];

        foreach ($holdings as $holding) {
            $quantity = (float) (
                $holding->quantity ?? 0
            );

            if ($quantity <= 0) {
                continue;
            }

            $currentValue = $this->currentValue(
                $holding
            );

            $costBasis = $this->costBasis(
                $holding
            );

            if (
                $currentValue === null
                || $costBasis === null
                || $costBasis <= 0
            ) {
                continue;
            }

            $unrealizedGainLoss =
                $currentValue - $costBasis;

            if ($unrealizedGainLoss >= 0) {
                continue;
            }

            $lossAmount = abs(
                $unrealizedGainLoss
            );

            $lossPercent =
                $lossAmount / $costBasis;

            if (
                $lossAmount < $minimumLossAmount
                || $lossPercent < $minimumLossPercent
            ) {
                continue;
            }

            $recentPurchases =
                $this->recentPurchases(
                    transactions: $transactions,
                    investmentAccountId:
                        $holding->investment_account_id,
                    securityId:
                        $holding->security_id,
                    asOfDate: $asOfDate,
                );

            $washSaleRisk =
                $recentPurchases->isNotEmpty();

            $opportunities[] = [
                'holding_id' =>
                    $holding->id,

                'investment_account_id' =>
                    $holding->investment_account_id,

                'security_id' =>
                    $holding->security_id,

                'security_symbol' =>
                    $securitySymbols->get(
                        $holding->security_id
                    ),

                'quantity' =>
                    round($quantity, 8),

                'current_value' =>
                    round($currentValue, 2),

                'cost_basis' =>
                    round($costBasis, 2),

                'unrealized_gain_loss' =>
                    round(
                        $unrealizedGainLoss,
                        2
                    ),

                'loss_amount' =>
                    round($lossAmount, 2),

                'loss_percent' =>
                    round($lossPercent, 8),

                'wash_sale_risk' =>
                    $washSaleRisk,

                'recent_purchase_count' =>
                    $recentPurchases->count(),

                'recent_purchase_dates' =>
                    $recentPurchases
                        ->pluck('transaction_date')
                        ->map(
                            fn ($date): string =>
                                $date->toDateString()
                        )
                        ->values()
                        ->all(),

                'confidence' =>
                    $washSaleRisk
                        ? 'review_required'
                        : 'higher',
            ];
        }

        $opportunities = collect(
            $opportunities
        )
            ->sortByDesc('loss_amount')
            ->values();

        $totalHarvestableLoss =
            $opportunities
                ->where(
                    'wash_sale_risk',
                    false
                )
                ->sum('loss_amount');

        $totalPotentialLoss =
            $opportunities
                ->sum('loss_amount');

        $washSaleRiskCount =
            $opportunities
                ->where(
                    'wash_sale_risk',
                    true
                )
                ->count();

        return [
            'status' => 'complete',

            'metrics' => [
                'opportunity_count' =>
                    $opportunities->count(),

                'wash_sale_risk_count' =>
                    $washSaleRiskCount,

                'total_potential_loss' =>
                    round(
                        $totalPotentialLoss,
                        2
                    ),

                'estimated_harvestable_loss' =>
                    round(
                        $totalHarvestableLoss,
                        2
                    ),

                'minimum_loss_amount' =>
                    round(
                        $minimumLossAmount,
                        2
                    ),

                'minimum_loss_percent' =>
                    round(
                        $minimumLossPercent,
                        8
                    ),
            ],

            'opportunities' =>
                $opportunities->all(),

            'flags' =>
                $this->buildFlags(
                    opportunityCount:
                        $opportunities->count(),

                    totalHarvestableLoss:
                        $totalHarvestableLoss,

                    washSaleRiskCount:
                        $washSaleRiskCount,
                ),

            'formula_version' =>
                'tax-loss-harvesting-0.1.0',
        ];
    }

    private function currentValue(
        Holding $holding
    ): ?float {
        foreach (
            [
                'market_value',
                'current_value',
                'position_value',
            ] as $attribute
        ) {
            $value =
                $holding->getAttribute(
                    $attribute
                );

            if ($value !== null) {
                return (float) $value;
            }
        }

        $quantity =
            $holding->getAttribute(
                'quantity'
            );

        $price =
            $holding->getAttribute(
                'current_price'
            )
            ?? $holding->getAttribute(
                'market_price'
            )
            ?? $holding->getAttribute(
                'price'
            );

        if (
            $quantity !== null
            && $price !== null
        ) {
            return (float) $quantity
                * (float) $price;
        }

        return null;
    }

    private function costBasis(
        Holding $holding
    ): ?float {
        foreach (
            [
                'cost_basis',
                'total_cost_basis',
                'book_value',
            ] as $attribute
        ) {
            $value =
                $holding->getAttribute(
                    $attribute
                );

            if ($value !== null) {
                return (float) $value;
            }
        }

        $quantity =
            $holding->getAttribute(
                'quantity'
            );

        $averageCost =
            $holding->getAttribute(
                'average_cost'
            )
            ?? $holding->getAttribute(
                'cost_per_share'
            );

        if (
            $quantity !== null
            && $averageCost !== null
        ) {
            return (float) $quantity
                * (float) $averageCost;
        }

        return null;
    }

    private function recentPurchases(
        Collection $transactions,
        int $investmentAccountId,
        int $securityId,
        CarbonInterface $asOfDate
    ): Collection {
        $windowStart = $asOfDate
            ->copy()
            ->subDays(
                self::WASH_SALE_WINDOW_DAYS
            );

        return $transactions
            ->filter(
                function (
                    InvestmentTransaction $transaction
                ) use (
                    $investmentAccountId,
                    $securityId,
                    $windowStart,
                    $asOfDate
                ): bool {
                    if (
                        $transaction
                            ->investment_account_id
                        !== $investmentAccountId
                        || $transaction
                            ->security_id
                        !== $securityId
                    ) {
                        return false;
                    }

                    if (
                        ! in_array(
                            $this->normalizeType(
                                $transaction
                                    ->transaction_type
                            ),
                            [
                                'buy',
                                'purchase',
                                'reinvestment',
                                'dividend_reinvestment',
                            ],
                            true
                        )
                    ) {
                        return false;
                    }

                    return $transaction
                        ->transaction_date
                        ->betweenIncluded(
                            $windowStart,
                            $asOfDate
                        );
                }
            )
            ->values();
    }

    private function buildFlags(
        int $opportunityCount,
        float $totalHarvestableLoss,
        int $washSaleRiskCount
    ): array {
        $flags = [];

        if (
            $opportunityCount > 0
            && $totalHarvestableLoss >= 1000
        ) {
            $flags[] = [
                'code' =>
                    'tax_loss_harvesting_opportunity',

                'severity' => 'moderate',

                'title' =>
                    'Tax-loss harvesting opportunities detected',

                'message' =>
                    'Open positions may contain meaningful unrealized losses that could potentially offset taxable gains.',
            ];
        }

        if ($washSaleRiskCount > 0) {
            $flags[] = [
                'code' =>
                    'harvesting_wash_sale_risk',

                'severity' => 'high',

                'title' =>
                    'Recent purchases may limit harvesting',

                'message' =>
                    "{$washSaleRiskCount} potential harvesting position(s) had recent purchases inside the 30-day wash-sale window.",
            ];
        }

        if ($flags === []) {
            $flags[] = [
                'code' =>
                    'no_major_harvesting_opportunities',

                'severity' =>
                    'informational',

                'title' =>
                    'No major harvesting opportunities detected',

                'message' =>
                    'No open positions exceeded Helmio’s current tax-loss harvesting thresholds.',
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

    private function emptyResult(
        string $message
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'message' => $message,

            'metrics' => [
                'opportunity_count' => 0,
                'wash_sale_risk_count' => 0,
                'total_potential_loss' => 0,
                'estimated_harvestable_loss' => 0,
                'minimum_loss_amount' => null,
                'minimum_loss_percent' => null,
            ],

            'opportunities' => [],
            'flags' => [],

            'formula_version' =>
                'tax-loss-harvesting-0.1.0',
        ];
    }
}