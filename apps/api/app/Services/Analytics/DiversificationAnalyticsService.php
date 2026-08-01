<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use Illuminate\Support\Collection;

class DiversificationAnalyticsService
{
    public const FORMULA_VERSION = 'diversification-1.0.0';

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(Collection $accounts): array
    {
        $holdings = $accounts
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

        $totalValue = (float) $holdings->sum(
            fn (array $item): float =>
                (float) $item['holding']->market_value,
        );

        if ($totalValue <= 0) {
            return $this->emptyResult();
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
                        'name' => $security?->name ?? 'Unknown security',
                        'security_type' => $security?->security_type,
                        'asset_class' => $security?->asset_class,
                        'sector' => $security?->sector,
                        'market_value' => round($marketValue, 2),
                        'weight' => $marketValue / $totalValue,
                    ];
                },
            )
            ->sortByDesc('weight')
            ->values();

        $sectorRows = $this->groupExposure(
            holdings: $holdings,
            totalValue: $totalValue,
            field: 'sector',
            unknownLabel: 'Unclassified sector',
        );

        $assetClassRows = $this->groupExposure(
            holdings: $holdings,
            totalValue: $totalValue,
            field: 'asset_class',
            unknownLabel: 'Unclassified asset class',
        );

        $classifiedSectorValue = (float) $holdings
            ->filter(
                fn (array $item): bool =>
                    filled($item['security']?->sector),
            )
            ->sum(
                fn (array $item): float =>
                    (float) $item['holding']->market_value,
            );

        $classifiedAssetClassValue = (float) $holdings
            ->filter(
                fn (array $item): bool =>
                    filled($item['security']?->asset_class),
            )
            ->sum(
                fn (array $item): float =>
                    (float) $item['holding']->market_value,
            );

        $largestSecurityWeight =
            (float) ($securityRows->first()['weight'] ?? 0);

        $topFiveWeight = (float) $securityRows
            ->take(5)
            ->sum('weight');

        $largestSectorWeight =
            (float) ($sectorRows->first()['weight'] ?? 0);

        $largestAssetClassWeight =
            (float) ($assetClassRows->first()['weight'] ?? 0);

        $securityHhi = $this->calculateHhi($securityRows);
        $sectorHhi = $this->calculateHhi($sectorRows);

        $scoreResult = $this->calculateScore(
            securityCount: $securityRows->count(),
            largestSecurityWeight: $largestSecurityWeight,
            topFiveWeight: $topFiveWeight,
            largestSectorWeight: $largestSectorWeight,
            largestAssetClassWeight: $largestAssetClassWeight,
            sectorCoverageRate: $classifiedSectorValue / $totalValue,
            assetClassCoverageRate:
                $classifiedAssetClassValue / $totalValue,
            securityHhi: $securityHhi,
            sectorHhi: $sectorHhi,
        );

        return [
            'score' => $scoreResult['score'],
            'label' => $scoreResult['label'],
            'reasons' => $scoreResult['reasons'],
            'recommendations' => $scoreResult['recommendations'],
            'metrics' => [
                'total_value' => round($totalValue, 2),
                'security_count' => $securityRows->count(),
                'largest_security_weight' => $largestSecurityWeight,
                'top_five_weight' => $topFiveWeight,
                'largest_sector_weight' => $largestSectorWeight,
                'largest_asset_class_weight' =>
                    $largestAssetClassWeight,
                'sector_coverage_rate' =>
                    $classifiedSectorValue / $totalValue,
                'asset_class_coverage_rate' =>
                    $classifiedAssetClassValue / $totalValue,
                'security_hhi' => $securityHhi,
                'sector_hhi' => $sectorHhi,
            ],
            'securities' => $securityRows,
            'sectors' => $sectorRows,
            'asset_classes' => $assetClassRows,
            'formula_version' => self::FORMULA_VERSION,
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $holdings
     * @return Collection<int, array<string, mixed>>
     */
    private function groupExposure(
        Collection $holdings,
        float $totalValue,
        string $field,
        string $unknownLabel,
    ): Collection {
        return $holdings
            ->groupBy(
                fn (array $item): string =>
                    filled($item['security']?->{$field})
                        ? (string) $item['security']->{$field}
                        : $unknownLabel,
            )
            ->map(
                function (
                    Collection $items,
                    string $label,
                ) use ($totalValue): array {
                    $marketValue = (float) $items->sum(
                        fn (array $item): float =>
                            (float) $item['holding']->market_value,
                    );

                    return [
                        'name' => $label,
                        'market_value' => round($marketValue, 2),
                        'weight' => $marketValue / $totalValue,
                    ];
                },
            )
            ->sortByDesc('weight')
            ->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function calculateHhi(Collection $rows): float
    {
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
        float $largestSectorWeight,
        float $largestAssetClassWeight,
        float $sectorCoverageRate,
        float $assetClassCoverageRate,
        float $securityHhi,
        float $sectorHhi,
    ): array {
        $score = 100;
        $reasons = [];
        $recommendations = [];

        if ($securityCount < 5) {
            $score -= 25;
            $reasons[] =
                'The portfolio contains fewer than five securities.';
            $recommendations[] =
                'Review whether the portfolio relies too heavily on a small number of positions.';
        } elseif ($securityCount < 10) {
            $score -= 10;
            $reasons[] =
                'The portfolio contains fewer than ten securities.';
        }

        if ($largestSecurityWeight > 0.40) {
            $score -= 35;
            $reasons[] = sprintf(
                'The largest security represents %.1f%% of the portfolio.',
                $largestSecurityWeight * 100,
            );
            $recommendations[] =
                'Review the investment thesis and risk limits for the largest position.';
        } elseif ($largestSecurityWeight > 0.25) {
            $score -= 25;
            $reasons[] = sprintf(
                'The largest security represents %.1f%% of the portfolio.',
                $largestSecurityWeight * 100,
            );
            $recommendations[] =
                'Consider whether the largest position creates unnecessary concentration risk.';
        } elseif ($largestSecurityWeight > 0.15) {
            $score -= 10;
            $reasons[] = sprintf(
                'The largest security represents %.1f%% of the portfolio.',
                $largestSecurityWeight * 100,
            );
        }

        if ($topFiveWeight > 0.85) {
            $score -= 20;
            $reasons[] = sprintf(
                'The five largest holdings represent %.1f%% of the portfolio.',
                $topFiveWeight * 100,
            );
            $recommendations[] =
                'Review whether the portfolio is sufficiently diversified beyond its five largest holdings.';
        } elseif ($topFiveWeight > 0.70) {
            $score -= 10;
            $reasons[] = sprintf(
                'The five largest holdings represent %.1f%% of the portfolio.',
                $topFiveWeight * 100,
            );
        }

        if ($largestSectorWeight > 0.50) {
            $score -= 25;
            $reasons[] = sprintf(
                'The largest sector represents %.1f%% of the portfolio.',
                $largestSectorWeight * 100,
            );
            $recommendations[] =
                'Review whether sector exposure is consistent with the investor’s risk tolerance.';
        } elseif ($largestSectorWeight > 0.35) {
            $score -= 15;
            $reasons[] = sprintf(
                'The largest sector represents %.1f%% of the portfolio.',
                $largestSectorWeight * 100,
            );
        } elseif ($largestSectorWeight > 0.25) {
            $score -= 5;
            $reasons[] = sprintf(
                'The largest sector represents %.1f%% of the portfolio.',
                $largestSectorWeight * 100,
            );
        }

        if ($largestAssetClassWeight > 0.95) {
            $score -= 15;
            $reasons[] = sprintf(
                'The largest asset class represents %.1f%% of the portfolio.',
                $largestAssetClassWeight * 100,
            );
            $recommendations[] =
                'Confirm that the asset allocation is appropriate for the investor’s time horizon and liquidity needs.';
        } elseif ($largestAssetClassWeight > 0.80) {
            $score -= 8;
            $reasons[] = sprintf(
                'The largest asset class represents %.1f%% of the portfolio.',
                $largestAssetClassWeight * 100,
            );
        }

        if ($sectorCoverageRate < 0.80) {
            $score -= 8;
            $reasons[] = sprintf(
                'Sector classification covers only %.1f%% of portfolio value.',
                $sectorCoverageRate * 100,
            );
            $recommendations[] =
                'Complete missing sector classifications before relying on sector analysis.';
        }

        if ($assetClassCoverageRate < 0.80) {
            $score -= 8;
            $reasons[] = sprintf(
                'Asset-class classification covers only %.1f%% of portfolio value.',
                $assetClassCoverageRate * 100,
            );
            $recommendations[] =
                'Complete missing asset-class classifications before relying on allocation analysis.';
        }

        if ($securityHhi > 0.25) {
            $score -= 10;
            $reasons[] =
                'Security concentration is elevated based on the portfolio concentration index.';
        }

        if ($sectorHhi > 0.30) {
            $score -= 10;
            $reasons[] =
                'Sector concentration is elevated based on the portfolio concentration index.';
        }

        $score = max(0, min(100, $score));

        if ($reasons === []) {
            $reasons[] =
                'No material concentration concerns were identified using the current classifications.';
        }

        if ($recommendations === []) {
            $recommendations[] =
                'Continue monitoring changes in security, sector and asset-class concentration.';
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
            'label' => 'Insufficient data',
            'reasons' => [
                'No holdings with positive market value are available.',
            ],
            'recommendations' => [
                'Add holdings and market values to calculate diversification.',
            ],
            'metrics' => [],
            'securities' => collect(),
            'sectors' => collect(),
            'asset_classes' => collect(),
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
