<?php

namespace App\Services\Simulation;

use App\Data\Simulation\PortfolioChangeData;
use App\Data\Simulation\SimulatedHoldingData;
use App\Data\Simulation\SimulatedPortfolioData;
use App\Models\InvestmentAccount;
use App\Models\User;

class PortfolioSimulationService
{
    public function __construct(
        private readonly PortfolioScenarioBuilder $scenarioBuilder,
        private readonly SimulationComparisonService $comparisonService,
        private readonly PortfolioSimulationAnalyticsService $analyticsService,
        private readonly ProductionPortfolioSimulationAnalyticsService $productionAnalyticsService,
    ) {
    }

    public function baseline(User $user): SimulatedPortfolioData
    {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with([
                'holdings.security',
            ])
            ->get();

        $holdings = collect();
        $cash = 0.0;

        foreach ($accounts as $account) {
            /*
             * Cash is stored at the InvestmentAccount level in Helmio.
             *
             * Example:
             * current_value = 500000
             * cash_value    = 48650
             * holdings      = 451350
             *
             * Use cash_value directly rather than trying to derive
             * all cash from holding rows.
             */
            $cash += (float) (
                data_get($account, 'cash_value')
                ?? 0
            );

            foreach ($account->holdings as $holding) {
                $symbol = strtoupper(
                    trim(
                        (string) (
                            data_get($holding, 'symbol')
                            ?? data_get($holding, 'ticker')
                            ?? data_get(
                                $holding,
                                'security.symbol'
                            )
                            ?? ''
                        )
                    )
                );

                if ($symbol === '') {
                    continue;
                }

                $name =
                    data_get($holding, 'name')
                    ?? data_get(
                        $holding,
                        'security.name'
                    )
                    ?? $symbol;

                $quantity = (float) (
                    data_get($holding, 'quantity')
                    ?? data_get($holding, 'shares')
                    ?? 0
                );

                $price = (float) (
                    data_get($holding, 'price')
                    ?? data_get(
                        $holding,
                        'current_price'
                    )
                    ?? data_get(
                        $holding,
                        'market_price'
                    )
                    ?? data_get(
                        $holding,
                        'security.last_price'
                    )
                    ?? 0
                );

                $marketValue = (float) (
                    data_get(
                        $holding,
                        'market_value'
                    )
                    ?? data_get(
                        $holding,
                        'marketValue'
                    )
                    ?? (
                        $quantity > 0
                        && $price > 0
                            ? $quantity * $price
                            : 0
                    )
                );

                /*
                 * Account cash_value is already counted.
                 * Skip explicit cash rows to avoid double-counting.
                 */
                if (
                    $this->isCashHolding(
                        $symbol,
                        $holding,
                    )
                ) {
                    continue;
                }

                $holdings->push(
                    new SimulatedHoldingData(
                        securityId:
                            data_get(
                                $holding,
                                'security_id'
                            ),

                        symbol:
                            $symbol,

                        name:
                            (string) $name,

                        quantity:
                            $quantity,

                        price:
                            $price,

                        marketValue:
                            $marketValue,

                        assetClass:
                            data_get(
                                $holding,
                                'asset_class'
                            )
                            ?? data_get(
                                $holding,
                                'security.asset_class'
                            ),

                        sector:
                            data_get(
                                $holding,
                                'sector'
                            )
                            ?? data_get(
                                $holding,
                                'security.sector'
                            ),

                        expenseRatio:
                            $this->nullableFloat(
                                data_get(
                                    $holding,
                                    'expense_ratio'
                                )
                                ?? data_get(
                                    $holding,
                                    'security.expense_ratio'
                                )
                            ),

                        costBasis:
                            $this->nullableFloat(
                                data_get(
                                    $holding,
                                    'cost_basis'
                                )
                            ),

                        accountId:
                            $account->id,

                        accountType:
                            data_get(
                                $account,
                                'account_type'
                            )
                            ?? data_get(
                                $account,
                                'type'
                            ),
                    )
                );
            }
        }

        return new SimulatedPortfolioData(
            holdings:
                $holdings,

            cash:
                $cash,

            advisoryFeeRate:
                $this->resolveAdvisoryFeeRate(
                    $user,
                    $accounts,
                ),
        );
    }

    /**
     * @param array<int, PortfolioChangeData> $changes
     */
    public function simulate(
        User $user,
        array $changes,
    ): array {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with([
                'holdings.security',
                'transactions',
                'institution',
            ])
            ->get();

        $baseline =
            $this->baseline(
                $user
            );

        $simulated =
            $this->scenarioBuilder->apply(
                $baseline,
                $changes,
            );

        $comparison =
            $this->comparisonService->compare(
                $baseline,
                $simulated,
            );

        /*
         * Lightweight simulation analytics are retained for
         * immediate portfolio-state metrics and UI details.
         */
        $currentAnalytics =
            $this->analyticsService->analyze(
                $baseline
            );

        $simulatedAnalytics =
            $this->analyticsService->analyze(
                $simulated
            );

        /*
         * Production analytics reuse Helmio's actual Cost,
         * Fund Expense and Diversification calculations against
         * transient in-memory InvestmentAccount/Holding models.
         *
         * Nothing is written to the database.
         */
        $currentProduction =
            $this->productionAnalyticsService->analyze(
                $accounts,
                $baseline,
            );

        $simulatedProduction =
            $this->productionAnalyticsService->analyze(
                $accounts,
                $simulated,
            );

        return [
            'baseline' =>
                $baseline,

            'simulated' =>
                $simulated,

            'comparison' =>
                $comparison,

            'analytics' => [
                'current' =>
                    $currentAnalytics,

                'simulated' =>
                    $simulatedAnalytics,
            ],

            'production_analytics' => [
                'current' =>
                    $currentProduction,

                'simulated' =>
                    $simulatedProduction,
            ],
        ];
    }

    private function isCashHolding(
        string $symbol,
        mixed $holding,
    ): bool {
        $assetClass = strtolower(
            trim(
                (string) (
                    data_get(
                        $holding,
                        'asset_class'
                    )
                    ?? data_get(
                        $holding,
                        'security.asset_class'
                    )
                    ?? ''
                )
            )
        );

        return in_array(
            strtoupper($symbol),
            [
                'CASH',
                'USD',
                'US DOLLAR',
            ],
            true,
        )
            || in_array(
                $assetClass,
                [
                    'cash',
                    'cash_equivalent',
                    'cash equivalent',
                    'cash equivalents',
                ],
                true,
            );
    }

    private function resolveAdvisoryFeeRate(
        User $user,
        mixed $accounts,
    ): ?float {
        /*
         * Prefer the actual account-level advisory fee.
         */
        $accountRate = $accounts
            ->pluck(
                'annual_advisory_fee_rate'
            )
            ->filter(
                fn ($value) =>
                    $value !== null
                    && $value !== ''
            )
            ->map(
                fn ($value) =>
                    (float) $value
            )
            ->first();

        if ($accountRate !== null) {
            return $accountRate;
        }

        /*
         * Fall back to user/profile-level values.
         */
        $rate =
            data_get(
                $user,
                'investorProfile.advisory_fee_rate'
            )
            ?? data_get(
                $user,
                'advisory_fee_rate'
            );

        return $this->nullableFloat(
            $rate
        );
    }

    private function nullableFloat(
        mixed $value,
    ): ?float {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return (float) $value;
    }
}