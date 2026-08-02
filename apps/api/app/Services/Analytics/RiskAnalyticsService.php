<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use App\Models\PortfolioSnapshot;
use Illuminate\Support\Collection;

class RiskAnalyticsService
{
    public const FORMULA_VERSION = 'risk-1.0.0';

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(Collection $accounts): array
    {
        $portfolioValue = (float) $accounts->sum(
            fn (InvestmentAccount $account): float =>
                (float) $account->current_value,
        );

        if ($portfolioValue <= 0) {
            return $this->emptyResult();
        }

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

        $cashValue = (float) $accounts->sum(
            fn (InvestmentAccount $account): float =>
                (float) $account->cash_value,
        );

        $cashWeight = $portfolioValue > 0
            ? $cashValue / $portfolioValue
            : null;

        $equityValue = (float) $holdings
            ->filter(
                fn (array $item): bool =>
                    $this->isEquityHolding($item),
            )
            ->sum(
                fn (array $item): float =>
                    (float) $item['holding']->market_value,
            );

        $equityWeight = $portfolioValue > 0
            ? $equityValue / $portfolioValue
            : null;

        $largestAccount = $accounts
            ->sortByDesc(
                fn (InvestmentAccount $account): float =>
                    (float) $account->current_value,
            )
            ->first();

        $largestAccountWeight = (
            $largestAccount !== null
            && $portfolioValue > 0
        )
            ? (float) $largestAccount->current_value
                / $portfolioValue
            : null;

        $returnSeries = $this->buildPortfolioReturnSeries(
            $accounts,
        );

        $volatility = $this->annualizedVolatility(
            $returnSeries,
        );

        $maximumDrawdown = $this->maximumDrawdown(
            $returnSeries,
        );

        $negativePeriodRate = $returnSeries->isNotEmpty()
            ? $returnSeries
                ->filter(
                    fn (array $period): bool =>
                        $period['return'] < 0,
                )
                ->count() / $returnSeries->count()
            : null;

        $scoreResult = $this->calculateScore(
            volatility: $volatility,
            maximumDrawdown: $maximumDrawdown,
            equityWeight: $equityWeight,
            cashWeight: $cashWeight,
            largestAccountWeight: $largestAccountWeight,
            negativePeriodRate: $negativePeriodRate,
            returnPeriodCount: $returnSeries->count(),
        );

        return [
            'score' => $scoreResult['score'],
            'label' => $scoreResult['label'],
            'reasons' => $scoreResult['reasons'],
            'recommendations' =>
                $scoreResult['recommendations'],

            'metrics' => [
                'portfolio_value' =>
                    round($portfolioValue, 2),

                'cash_value' =>
                    round($cashValue, 2),

                'cash_weight' =>
                    $cashWeight,

                'equity_value' =>
                    round($equityValue, 2),

                'equity_weight' =>
                    $equityWeight,

                'largest_account_name' =>
                    $largestAccount?->name,

                'largest_account_weight' =>
                    $largestAccountWeight,

                'annualized_volatility' =>
                    $volatility,

                'maximum_drawdown' =>
                    $maximumDrawdown,

                'negative_period_rate' =>
                    $negativePeriodRate,

                'return_period_count' =>
                    $returnSeries->count(),
            ],

            'return_series' => $returnSeries,

            'account_exposure' => $accounts
                ->map(
                    function (
                        InvestmentAccount $account,
                    ) use ($portfolioValue): array {
                        $value =
                            (float) $account->current_value;

                        return [
                            'account_id' => $account->id,
                            'account_name' => $account->name,
                            'value' => round($value, 2),
                            'weight' => $portfolioValue > 0
                                ? $value / $portfolioValue
                                : null,
                        ];
                    },
                )
                ->sortByDesc('weight')
                ->values(),

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function isEquityHolding(
        array $item,
    ): bool {
        $assetClass = strtolower(
            trim(
                (string) (
                    $item['security']?->asset_class
                    ?? ''
                ),
            ),
        );

        $securityType = strtolower(
            trim(
                (string) (
                    $item['security']?->security_type
                    ?? ''
                ),
            ),
        );

        if ($securityType === 'stock') {
            return true;
        }

        return str_contains($assetClass, 'equity')
            || str_contains($assetClass, 'stock');
    }

    /**
     * Build a portfolio-level return series by combining account
     * snapshot returns for matching ending dates.
     *
     * @param Collection<int, InvestmentAccount> $accounts
     * @return Collection<int, array<string, mixed>>
     */
    private function buildPortfolioReturnSeries(
        Collection $accounts,
    ): Collection {
        $accountPeriods = collect();

        foreach ($accounts as $account) {
            $account->loadMissing(
                'portfolioSnapshots',
            );

            $snapshots = $account
                ->portfolioSnapshots
                ->sortBy(
                    fn (
                        PortfolioSnapshot $snapshot,
                    ): int =>
                        $snapshot
                            ->snapshot_date
                            ->timestamp,
                )
                ->values();

            for (
                $index = 1;
                $index < $snapshots->count();
                $index++
            ) {
                $previous = $snapshots[$index - 1];
                $current = $snapshots[$index];

                $beginningValue =
                    (float) $previous->ending_value;

                if ($beginningValue <= 0) {
                    continue;
                }

                $endingValue =
                    (float) $current->ending_value;

                $externalCashFlow =
                    (float) $current
                        ->external_cash_flow;

                $periodReturn = (
                    $endingValue
                    - $externalCashFlow
                    - $beginningValue
                ) / $beginningValue;

                $accountPeriods->push([
                    'account_id' => $account->id,
                    'account_name' => $account->name,
                    'start_date' => $previous
                        ->snapshot_date
                        ->toDateString(),
                    'end_date' => $current
                        ->snapshot_date
                        ->toDateString(),
                    'beginning_value' =>
                        $beginningValue,
                    'ending_value' =>
                        $endingValue,
                    'external_cash_flow' =>
                        $externalCashFlow,
                    'return' => $periodReturn,
                ]);
            }
        }

        return $accountPeriods
            ->groupBy('end_date')
            ->map(
                function (
                    Collection $periods,
                    string $endDate,
                ): array {
                    $beginningValue = (float) $periods
                        ->sum('beginning_value');

                    $weightedReturn =
                        $beginningValue > 0
                            ? (float) $periods->sum(
                                fn (
                                    array $period,
                                ): float =>
                                    $period['return']
                                    * $period[
                                        'beginning_value'
                                    ],
                            ) / $beginningValue
                            : 0;

                    return [
                        'start_date' =>
                            $periods->min(
                                'start_date',
                            ),
                        'end_date' => $endDate,
                        'beginning_value' =>
                            round(
                                $beginningValue,
                                2,
                            ),
                        'ending_value' =>
                            round(
                                (float) $periods
                                    ->sum(
                                        'ending_value',
                                    ),
                                2,
                            ),
                        'external_cash_flow' =>
                            round(
                                (float) $periods
                                    ->sum(
                                        'external_cash_flow',
                                    ),
                                2,
                            ),
                        'return' =>
                            $weightedReturn,
                    ];
                },
            )
            ->sortBy('end_date')
            ->values();
    }

    /**
     * Returns annualized standard deviation.
     *
     * The first implementation assumes monthly return periods and
     * multiplies period volatility by the square root of 12.
     */
    private function annualizedVolatility(
        Collection $returnSeries,
    ): ?float {
        if ($returnSeries->count() < 3) {
            return null;
        }

        $returns = $returnSeries
            ->pluck('return')
            ->map(
                fn ($return): float =>
                    (float) $return,
            );

        $mean = (float) $returns->average();

        $variance = (float) $returns->sum(
            fn (float $return): float =>
                ($return - $mean) ** 2,
        ) / max(1, $returns->count() - 1);

        $periodVolatility = sqrt($variance);

        return $periodVolatility * sqrt(12);
    }

    /**
     * Maximum peak-to-trough decline in the compounded return path.
     */
    private function maximumDrawdown(
        Collection $returnSeries,
    ): ?float {
        if ($returnSeries->isEmpty()) {
            return null;
        }

        $wealth = 1.0;
        $peak = 1.0;
        $maximumDrawdown = 0.0;

        foreach ($returnSeries as $period) {
            $wealth *= 1 + (float) $period['return'];

            $peak = max($peak, $wealth);

            if ($peak <= 0) {
                continue;
            }

            $drawdown = (
                $wealth - $peak
            ) / $peak;

            $maximumDrawdown = min(
                $maximumDrawdown,
                $drawdown,
            );
        }

        return abs($maximumDrawdown);
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateScore(
        ?float $volatility,
        ?float $maximumDrawdown,
        ?float $equityWeight,
        ?float $cashWeight,
        ?float $largestAccountWeight,
        ?float $negativePeriodRate,
        int $returnPeriodCount,
    ): array {
        $score = 100;
        $reasons = [];
        $recommendations = [];

        if ($returnPeriodCount < 3) {
            $score -= 20;

            $reasons[] =
                'Fewer than three return periods are available, so volatility cannot be estimated reliably.';

            $recommendations[] =
                'Add monthly portfolio snapshots to improve risk measurement.';
        }

        if ($volatility !== null) {
            if ($volatility > 0.35) {
                $score -= 35;

                $reasons[] = sprintf(
                    'Estimated annualized volatility is %.1f%%.',
                    $volatility * 100,
                );

                $recommendations[] =
                    'Review whether portfolio volatility is consistent with the investor’s time horizon and loss tolerance.';
            } elseif ($volatility > 0.25) {
                $score -= 25;

                $reasons[] = sprintf(
                    'Estimated annualized volatility is %.1f%%.',
                    $volatility * 100,
                );
            } elseif ($volatility > 0.18) {
                $score -= 12;

                $reasons[] = sprintf(
                    'Estimated annualized volatility is %.1f%%.',
                    $volatility * 100,
                );
            } else {
                $reasons[] = sprintf(
                    'Estimated annualized volatility is %.1f%%.',
                    $volatility * 100,
                );
            }
        }

        if ($maximumDrawdown !== null) {
            if ($maximumDrawdown > 0.35) {
                $score -= 35;

                $reasons[] = sprintf(
                    'Maximum measured drawdown is %.1f%%.',
                    $maximumDrawdown * 100,
                );

                $recommendations[] =
                    'Review the causes of the largest decline and whether portfolio risk controls are appropriate.';
            } elseif ($maximumDrawdown > 0.25) {
                $score -= 25;

                $reasons[] = sprintf(
                    'Maximum measured drawdown is %.1f%%.',
                    $maximumDrawdown * 100,
                );
            } elseif ($maximumDrawdown > 0.15) {
                $score -= 12;

                $reasons[] = sprintf(
                    'Maximum measured drawdown is %.1f%%.',
                    $maximumDrawdown * 100,
                );
            } else {
                $reasons[] = sprintf(
                    'Maximum measured drawdown is %.1f%%.',
                    $maximumDrawdown * 100,
                );
            }
        }

        if ($equityWeight !== null) {
            if ($equityWeight > 0.95) {
                $score -= 20;

                $reasons[] = sprintf(
                    'Equity exposure represents %.1f%% of portfolio value.',
                    $equityWeight * 100,
                );

                $recommendations[] =
                    'Confirm that the equity allocation matches the investor’s objectives, liquidity needs and ability to withstand losses.';
            } elseif ($equityWeight > 0.85) {
                $score -= 10;

                $reasons[] = sprintf(
                    'Equity exposure represents %.1f%% of portfolio value.',
                    $equityWeight * 100,
                );
            }
        }

        if ($cashWeight !== null) {
            if ($cashWeight > 0.40) {
                $score -= 15;

                $reasons[] = sprintf(
                    'Cash represents %.1f%% of portfolio value.',
                    $cashWeight * 100,
                );

                $recommendations[] =
                    'Review whether the cash level is intentional or creating long-term return drag.';
            } elseif ($cashWeight > 0.25) {
                $score -= 8;

                $reasons[] = sprintf(
                    'Cash represents %.1f%% of portfolio value.',
                    $cashWeight * 100,
                );
            }
        }

        if (
            $largestAccountWeight !== null
            && $largestAccountWeight > 0.90
        ) {
            $score -= 8;

            $reasons[] = sprintf(
                'One account represents %.1f%% of total portfolio value.',
                $largestAccountWeight * 100,
            );
        }

        if (
            $negativePeriodRate !== null
            && $negativePeriodRate > 0.60
        ) {
            $score -= 10;

            $reasons[] = sprintf(
                'Negative returns occurred in %.0f%% of measured periods.',
                $negativePeriodRate * 100,
            );
        }

        $score = max(
            0,
            min(100, $score),
        );

        if ($reasons === []) {
            $reasons[] =
                'No material risk indicators were identified using the available data.';
        }

        if ($recommendations === []) {
            $recommendations[] =
                'Continue monitoring volatility, drawdowns and portfolio exposure as additional history becomes available.';
        }

        return [
            'score' => $score,
            'label' =>
                $this->scoreLabel($score),
            'reasons' =>
                array_values(
                    array_unique($reasons),
                ),
            'recommendations' =>
                array_values(
                    array_unique(
                        $recommendations,
                    ),
                ),
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
                'A positive portfolio value is required to calculate risk.',
            ],
            'recommendations' => [
                'Add account values, holdings and portfolio snapshots.',
            ],
            'metrics' => [],
            'return_series' => collect(),
            'account_exposure' => collect(),
            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function scoreLabel(
        int $score,
    ): string {
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
