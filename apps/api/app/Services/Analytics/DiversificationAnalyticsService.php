<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use Illuminate\Support\Collection;

class DiversificationAnalyticsService
{
    public const FORMULA_VERSION = 'diversification-1.2.0';

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


        $findings = $this->buildFindings(
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

            sectors:
                $sectorRows,
        );

        $summary = $this->buildSummary(
            score:
                $scoreResult['score'],

            findings:
                $findings,
        );

        $actions = $this->buildActions(
            findings:
                $findings,

            recommendations:
                $scoreResult['recommendations'],
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

            'summary' =>
                $summary,

            'findings' =>
                $findings,

            'actions' =>
                $actions,

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
     * Build structured, ranked diversification findings.
     *
     * These findings intentionally mirror the existing scoring
     * thresholds so the UI can explain the score without creating
     * a second scoring system.
     *
     * @param Collection<int, array<string, mixed>> $sectors
     * @return array<int, array<string, mixed>>
     */
    private function buildFindings(
        int $securityCount,
        float $largestSecurityWeight,
        float $topFiveWeight,
        ?float $largestSectorWeight,
        ?float $largestAssetClassWeight,
        float $sectorCoverageRate,
        float $assetClassCoverageRate,
        float $securityHhi,
        ?float $sectorHhi,
        Collection $sectors,
    ): array {
        $findings = [];

        if ($securityCount < 5) {
            $findings[] = [
                'code' => 'low_security_count',
                'severity' => 'high',
                'title' => 'Portfolio relies on very few securities',
                'message' => sprintf(
                    'The invested portfolio contains only %d non-cash securities.',
                    $securityCount,
                ),
                'metric' => (string) $securityCount,
                'score_impact' => -25,
                'priority' => 1,
            ];
        } elseif ($securityCount < 10) {
            $findings[] = [
                'code' => 'low_security_count',
                'severity' => 'moderate',
                'title' => 'Security count is limited',
                'message' => sprintf(
                    'The invested portfolio contains %d non-cash securities.',
                    $securityCount,
                ),
                'metric' => (string) $securityCount,
                'score_impact' => -10,
                'priority' => 4,
            ];
        }

        if ($largestSecurityWeight > 0.40) {
            $findings[] = [
                'code' => 'largest_security_concentration',
                'severity' => 'critical',
                'title' => 'One position dominates the portfolio',
                'message' => sprintf(
                    'The largest invested security represents %.1f%% of invested portfolio value.',
                    $largestSecurityWeight * 100,
                ),
                'metric' => sprintf(
                    '%.1f%%',
                    $largestSecurityWeight * 100,
                ),
                'score_impact' => -35,
                'priority' => 1,
            ];
        } elseif ($largestSecurityWeight > 0.25) {
            $findings[] = [
                'code' => 'largest_security_concentration',
                'severity' => 'high',
                'title' => 'Largest position is highly concentrated',
                'message' => sprintf(
                    'The largest invested security represents %.1f%% of invested portfolio value.',
                    $largestSecurityWeight * 100,
                ),
                'metric' => sprintf(
                    '%.1f%%',
                    $largestSecurityWeight * 100,
                ),
                'score_impact' => -25,
                'priority' => 1,
            ];
        } elseif ($largestSecurityWeight > 0.15) {
            $findings[] = [
                'code' => 'largest_security_concentration',
                'severity' => 'moderate',
                'title' => 'Largest position deserves review',
                'message' => sprintf(
                    'The largest invested security represents %.1f%% of invested portfolio value.',
                    $largestSecurityWeight * 100,
                ),
                'metric' => sprintf(
                    '%.1f%%',
                    $largestSecurityWeight * 100,
                ),
                'score_impact' => -10,
                'priority' => 3,
            ];
        }

        if ($topFiveWeight > 0.85) {
            $findings[] = [
                'code' => 'top_five_concentration',
                'severity' => 'high',
                'title' => 'Top five holdings dominate the portfolio',
                'message' => sprintf(
                    'The five largest invested holdings represent %.1f%% of invested portfolio value.',
                    $topFiveWeight * 100,
                ),
                'metric' => sprintf(
                    '%.1f%%',
                    $topFiveWeight * 100,
                ),
                'score_impact' => -20,
                'priority' => 2,
            ];
        } elseif ($topFiveWeight > 0.70) {
            $findings[] = [
                'code' => 'top_five_concentration',
                'severity' => 'moderate',
                'title' => 'Portfolio is concentrated in its largest holdings',
                'message' => sprintf(
                    'The five largest invested holdings represent %.1f%% of invested portfolio value.',
                    $topFiveWeight * 100,
                ),
                'metric' => sprintf(
                    '%.1f%%',
                    $topFiveWeight * 100,
                ),
                'score_impact' => -10,
                'priority' => 2,
            ];
        }

        if (
            $largestSectorWeight !== null
            && $sectorCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            $largestSectorName =
                (string) (
                    $sectors->first()['name']
                    ?? 'Largest sector'
                );

            if ($largestSectorWeight > 0.50) {
                $findings[] = [
                    'code' => 'sector_concentration',
                    'severity' => 'critical',
                    'title' => "{$largestSectorName} exposure is very high",
                    'message' => sprintf(
                        '%s represents %.1f%% of classified invested value.',
                        $largestSectorName,
                        $largestSectorWeight * 100,
                    ),
                    'metric' => sprintf(
                        '%.1f%%',
                        $largestSectorWeight * 100,
                    ),
                    'score_impact' => -25,
                    'priority' => 1,
                ];
            } elseif ($largestSectorWeight > 0.35) {
                $findings[] = [
                    'code' => 'sector_concentration',
                    'severity' => 'high',
                    'title' => "{$largestSectorName} exposure is elevated",
                    'message' => sprintf(
                        '%s represents %.1f%% of classified invested value.',
                        $largestSectorName,
                        $largestSectorWeight * 100,
                    ),
                    'metric' => sprintf(
                        '%.1f%%',
                        $largestSectorWeight * 100,
                    ),
                    'score_impact' => -15,
                    'priority' => 1,
                ];
            } elseif ($largestSectorWeight > 0.25) {
                $findings[] = [
                    'code' => 'sector_concentration',
                    'severity' => 'moderate',
                    'title' => "{$largestSectorName} exposure is concentrated",
                    'message' => sprintf(
                        '%s represents %.1f%% of classified invested value.',
                        $largestSectorName,
                        $largestSectorWeight * 100,
                    ),
                    'metric' => sprintf(
                        '%.1f%%',
                        $largestSectorWeight * 100,
                    ),
                    'score_impact' => -5,
                    'priority' => 3,
                ];
            }
        }

        if (
            $largestAssetClassWeight !== null
            && $assetClassCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            if ($largestAssetClassWeight > 0.95) {
                $findings[] = [
                    'code' => 'asset_class_concentration',
                    'severity' => 'high',
                    'title' => 'Portfolio is concentrated in one asset class',
                    'message' => sprintf(
                        'The largest classified asset class represents %.1f%% of classified invested value.',
                        $largestAssetClassWeight * 100,
                    ),
                    'metric' => sprintf(
                        '%.1f%%',
                        $largestAssetClassWeight * 100,
                    ),
                    'score_impact' => -15,
                    'priority' => 2,
                ];
            } elseif ($largestAssetClassWeight > 0.80) {
                $findings[] = [
                    'code' => 'asset_class_concentration',
                    'severity' => 'moderate',
                    'title' => 'Asset allocation is concentrated',
                    'message' => sprintf(
                        'The largest classified asset class represents %.1f%% of classified invested value.',
                        $largestAssetClassWeight * 100,
                    ),
                    'metric' => sprintf(
                        '%.1f%%',
                        $largestAssetClassWeight * 100,
                    ),
                    'score_impact' => -8,
                    'priority' => 3,
                ];
            }
        }

        if ($securityHhi > 0.25) {
            $findings[] = [
                'code' => 'security_hhi',
                'severity' => 'moderate',
                'title' => 'Security concentration index is elevated',
                'message' =>
                    'The invested-security concentration index indicates elevated concentration across positions.',
                'metric' => number_format(
                    $securityHhi,
                    3
                ),
                'score_impact' => -10,
                'priority' => 4,
            ];
        }

        if (
            $sectorHhi !== null
            && $sectorCoverageRate
                >= self::CLASSIFICATION_COVERAGE_THRESHOLD
            && $sectorHhi > 0.30
        ) {
            $findings[] = [
                'code' => 'sector_hhi',
                'severity' => 'moderate',
                'title' => 'Sector concentration index is elevated',
                'message' =>
                    'The classified-sector concentration index indicates elevated sector concentration.',
                'metric' => number_format(
                    $sectorHhi,
                    3
                ),
                'score_impact' => -10,
                'priority' => 4,
            ];
        }

        if (
            $sectorCoverageRate
            < self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            $findings[] = [
                'code' => 'limited_sector_classification_coverage',
                'severity' => 'information',
                'title' => 'Sector classification is incomplete',
                'message' => sprintf(
                    'Sector classification covers %.1f%% of invested portfolio value, so sector concentration was not fully scored.',
                    $sectorCoverageRate * 100,
                ),
                'metric' => sprintf(
                    '%.1f%%',
                    $sectorCoverageRate * 100,
                ),
                'score_impact' => 0,
                'priority' => 5,
            ];
        }

        if (
            $assetClassCoverageRate
            < self::CLASSIFICATION_COVERAGE_THRESHOLD
        ) {
            $findings[] = [
                'code' => 'limited_asset_class_classification_coverage',
                'severity' => 'information',
                'title' => 'Asset-class classification is incomplete',
                'message' => sprintf(
                    'Asset-class classification covers %.1f%% of invested portfolio value, so allocation concentration was not fully scored.',
                    $assetClassCoverageRate * 100,
                ),
                'metric' => sprintf(
                    '%.1f%%',
                    $assetClassCoverageRate * 100,
                ),
                'score_impact' => 0,
                'priority' => 5,
            ];
        }

        usort(
            $findings,
            function (
                array $a,
                array $b
            ): int {
                $severityOrder = [
                    'critical' => 0,
                    'high' => 1,
                    'moderate' => 2,
                    'information' => 3,
                ];

                return [
                    $a['priority'] ?? 99,
                    $severityOrder[
                        $a['severity']
                        ?? 'information'
                    ] ?? 99,
                    $a['code'] ?? '',
                ] <=> [
                    $b['priority'] ?? 99,
                    $severityOrder[
                        $b['severity']
                        ?? 'information'
                    ] ?? 99,
                    $b['code'] ?? '',
                ];
            }
        );

        return array_values(
            $findings
        );
    }

    /**
     * @param array<int, array<string, mixed>> $findings
     * @return array<string, mixed>
     */
    private function buildSummary(
        int $score,
        array $findings,
    ): array {
        $materialFindings =
            collect($findings)
                ->whereIn(
                    'severity',
                    [
                        'critical',
                        'high',
                        'moderate',
                    ]
                )
                ->values();

        $primaryFinding =
            $materialFindings->first();

        $headline = match (true) {
            $score >= 80 =>
                'Diversification appears well balanced',

            $score >= 60 =>
                'Some concentration deserves review',

            $score >= 40 =>
                'Moderate concentration risk identified',

            default =>
                'Significant concentration risk identified',
        };

        if ($primaryFinding === null) {
            return [
                'headline' =>
                    $headline,

                'message' =>
                    'No material concentration concerns were identified using the current invested holdings and available classifications.',

                'primary_driver' =>
                    null,

                'material_finding_count' =>
                    0,
            ];
        }

        $secondaryFinding =
            $materialFindings
                ->skip(1)
                ->first();

        $message =
            $secondaryFinding !== null
                ? sprintf(
                    '%s %s',
                    rtrim(
                        (string) $primaryFinding[
                            'message'
                        ],
                        '.'
                    ) . '.',
                    (string) $secondaryFinding[
                        'message'
                    ]
                )
                : (string) $primaryFinding[
                    'message'
                ];

        return [
            'headline' =>
                $headline,

            'message' =>
                $message,

            'primary_driver' =>
                $primaryFinding[
                    'code'
                ] ?? null,

            'material_finding_count' =>
                $materialFindings->count(),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $findings
     * @param array<int, string> $recommendations
     * @return array<int, array<string, mixed>>
     */
    private function buildActions(
        array $findings,
        array $recommendations,
    ): array {
        $actions = [];

        foreach ($findings as $finding) {
            $code =
                $finding['code']
                ?? null;

            $action = match ($code) {
                'sector_concentration' => [
                    'title' =>
                        'Review sector concentration',

                    'message' =>
                        'Determine whether the largest sector allocation is intentional and consistent with the investor’s documented risk profile.',
                ],

                'largest_security_concentration' => [
                    'title' =>
                        'Review the largest position',

                    'message' =>
                        'Confirm the investment thesis, target weight, and acceptable downside for the largest holding.',
                ],

                'top_five_concentration' => [
                    'title' =>
                        'Review concentration in the top five holdings',

                    'message' =>
                        'Evaluate whether the portfolio is sufficiently diversified beyond its largest positions.',
                ],

                'asset_class_concentration' => [
                    'title' =>
                        'Review asset allocation',

                    'message' =>
                        'Confirm that the current asset-class mix is appropriate for the investor’s time horizon, liquidity needs, and risk tolerance.',
                ],

                'low_security_count' => [
                    'title' =>
                        'Review breadth of holdings',

                    'message' =>
                        'Determine whether the portfolio relies too heavily on a small number of invested positions.',
                ],

                'limited_sector_classification_coverage' => [
                    'title' =>
                        'Complete sector classifications',

                    'message' =>
                        'Improve sector metadata before relying on sector-level diversification conclusions.',
                ],

                'limited_asset_class_classification_coverage' => [
                    'title' =>
                        'Complete asset-class classifications',

                    'message' =>
                        'Improve asset-class metadata before relying on allocation-level diversification conclusions.',
                ],

                default =>
                    null,
            };

            if ($action === null) {
                continue;
            }

            $actions[] = [
                'priority' =>
                    (int) (
                        $finding['priority']
                        ?? 99
                    ),

                'severity' =>
                    $finding['severity']
                    ?? 'information',

                'code' =>
                    $code,

                'title' =>
                    $action['title'],

                'message' =>
                    $action['message'],
            ];
        }

        if ($actions === []) {
            foreach (
                $recommendations
                as $index => $recommendation
            ) {
                $actions[] = [
                    'priority' =>
                        $index + 1,

                    'severity' =>
                        'information',

                    'code' =>
                        'general_review',

                    'title' =>
                        'Continue diversification review',

                    'message' =>
                        $recommendation,
                ];
            }
        }

        usort(
            $actions,
            fn (
                array $a,
                array $b
            ): int =>
                ($a['priority'] ?? 99)
                <=>
                ($b['priority'] ?? 99)
        );

        return array_values(
            collect($actions)
                ->unique('code')
                ->take(4)
                ->all()
        );
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

            'summary' => [
                'headline' =>
                    'Diversification data is unavailable',

                'message' =>
                    'No non-cash holdings with positive market value are available for diversification analysis.',

                'primary_driver' =>
                    null,

                'material_finding_count' =>
                    0,
            ],

            'findings' =>
                [],

            'actions' => [
                [
                    'priority' =>
                        1,

                    'severity' =>
                        'information',

                    'code' =>
                        'add_invested_holdings',

                    'title' =>
                        'Add invested holdings',

                    'message' =>
                        'Add invested holdings and market values to calculate diversification.',
                ],
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