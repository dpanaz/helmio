<?php

namespace App\Services\Portfolio;

use App\Models\BrokerageConnection;
use App\Models\BrokerageSyncRun;
use App\Models\InvestmentAccount;
use App\Models\PortfolioStateSnapshot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PortfolioSnapshotRecorderService
{
    public function record(
        BrokerageConnection $connection,
        BrokerageSyncRun $syncRun,
    ): PortfolioStateSnapshot {
        return DB::transaction(
            function () use (
                $connection,
                $syncRun,
            ): PortfolioStateSnapshot {
                $accounts = InvestmentAccount::query()
                    ->where(
                        'user_id',
                        $connection->user_id,
                    )
                    ->with([
                        'holdings.security',
                    ])
                    ->orderBy('id')
                    ->get();

                $portfolioValue = (float) $accounts
                    ->sum('current_value');

                $cashValue = (float) $accounts
                    ->sum('cash_value');

                $holdings = $accounts
                    ->flatMap(
                        fn (
                            InvestmentAccount $account,
                        ): Collection => $account
                            ->holdings
                            ->map(
                                fn ($holding): array => [
                                    'account' => $account,
                                    'holding' => $holding,
                                ],
                            ),
                    )
                    ->values();

                $snapshot = PortfolioStateSnapshot::query()
                    ->updateOrCreate(
                        [
                            'brokerage_sync_run_id' =>
                                $syncRun->id,
                        ],
                        [
                            'user_id' =>
                                $connection->user_id,

                            'brokerage_connection_id' =>
                                $connection->id,

                            'source' =>
                                'brokerage_sync',

                            'captured_at' =>
                                $syncRun->finished_at
                                ?? now(),

                            'portfolio_value' =>
                                $portfolioValue,

                            'cash_value' =>
                                $cashValue,

                            'invested_value' =>
                                max(
                                    0,
                                    $portfolioValue
                                        - $cashValue,
                                ),

                            'account_count' =>
                                $accounts->count(),

                            'holding_count' =>
                                $holdings->count(),

                            'metadata' => [
                                'provider' =>
                                    $connection->provider,

                                'connection_status' =>
                                    $connection->status,

                                'brokerage_connection_id' =>
                                    $connection->id,

                                'brokerage_sync_run_id' =>
                                    $syncRun->id,
                            ],
                        ],
                    );

                /*
                 * Rebuild the holding-state rows whenever the same sync
                 * run is processed again.
                 */
                $snapshot->holdings()->delete();

                foreach ($holdings as $item) {
                    /** @var InvestmentAccount $account */
                    $account = $item['account'];

                    $holding = $item['holding'];
                    $security = $holding->security;

                    $marketValue = (float) (
                        $holding->market_value ?? 0
                    );

                    $weight = $portfolioValue > 0
                        ? $marketValue / $portfolioValue
                        : null;

                    /*
                     * Provider position IDs are preferred because they
                     * remain stable across syncs. Manual holdings fall
                     * back to the holding row's primary key.
                     */
                    $holdingKey = $this->holdingKey(
                        accountId: $account->id,
                        holdingId: $holding->id,
                        providerPositionId:
                            $holding->provider_position_id,
                    );

                    $snapshot->holdings()->create([
                        'investment_account_id' =>
                            $account->id,

                        'security_id' =>
                            $security?->id,

                        'holding_key' =>
                            $holdingKey,

                        'symbol' =>
                            $security?->symbol,

                        'name' =>
                            $security?->name
                            ?? 'Unknown security',

                        'security_type' =>
                            $security?->security_type,

                        'asset_class' =>
                            $security?->asset_class,

                        'sector' =>
                            $security?->sector,

                        'quantity' =>
                            (float) $holding->quantity,

                        'price' =>
                            $holding->price !== null
                                ? (float) $holding->price
                                : null,

                        'market_value' =>
                            $marketValue,

                        'cost_basis' =>
                            $holding->cost_basis !== null
                                ? (float) $holding->cost_basis
                                : null,

                        'portfolio_weight' =>
                            $weight,

                        'metadata' => [
                            'holding_id' =>
                                $holding->id,

                            'provider_position_id' =>
                                $holding
                                    ->provider_position_id,

                            'as_of_date' =>
                                $holding->as_of_date
                                    ?->toDateString(),

                            'account_name' =>
                                $account->name,

                            'institution_name' =>
                                $account
                                    ->institution
                                    ?->name,
                        ],
                    ]);
                }

                return $snapshot->load('holdings');
            },
        );
    }

    private function holdingKey(
        int $accountId,
        int $holdingId,
        ?string $providerPositionId,
    ): string {
        $identity = filled($providerPositionId)
            ? 'provider-'.$providerPositionId
            : 'holding-'.$holdingId;

        return $accountId.':'.$identity;
    }
}