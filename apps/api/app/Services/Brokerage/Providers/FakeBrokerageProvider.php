<?php

namespace App\Services\Brokerage\Providers;

use App\Contracts\Brokerage\BrokerageProviderInterface;
use App\Data\Brokerage\BrokerageAccountData;
use App\Data\Brokerage\BrokeragePositionData;
use App\Data\Brokerage\BrokerageTransactionData;
use App\Models\BrokerageConnection;
use App\Models\BrokerageProviderUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FakeBrokerageProvider implements BrokerageProviderInterface
{
    public function providerName(): string
    {
        return 'fake';
    }

    public function registerUser(
        User $user,
    ): BrokerageProviderUser {
        return BrokerageProviderUser::query()
            ->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'provider' => $this->providerName(),
                ],
                [
                    'provider_user_id' =>
                        'helmio-'.$user->id,

                    'provider_user_secret' =>
                        Str::random(64),

                    'registered_at' => now(),
                ],
            );
    }

    public function createConnectionUrl(
        User $user,
        string $redirectUrl,
        ?string $brokerageSlug = null,
        ?BrokerageConnection $reconnect = null,
    ): string {
        $this->registerUser($user);

        return route('brokerage-connections.fake-complete', [
            'redirect' => $redirectUrl,
        ]);
    }

    public function listConnections(
        User $user,
    ): Collection {
        return $user
            ->brokerageConnections()
            ->where('provider', $this->providerName())
            ->get();
    }

    public function getAccounts(
        BrokerageConnection $connection,
    ): Collection {
        return collect([
            new BrokerageAccountData(
                providerAccountId:
                    'fake-account-'.$connection->id,

                name: 'Synced Brokerage Account',
                institutionName:
                    $connection->brokerage_name
                    ?: 'Helmio Test Brokerage',

                accountType: 'individual',
                accountNumberMask: '4321',
                totalValue: 250000,
                cashValue: 15000,
                metadata: [
                    'development_provider' => true,
                ],
            ),
        ]);
    }

    public function getPositions(
        BrokerageConnection $connection,
        string $providerAccountId,
    ): Collection {
        return collect([
            new BrokeragePositionData(
                providerPositionId: 'fake-position-aapl',
                providerSecurityId: 'fake-security-aapl',
                symbol: 'AAPL',
                name: 'Apple Inc.',
                securityType: 'stock',
                assetClass: 'US Equity',
                sector: 'Technology',
                quantity: 500,
                price: 200,
                marketValue: 100000,
                costBasis: 75000,
                expenseRatio: null,
            ),

            new BrokeragePositionData(
                providerPositionId: 'fake-position-vti',
                providerSecurityId: 'fake-security-vti',
                symbol: 'VTI',
                name: 'Vanguard Total Stock Market ETF',
                securityType: 'etf',
                assetClass: 'US Equity',
                sector: null,
                quantity: 500,
                price: 270,
                marketValue: 135000,
                costBasis: 110000,
                expenseRatio: 0.0003,
            ),
        ]);
    }

    public function getTransactions(
        BrokerageConnection $connection,
        string $providerAccountId,
        ?string $cursor = null,
    ): Collection {
        return collect();
    }

    public function requestRefresh(
        BrokerageConnection $connection,
    ): void {
        $connection->update([
            'status' => BrokerageConnection::STATUS_SYNCING,
            'last_sync_started_at' => now(),
        ]);
    }

    public function disconnect(
        BrokerageConnection $connection,
    ): void {
        $connection->update([
            'status' =>
                BrokerageConnection::STATUS_DISCONNECTED,

            'disabled_at' => now(),
        ]);
    }
}
