<?php

namespace App\Services\Brokerage;

use App\Data\Brokerage\BrokerageAccountData;
use App\Data\Brokerage\BrokeragePositionData;
use App\Data\Brokerage\BrokerageTransactionData;
use App\Models\BrokerageConnection;
use App\Models\Holding;
use App\Models\Institution;
use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use App\Models\Security;
use Illuminate\Support\Facades\DB;
use Throwable;

class BrokerageSyncService
{
    public function __construct(
        private readonly BrokerageProviderManager $providerManager,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function sync(
        BrokerageConnection $connection,
    ): array {
        $connection->update([
            'status' => BrokerageConnection::STATUS_SYNCING,
            'last_sync_started_at' => now(),
            'last_error' => null,
        ]);

        $stats = [
            'accounts' => 0,
            'positions' => 0,
            'transactions' => 0,
        ];

        try {
            $provider = $this->providerManager->driver(
                $connection->provider,
            );

            $providerAccounts = $provider->getAccounts(
                $connection,
            );

            DB::transaction(
                function () use (
                    $connection,
                    $provider,
                    $providerAccounts,
                    &$stats,
                ): void {
                    foreach ($providerAccounts as $providerAccount) {
                        $account = $this->syncAccount(
                            $connection,
                            $providerAccount,
                        );

                        $stats['accounts']++;

                        $positions = $provider->getPositions(
                            $connection,
                            $providerAccount->providerAccountId,
                        );

                        $this->syncPositions(
                            $account,
                            $positions,
                        );

                        $stats['positions'] += $positions->count();

                        $transactions = $provider->getTransactions(
                            $connection,
                            $providerAccount->providerAccountId,
                        );

                        $this->syncTransactions(
                            $account,
                            $transactions,
                        );

                        $stats['transactions'] += $transactions->count();
                    }
                },
            );

            $connection->update([
                'status' => BrokerageConnection::STATUS_ACTIVE,
                'connected_at' =>
                    $connection->connected_at ?: now(),
                'last_synced_at' => now(),
                'last_successful_sync_at' => now(),
                'last_error' => null,
            ]);

            return $stats;
        } catch (Throwable $exception) {
            $connection->update([
                'status' => BrokerageConnection::STATUS_ERROR,
                'last_synced_at' => now(),
                'last_error' => $exception->getMessage(),
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
            $institution = Institution::query()->firstOrCreate([
                'name' => $data->institutionName,
            ]);
        }

        return InvestmentAccount::query()->updateOrCreate(
            [
                'provider' => $connection->provider,
                'provider_account_id' => $data->providerAccountId,
            ],
            [
                'user_id' => $connection->user_id,
                'brokerage_connection_id' => $connection->id,
                'institution_id' => $institution?->id,
                'name' => $data->name,
                'account_type' =>
                    $this->normalizeAccountType(
                        $data->accountType,
                    ),
                'account_number_mask' =>
                    $data->accountNumberMask,
                'current_value' => $data->totalValue,
                'cash_value' => $data->cashValue,
                'provider_synced_at' => now(),
                'provider_metadata' => $data->metadata,
            ],
        );
    }

    /**
     * @param \Illuminate\Support\Collection<int, BrokeragePositionData> $positions
     */
    private function syncPositions(
        InvestmentAccount $account,
        $positions,
    ): void {
        $syncedPositionIds = [];

        foreach ($positions as $position) {
            $security = $this->syncSecurity($position);

            Holding::query()->updateOrCreate(
                [
                    'investment_account_id' => $account->id,
                    'provider_position_id' =>
                        $position->providerPositionId,
                ],
                [
                    'security_id' => $security->id,
                    'quantity' => $position->quantity,
                    'price' => $position->price,
                    'market_value' => $position->marketValue,
                    'cost_basis' => $position->costBasis,
                    'provider_synced_at' => now(),
                    'provider_metadata' => $position->metadata,
                ],
            );

            $syncedPositionIds[] =
                $position->providerPositionId;
        }

        /*
         * Remove stale imported positions. Manual positions have a null
         * provider_position_id and are not affected.
         */
        Holding::query()
            ->where('investment_account_id', $account->id)
            ->whereNotNull('provider_position_id')
            ->when(
                $syncedPositionIds !== [],
                fn ($query) => $query->whereNotIn(
                    'provider_position_id',
                    $syncedPositionIds,
                ),
            )
            ->delete();

        $account->update([
            'current_value' =>
                (float) $account->holdings()->sum('market_value')
                + (float) $account->cash_value,

            'provider_synced_at' => now(),
        ]);
    }

    private function syncSecurity(
        BrokeragePositionData $position,
    ): Security {
        $security = null;

        if ($position->symbol) {
            $security = Security::query()
                ->where('symbol', $position->symbol)
                ->first();
        }

        if ($security === null) {
            $security = Security::query()->firstOrCreate(
                [
                    'name' => $position->name,
                    'symbol' => $position->symbol,
                ],
                [
                    'security_type' =>
                        $position->securityType ?: 'other',

                    'asset_class' =>
                        $position->assetClass,

                    'sector' =>
                        $position->sector,

                    'expense_ratio' =>
                        $position->expenseRatio,
                ],
            );
        } else {
            $security->fill([
                'name' => $position->name,
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
            ])->save();
        }

        return $security;
    }

    /**
     * @param \Illuminate\Support\Collection<int, BrokerageTransactionData> $transactions
     */
    private function syncTransactions(
        InvestmentAccount $account,
        $transactions,
    ): void {
        foreach ($transactions as $transaction) {
            $security = null;

            if ($transaction->providerSecurityId) {
                $security = Security::query()
                    ->where(
                        'provider_security_id',
                        $transaction->providerSecurityId,
                    )
                    ->first();
            }

            InvestmentTransaction::query()->updateOrCreate(
                [
                    'investment_account_id' => $account->id,
                    'provider_transaction_id' =>
                        $transaction->providerTransactionId,
                ],
                [
                    'security_id' => $security?->id,
                    'transaction_type' =>
                        $transaction->transactionType,
                    'transaction_date' =>
                        $transaction->transactionDate,
                    'settlement_date' =>
                        $transaction->settlementDate,
                    'quantity' => $transaction->quantity,
                    'price' => $transaction->price,
                    'gross_amount' =>
                        $transaction->grossAmount,
                    'fees' => $transaction->fees,
                    'net_amount' => $transaction->netAmount,
                    'description' =>
                        $transaction->description,
                    'provider_synced_at' => now(),
                    'provider_metadata' =>
                        $transaction->metadata,
                    'metadata' => [
                        'entry_method' =>
                            'brokerage_sync',
                    ],
                ],
            );
        }
    }

    private function normalizeAccountType(
        ?string $accountType,
    ): string {
        $value = strtolower(
            trim((string) $accountType),
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

            default => 'other',
        };
    }
}