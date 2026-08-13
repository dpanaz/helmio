<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use Illuminate\Support\Collection;

class DiversificationAnalyticsService
{
    public const FORMULA_VERSION = 'diversification-1.1.0';

    private const CLASSIFICATION_COVERAGE_THRESHOLD = 0.80;

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(Collection $accounts): array
    {
        $allPositiveHoldings = $accounts
            ->flatMap(
                fn (InvestmentAccount $account): Collection =>
                    $account->holdings->map(
                        fn ($holding): array => [
                            'account_id' => $account->id,
                            'account_name' => $account->name,
                            'holding' => $holding,
                            'security' => $holding->security,
                        ],
                    ),
            )
            ->filter(
                fn (array $item): bool =>
                    (float) $item['holding']->market_value > 0,
            )
            ->values();

        /*
         * Cash and cash-equivalent positions are liquidity, not invested
         * security diversification. Excluding them prevents money-market
         * sweep positions such as SPAXX from being counted as a second
         * invested security or as an "unclassified" sector/asset class.
         */
        $cashHoldings = $allPositiveHoldings
            ->filter(
                fn (array $item): bool =>
                    $this->isCashSecurity(
                        $item['security']
                    ),
            )
            ->values();

        $holdings = $allPositiveHoldings
            ->reject(
                fn (array $item): bool =>
                    $this->isCashSecurity(
                        $item['security']
                    ),
            )
            ->values();

        $portfolioValue = (float) $allPositiveHoldings->sum(
            fn (array $item): float =>
                (float) $item['holding']->market_value,
        );

        $excludedCashValue = (float) $cashHoldings->sum(
            fn (array $item): float =>
                (float) $item['holding']->market_value,
        );

        $totalValue = (float) $holdings->sum(
            fn (array $item): float =>
                (float) $item['holding']->market_value,
        );

        if ($totalValue <= 0) {
            return $this->emptyResult(
                portfolioValue: $portfolioValue,
                excludedCashValue: $excludedCashValue,
            );
        }

        $securityRows = $holdings
            ->groupBy(
                fn (array $item): string =>
                    (string) (
                        $item['security']?->id
                        ?? 'unknown-'.$item['holding']->id
                    ),
            )
            ->map(
                function (Collection $items) use ($totalValue): array {
                    $first = $items->first();
                    $security = $first['security'];

                    $marketValue = (float) $items->sum(
                        fn (array $item): float =>
                            (float) $item['holding']->market_value,
                    );

                    return [
                        'security_id' => $security?->id,
                        'symbol' => $security?->symbol,
                        'name' =>
                            $security?->name
                            ?? 'Unknown security',
                        'security_type' =>
                            $security?->security_type,
                        'asset_class' =>
                            $security?->asset_class,
                        'sector' =>
                            $security?->sector,
                        'market_value' =>
                            round($marketValue, 2),
                        'weight' =>
                            $marketValue / $totalValue,
                    ];
                },
            )
            ->sortByDesc('weight')
            ->values();

        /*
         * Classification coverage is measured against invested value only.
         * Unclassified holdings do not become a fake "sector" or fake
         * "asset class". They are excluded from concentration math and
         * surfaced separately as data-quality warnings.
         */
        $classifiedSectorHoldings = $holdings
            ->filter(
                fn (array $item): bool =>
                    filled($item['security']?->sector),
            )
            ->values();

        $classifiedAssetClassHoldings = $holdings
            ->filter(
                fn (array $item): bool =>
                    filled($item['security']?->asset_class),
            )
            ->values();

        $classifiedSectorValue =
            (float) $classifiedSectorHoldings->sum(
                fn (array $item): float =>
                    (float) $item['holding']->market_value,
            );

        $classifiedAssetClassValue =
            (float) $classifiedAssetClassHoldings->sum(
                fn (array $item): float =>
                    (float) $item['holding']->market_value,
            );

        $sectorCoverageRate =
            $totalValue > 0
                ? $classifiedSectorValue / $totalValue
                : 0.0;

        $assetClassCoverageRate =
            $totalValue > 0
                ? $classifiedAssetClassValue / $totalValue
                : 0.0;

        $sectorRows = $this->groupClassifiedExposure(
            holdings: $classifiedSectorHoldings,
            classifiedValue: $classifiedSectorValue,
            field: 'sector',
        );

        $assetClassRows = $this->groupClassifiedExposure(
            holdings: $classifiedAssetClassHoldings,
            classifiedValue: $classifiedAssetClassValue,
            field: 'asset_class',
        );

        $largestSecurityWeight =
            (float) (
                $securityRows->first()['weight']
                ?? 0
            );

        $topFiveWeight =
            (float) $securityRows
                ->take(5)
                ->sum('weight');

        $largestSectorWeight =
            $sectorCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
                    ? (
                        isset($sectorRows->first()['weight'])
                            ? (float) $sectorRows->first()['weight']
                            : null
                    )
                    : null;

        $largestAssetClassWeight =
            $assetClassCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
                    ? (
                        isset($assetClassRows->first()['weight'])
                            ? (float) $assetClassRows->first()['weight']
                            : null
                    )
                    : null;

        $securityHhi =
            $this->calculateHhi(
                $securityRows
            );

        $sectorHhi =
            $sectorCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
                    ? $this->calculateHhi(
                        $sectorRows
                    )
                    : null;

        $scoreResult = $this->calculateScore(
            securityCount:
                $securityRows->count(),

            largestSecurityWeight:
                $largestSecurityWeight,

            topFiveWeight:
                $topFiveWeight,

            largestSectorWeight:
                $largestSectorWeight,

            largestAssetClassWeight:
                $largestAssetClassWeight,

            sectorCoverageRate:
                $sectorCoverageRate,

            assetClassCoverageRate:
                $assetClassCoverageRate,

            securityHhi:
                $securityHhi,

            sectorHhi:
                $sectorHhi,
        );

        $warnings = $this->buildWarnings(
            sectorCoverageRate:
                $sectorCoverageRate,

            assetClassCoverageRate:
                $assetClassCoverageRate,

            excludedCashValue:
                $excludedCashValue,

            portfolioValue:
                $portfolioValue,
        );

        return [
            'status' =>
                'complete',

            'score' =>
                $scoreResult['score'],

            'label' =>
                $scoreResult['label'],

            'reasons' =>
                $scoreResult['reasons'],

            'recommendations' =>
                $scoreResult['recommendations'],

            'warnings' =>
                $warnings,

            'metrics' => [
                /*
                 * total_value remains the invested value used by
                 * diversification calculations.
                 */
                'total_value' =>
                    round($totalValue, 2),

                'portfolio_value' =>
                    round($portfolioValue, 2),

                'excluded_cash_value' =>
                    round($excludedCashValue, 2),

                'excluded_cash_weight' =>
                    $portfolioValue > 0
                        ? $excludedCashValue
                            / $portfolioValue
                        : 0.0,

                'security_count' =>
                    $securityRows->count(),

                'largest_security_weight' =>
                    $largestSecurityWeight,

                'top_five_weight' =>
                    $topFiveWeight,

                'largest_sector_weight' =>
                    $largestSectorWeight,

                'largest_asset_class_weight' =>
                    $largestAssetClassWeight,

                'sector_coverage_rate' =>
                    $sectorCoverageRate,

                'asset_class_coverage_rate' =>
                    $assetClassCoverageRate,

                'security_hhi' =>
                    $securityHhi,

                'sector_hhi' =>
                    $sectorHhi,
            ],

            'securities' =>
                $securityRows,

            'sectors' =>
                $sectorRows,

            'asset_classes' =>
                $assetClassRows,

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    /**
     * Group only holdings that have a real classification.
     *
     * Weights are relative to classified value, not total portfolio value.
     * The separate coverage metric tells consumers how much of the invested
     * portfolio the classified exposure actually represents.
     *
     * @param Collection<int, array<string, mixed>> $holdings
     * @return Collection<int, array<string, mixed>>
     */
    private function groupClassifiedExposure(
        Collection $holdings,
        float $classifiedValue,
        string $field,
    ): Collection {
        if (
            $holdings->isEmpty()
            || $classifiedValue <= 0
        ) {
            return collect();
        }

        return $holdings
            ->groupBy(
                fn (array $item): string =>
                    (string) $item[
                        'security'
                    ]->{$field},
            )
            ->map(
                function (
                    Collection $items,
                    string $label,
                ) use ($classifiedValue): array {
                    $marketValue =
                        (float) $items->sum(
                            fn (array $item): float =>
                                (float) $item[
                                    'holding'
                                ]->market_value,
                        );

                    return [
                        'name' =>
                            $label,

                        'market_value' =>
                            round($marketValue, 2),

                        'weight' =>
                            $marketValue
                            / $classifiedValue,
                    ];
                },
            )
            ->sortByDesc('weight')
            ->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function calculateHhi(
        Collection $rows
    ): float {
        return (float) $rows->sum(
            fn (array $row): float =>
                ((float) $row['weight']) ** 2,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateScore(
        int $securityCount,
        float $largestSecurityWeight,
        float $topFiveWeight,
        ?float $largestSectorWeight,
        ?float $largestAssetClassWeight,
        float $sectorCoverageRate,
        float $assetClassCoverageRate,
        float $securityHhi,
        ?float $sectorHhi,
    ): array {
        $score = 100;
        $reasons = [];
        $recommendations = [];

        if ($securityCount < 5) {
            $score -= 25;

            $reasons[] =
                'The invested portfolio contains fewer than five non-cash securities.';

            $recommendations[] =
                'Review whether the portfolio relies too heavily on a small number of invested positions.';
        } elseif ($securityCount < 10) {
            $score -= 10;

            $reasons[] =
                'The invested portfolio contains fewer than ten non-cash securities.';
        }

        if ($largestSecurityWeight > 0.40) {
            $score -= 35;

            $reasons[] = sprintf(
                'The largest invested security represents %.1f%% of invested portfolio value.',
                $largestSecurityWeight * 100,
            );

            $recommendations[] =
                'Review the investment thesis and risk limits for the largest invested position.';
        } elseif ($largestSecurityWeight > 0.25) {
            $score -= 25;

            $reasons[] = sprintf(
                'The largest invested security represents %.1f%% of invested portfolio value.',
                $largestSecurityWeight * 100,
            );

            $recommendations[] =
                'Consider whether the largest invested position creates unnecessary concentration risk.';
        } elseif ($largestSecurityWeight > 0.15) {
            $score -= 10;

            $reasons[] = sprintf(
                'The largest invested security represents %.1f%% of invested portfolio value.',
                $largestSecurityWeight * 100,
            );
        }

        if ($topFiveWeight > 0.85) {
            $score -= 20;

            $reasons[] = sprintf(
                'The five largest invested holdings represent %.1f%% of invested portfolio value.',
                $topFiveWeight * 100,
            );

            $recommendations[] =
                'Review whether the portfolio is sufficiently diversified beyond its five largest invested holdings.';
        } elseif ($topFiveWeight > 0.70) {
            $score -= 10;

            $reasons[] = sprintf(
                'The five largest invested holdings represent %.1f%% of invested portfolio value.',
                $topFiveWeight * 100,
            );
        }

        /*
         * Sector and asset-class concentration are scored only when
         * classification coverage is adequate. Missing classifications
         * are a data-quality issue, not evidence that one fake
         * "Unclassified" category represents 100% of the portfolio.
         */
        if (
            $largestSectorWeight !== null
            && $sectorCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            if ($largestSectorWeight > 0.50) {
                $score -= 25;

                $reasons[] = sprintf(
                    'The largest classified sector represents %.1f%% of classified invested value.',
                    $largestSectorWeight * 100,
                );

                $recommendations[] =
                    'Review whether sector exposure is consistent with the investor’s risk tolerance.';
            } elseif ($largestSectorWeight > 0.35) {
                $score -= 15;

                $reasons[] = sprintf(
                    'The largest classified sector represents %.1f%% of classified invested value.',
                    $largestSectorWeight * 100,
                );
            } elseif ($largestSectorWeight > 0.25) {
                $score -= 5;

                $reasons[] = sprintf(
                    'The largest classified sector represents %.1f%% of classified invested value.',
                    $largestSectorWeight * 100,
                );
            }
        }

        if (
            $largestAssetClassWeight !== null
            && $assetClassCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            if ($largestAssetClassWeight > 0.95) {
                $score -= 15;

                $reasons[] = sprintf(
                    'The largest classified asset class represents %.1f%% of classified invested value.',
                    $largestAssetClassWeight * 100,
                );

                $recommendations[] =
                    'Confirm that the asset allocation is appropriate for the investor’s time horizon and liquidity needs.';
            } elseif ($largestAssetClassWeight > 0.80) {
                $score -= 8;

                $reasons[] = sprintf(
                    'The largest classified asset class represents %.1f%% of classified invested value.',
                    $largestAssetClassWeight * 100,
                );
            }
        }

        /*
         * Missing classification coverage is disclosed, but it does not
         * directly subtract from the diversification score. Otherwise the
         * same missing metadata is penalized once as a coverage problem
         * and again as fabricated concentration.
         */
        if (
            $sectorCoverageRate
            < self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            $reasons[] = sprintf(
                'Sector classification covers only %.1f%% of invested portfolio value, so sector concentration was not scored.',
                $sectorCoverageRate * 100,
            );

            $recommendations[] =
                'Complete missing sector classifications before relying on sector concentration analysis.';
        }

        if (
            $assetClassCoverageRate
            < self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            $reasons[] = sprintf(
                'Asset-class classification covers only %.1f%% of invested portfolio value, so asset-class concentration was not scored.',
                $assetClassCoverageRate * 100,
            );

            $recommendations[] =
                'Complete missing asset-class classifications before relying on allocation analysis.';
        }

        if ($securityHhi > 0.25) {
            $score -= 10;

            $reasons[] =
                'Security concentration is elevated based on the invested-security concentration index.';
        }

        if (
            $sectorHhi !== null
            && $sectorCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
            && $sectorHhi > 0.30
        ) {
            $score -= 10;

            $reasons[] =
                'Sector concentration is elevated based on the classified-sector concentration index.';
        }

        $score = max(
            0,
            min(100, $score)
        );

        if ($reasons === []) {
            $reasons[] =
                'No material concentration concerns were identified using the current invested holdings and available classifications.';
        }

        if ($recommendations === []) {
            $recommendations[] =
                'Continue monitoring changes in security, sector and asset-class concentration.';
        }

        return [
            'score' =>
                $score,

            'label' =>
                $this->scoreLabel(
                    $score
                ),

            'reasons' =>
                array_values(
                    array_unique(
                        $reasons
                    )
                ),

            'recommendations' =>
                array_values(
                    array_unique(
                        $recommendations
                    )
                ),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWarnings(
        float $sectorCoverageRate,
        float $assetClassCoverageRate,
        float $excludedCashValue,
        float $portfolioValue,
    ): array {
        $warnings = [];

        if (
            $sectorCoverageRate
            < self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            $warnings[] = [
                'code' =>
                    'limited_sector_classification_coverage',

                'message' =>
                    sprintf(
                        'Sector classification covers %.1f%% of invested portfolio value; sector concentration was not scored.',
                        $sectorCoverageRate * 100,
                    ),
            ];
        }

        if (
            $assetClassCoverageRate
            < self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            $warnings[] = [
                'code' =>
                    'limited_asset_class_classification_coverage',

                'message' =>
                    sprintf(
                        'Asset-class classification covers %.1f%% of invested portfolio value; asset-class concentration was not scored.',
                        $assetClassCoverageRate * 100,
                    ),
            ];
        }

        if ($excludedCashValue > 0) {
            $warnings[] = [
                'code' =>
                    'cash_excluded_from_diversification',

                'message' =>
                    sprintf(
                        '$%s of cash or cash-equivalent holdings was excluded from invested-security diversification calculations%s.',
                        number_format(
                            $excludedCashValue,
                            2
                        ),
                        $portfolioValue > 0
                            ? sprintf(
                                ' (%.1f%% of total positive holding value)',
                                (
                                    $excludedCashValue
                                    / $portfolioValue
                                ) * 100
                            )
                            : ''
                    ),
            ];
        }

        return $warnings;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyResult(
        float $portfolioValue = 0.0,
        float $excludedCashValue = 0.0,
    ): array {
        return [
            'status' =>
                'insufficient_data',

            'score' =>
                null,

            'label' =>
                'Insufficient data',

            'reasons' => [
                'No non-cash holdings with positive market value are available for diversification analysis.',
            ],

            'recommendations' => [
                'Add invested holdings and market values to calculate diversification.',
            ],

            'warnings' =>
                $excludedCashValue > 0
                    ? [
                        [
                            'code' =>
                                'cash_only_portfolio',

                            'message' =>
                                'Only cash or cash-equivalent positive-value holdings were available.',
                        ],
                    ]
                    : [],

            'metrics' => [
                'total_value' =>
                    0.0,

                'portfolio_value' =>
                    round($portfolioValue, 2),

                'excluded_cash_value' =>
                    round($excludedCashValue, 2),

                'excluded_cash_weight' =>
                    $portfolioValue > 0
                        ? $excludedCashValue
                            / $portfolioValue
                        : 0.0,

                'security_count' =>
                    0,

                'largest_security_weight' =>
                    null,

                'top_five_weight' =>
                    null,

                'largest_sector_weight' =>
                    null,

                'largest_asset_class_weight' =>
                    null,

                'sector_coverage_rate' =>
                    0.0,

                'asset_class_coverage_rate' =>
                    0.0,

                'security_hhi' =>
                    null,

                'sector_hhi' =>
                    null,
            ],

            'securities' =>
                collect(),

            'sectors' =>
                collect(),

            'asset_classes' =>
                collect(),

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function isCashSecurity(
        mixed $security
    ): bool {
        if ($security === null) {
            return false;
        }

        return strtolower(
            (string) (
                $security->security_type
                ?? ''
            )
        ) === 'cash';
    }

    private function scoreLabel(
        int $score
    ): string {
        return match (true) {
            $score >= 90 =>
                'Excellent',

            $score >= 80 =>
                'Very good',

            $score >= 70 =>
                'Good',

            $score >= 60 =>
                'Fair',

            $score >= 40 =>
                'Needs attention',

            default =>
                'Action recommended',
        };
    }
}