<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use App\Models\InvestorProfile;
use Illuminate\Support\Collection;

class PortfolioDriftService
{
    public const FORMULA_VERSION =
        'portfolio-drift-0.1.0';

    /**
     * Calculate portfolio drift versus the investor's documented
     * target asset allocation.
     *
     * Target allocation values may be stored either as whole
     * percentages (55) or decimals (0.55). The service normalizes
     * both formats to decimal weights.
     *
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(
        Collection $accounts,
        ?InvestorProfile $profile,
    ): array {
        $targetAllocation =
            $this->normalizeTargetAllocation(
                $profile?->target_allocation,
            );

        if ($targetAllocation === []) {
            return $this->insufficientResult(
                reason:
                    'No target allocation has been documented for this investor.',
            );
        }

        $targetTotal =
            array_sum(
                $targetAllocation,
            );

        if (
            $targetTotal < 0.995
            || $targetTotal > 1.005
        ) {
            return $this->insufficientResult(
                reason:
                    sprintf(
                        'Target allocation must total 100%%. Current target totals %.1f%%.',
                        $targetTotal * 100,
                    ),
            );
        }

        $currentDollarAllocation =
            $this->currentDollarAllocation(
                $accounts,
            );

        $totalPortfolioValue =
            array_sum(
                $currentDollarAllocation,
            );

        if ($totalPortfolioValue <= 0) {
            return $this->insufficientResult(
                reason:
                    'No positive portfolio value is available for drift analysis.',
            );
        }

        $allAssetClasses =
            collect(
                [
                    ...array_keys(
                        $targetAllocation,
                    ),

                    ...array_keys(
                        $currentDollarAllocation,
                    ),
                ],
            )
                ->unique()
                ->values();

        $rows =
            $allAssetClasses
                ->map(
                    function (
                        string $assetClass
                    ) use (
                        $targetAllocation,
                        $currentDollarAllocation,
                        $totalPortfolioValue,
                    ): array {
                        $targetWeight =
                            (float) (
                                $targetAllocation[
                                    $assetClass
                                ] ?? 0
                            );

                        $currentValue =
                            (float) (
                                $currentDollarAllocation[
                                    $assetClass
                                ] ?? 0
                            );

                        $currentWeight =
                            $totalPortfolioValue > 0
                                ? $currentValue
                                    / $totalPortfolioValue
                                : 0.0;

                        $drift =
                            $currentWeight
                            - $targetWeight;

                        $absoluteDrift =
                            abs(
                                $drift,
                            );

                        return [
                            'asset_class' =>
                                $assetClass,

                            'label' =>
                                $this->assetClassLabel(
                                    $assetClass,
                                ),

                            'current_value' =>
                                round(
                                    $currentValue,
                                    2,
                                ),

                            'current_weight' =>
                                round(
                                    $currentWeight,
                                    10,
                                ),

                            'target_weight' =>
                                round(
                                    $targetWeight,
                                    10,
                                ),

                            'drift' =>
                                round(
                                    $drift,
                                    10,
                                ),

                            'absolute_drift' =>
                                round(
                                    $absoluteDrift,
                                    10,
                                ),

                            'direction' =>
                                match (true) {
                                    $drift > 0.0005 =>
                                        'overweight',

                                    $drift < -0.0005 =>
                                        'underweight',

                                    default =>
                                        'on_target',
                                },

                            'status' =>
                                $this->driftStatus(
                                    $absoluteDrift,
                                ),
                        ];
                    },
                )
                ->sortByDesc(
                    'absolute_drift',
                )
                ->values();

        $largestDrift =
            $rows->first();

        /*
         * Sum of absolute drift double-counts portfolio displacement
         * because every overweight has a corresponding underweight.
         * Dividing by two gives the minimum portion of the portfolio
         * that would need to move to restore targets.
         */
        $totalAbsoluteDrift =
            (float) $rows->sum(
                'absolute_drift',
            );

        $rebalanceDistance =
            $totalAbsoluteDrift
            / 2;

        $highestAbsoluteDrift =
            (float) (
                $largestDrift[
                    'absolute_drift'
                ] ?? 0
            );

        $overallStatus =
            $this->driftStatus(
                $highestAbsoluteDrift,
            );

        $reviewRecommended =
            $highestAbsoluteDrift >= 0.05
            || $rebalanceDistance >= 0.05;

        $highDriftCount =
            $rows
                ->filter(
                    fn (
                        array $row
                    ): bool =>
                        $row['absolute_drift']
                        >= 0.10,
                )
                ->count();

        $moderateDriftCount =
            $rows
                ->filter(
                    fn (
                        array $row
                    ): bool =>
                        $row['absolute_drift']
                            >= 0.05
                        && $row['absolute_drift']
                            < 0.10,
                )
                ->count();

        return [
            'status' =>
                'complete',

            'metrics' => [
                'portfolio_value' =>
                    round(
                        $totalPortfolioValue,
                        2,
                    ),

                'largest_drift' =>
                    round(
                        $highestAbsoluteDrift,
                        10,
                    ),

                'largest_drift_asset_class' =>
                    $largestDrift[
                        'asset_class'
                    ] ?? null,

                'largest_drift_label' =>
                    $largestDrift[
                        'label'
                    ] ?? null,

                'total_absolute_drift' =>
                    round(
                        $totalAbsoluteDrift,
                        10,
                    ),

                'rebalance_distance' =>
                    round(
                        $rebalanceDistance,
                        10,
                    ),

                'high_drift_count' =>
                    $highDriftCount,

                'moderate_drift_count' =>
                    $moderateDriftCount,

                'overall_status' =>
                    $overallStatus,

                'review_recommended' =>
                    $reviewRecommended,
            ],

            'allocations' =>
                $rows,

            'summary' =>
                $this->buildSummary(
                    largestDrift:
                        $largestDrift,

                    rebalanceDistance:
                        $rebalanceDistance,

                    reviewRecommended:
                        $reviewRecommended,
                ),

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    /**
     * @return array<string, float>
     */
    private function currentDollarAllocation(
        Collection $accounts,
    ): array {
        $allocation = [];

        foreach (
            $accounts
            as $account
        ) {
            foreach (
                $account->holdings
                as $holding
            ) {
                $value =
                    (float) (
                        $holding->market_value
                        ?? 0
                    );

                if ($value <= 0) {
                    continue;
                }

                $security =
                    $holding->security;

                $blendedAllocation =
                    $this->securityAllocationBreakdown(
                        security:
                            $security,
                    );

                if ($blendedAllocation !== []) {
                    foreach (
                        $blendedAllocation
                        as $assetClass => $weight
                    ) {
                        $allocation[
                            $assetClass
                        ] =
                            (
                                $allocation[
                                    $assetClass
                                ] ?? 0
                            )
                            + (
                                $value
                                * $weight
                            );
                    }

                    continue;
                }

                $assetClass =
                    $this->normalizeAssetClass(
                        assetClass:
                            $security?->asset_class,

                        securityType:
                            $security?->security_type,
                    );

                $allocation[
                    $assetClass
                ] =
                    (
                        $allocation[
                            $assetClass
                        ] ?? 0
                    )
                    + $value;
            }

            /*
             * Account-level cash is included when present.
             * Avoid adding it when cash is already represented by
             * an explicit cash holding.
             */
            $hasExplicitCashHolding =
                $account->holdings
                    ->contains(
                        fn (
                            $holding
                        ): bool =>
                            strtolower(
                                (string) (
                                    $holding->security
                                        ?->security_type
                                    ?? ''
                                ),
                            ) === 'cash',
                    );

            if (
                ! $hasExplicitCashHolding
            ) {
                $cashValue =
                    (float) (
                        $account->cash_value
                        ?? 0
                    );

                if ($cashValue > 0) {
                    $allocation[
                        'cash'
                    ] =
                        (
                            $allocation[
                                'cash'
                            ] ?? 0
                        )
                        + $cashValue;
                }
            }
        }

        return $allocation;
    }

    /**
     * Return a normalized look-through asset allocation for blended
     * securities when the security metadata provides one.
     *
     * Supported metadata examples:
     *
     * "asset_allocation" => [
     *     "us_equity" => 60,
     *     "international_equity" => 10,
     *     "fixed_income" => 30,
     * ]
     *
     * or decimal equivalents such as 0.60 / 0.10 / 0.30.
     *
     * @return array<string, float>
     */
    private function securityAllocationBreakdown(
        mixed $security,
    ): array {
        if ($security === null) {
            return [];
        }

        $assetClass =
            strtolower(
                trim(
                    (string) (
                        $security->asset_class
                        ?? ''
                    ),
                ),
            );

        if (
            ! in_array(
                $assetClass,
                [
                    'allocation',
                    'multi_asset',
                    'multi-asset',
                    'balanced',
                    'asset_allocation',
                ],
                true,
            )
        ) {
            return [];
        }

        $metadata =
            is_array(
                $security->metadata,
            )
                ? $security->metadata
                : [];

        $rawAllocation =
            data_get(
                $metadata,
                'asset_allocation',
            )
            ?? data_get(
                $metadata,
                'allocation',
            )
            ?? data_get(
                $metadata,
                'look_through.asset_allocation',
            );

        if (! is_array($rawAllocation)) {
            return [];
        }

        $normalized = [];

        foreach (
            $rawAllocation
            as $assetClassKey => $weight
        ) {
            if (
                ! is_string(
                    $assetClassKey,
                )
                || ! is_numeric(
                    $weight,
                )
            ) {
                continue;
            }

            $normalizedAssetClass =
                $this->normalizeAssetClass(
                    assetClass:
                        $assetClassKey,

                    securityType:
                        null,
                );

            $numericWeight =
                (float) $weight;

            if ($numericWeight < 0) {
                continue;
            }

            $normalizedWeight =
                $numericWeight > 1
                    ? $numericWeight / 100
                    : $numericWeight;

            $normalized[
                $normalizedAssetClass
            ] =
                (
                    $normalized[
                        $normalizedAssetClass
                    ] ?? 0
                )
                + $normalizedWeight;
        }

        $total =
            array_sum(
                $normalized,
            );

        if (
            $total < 0.995
            || $total > 1.005
        ) {
            return [];
        }

        return $normalized;
    }

    /**
     * @return array<string, float>
     */
    private function normalizeTargetAllocation(
        mixed $allocation,
    ): array {
        if (! is_array($allocation)) {
            return [];
        }

        $normalized = [];

        foreach (
            $allocation
            as $assetClass => $weight
        ) {
            if (
                ! is_string(
                    $assetClass,
                )
                || ! is_numeric(
                    $weight,
                )
            ) {
                continue;
            }

            $normalizedAssetClass =
                $this->normalizeAssetClass(
                    assetClass:
                        $assetClass,

                    securityType:
                        null,
                );

            $numericWeight =
                (float) $weight;

            if ($numericWeight < 0) {
                continue;
            }

            $normalizedWeight =
                $numericWeight > 1
                    ? $numericWeight / 100
                    : $numericWeight;

            $normalized[
                $normalizedAssetClass
            ] =
                (
                    $normalized[
                        $normalizedAssetClass
                    ] ?? 0
                )
                + $normalizedWeight;
        }

        return $normalized;
    }

    private function normalizeAssetClass(
        ?string $assetClass,
        ?string $securityType,
    ): string {
        $assetClass =
            strtolower(
                trim(
                    (string) $assetClass,
                ),
            );

        $securityType =
            strtolower(
                trim(
                    (string) $securityType,
                ),
            );

        if (
            $securityType === 'cash'
            || in_array(
                $assetClass,
                [
                    'cash',
                    'cash_equivalent',
                    'cash_equivalents',
                    'money_market',
                    'money_market_fund',
                ],
                true,
            )
        ) {
            return 'cash';
        }

        return match ($assetClass) {
            'us_equity',
            'us equity',
            'domestic_equity',
            'domestic equity',
            'equity',
            'stock',
            'stocks' =>
                'us_equity',

            'international_equity',
            'international equity',
            'intl_equity',
            'foreign_equity',
            'foreign equity',
            'global_ex_us_equity' =>
                'international_equity',

            'fixed_income',
            'fixed income',
            'bond',
            'bonds',
            'us_fixed_income',
            'us fixed income' =>
                'fixed_income',

            'alternatives',
            'alternative',
            'real_estate',
            'real estate',
            'commodity',
            'commodities',
            'private_equity',
            'private equity' =>
                'alternatives',

            '',
            'unknown',
            'other' =>
                $this->fallbackAssetClass(
                    $securityType,
                ),

            default =>
                str_replace(
                    ' ',
                    '_',
                    $assetClass,
                ),
        };
    }

    private function fallbackAssetClass(
        string $securityType,
    ): string {
        return match ($securityType) {
            'stock',
            'etf',
            'mutual_fund' =>
                'us_equity',

            'bond' =>
                'fixed_income',

            'cash' =>
                'cash',

            default =>
                'other',
        };
    }

    private function driftStatus(
        float $absoluteDrift,
    ): string {
        return match (true) {
            $absoluteDrift >= 0.10 =>
                'high',

            $absoluteDrift >= 0.05 =>
                'moderate',

            $absoluteDrift >= 0.025 =>
                'mild',

            default =>
                'on_target',
        };
    }

    private function assetClassLabel(
        string $assetClass,
    ): string {
        return match ($assetClass) {
            'us_equity' =>
                'U.S. Equity',

            'international_equity' =>
                'International Equity',

            'fixed_income' =>
                'Fixed Income',

            'cash' =>
                'Cash',

            'alternatives' =>
                'Alternatives',

            default =>
                str(
                    $assetClass,
                )
                    ->replace(
                        '_',
                        ' ',
                    )
                    ->title()
                    ->toString(),
        };
    }

    /**
     * @param array<string, mixed>|null $largestDrift
     * @return array<string, mixed>
     */
    private function buildSummary(
        ?array $largestDrift,
        float $rebalanceDistance,
        bool $reviewRecommended,
    ): array {
        if ($largestDrift === null) {
            return [
                'headline' =>
                    'Portfolio is on target',

                'message' =>
                    'No material portfolio drift is currently detected.',
            ];
        }

        $direction =
            $largestDrift[
                'direction'
            ] ?? 'on_target';

        $directionLabel =
            match ($direction) {
                'overweight' =>
                    'above',

                'underweight' =>
                    'below',

                default =>
                    'near',
            };

        $headline =
            $reviewRecommended
                ? 'Portfolio drift detected'
                : 'Portfolio remains near target';

        $message =
            sprintf(
                '%s is %.1f percentage points %s target. Approximately %.1f%% of portfolio value would need to shift across asset classes to fully restore the documented allocation.',
                $largestDrift[
                    'label'
                ] ?? 'The largest allocation',
                (
                    $largestDrift[
                        'absolute_drift'
                    ] ?? 0
                ) * 100,
                $directionLabel,
                $rebalanceDistance * 100,
            );

        return [
            'headline' =>
                $headline,

            'message' =>
                $message,
        ];
    }

    private function insufficientResult(
        string $reason,
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'reason' =>
                $reason,

            'metrics' => [
                'portfolio_value' =>
                    null,

                'largest_drift' =>
                    null,

                'largest_drift_asset_class' =>
                    null,

                'largest_drift_label' =>
                    null,

                'total_absolute_drift' =>
                    null,

                'rebalance_distance' =>
                    null,

                'high_drift_count' =>
                    0,

                'moderate_drift_count' =>
                    0,

                'overall_status' =>
                    null,

                'review_recommended' =>
                    false,
            ],

            'allocations' =>
                collect(),

            'summary' => [
                'headline' =>
                    'Target allocation needed',

                'message' =>
                    $reason,
            ],

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }
}