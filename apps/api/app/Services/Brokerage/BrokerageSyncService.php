<?php

namespace App\Services\Brokerage;

use App\Data\Brokerage\BrokerageAccountData;
use App\Data\Brokerage\BrokeragePositionData;
use App\Data\Brokerage\BrokerageTransactionData;
use App\Models\AiInsightRun;
use App\Models\BrokerageConnection;
use App\Models\BrokerageSyncRun;
use App\Models\Holding;
use App\Models\Institution;
use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use App\Models\Security;
use App\Services\AI\AiInsightStalenessService;
use App\Services\Dashboard\DashboardService;
use App\Services\Portfolio\PortfolioSnapshotRecorderService;
use App\Services\Timeline\HoldingChangeDetectionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use App\Jobs\GenerateAiPortfolioInsight;

class BrokerageSyncService
{
    public function __construct(
        private readonly BrokerageProviderManager $providerManager,
        private readonly PortfolioSnapshotRecorderService $snapshotRecorder,
        private readonly HoldingChangeDetectionService $holdingChangeDetector,
        private readonly DashboardService $dashboardService,
        private readonly AiInsightStalenessService $insightStalenessService,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function sync(
        BrokerageConnection $connection,
        string $trigger = 'manual',
    ): array {
        $startedAt = now();
        $startedAtFloat = microtime(true);

        $syncRun = BrokerageSyncRun::query()->create([
            'brokerage_connection_id' =>
                $connection->id,

            'user_id' =>
                $connection->user_id,

            'provider' =>
                $connection->provider,

            'status' =>
                BrokerageSyncRun::STATUS_RUNNING,

            'started_at' =>
                $startedAt,

            'metadata' => [
                'trigger' =>
                    $trigger,
            ],
        ]);

        $connection->update([
            'status' =>
                BrokerageConnection::STATUS_SYNCING,

            'last_sync_started_at' =>
                $startedAt,

            'last_error' =>
                null,
        ]);

        $stats = [
            'accounts' =>
                0,

            'positions' =>
                0,

            'transactions' =>
                0,
        ];

        try {
            $provider = $this->providerManager->driver(
                $connection->provider
            );

            $providerAccounts = $provider->getAccounts(
                $connection
            );

            DB::transaction(
                function () use (
                    $connection,
                    $provider,
                    $providerAccounts,
                    &$stats,
                ): void {
                    foreach (
                        $providerAccounts
                        as $providerAccount
                    ) {
                        $account = $this->syncAccount(
                            $connection,
                            $providerAccount
                        );

                        $stats['accounts']++;

                        $positions = $provider->getPositions(
                            $connection,
                            $providerAccount
                                ->providerAccountId
                        );

                        $this->syncPositions(
                            $account,
                            $positions
                        );

                        $stats['positions'] +=
                            $positions->count();

                        $transactions =
                            $provider->getTransactions(
                                $connection,
                                $providerAccount
                                    ->providerAccountId
                            );

                        $this->syncTransactions(
                            $account,
                            $transactions
                        );

                        $stats['transactions'] +=
                            $transactions->count();
                    }
                }
            );

            $finishedAt = now();

            $durationMs = (int) round(
                (
                    microtime(true)
                    - $startedAtFloat
                ) * 1000
            );

            $connection->update([
                'status' =>
                    BrokerageConnection::STATUS_ACTIVE,

                'connected_at' =>
                    $connection->connected_at
                    ?: now(),

                'last_synced_at' =>
                    $finishedAt,

                'last_successful_sync_at' =>
                    $finishedAt,

                'last_error' =>
                    null,
            ]);

            $syncRun->update([
                'status' =>
                    BrokerageSyncRun::STATUS_SUCCESS,

                'finished_at' =>
                    $finishedAt,

                'accounts_imported' =>
                    $stats['accounts'],

                'positions_imported' =>
                    $stats['positions'],

                'transactions_imported' =>
                    $stats['transactions'],

                'duration_ms' =>
                    $durationMs,

                'metadata' => [
                    'trigger' =>
                        $trigger,

                    'completed_at' =>
                        $finishedAt
                            ->toIso8601String(),
                ],
            ]);

            $freshConnection = $connection
                ->fresh()
                ?->load('user');

            if ($freshConnection !== null) {
                $this->snapshotRecorder->record(
                    $freshConnection,
                    $syncRun->fresh()
                );

                if ($freshConnection->user !== null) {
                    $this->holdingChangeDetector
                        ->detectLatest(
                            $freshConnection->user
                        );
                }
            }

            $this->dashboardService
                ->clearAdvisorAuditCache(
                    $connection->user_id
                );

            $staleInsightCount =
                $this->insightStalenessService
                    ->markIfPortfolioChanged(
                        $connection->user_id,
                        'Portfolio values changed after brokerage synchronization.'
                    );

            $hasCompletedInsight = AiInsightRun::query()
                ->where(
                    'user_id',
                    $connection->user_id,
                )
                ->where(
                    'status',
                    'completed',
                )
                ->exists();

            $shouldGenerateInsight =
                $staleInsightCount > 0
                || ! $hasCompletedInsight;

            if ($shouldGenerateInsight) {
                GenerateAiPortfolioInsight::dispatch(
                    userId:
                        $connection->user_id,

                    trigger:
                        $hasCompletedInsight
                            ? 'brokerage_sync'
                            : 'onboarding_initial_analysis',
                )->delay(
                    now()->addSeconds(15)
                );
            }

return $stats;
        } catch (Throwable $exception) {
            $finishedAt = now();

            $durationMs = (int) round(
                (
                    microtime(true)
                    - $startedAtFloat
                ) * 1000
            );

            $connection->update([
                'status' =>
                    BrokerageConnection::STATUS_ERROR,

                'last_synced_at' =>
                    $finishedAt,

                'last_error' =>
                    $exception->getMessage(),
            ]);

            $syncRun->update([
                'status' =>
                    BrokerageSyncRun::STATUS_FAILED,

                'finished_at' =>
                    $finishedAt,

                'accounts_imported' =>
                    $stats['accounts'],

                'positions_imported' =>
                    $stats['positions'],

                'transactions_imported' =>
                    $stats['transactions'],

                'duration_ms' =>
                    $durationMs,

                'error_message' =>
                    $exception->getMessage(),

                'metadata' => [
                    'trigger' =>
                        $trigger,

                    'exception_class' =>
                        $exception::class,
                ],
            ]);

            throw $exception;
        }
    }

    private function syncAccount(
        BrokerageConnection $connection,
        BrokerageAccountData $data,
    ): InvestmentAccount {
        $institution = null;

        if ($data->institutionName) {
            $institution = Institution::query()
                ->where(
                    'name',
                    $data->institutionName
                )
                ->first();

            if ($institution === null) {
                $baseSlug = Str::slug(
                    $data->institutionName
                );

                $slug = $baseSlug;
                $suffix = 2;

                while (
                    Institution::query()
                        ->where('slug', $slug)
                        ->exists()
                ) {
                    $slug =
                        $baseSlug.'-'.$suffix;

                    $suffix++;
                }

                $institution =
                    Institution::query()->create([
                        'name' =>
                            $data->institutionName,

                        'slug' =>
                            $slug,
                    ]);
            }
        }

        return InvestmentAccount::query()
            ->updateOrCreate(
                [
                    'provider' =>
                        $connection->provider,

                    'provider_account_id' =>
                        $data->providerAccountId,
                ],
                [
                    'user_id' =>
                        $connection->user_id,

                    'brokerage_connection_id' =>
                        $connection->id,

                    'institution_id' =>
                        $institution?->id,

                    'name' =>
                        $data->name,

                    'account_type' =>
                        $this->normalizeAccountType(
                            $data->accountType
                        ),

                    'account_number_mask' =>
                        $data->accountNumberMask,

                    'current_value' =>
                        $data->totalValue,

                    'cash_value' =>
                        $data->cashValue,

                    'provider_synced_at' =>
                        now(),

                    'provider_metadata' =>
                        $data->metadata,
                ],
            );
    }

    /**
     * @param Collection<int, BrokeragePositionData> $positions
     */
    /**
 * @param Collection<int, BrokeragePositionData> $positions
 */
private function syncPositions(
    InvestmentAccount $account,
    Collection $positions,
): void {
    $syncedPositionIds = [];

    $asOfDate = now()->toDateString();

    foreach ($positions as $position) {
        $security = $this->syncSecurity(
            $position,
        );

        /*
         * Holdings are uniquely identified in Helmio by:
         *
         * investment_account_id
         * security_id
         * as_of_date
         *
         * Do not rely exclusively on provider_position_id because a
         * provider may change the identifier or our normalization of
         * that identifier may improve over time.
         */
        $holding = Holding::query()
            ->where(
                'investment_account_id',
                $account->id,
            )
            ->where(
                'security_id',
                $security->id,
            )
            ->whereDate(
                'as_of_date',
                $asOfDate,
            )
            ->first();

        if ($holding === null) {
            $holding = new Holding();

            $holding->investment_account_id =
                $account->id;

            $holding->security_id =
                $security->id;

            $holding->as_of_date =
                $asOfDate;
        }

        $holding->fill([
            'provider_position_id' =>
                $position->providerPositionId,

            'security_id' =>
                $security->id,

            'quantity' =>
                $position->quantity,

            'price' =>
                $position->price,

            'market_value' =>
                $position->marketValue,

            'cost_basis' =>
                $position->costBasis,

            'unrealized_gain_loss' =>
                $position->costBasis !== null
                    ? $position->marketValue
                        - $position->costBasis
                    : null,

            'as_of_date' =>
                $asOfDate,

            'provider_synced_at' =>
                now(),

            'provider_metadata' =>
                $position->metadata,
        ]);

        $holding->save();

        $syncedPositionIds[] =
            $position->providerPositionId;
    }

    /*
     * Remove stale provider-synced holdings for today's snapshot.
     * Limit this cleanup to the current as-of date so historical
     * holding records are never accidentally removed.
     */
    $staleHoldings = Holding::query()
        ->where(
            'investment_account_id',
            $account->id,
        )
        ->whereDate(
            'as_of_date',
            $asOfDate,
        )
        ->whereNotNull(
            'provider_position_id',
        );

    if ($syncedPositionIds !== []) {
        $staleHoldings->whereNotIn(
            'provider_position_id',
            $syncedPositionIds,
        );
    }

    $staleHoldings->delete();

    $account->update([
        'current_value' =>
            (float) $account
                ->holdings()
                ->whereDate(
                    'as_of_date',
                    $asOfDate,
                )
                ->sum('market_value')
            + (float) $account->cash_value,

        'provider_synced_at' =>
            now(),
    ]);
}

    private function syncSecurity(
        BrokeragePositionData $position,
    ): Security {
        $security = null;

        if ($position->providerSecurityId) {
            $security = Security::query()
                ->where(
                    'provider_security_id',
                    $position->providerSecurityId
                )
                ->first();
        }

        if (
            $security === null
            && $position->symbol
        ) {
            $security = Security::query()
                ->where(
                    'symbol',
                    $position->symbol
                )
                ->first();
        }

        if ($security === null) {
            return Security::query()->create([
                'provider_security_id' =>
                    $position->providerSecurityId,

                'symbol' =>
                    $position->symbol,

                'name' =>
                    $position->name,

                'security_type' =>
                    $position->securityType
                    ?: 'other',

                'asset_class' =>
                    $position->assetClass,

                'sector' =>
                    $position->sector,

                'expense_ratio' =>
                    $position->expenseRatio,

                'last_price' =>
                    $position->price,

                'price_as_of' =>
                    now(),
            ]);
        }

        $security->fill([
            'provider_security_id' =>
                $position->providerSecurityId
                ?? $security
                    ->provider_security_id,

            'symbol' =>
                $position->symbol
                ?? $security->symbol,

            'name' =>
                $position->name,

            'security_type' =>
                $position->securityType
                ?: $security->security_type,

            'asset_class' =>
                $position->assetClass
                ?: $security->asset_class,

            'sector' =>
                $position->sector
                ?: $security->sector,

            'expense_ratio' =>
                $position->expenseRatio
                ?? $security->expense_ratio,

            'last_price' =>
                $position->price
                ?? $security->last_price,

            'price_as_of' =>
                $position->price !== null
                    ? now()
                    : $security->price_as_of,
        ])->save();

        return $security;
    }

    /**
     * @param Collection<int, BrokerageTransactionData> $transactions
     */
    private function syncTransactions(
        InvestmentAccount $account,
        Collection $transactions,
    ): void {
        foreach ($transactions as $transaction) {
            $security = null;

            if ($transaction->providerSecurityId) {
                $security = Security::query()
                    ->where(
                        'provider_security_id',
                        $transaction
                            ->providerSecurityId
                    )
                    ->first();
            }

            InvestmentTransaction::query()
                ->updateOrCreate(
                    [
                        'investment_account_id' =>
                            $account->id,

                        'provider_transaction_id' =>
                            $transaction
                                ->providerTransactionId,
                    ],
                    [
                        'security_id' =>
                            $security?->id,

                        'transaction_type' =>
                            $transaction
                                ->transactionType,

                        'transaction_date' =>
                            $transaction
                                ->transactionDate,

                        'settlement_date' =>
                            $transaction
                                ->settlementDate,

                        'quantity' =>
                            $transaction->quantity,

                        'price' =>
                            $transaction->price,

                        'gross_amount' =>
                            $transaction
                                ->grossAmount,

                        'fees' =>
                            $transaction->fees,

                        'net_amount' =>
                            $transaction->netAmount,

                        'description' =>
                            $transaction
                                ->description,

                        'provider_synced_at' =>
                            now(),

                        'provider_metadata' =>
                            $transaction->metadata,

                        'metadata' => [
                            'entry_method' =>
                                'brokerage_sync',

                            'provider_account_id' =>
                                $transaction
                                    ->providerAccountId,

                            'provider_security_id' =>
                                $transaction
                                    ->providerSecurityId,
                        ],
                    ],
                );
        }
    }

    private function normalizeAccountType(
        ?string $accountType,
    ): string {
        $value = strtolower(
            trim((string) $accountType)
        );

        return match (true) {
            str_contains($value, 'roth') =>
                'roth_ira',

            str_contains($value, 'traditional')
                && str_contains($value, 'ira') =>
                'traditional_ira',

            str_contains($value, 'sep') =>
                'sep_ira',

            str_contains($value, '401') =>
                '401k',

            str_contains($value, '403') =>
                '403b',

            str_contains($value, 'joint') =>
                'joint',

            str_contains($value, 'trust') =>
                'trust',

            str_contains($value, '529') =>
                '529',

            str_contains($value, 'individual')
                || str_contains($value, 'brokerage') =>
                'individual',

            default =>
                'other',
        };
    }
}