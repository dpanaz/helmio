<?php

namespace App\Services\Analytics\Performance;

use App\Models\InvestmentAccount;
use App\Models\PortfolioValuation;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use RuntimeException;
use App\Models\Holding;

class PortfolioValuationGenerator
{
    public function __construct(
    private readonly PortfolioCashFlowService $cashFlowService,
    private readonly HistoricalPriceService $historicalPriceService,
    private readonly HistoricalQuantityService $historicalQuantityService
) {
}

    /**
     * Generate account-level valuations and one consolidated
     * portfolio valuation for the supplied user and date.
     */
    public function generateForUser(
        User $user,
        CarbonInterface $valuationDate
    ): array {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with('holdings')
            ->get();

        $accountValuations = collect();

        foreach ($accounts as $account) {
            $accountValuations->push(
                $this->generateForAccount(
                    account: $account,
                    valuationDate: $valuationDate,
                )
            );
        }

        $portfolioValuation = $this->generateConsolidated(
            user: $user,
            accountValuations: $accountValuations,
            valuationDate: $valuationDate,
        );

        return [
            'user_id' => $user->id,
            'valuation_date' => $valuationDate->toDateString(),
            'account_count' => $accountValuations->count(),

            'account_valuations' => $accountValuations
                ->map(
                    fn (
                        PortfolioValuation $valuation
                    ): array => [
                        'investment_account_id' =>
                            $valuation->investment_account_id,

                        'market_value' =>
                            (float) $valuation->market_value,

                        'cash_value' =>
                            (float) $valuation->cash_value,

                        'net_cash_flow' =>
                            (float) $valuation->net_cash_flow,

                        'total_value' =>
                            $valuation->total_value,
                    ]
                )
                ->values()
                ->all(),

            'portfolio_valuation' => [
                'market_value' =>
                    (float) $portfolioValuation->market_value,

                'cash_value' =>
                    (float) $portfolioValuation->cash_value,

                'net_cash_flow' =>
                    (float) $portfolioValuation->net_cash_flow,

                'total_value' =>
                    $portfolioValuation->total_value,
            ],
        ];
    }

    /**
     * Generate a valuation for one investment account.
     */
    public function generateForAccount(
        InvestmentAccount $account,
        CarbonInterface $valuationDate
    ): PortfolioValuation {
        $account->loadMissing('holdings.security');

        /*
         * Brokerage syncs keep dated holding snapshots. The same provider
         * position can therefore exist in more than one holdings row.
         *
         * Valuation generation must use only the latest row for each
         * provider position or the portfolio will be double-counted.
         *
         * Manual holdings do not have a provider position ID, so they keep
         * their own holding ID as the identity key and are never collapsed
         * merely because they share a security.
         */
        $holdings = $this->canonicalHoldings(
            $account->holdings,
        );

        $investedHoldings = $holdings
            ->filter(
                fn (Holding $holding): bool =>
                    $holding->security === null
                    || $holding->security->security_type !== 'cash',
            )
            ->values();

        $cashEquivalentHoldings = $holdings
            ->filter(
                fn (Holding $holding): bool =>
                    $holding->security !== null
                    && $holding->security->security_type === 'cash',
            )
            ->values();

        $marketValue = $investedHoldings->sum(
            fn (Holding $holding): float =>
                $this->holdingMarketValue(
                    holding: $holding,
                    valuationDate: $valuationDate,
                )
        );

        $cashEquivalentValue = $cashEquivalentHoldings->sum(
            fn (Holding $holding): float =>
                $this->holdingMarketValue(
                    holding: $holding,
                    valuationDate: $valuationDate,
                )
        );

        $cashValue =
            $this->accountCashValue($account)
            + $cashEquivalentValue;

        $cashFlow = $this->cashFlowService
            ->forAccountOnDate(
                investmentAccountId: $account->id,
                date: $valuationDate,
            );

        $priceableHoldings = $investedHoldings
    ->filter(function ($holding): bool {
        if ($holding->security === null) {
            return false;
        }

        return ! in_array(
            $holding->security->security_type,
            [
                'cash',
            ],
            true,
        );
    });

        $historicalPriceCount = $priceableHoldings
            ->filter(function ($holding) use ($valuationDate): bool {
                $securityId = $holding->getAttribute('security_id');

                if ($securityId === null) {
                    return false;
                }

                return $this->historicalPriceService
                    ->valueOnOrBefore(
                        security: (int) $securityId,
                        date: $valuationDate,
                    ) !== null;
            })
            ->count();

        $missingHistoricalPriceCount =
            $priceableHoldings->count()
            - $historicalPriceCount;

        $valuation = PortfolioValuation::query()
            ->where('user_id', $account->user_id)
            ->where(
                'investment_account_id',
                $account->id
            )
            ->whereDate(
                'valuation_date',
                $valuationDate->toDateString()
            )
            ->first();

        $historicalQuantities = $holdings
    ->mapWithKeys(
        fn (Holding $holding): array => [
            $holding->id =>
                $this->historicalQuantityService
                    ->quantityOnDate(
                        holding: $holding,
                        date: $valuationDate,
                    ),
        ]
    )
    ->all();

        /*
         * Performance/risk history should begin only once the account
         * actually contains a non-cash invested position on this date.
         *
         * Cash-equivalent holdings such as money-market sweep positions
         * do not start investment-performance history by themselves.
         */
        $hasInvestedPositions = $holdings
            ->contains(
                function (Holding $holding) use (
                    $historicalQuantities,
                ): bool {
                    if ($holding->security === null) {
                        return false;
                    }

                    if (
                        in_array(
                            $holding->security->security_type,
                            [
                                'cash',
                            ],
                            true,
                        )
                    ) {
                        return false;
                    }

                    return (
                        (float) (
                            $historicalQuantities[
                                $holding->id
                            ]
                            ?? 0
                        )
                    ) > 0;
                },
            );

        if ($valuation === null) {
            $valuation = new PortfolioValuation();

            $valuation->user_id = $account->user_id;
            $valuation->investment_account_id =
                $account->id;
            $valuation->valuation_date =
                $valuationDate->toDateString();
        }

        $valuation->fill([
            'market_value' =>
                round($marketValue, 2),

            'cash_value' =>
                round($cashValue, 2),

            'net_cash_flow' =>
                round(
                    $cashFlow['net_external_cash_flow'],
                    2
                ),

            'currency' =>
                $account->currency ?? 'USD',

            'source' => 'generated',

            'metadata' => [
                'holding_count' =>
                    $holdings->count(),

                'raw_holding_row_count' =>
                    $account->holdings->count(),

                'cash_equivalent_holding_count' =>
                    $cashEquivalentHoldings->count(),

                'cash_equivalent_value' =>
                    round($cashEquivalentValue, 2),

                'historical_price_count' =>
                    $historicalPriceCount,

                'missing_historical_price_count' =>
                    $missingHistoricalPriceCount,

                'external_inflows' =>
                    $cashFlow['external_inflows'],

                'external_outflows' =>
                    $cashFlow['external_outflows'],

                'external_transaction_count' =>
                    $cashFlow[
                        'external_transaction_count'
                    ],

                'unknown_transaction_count' =>
                    $cashFlow[
                        'unknown_transaction_count'
                    ],

                'generated_at' =>
                    now()->toIso8601String(),

                'has_invested_positions' =>
                    $hasInvestedPositions,

                'historical_quantities' =>
                    $historicalQuantities,
            ],
        ]);

        $valuation->save();

        return $valuation->refresh();
    }

    /**
     * Generate a consolidated valuation by adding together
     * the account-level valuation records.
     */
    private function generateConsolidated(
        User $user,
        Collection $accountValuations,
        CarbonInterface $valuationDate
    ): PortfolioValuation {
        $marketValue = $accountValuations->sum(
            fn (
                PortfolioValuation $valuation
            ): float =>
                (float) $valuation->market_value
        );

        $cashValue = $accountValuations->sum(
            fn (
                PortfolioValuation $valuation
            ): float =>
                (float) $valuation->cash_value
        );

        $netCashFlow = $accountValuations->sum(
            fn (
                PortfolioValuation $valuation
            ): float =>
                (float) $valuation->net_cash_flow
        );

        $externalInflows = $accountValuations->sum(
            function (
                PortfolioValuation $valuation
            ): float {
                return (float) data_get(
                    $valuation->metadata,
                    'external_inflows',
                    0
                );
            }
        );

        $externalOutflows = $accountValuations->sum(
            function (
                PortfolioValuation $valuation
            ): float {
                return (float) data_get(
                    $valuation->metadata,
                    'external_outflows',
                    0
                );
            }
        );

        $externalTransactionCount =
            $accountValuations->sum(
                function (
                    PortfolioValuation $valuation
                ): int {
                    return (int) data_get(
                        $valuation->metadata,
                        'external_transaction_count',
                        0
                    );
                }
            );

        $unknownTransactionCount =
            $accountValuations->sum(
                function (
                    PortfolioValuation $valuation
                ): int {
                    return (int) data_get(
                        $valuation->metadata,
                        'unknown_transaction_count',
                        0
                    );
                }
            );

        $historicalPriceCount =
            $accountValuations->sum(
                function (
                    PortfolioValuation $valuation
                ): int {
                    return (int) data_get(
                        $valuation->metadata,
                        'historical_price_count',
                        0
                    );
                }
            );

        $missingHistoricalPriceCount =
            $accountValuations->sum(
                function (
                    PortfolioValuation $valuation
                ): int {
                    return (int) data_get(
                        $valuation->metadata,
                        'missing_historical_price_count',
                        0
                    );
                }
            );

        /*
         * Consolidated performance history is active once at least
         * one account contains a genuine non-cash position.
         */
        $hasInvestedPositions =
            $accountValuations->contains(
                fn (
                    PortfolioValuation $valuation
                ): bool =>
                    (bool) data_get(
                        $valuation->metadata,
                        'has_invested_positions',
                        false,
                    ),
            );

        $valuation = PortfolioValuation::query()
            ->where('user_id', $user->id)
            ->whereNull('investment_account_id')
            ->whereDate(
                'valuation_date',
                $valuationDate->toDateString()
            )
            ->first();

        if ($valuation === null) {
            $valuation = new PortfolioValuation();

            $valuation->user_id = $user->id;
            $valuation->investment_account_id = null;
            $valuation->valuation_date =
                $valuationDate->toDateString();
        }

        $valuation->fill([
            'market_value' =>
                round($marketValue, 2),

            'cash_value' =>
                round($cashValue, 2),

            'net_cash_flow' =>
                round($netCashFlow, 2),

            'currency' => 'USD',

            'source' => 'generated',

            'metadata' => [
                'account_count' =>
                    $accountValuations->count(),

                'historical_price_count' =>
                    $historicalPriceCount,

                'missing_historical_price_count' =>
                    $missingHistoricalPriceCount,

                'external_inflows' =>
                    round($externalInflows, 2),

                'external_outflows' =>
                    round($externalOutflows, 2),

                'external_transaction_count' =>
                    $externalTransactionCount,

                'unknown_transaction_count' =>
                    $unknownTransactionCount,

                'generated_at' =>
                    now()->toIso8601String(),

                'has_invested_positions' =>
                    $hasInvestedPositions,
            ],
        ]);

        $valuation->save();

        return $valuation->refresh();
    }

    /**
     * Return one current/canonical row for each brokerage position.
     *
     * Provider-backed holdings are de-duplicated by provider_position_id,
     * keeping the newest snapshot row. Manual holdings remain unique by ID.
     *
     * @param Collection<int, Holding> $holdings
     * @return Collection<int, Holding>
     */
    private function canonicalHoldings(
        Collection $holdings
    ): Collection {
        return $holdings
            ->sortByDesc(
                function (Holding $holding): string {
                    $date = $holding->as_of_date
                        ?->format('YmdHis')
                        ?? '00000000000000';

                    return sprintf(
                        '%s-%020d',
                        $date,
                        $holding->id,
                    );
                }
            )
            ->unique(
                fn (Holding $holding): string =>
                    filled($holding->provider_position_id)
                        ? 'provider:'
                            .$holding->provider_position_id
                        : 'holding:'
                            .$holding->id
            )
            ->values();
    }

    /**
     * Determine a holding's value for the requested valuation date.
     *
     * Historical prices are preferred. Current holding values are used
     * only when no historical price can be found.
     */
    private function holdingMarketValue(
    Holding $holding,
    CarbonInterface $valuationDate
): float {
       $quantity = $this->historicalQuantityService
    ->quantityOnDate(
        holding: $holding,
        date: $valuationDate,
    );

        $securityId = $holding->getAttribute('security_id');

        if (
            $quantity !== null
            && $securityId !== null
        ) {
            $historicalPrice =
                $this->historicalPriceService
                    ->valueOnOrBefore(
                        security: (int) $securityId,
                        date: $valuationDate,
                    );

            if ($historicalPrice !== null) {
                return (float) $quantity
                    * $historicalPrice;
            }
        }

        foreach (
            [
                'market_value',
                'current_value',
                'position_value',
            ] as $attribute
        ) {
            $value = $holding->getAttribute($attribute);

            if ($value !== null) {
                return (float) $value;
            }
        }

        $currentPrice =
            $holding->getAttribute('current_price')
            ?? $holding->getAttribute('market_price')
            ?? $holding->getAttribute('price');

        if (
            $quantity !== null
            && $currentPrice !== null
        ) {
            return (float) $quantity
                * (float) $currentPrice;
        }

        throw new RuntimeException(
            sprintf(
                'Unable to calculate market value for holding ID %s. ' .
                'No historical price, stored market value, or current price was available.',
                $holding->getAttribute('id') ?? 'unknown'
            )
        );
    }

    /**
     * Determine the cash balance for an account.
     */
    private function accountCashValue(
        InvestmentAccount $account
    ): float {
        foreach (
            [
                'cash_balance',
                'cash_value',
                'available_cash',
            ] as $attribute
        ) {
            $value = $account->getAttribute($attribute);

            if ($value !== null) {
                return (float) $value;
            }
        }

        return 0.0;
    }
}