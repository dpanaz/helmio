<?php

namespace App\Services\Analytics;

use App\Models\FundConstituent;
use App\Models\InvestmentAccount;
use App\Models\Security;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LookThroughDiversificationService
{
    public const FORMULA_VERSION =
        'look-through-diversification-0.1.0';

    /**
     * Calculate known look-through exposure for ETFs and direct holdings.
     *
     * Direct stocks contribute 100% of their portfolio weight to themselves.
     * ETFs contribute portfolio weight × constituent weight.
     *
     * Mutual funds and other pooled investments without constituent data are
     * included in coverage calculations but are not decomposed.
     *
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(
        Collection $accounts,
    ): array {
        $holdings = $accounts
            ->flatMap(
                fn (InvestmentAccount $account): Collection =>
                    $account->holdings
                        ->map(
                            fn ($holding): array => [
                                'account_id' =>
                                    $account->id,

                                'account_name' =>
                                    $account->name,

                                'holding' =>
                                    $holding,

                                'security' =>
                                    $holding->security,
                            ],
                        ),
            )
            ->filter(
                fn (array $item): bool =>
                    (float) $item['holding']
                        ->market_value > 0,
            )
            ->reject(
                fn (array $item): bool =>
                    $this->isCashSecurity(
                        $item['security'],
                    ),
            )
            ->values();

        $totalInvestedValue =
            (float) $holdings->sum(
                fn (array $item): float =>
                    (float) $item['holding']
                        ->market_value,
            );

        if ($totalInvestedValue <= 0) {
            return $this->emptyResult();
        }

        $directExposure = [];

        $lookThroughExposure = [];

        $fundBreakdown = [];

        $lookThroughEligibleValue = 0.0;

        $lookThroughCoveredValue = 0.0;

        foreach ($holdings as $item) {
            /** @var Security|null $security */
            $security =
                $item['security'];

            $marketValue =
                (float) $item['holding']
                    ->market_value;

            $portfolioWeight =
                $marketValue
                / $totalInvestedValue;

            if ($security === null) {
                continue;
            }

            $symbol =
                strtoupper(
                    trim(
                        (string) $security->symbol,
                    ),
                );

            $type =
                strtolower(
                    trim(
                        (string) $security->security_type,
                    ),
                );

            /*
             * Direct securities are fully transparent.
             */
            if (
                ! in_array(
                    $type,
                    [
                        'etf',
                        'mutual_fund',
                    ],
                    true,
                )
            ) {
                $this->accumulateExposure(
                    bucket:
                        $directExposure,

                    security:
                        $security,

                    exposure:
                        $portfolioWeight,

                    sourceSymbol:
                        $symbol,

                    sourceType:
                        'direct',
                );

                continue;
            }

            /*
             * ETFs and mutual funds are eligible for look-through.
             */
            $lookThroughEligibleValue +=
                $marketValue;

            $latestDate =
                FundConstituent::query()
                    ->where(
                        'fund_security_id',
                        $security->id,
                    )
                    ->max(
                        'as_of_date',
                    );

            $latestDateString =
                $latestDate !== null
                    ? Carbon::parse(
                        $latestDate,
                    )->toDateString()
                    : null;

            $constituents =
                $latestDateString
                    ? FundConstituent::query()
                        ->where(
                            'fund_security_id',
                            $security->id,
                        )
                        ->whereDate(
                            'as_of_date',
                            $latestDateString,
                        )
                        ->with(
                            'constituent',
                        )
                        ->orderByDesc(
                            'weight',
                        )
                        ->get()
                    : collect();

            if ($constituents->isEmpty()) {
                $fundBreakdown[] = [
                    'fund_security_id' =>
                        $security->id,

                    'symbol' =>
                        $symbol,

                    'name' =>
                        $security->name,

                    'security_type' =>
                        $type,

                    'portfolio_weight' =>
                        $portfolioWeight,

                    'coverage' =>
                        0.0,

                    'as_of_date' =>
                        null,

                    'constituent_count' =>
                        0,

                    'status' =>
                        'unavailable',
                ];

                continue;
            }

            $fundCoverage =
                min(
                    1.0,
                    max(
                        0.0,
                        (float) $constituents
                            ->sum(
                                'weight',
                            ),
                    ),
                );

            $lookThroughCoveredValue +=
                $marketValue
                * $fundCoverage;

            foreach (
                $constituents
                as $constituentRow
            ) {
                $constituent =
                    $constituentRow
                        ->constituent;

                if ($constituent === null) {
                    continue;
                }

                $effectiveExposure =
                    $portfolioWeight
                    * (float) $constituentRow
                        ->weight;

                $this->accumulateExposure(
                    bucket:
                        $lookThroughExposure,

                    security:
                        $constituent,

                    exposure:
                        $effectiveExposure,

                    sourceSymbol:
                        $symbol,

                    sourceType:
                        'fund',
                );
            }

            $fundBreakdown[] = [
                'fund_security_id' =>
                    $security->id,

                'symbol' =>
                    $symbol,

                'name' =>
                    $security->name,

                'security_type' =>
                    $type,

                'portfolio_weight' =>
                    $portfolioWeight,

                'coverage' =>
                    $fundCoverage,

                'as_of_date' =>
                    $latestDateString,

                'constituent_count' =>
                    $constituents->count(),

                'status' =>
                    'complete',
            ];
        }

        /*
         * Merge direct and look-through exposure.
         */
        $effectiveExposure =
            $directExposure;

        foreach (
            $lookThroughExposure
            as $securityId => $row
        ) {
            if (
                isset(
                    $effectiveExposure[
                        $securityId
                    ],
                )
            ) {
                $effectiveExposure[
                    $securityId
                ]['exposure'] +=
                    $row['exposure'];

                $effectiveExposure[
                    $securityId
                ]['sources'] =
                    collect(
                        [
                            ...$effectiveExposure[
                                $securityId
                            ]['sources'],

                            ...$row['sources'],
                        ],
                    )
                        ->unique(
                            fn (
                                array $source
                            ): string =>
                                (
                                    $source[
                                        'symbol'
                                    ] ?? ''
                                )
                                . '|'
                                . (
                                    $source[
                                        'type'
                                    ] ?? ''
                                ),
                        )
                        ->values()
                        ->all();
            } else {
                $effectiveExposure[
                    $securityId
                ] = $row;
            }
        }

        $effectiveExposureRows =
            collect(
                $effectiveExposure,
            )
                ->map(
                    function (
                        array $row,
                    ): array {
                        return [
                            ...$row,

                            'exposure' =>
                                round(
                                    $row['exposure'],
                                    10,
                                ),
                        ];
                    },
                )
                ->sortByDesc(
                    'exposure',
                )
                ->values();

        /*
         * Calculate fund-to-fund overlap using the latest stored
         * constituent snapshots for all transparent funds.
         */
        $fundPairs =
            $this->calculateFundPairs(
                collect(
                    $fundBreakdown,
                )
                    ->where(
                        'status',
                        'complete',
                    )
                    ->values(),
            );

        $portfolioCoverage =
            $totalInvestedValue > 0
                ? (
                    (
                        $totalInvestedValue
                        - $lookThroughEligibleValue
                    )
                    + $lookThroughCoveredValue
                )
                / $totalInvestedValue
                : 0.0;

        $fundCoverage =
            $lookThroughEligibleValue > 0
                ? $lookThroughCoveredValue
                    / $lookThroughEligibleValue
                : 1.0;

        $warnings = [];

        $unavailableFunds =
            collect(
                $fundBreakdown,
            )
                ->where(
                    'status',
                    'unavailable',
                )
                ->values();

        if ($unavailableFunds->isNotEmpty()) {
            $warnings[] = [
                'code' =>
                    'look_through_unavailable',

                'message' =>
                    sprintf(
                        'Look-through data is unavailable for %d pooled investment%s.',
                        $unavailableFunds->count(),
                        $unavailableFunds->count() === 1
                            ? ''
                            : 's',
                    ),

                'symbols' =>
                    $unavailableFunds
                        ->pluck(
                            'symbol',
                        )
                        ->filter()
                        ->values()
                        ->all(),
            ];
        }

        if ($portfolioCoverage < 0.95) {
            $warnings[] = [
                'code' =>
                    'partial_look_through_coverage',

                'message' =>
                    sprintf(
                        'Known look-through exposure covers %.1f%% of invested portfolio value.',
                        $portfolioCoverage * 100,
                    ),
            ];
        }

        return [
            'status' =>
                'complete',

            'metrics' => [
                'total_invested_value' =>
                    round(
                        $totalInvestedValue,
                        2,
                    ),

                'portfolio_coverage' =>
                    round(
                        $portfolioCoverage,
                        10,
                    ),

                'fund_coverage' =>
                    round(
                        $fundCoverage,
                        10,
                    ),

                'look_through_eligible_value' =>
                    round(
                        $lookThroughEligibleValue,
                        2,
                    ),

                'look_through_covered_value' =>
                    round(
                        $lookThroughCoveredValue,
                        2,
                    ),

                'effective_security_count' =>
                    $effectiveExposureRows
                        ->count(),

                'largest_effective_exposure' =>
                    $effectiveExposureRows->isEmpty()
                        ? null
                        : (float) $effectiveExposureRows
                            ->first()['exposure'],
            ],

            'effective_exposures' =>
                $effectiveExposureRows,

            'funds' =>
                collect(
                    $fundBreakdown,
                )
                    ->sortByDesc(
                        'portfolio_weight',
                    )
                    ->values(),

            'fund_pairs' =>
                $fundPairs,

            'warnings' =>
                $warnings,

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    /**
     * @param array<int|string, array<string, mixed>> $bucket
     */
    private function accumulateExposure(
        array &$bucket,
        Security $security,
        float $exposure,
        string $sourceSymbol,
        string $sourceType,
    ): void {
        $securityId =
            (string) $security->id;

        if (
            ! isset(
                $bucket[
                    $securityId
                ],
            )
        ) {
            $bucket[
                $securityId
            ] = [
                'security_id' =>
                    $security->id,

                'symbol' =>
                    $security->symbol,

                'name' =>
                    $security->name,

                'sector' =>
                    $security->sector,

                'asset_class' =>
                    $security->asset_class,

                'exposure' =>
                    0.0,

                'sources' =>
                    [],
            ];
        }

        $bucket[
            $securityId
        ]['exposure'] +=
            $exposure;

        $bucket[
            $securityId
        ]['sources'][] = [
            'symbol' =>
                $sourceSymbol,

            'type' =>
                $sourceType,

            'exposure' =>
                round(
                    $exposure,
                    10,
                ),
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $funds
     * @return Collection<int, array<string, mixed>>
     */
    private function calculateFundPairs(
        Collection $funds,
    ): Collection {
        $pairs = [];

        $funds =
            $funds->values();

        for (
            $i = 0;
            $i < $funds->count();
            $i++
        ) {
            for (
                $j = $i + 1;
                $j < $funds->count();
                $j++
            ) {
                $left =
                    $funds[
                        $i
                    ];

                $right =
                    $funds[
                        $j
                    ];

                $leftConstituents =
                    $this->latestConstituentWeights(
                        (int) $left[
                            'fund_security_id'
                        ],
                    );

                $rightConstituents =
                    $this->latestConstituentWeights(
                        (int) $right[
                            'fund_security_id'
                        ],
                    );

                if (
                    $leftConstituents->isEmpty()
                    || $rightConstituents->isEmpty()
                ) {
                    continue;
                }

                /*
                 * Weighted overlap = sum of the smaller weight for every
                 * shared constituent. This is an intuitive "how much of
                 * the two funds is effectively the same exposure" metric.
                 */
                $sharedIds =
                    $leftConstituents
                        ->keys()
                        ->intersect(
                            $rightConstituents
                                ->keys(),
                        );

                $overlapWeight =
                    (float) $sharedIds
                        ->sum(
                            fn (
                                mixed $securityId
                            ): float =>
                                min(
                                    (float) $leftConstituents[
                                        $securityId
                                    ],

                                    (float) $rightConstituents[
                                        $securityId
                                    ],
                                ),
                        );

                $pairs[] = [
                    'left_fund_security_id' =>
                        $left[
                            'fund_security_id'
                        ],

                    'left_symbol' =>
                        $left[
                            'symbol'
                        ],

                    'right_fund_security_id' =>
                        $right[
                            'fund_security_id'
                        ],

                    'right_symbol' =>
                        $right[
                            'symbol'
                        ],

                    'overlap_weight' =>
                        round(
                            $overlapWeight,
                            10,
                        ),

                    'shared_constituent_count' =>
                        $sharedIds
                            ->count(),

                    'rating' =>
                        $this->overlapRating(
                            $overlapWeight,
                        ),
                ];
            }
        }

        return collect(
            $pairs,
        )
            ->sortByDesc(
                'overlap_weight',
            )
            ->values();
    }

    /**
     * @return Collection<string, float>
     */
    private function latestConstituentWeights(
        int $fundSecurityId,
    ): Collection {
        $latestDate =
            FundConstituent::query()
                ->where(
                    'fund_security_id',
                    $fundSecurityId,
                )
                ->max(
                    'as_of_date',
                );

        if ($latestDate === null) {
            return collect();
        }

        $latestDateString =
            Carbon::parse(
                $latestDate,
            )->toDateString();

        return FundConstituent::query()
            ->where(
                'fund_security_id',
                $fundSecurityId,
            )
            ->whereDate(
                'as_of_date',
                $latestDateString,
            )
            ->get([
                'constituent_security_id',
                'weight',
            ])
            ->mapWithKeys(
                fn (
                    FundConstituent $row
                ): array => [
                    (string) $row
                        ->constituent_security_id =>
                        (float) $row
                            ->weight,
                ],
            );
    }

    private function overlapRating(
        float $weight,
    ): string {
        return match (true) {
            $weight >= 0.60 =>
                'very_high',

            $weight >= 0.40 =>
                'high',

            $weight >= 0.20 =>
                'moderate',

            $weight >= 0.10 =>
                'low',

            default =>
                'minimal',
        };
    }

    private function emptyResult(): array
    {
        return [
            'status' =>
                'insufficient_data',

            'metrics' => [
                'total_invested_value' =>
                    0.0,

                'portfolio_coverage' =>
                    0.0,

                'fund_coverage' =>
                    0.0,

                'look_through_eligible_value' =>
                    0.0,

                'look_through_covered_value' =>
                    0.0,

                'effective_security_count' =>
                    0,

                'largest_effective_exposure' =>
                    null,
            ],

            'effective_exposures' =>
                collect(),

            'funds' =>
                collect(),

            'fund_pairs' =>
                collect(),

            'warnings' =>
                [],

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function isCashSecurity(
        mixed $security,
    ): bool {
        if ($security === null) {
            return false;
        }

        return strtolower(
            (string) (
                $security->security_type
                ?? ''
            ),
        ) === 'cash';
    }
}