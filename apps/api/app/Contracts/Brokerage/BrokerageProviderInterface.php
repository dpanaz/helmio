<?php

namespace App\Contracts\Brokerage;

use App\Data\Brokerage\BrokerageAccountData;
use App\Data\Brokerage\BrokeragePositionData;
use App\Data\Brokerage\BrokerageTransactionData;
use App\Models\BrokerageConnection;
use App\Models\BrokerageProviderUser;
use App\Models\User;
use Illuminate\Support\Collection;

interface BrokerageProviderInterface
{
    public function providerName(): string;

    public function registerUser(
        User $user,
    ): BrokerageProviderUser;

    public function createConnectionUrl(
        User $user,
        string $redirectUrl,
        ?string $brokerageSlug = null,
        ?BrokerageConnection $reconnect = null,
    ): string;

    /**
     * @return Collection<int, BrokerageConnection>
     */
    public function listConnections(
        User $user,
    ): Collection;

    /**
     * @return Collection<int, BrokerageAccountData>
     */
    public function getAccounts(
        BrokerageConnection $connection,
    ): Collection;

    /**
     * @return Collection<int, BrokeragePositionData>
     */
    public function getPositions(
        BrokerageConnection $connection,
        string $providerAccountId,
    ): Collection;

    /**
     * @return Collection<int, BrokerageTransactionData>
     */
    public function getTransactions(
        BrokerageConnection $connection,
        string $providerAccountId,
        ?string $cursor = null,
    ): Collection;

    public function requestRefresh(
        BrokerageConnection $connection,
    ): void;

    public function disconnect(
        BrokerageConnection $connection,
    ): void;
}