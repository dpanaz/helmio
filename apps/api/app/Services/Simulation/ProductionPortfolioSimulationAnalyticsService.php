<?php

namespace App\Services\Simulation;

use App\Data\Simulation\SimulatedHoldingData;
use App\Data\Simulation\SimulatedPortfolioData;
use App\Models\Holding;
use App\Models\InvestmentAccount;
use App\Models\Security;
use App\Services\Analytics\HelmScoreService;
use Illuminate\Support\Collection;

class ProductionPortfolioSimulationAnalyticsService
{
    public function __construct(
        private readonly HelmScoreService $helmScoreService,
    ) {
    }

    /**
     * Run Helmio's production portfolio-state analytics against
     * a hypothetical portfolio without saving anything.
     *
     * @param Collection<int, InvestmentAccount> $sourceAccounts
     * @return array<string, mixed>
     */
    public function analyze(
        Collection $sourceAccounts,
        SimulatedPortfolioData $portfolio,
    ): array {
        if ($sourceAccounts->isEmpty()) {
            return $this->emptyResult();
        }

        $sourceAccounts->loadMissing([
            'holdings.security',
            'transactions',
            'institution',
        ]);

        $accounts =
            $this->buildTransientAccounts(
                $sourceAccounts,
                $portfolio,
            );

        return $this->helmScoreService
            ->calculatePortfolioStateCategories(
                $accounts
            );
    }

    /**
     * @param Collection<int, InvestmentAccount> $sourceAccounts
     * @return Collection<int, InvestmentAccount>
     */
    private function buildTransientAccounts(
        Collection $sourceAccounts,
        SimulatedPortfolioData $portfolio,
    ): Collection {
        /** @var InvestmentAccount $primaryAccount */
        $primaryAccount =
            $sourceAccounts->first();

        $primaryAccountId =
            $primaryAccount->id;

        $sourceAccountIds =
            $sourceAccounts
                ->pluck('id')
                ->map(
                    fn ($id) => (int) $id
                )
                ->all();

        /*
         * New hypothetical securities do not yet have an account
         * assignment. Place them in the first account for now.
         *
         * Existing holdings retain their original account ID.
         */
        $holdingsByAccount =
            $portfolio->holdings
                ->groupBy(
                    function (
                        SimulatedHoldingData $holding
                    ) use (
                        $sourceAccountIds,
                        $primaryAccountId,
                    ) {
                        if (
                            $holding->accountId !== null
                            && in_array(
                                (int) $holding->accountId,
                                $sourceAccountIds,
                                true,
                            )
                        ) {
                            return
                                (int) $holding->accountId;
                        }

                        return
                            (int) $primaryAccountId;
                    }
                );

        $securityIds =
            $portfolio->holdings
                ->pluck('securityId')
                ->filter(
                    fn ($id) =>
                        $id !== null
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values();

        $securities =
            Security::query()
                ->whereIn(
                    'id',
                    $securityIds
                )
                ->get()
                ->keyBy('id');

        $cashAllocation =
            $this->allocateCash(
                $sourceAccounts,
                $portfolio->cash,
            );

        $syntheticHoldingId = -1;

        return $sourceAccounts
            ->map(
                function (
                    InvestmentAccount $sourceAccount
                ) use (
                    $holdingsByAccount,
                    $securities,
                    $cashAllocation,
                    &$syntheticHoldingId,
                ): InvestmentAccount {
                    $account =
                        new InvestmentAccount();

                    /*
                     * Copy all real account attributes:
                     *
                     * advisory fee
                     * account fee
                     * account type
                     * user_id
                     * institution_id
                     * etc.
                     */
                    $account->forceFill(
                        $sourceAccount->getAttributes()
                    );

                    /*
                     * Mark as existing so Eloquent behaves like a
                     * normal hydrated account, while nothing is saved.
                     */
                    $account->exists = true;

                    $simulatedHoldings =
                        collect(
                            $holdingsByAccount->get(
                                $sourceAccount->id,
                                collect(),
                            )
                        )
                            ->map(
                                function (
                                    SimulatedHoldingData $simulated
                                ) use (
                                    $securities,
                                    $sourceAccount,
                                    &$syntheticHoldingId,
                                ): Holding {
                                    $holding =
                                        new Holding();

                                    $holding->forceFill([
                                        'id' =>
                                            $syntheticHoldingId--,

                                        'investment_account_id' =>
                                            $sourceAccount->id,

                                        'security_id' =>
                                            $simulated->securityId,

                                        'quantity' =>
                                            $simulated->quantity,

                                        'market_value' =>
                                            $simulated->marketValue,

                                        'cost_basis' =>
                                            $simulated->costBasis,
                                    ]);

                                    $holding->exists = true;

                                    $security =
                                        $simulated->securityId !== null
                                            ? $securities->get(
                                                $simulated->securityId
                                            )
                                            : null;

                                    if (! $security) {
                                        $security =
                                            $this->syntheticSecurity(
                                                $simulated
                                            );
                                    }

                                    /*
                                     * Production analytics accesses:
                                     *
                                     * $holding->security
                                     */
                                    $holding->setRelation(
                                        'security',
                                        $security,
                                    );

                                    return $holding;
                                }
                            )
                            ->values();

                    $cash =
                        (float) (
                            $cashAllocation[
                                $sourceAccount->id
                            ]
                            ?? 0
                        );

                    $holdingsValue =
                        (float) $simulatedHoldings
                            ->sum('market_value');

                    /*
                     * Production CostAnalyticsService reads these
                     * directly from InvestmentAccount.
                     */
                    $account->setAttribute(
                        'cash_value',
                        $cash,
                    );

                    $account->setAttribute(
                        'current_value',
                        $holdingsValue + $cash,
                    );

                    /*
                     * Preload every relation used by the production
                     * services so loadMissing() never tries to find
                     * these transient holdings in the database.
                     */
                    $account->setRelation(
                        'holdings',
                        $simulatedHoldings,
                    );

                    $account->setRelation(
                        'transactions',
                        $sourceAccount->transactions,
                    );

                    $account->setRelation(
                        'institution',
                        $sourceAccount->institution,
                    );

                    return $account;
                }
            )
            ->values();
    }

    /**
     * Allocate the simulated portfolio's total cash across the
     * original accounts.
     *
     * This preserves the original relative cash distribution
     * for multi-account portfolios.
     *
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<int, float>
     */
    private function allocateCash(
        Collection $accounts,
        float $simulatedCash,
    ): array {
        $simulatedCash =
            max(
                0,
                $simulatedCash
            );

        $originalCash =
            (float) $accounts->sum(
                fn (InvestmentAccount $account) =>
                    (float) $account->cash_value
            );

        $allocation = [];

        if ($originalCash > 0) {
            $remaining =
                $simulatedCash;

            $accounts
                ->values()
                ->each(
                    function (
                        InvestmentAccount $account,
                        int $index,
                    ) use (
                        $accounts,
                        $originalCash,
                        $simulatedCash,
                        &$remaining,
                        &$allocation,
                    ): void {
                        $isLast =
                            $index
                            === $accounts->count() - 1;

                        if ($isLast) {
                            $value =
                                $remaining;
                        } else {
                            $weight =
                                (float) $account->cash_value
                                / $originalCash;

                            $value =
                                $simulatedCash
                                * $weight;

                            $remaining -=
                                $value;
                        }

                        $allocation[
                            $account->id
                        ] = max(
                            0,
                            $value
                        );
                    }
                );

            return $allocation;
        }

        /** @var InvestmentAccount|null $first */
        $first =
            $accounts->first();

        if ($first) {
            $allocation[
                $first->id
            ] = $simulatedCash;
        }

        return $allocation;
    }

    private function syntheticSecurity(
        SimulatedHoldingData $holding,
    ): Security {
        $security =
            new Security();

        $security->forceFill([
            'id' =>
                $holding->securityId,

            'symbol' =>
                $holding->symbol,

            'name' =>
                $holding->name,

            'asset_class' =>
                $holding->assetClass,

            'sector' =>
                $holding->sector,

            'expense_ratio' =>
                $holding->expenseRatio,
        ]);

        $security->exists = false;

        return $security;
    }

    private function emptyResult(): array
    {
        return [
            'categories' => [
                'cost' => [
                    'score' => null,
                ],

                'diversification' => [
                    'score' => null,
                ],
            ],

            'cost_analytics' =>
                [],

            'fund_analytics' =>
                [],

            'diversification_analytics' =>
                [],
        ];
    }
}