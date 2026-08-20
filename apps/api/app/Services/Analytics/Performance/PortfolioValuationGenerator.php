<?php

namespace App\Services\Analytics\Performance;

use App\Models\Holding;
use App\Models\InvestmentAccount;
use App\Models\PortfolioValuation;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use RuntimeException;

class PortfolioValuationGenerator
{
    public function __construct(
        private readonly PortfolioCashFlowService $cashFlowService,
        private readonly HistoricalPriceService $historicalPriceService,
        private readonly HistoricalQuantityService $historicalQuantityService,
        private readonly HistoricalCashBalanceService $historicalCashBalanceService,
    ) {
    }

    /**
     * Generate account-level valuations and one consolidated
     * portfolio valuation for the supplied user and date.
     *
     * Dates before the user has a genuine non-cash invested position are
     * intentionally excluded from performance/risk valuation history.
     *
     * @return array<string, mixed>
     */
    public function generateForUser(
        User $user,
        CarbonInterface $valuationDate
    ): array {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with('holdings.security')
            ->get();

        $accountValuations = collect();

        foreach ($accounts as $account) {
            $valuation = $this->generateForAccount(
                account: $account,
                valuationDate: $valuationDate,
            );

            if ($valuation !== null) {
                $accountValuations->push($valuation);
            }
        }

        $portfolioValuation = $this->generateConsolidated(
            user: $user,
            accountValuations: $accountValuations,
            valuationDate: $valuationDate,
        );

        return [
            'user_id' => $user->id,
            'valuation_date' => $valuationDate->toDateString(),

            // Preserve the original meaning of account_count: how many
            // investment accounts belong to the user.
            'account_count' => $accounts->count(),

            // Helpful when some accounts have not yet begun invested history.
            'active_account_count' => $accountValuations->count(),

            'account_valuations' => $accountValuations
                ->map(
                    fn (PortfolioValuation $valuation): array => [
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

            'portfolio_valuation' =>
                $portfolioValuation === null
                    ? null
                    : [
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
     *
     * A generated valuation is persisted only when the account has a
     * genuine non-cash position with a positive historical market value
     * on the requested date.
     */
    public function generateForAccount(
        InvestmentAccount $account,
        CarbonInterface $valuationDate
    ): ?PortfolioValuation {
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
                            ['cash'],
                            true,
                        )
                    ) {
                        return false;
                    }

                    return (
                        (float) (
                            $historicalQuantities[
                                $holding->id
                            ] ?? 0
                        )
                    ) > 0;
                },
            );

        /*
         * Only holdings that actually existed on this historical date
         * participate in historical-price coverage calculations.
         */
        $priceableHoldings = $investedHoldings
            ->filter(
                function (Holding $holding) use (
                    $historicalQuantities,
                ): bool {
                    if ($holding->security === null) {
                        return false;
                    }

                    if (
                        $holding->security->security_type === 'cash'
                    ) {
                        return false;
                    }

                    return (
                        (float) (
                            $historicalQuantities[
                                $holding->id
                            ] ?? 0
                        )
                    ) > 0;
                }
            )
            ->values();

        $historicalPriceCount = $priceableHoldings
            ->filter(
                function (
                    Holding $holding
                ) use ($valuationDate): bool {
                    $securityId =
                        $holding->getAttribute('security_id');

                    if ($securityId === null) {
                        return false;
                    }

                    return $this->historicalPriceService
                        ->valueOnOrBefore(
                            security: (int) $securityId,
                            date: $valuationDate,
                        ) !== null;
                }
            )
            ->count();

        $missingHistoricalPriceCount =
            $priceableHoldings->count()
            - $historicalPriceCount;

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

        /*
         * Reconstruct non-security cash for the requested valuation date.
         *
         * Using today's account cash balance for every historical date causes
         * future deposits, withdrawals, buys, sells, dividends, and fees to
         * leak backward into historical portfolio values. That can make TWR
         * double-count external flows and create artificial gains/losses.
         */
        $cashValue =
            $this->historicalCashBalanceService
                ->balanceOnDate(
                    account: $account,
                    date: $valuationDate,
                )
            + $cashEquivalentValue;

        $cashFlow = $this->cashFlowService
            ->forAccountOnDate(
                investmentAccountId: $account->id,
                date: $valuationDate,
            );

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

        /*
         * Critical guard:
         *
         * Do not create performance/risk history from a tiny cash-only
         * balance before the account actually owns an investment.
         *
         * Historical market value must also be positive. This prevents
         * missing historical prices from creating misleading zero-market
         * valuation rows.
         */
        if (
            ! $hasInvestedPositions
            || $marketValue <= 0
        ) {
            if (
                $valuation !== null
                && $valuation->source === 'generated'
            ) {
                $valuation->delete();
            }

            /*
             * Preserve an explicit/manual valuation if one already exists.
             * Generated history is the only history this service owns.
             */
            if (
                $valuation !== null
                && $valuation->source !== 'generated'
            ) {
                return $valuation;
            }

            return null;
        }

        if ($valuation === null) {
            $valuation = new PortfolioValuation();

            $valuation->user_id = $account->user_id;
            $valuation->investment_account_id =
                $account->id;
            $valuation->valuation_date =
                $valuationDate->toDateString();
        }

        /*
         * Do not silently replace an explicit/manual valuation with a
         * generated one.
         */
        if (
            $valuation->exists
            && $valuation->source !== null
            && $valuation->source !== 'generated'
        ) {
            return $valuation;
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
                    true,

                'historical_quantities' =>
                    $historicalQuantities,
            ],
        ]);

        $valuation->save();

        return $valuation->refresh();
    }

    /**
     * Generate a consolidated valuation by adding together the valid
     * account-level valuation records.
     *
     * No consolidated generated valuation is persisted until at least one
     * account has genuine invested history.
     */
    private function generateConsolidated(
        User $user,
        Collection $accountValuations,
        CarbonInterface $valuationDate
    ): ?PortfolioValuation {
        $existingValuation = PortfolioValuation::query()
            ->where('user_id', $user->id)
            ->whereNull('investment_account_id')
            ->whereDate(
                'valuation_date',
                $valuationDate->toDateString()
            )
            ->first();

        /*
         * If no account qualifies for performance/risk history on this
         * date, remove only a previously generated consolidated row.
         */
        if ($accountValuations->isEmpty()) {
            if (
                $existingValuation !== null
                && $existingValuation->source === 'generated'
            ) {
                $existingValuation->delete();
            }

            if (
                $existingValuation !== null
                && $existingValuation->source !== 'generated'
            ) {
                return $existingValuation;
            }

            return null;
        }

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

        $hasInvestedPositions =
            $accountValuations->contains(
                fn (
                    PortfolioValuation $valuation
                ): bool =>
                    (bool) data_get(
                        $valuation->metadata,
                        'has_invested_positions',
                        false,
                    )
                    || (float) $valuation->market_value > 0,
            );

        /*
         * A consolidated performance/risk valuation must have a positive
         * invested market value. Cash-only consolidated rows are excluded.
         */
        if (
            ! $hasInvestedPositions
            || $marketValue <= 0
        ) {
            if (
                $existingValuation !== null
                && $existingValuation->source === 'generated'
            ) {
                $existingValuation->delete();
            }

            if (
                $existingValuation !== null
                && $existingValuation->source !== 'generated'
            ) {
                return $existingValuation;
            }

            return null;
        }

        $valuation =
            $existingValuation
            ?? new PortfolioValuation();

        if (! $valuation->exists) {
            $valuation->user_id = $user->id;
            $valuation->investment_account_id = null;
            $valuation->valuation_date =
                $valuationDate->toDateString();
        }

        /*
         * Preserve explicit/manual consolidated valuations.
         */
        if (
            $valuation->exists
            && $valuation->source !== null
            && $valuation->source !== 'generated'
        ) {
            return $valuation;
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
                    true,
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
     * Historical dates must use historical quantities and historical prices.
     * Current stored values/prices are allowed only for today's valuation so
     * a present-day value is never silently backfilled into the past.
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

        /*
         * The position did not exist on this historical date.
         */
        if (
            $quantity !== null
            && (float) $quantity <= 0
        ) {
            return 0.0;
        }

        $securityId =
            $holding->getAttribute('security_id');

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

        /*
         * Never use today's stored market value or current price to invent
         * a historical value. Missing historical prices are intentionally
         * represented as zero and surfaced through data-quality warnings.
         */
        if (
            $valuationDate->toDateString()
            < now()->toDateString()
        ) {
            return 0.0;
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

}