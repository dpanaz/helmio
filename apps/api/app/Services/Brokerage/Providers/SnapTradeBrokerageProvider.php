<?php

namespace App\Services\Brokerage\Providers;

use App\Contracts\Brokerage\BrokerageProviderInterface;
use App\Data\Brokerage\BrokerageAccountData;
use App\Data\Brokerage\BrokeragePositionData;
use App\Data\Brokerage\BrokerageTransactionData;
use App\Models\BrokerageConnection;
use App\Models\BrokerageProviderUser;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use SnapTrade\Client;
use Throwable;

class SnapTradeBrokerageProvider implements BrokerageProviderInterface
{
    private const PROVIDER = 'snaptrade';

    private const TRANSACTION_PAGE_SIZE = 1000;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function providerName(): string
    {
        return self::PROVIDER;
    }

    public function registerUser(
        User $user,
    ): BrokerageProviderUser {
        $existing = BrokerageProviderUser::query()
            ->where('user_id', $user->id)
            ->where('provider', self::PROVIDER)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $providerUserId =
            'helmio-user-'.$user->id;

        try {
            $response = $this->client
                ->authentication
                ->registerSnapTradeUser(
                    user_id: $providerUserId,
                );

            $data = $this->toArray(
                $response,
            );

            $returnedUserId =
                $this->stringValue(
                    data_get(
                        $data,
                        'userId',
                    )
                    ?? data_get(
                        $data,
                        'user_id',
                    ),
                )
                ?: $providerUserId;

            $userSecret =
                $this->stringValue(
                    data_get(
                        $data,
                        'userSecret',
                    )
                    ?? data_get(
                        $data,
                        'user_secret',
                    ),
                );

            if ($userSecret === null) {
                throw new RuntimeException(
                    'SnapTrade did not return a user secret.',
                );
            }

            return BrokerageProviderUser::query()
                ->create([
                    'user_id' =>
                        $user->id,

                    'provider' =>
                        self::PROVIDER,

                    'provider_user_id' =>
                        $returnedUserId,

                    'provider_user_secret' =>
                        $userSecret,

                    'registered_at' =>
                        now(),

                    'last_verified_at' =>
                        now(),

                    'metadata' => [
                        'registration_source' =>
                            'snaptrade_api',
                    ],
                ]);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Unable to register the Helmio user with SnapTrade: '
                .$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    public function createConnectionUrl(
        User $user,
        string $redirectUrl,
        ?string $brokerageSlug = null,
        ?BrokerageConnection $reconnect = null,
    ): string {
        $providerUser =
            $this->registerUser(
                $user,
            );

        $response = $this->client
            ->authentication
            ->loginSnapTradeUser(
                user_id:
                    $providerUser->provider_user_id,

                user_secret:
                    $providerUser->provider_user_secret,

                broker:
                    $brokerageSlug,

                immediate_redirect:
                    true,

                custom_redirect:
                    $redirectUrl,

                reconnect:
                    $reconnect?->provider_connection_id,

                connection_type:
                    'read',

                show_close_button:
                    true,

                dark_mode:
                    false,

                connection_portal_version:
                    'v4',
            );

        $data = $this->toArray(
            $response,
        );

        $url =
            $this->stringValue(
                data_get(
                    $data,
                    'redirectURI',
                )
                ?? data_get(
                    $data,
                    'redirectUri',
                )
                ?? data_get(
                    $data,
                    'redirect_uri',
                )
                ?? data_get(
                    $data,
                    'url',
                ),
            );

        if ($url === null) {
            throw new RuntimeException(
                'SnapTrade did not return a Connection Portal URL.',
            );
        }

        return $url;
    }

    /**
     * Reconcile remote SnapTrade connections into Helmio.
     *
     * @return Collection<int, BrokerageConnection>
     */
    public function listConnections(
        User $user,
    ): Collection {
        $providerUser =
            $this->providerUser(
                $user,
            );

        $response = $this->client
            ->connections
            ->listBrokerageAuthorizations(
                user_id:
                    $providerUser->provider_user_id,

                user_secret:
                    $providerUser->provider_user_secret,
            );

        return collect(
            $this->listFromResponse(
                $response,
            ),
        )
            ->map(
                function (
                    array $remote,
                ) use (
                    $user,
                ): BrokerageConnection {
                    $connectionId =
                        $this->stringValue(
                            data_get(
                                $remote,
                                'id',
                            )
                            ?? data_get(
                                $remote,
                                'authorizationId',
                            )
                            ?? data_get(
                                $remote,
                                'authorization_id',
                            ),
                        );

                    if ($connectionId === null) {
                        throw new RuntimeException(
                            'A SnapTrade connection was missing its ID.',
                        );
                    }

                    $brokerageName =
                        $this->stringValue(
                            data_get(
                                $remote,
                                'brokerage.name',
                            )
                            ?? data_get(
                                $remote,
                                'brokerage_name',
                            )
                            ?? data_get(
                                $remote,
                                'name',
                            ),
                        )
                        ?: 'Connected Brokerage';

                    $brokerageSlug =
                        $this->stringValue(
                            data_get(
                                $remote,
                                'brokerage.slug',
                            )
                            ?? data_get(
                                $remote,
                                'brokerage_slug',
                            ),
                        );

                    $remoteDisabled =
                        (bool) (
                            data_get(
                                $remote,
                                'disabled',
                            )
                            ?? false
                        );

                    return BrokerageConnection::query()
                        ->updateOrCreate(
                            [
                                'user_id' =>
                                    $user->id,

                                'provider' =>
                                    self::PROVIDER,

                                'provider_connection_id' =>
                                    $connectionId,
                            ],
                            [
                                'brokerage_name' =>
                                    $brokerageName,

                                'brokerage_slug' =>
                                    $brokerageSlug,

                                'status' =>
                                    $remoteDisabled
                                        ? BrokerageConnection::STATUS_DISABLED
                                        : BrokerageConnection::STATUS_ACTIVE,

                                'read_only' =>
                                    true,

                                'connected_at' =>
                                    now(),

                                'capabilities' => [
                                    'accounts',
                                    'positions',
                                    'transactions',
                                    'refresh',
                                ],

                                'metadata' =>
                                    $remote,
                            ],
                        );
                },
            )
            ->values();
    }

    /**
     * @return Collection<int, BrokerageAccountData>
     */
    public function getAccounts(
        BrokerageConnection $connection,
    ): Collection {
        $providerUser =
            $this->providerUser(
                $connection->user,
            );

        if ($connection->provider_connection_id) {
            $response = $this->client
                ->connections
                ->listBrokerageAuthorizationAccounts(
                    authorization_id:
                        $connection
                            ->provider_connection_id,

                    user_id:
                        $providerUser
                            ->provider_user_id,

                    user_secret:
                        $providerUser
                            ->provider_user_secret,
                );
        } else {
            $response = $this->client
                ->accountInformation
                ->listUserAccounts(
                    user_id:
                        $providerUser
                            ->provider_user_id,

                    user_secret:
                        $providerUser
                            ->provider_user_secret,
                );
        }

        return collect(
            $this->listFromResponse(
                $response,
            ),
        )
            ->filter(
                function (
                    array $account,
                ) use (
                    $connection,
                ): bool {
                    if (
                        $connection
                            ->provider_connection_id
                        === null
                    ) {
                        return true;
                    }

                    $authorizationId =
                        $this->stringValue(
                            data_get(
                                $account,
                                'brokerage_authorization',
                            )
                            ?? data_get(
                                $account,
                                'brokerageAuthorization',
                            )
                            ?? data_get(
                                $account,
                                'brokerage_authorization_id',
                            ),
                        );

                    return $authorizationId === null
                        || $authorizationId
                            === $connection
                                ->provider_connection_id;
                },
            )
            ->map(
                function (
                    array $account,
                ): BrokerageAccountData {
                    $accountId =
                        $this->requiredString(
                            data_get(
                                $account,
                                'id',
                            ),
                            'SnapTrade account ID',
                        );

                    $accountNumber =
                        $this->stringValue(
                            data_get(
                                $account,
                                'number',
                            ),
                        );

                    $totalValue =
                        $this->floatValue(
                            data_get(
                                $account,
                                'balance.total.amount',
                            )
                            ?? data_get(
                                $account,
                                'balance.total',
                            )
                            ?? data_get(
                                $account,
                                'total_value.amount',
                            )
                            ?? data_get(
                                $account,
                                'total_value',
                            )
                            ?? data_get(
                                $account,
                                'balance',
                            ),
                        );

                    $cashValue =
                        $this->floatValue(
                            data_get(
                                $account,
                                'cash_restrictions',
                            )
                            ?? data_get(
                                $account,
                                'cash_value',
                            )
                            ?? data_get(
                                $account,
                                'cash',
                            ),
                        );

                    return new BrokerageAccountData(
                        providerAccountId:
                            $accountId,

                        name:
                            $this->stringValue(
                                data_get(
                                    $account,
                                    'name',
                                ),
                            )
                            ?: 'SnapTrade Account',

                        institutionName:
                            $this->stringValue(
                                data_get(
                                    $account,
                                    'institution_name',
                                )
                                ?? data_get(
                                    $account,
                                    'institutionName',
                                ),
                            ),

                        accountType:
                            $this->stringValue(
                                data_get(
                                    $account,
                                    'meta.type',
                                )
                                ?? data_get(
                                    $account,
                                    'type',
                                )
                                ?? data_get(
                                    $account,
                                    'account_type',
                                ),
                            ),

                        accountNumberMask:
                            $this->lastFour(
                                $accountNumber,
                            ),

                        totalValue:
                            $totalValue,

                        cashValue:
                            $cashValue,

                        metadata:
                            $account,
                    );
                },
            )
            ->values();
    }

    /**
     * @return Collection<int, BrokeragePositionData>
     */
    public function getPositions(
        BrokerageConnection $connection,
        string $providerAccountId,
    ): Collection {
        $providerUser =
            $this->providerUser(
                $connection->user,
            );

        /*
         * Use the compatible positions endpoint.
         *
         * SnapTrade PHP SDK 2.1.0 currently has a generated-model
         * discriminator issue with getAllAccountPositions().
         *
         * The unified endpoint may return:
         *
         *     kind = "etf"
         *
         * while the SDK generic Instrument model only accepts:
         *
         *     kind = "other"
         *
         * This endpoint avoids that SDK deserialization failure.
         */
        $response = $this->client
            ->accountInformation
            ->getUserAccountPositions(
                user_id:
                    $providerUser
                        ->provider_user_id,

                user_secret:
                    $providerUser
                        ->provider_user_secret,

                account_id:
                    $providerAccountId,
            );

        $positions =
            $this->listFromResponse(
                $response,
                preferredKeys: [
                    'results',
                    'positions',
                    'data',
                ],
            );

        return collect(
            $positions,
        )
            ->map(
                function (
                    array $position,
                    int|string $index,
                ) use (
                    $providerAccountId,
                ): BrokeragePositionData {
                    /*
                     * The older SnapTrade positions endpoint nests the
                     * security underneath:
                     *
                     * symbol.symbol
                     *
                     * Example:
                     *
                     * symbol.symbol.symbol
                     * symbol.symbol.description
                     * symbol.symbol.type.code
                     * symbol.symbol.type.description
                     */
                    $security =
                        (array) (
                            data_get(
                                $position,
                                'symbol.symbol',
                            )
                            ?? data_get(
                                $position,
                                'instrument',
                            )
                            ?? []
                        );

                    $symbol =
                        $this->stringValue(
                            data_get(
                                $security,
                                'symbol',
                            )
                            ?? data_get(
                                $security,
                                'raw_symbol',
                            )
                            ?? data_get(
                                $position,
                                'symbol.symbol.symbol',
                            )
                            ?? data_get(
                                $position,
                                'symbol',
                            ),
                        );

                    $instrumentId =
    $this->stringValue(
        data_get(
            $security,
            'id',
        )
        ?? data_get(
            $position,
            'symbol.symbol.id',
        )
        ?? data_get(
            $position,
            'universal_symbol_id',
        )
        ?? data_get(
            $position,
            'symbol.id',
        ),
    );

                    $quantity =
                        $this->floatValue(
                            data_get(
                                $position,
                                'units',
                            )
                            ?? data_get(
                                $position,
                                'quantity',
                            )
                            ?? data_get(
                                $position,
                                'fractional_units',
                            ),
                        );

                    $price =
                        $this->nullableFloat(
                            data_get(
                                $position,
                                'price',
                            )
                            ?? data_get(
                                $position,
                                'market_price',
                            )
                            ?? data_get(
                                $position,
                                'last_price',
                            ),
                        );

                    $marketValue =
                        $this->floatValue(
                            data_get(
                                $position,
                                'market_value',
                            )
                            ?? data_get(
                                $position,
                                'value',
                            )
                            ?? (
                                $price !== null
                                    ? $quantity
                                        * $price
                                    : 0
                            ),
                        );

                    /*
                     * SnapTrade's compatible positions endpoint exposes
                     * average_purchase_price rather than total cost basis.
                     *
                     * Convert average purchase price into total basis.
                     */
                    $averagePurchasePrice =
                        $this->nullableFloat(
                            data_get(
                                $position,
                                'average_purchase_price',
                            )
                            ?? data_get(
                                $position,
                                'average_price',
                            ),
                        );

                    $explicitCostBasis =
                        $this->nullableFloat(
                            data_get(
                                $position,
                                'cost_basis',
                            )
                            ?? data_get(
                                $position,
                                'total_cost_basis',
                            ),
                        );

                    $costBasis =
                        $explicitCostBasis
                        ?? (
                            $averagePurchasePrice !== null
                                ? $averagePurchasePrice
                                    * $quantity
                                : null
                        );

                    /*
                     * Determine security type from SnapTrade's type
                     * description/code.
                     *
                     * Fidelity examples:
                     *
                     * VOO:
                     *   code = "et"
                     *   description = "ETF"
                     *
                     * SPAXX:
                     *   code = "oef"
                     *   description = "Open Ended Fund"
                     *   cash_equivalent = true
                     */
                    $securityTypeCode =
                        $this->stringValue(
                            data_get(
                                $security,
                                'type.code',
                            )
                            ?? data_get(
                                $position,
                                'symbol.symbol.type.code',
                            ),
                        );

                    $securityTypeDescription =
                        $this->stringValue(
                            data_get(
                                $security,
                                'type.description',
                            )
                            ?? data_get(
                                $position,
                                'symbol.symbol.type.description',
                            ),
                        );

                    $cashEquivalent =
                        (bool) (
                            data_get(
                                $position,
                                'cash_equivalent',
                            )
                            ?? false
                        );

                    $securityType =
                        $this->normalizePositionSecurityType(
                            code:
                                $securityTypeCode,

                            description:
                                $securityTypeDescription,

                            cashEquivalent:
                                $cashEquivalent,
                        );

                    $positionId =
                        $this->stringValue(
                            data_get(
                                $position,
                                'id',
                            ),
                        )
                        ?: hash(
                            'sha256',
                            implode(
                                '|',
                                [
                                    $providerAccountId,
                                    $instrumentId
                                        ?? $symbol
                                        ?? 'unknown',
                                    (string) $index,
                                ],
                            ),
                        );

                    return new BrokeragePositionData(
                        providerPositionId:
                            $positionId,

                        providerSecurityId:
                            $instrumentId,

                        symbol:
                            $symbol,

                        name:
                            $this->stringValue(
                                data_get(
                                    $security,
                                    'description',
                                )
                                ?? data_get(
                                    $position,
                                    'symbol.description',
                                )
                                ?? data_get(
                                    $position,
                                    'description',
                                ),
                            )
                            ?: (
                                $symbol
                                ?: 'Unknown Security'
                            ),

                        securityType:
                            $securityType,

                        assetClass:
                            $this->stringValue(
                                data_get(
                                    $security,
                                    'asset_class',
                                )
                                ?? data_get(
                                    $position,
                                    'asset_class',
                                ),
                            ),

                        sector:
                            $this->stringValue(
                                data_get(
                                    $security,
                                    'sector',
                                )
                                ?? data_get(
                                    $position,
                                    'sector',
                                ),
                            ),

                        quantity:
                            $quantity,

                        price:
                            $price,

                        marketValue:
                            $marketValue,

                        costBasis:
                            $costBasis,

                        expenseRatio:
                            $this->nullableFloat(
                                data_get(
                                    $security,
                                    'expense_ratio',
                                )
                                ?? data_get(
                                    $position,
                                    'expense_ratio',
                                ),
                            ),

                        metadata:
                            $position,
                    );
                },
            )
            ->values();
    }

    /**
     * @return Collection<int, BrokerageTransactionData>
     */
    public function getTransactions(
        BrokerageConnection $connection,
        string $providerAccountId,
        ?string $cursor = null,
    ): Collection {
        $providerUser =
            $this->providerUser(
                $connection->user,
            );

        $offset =
            is_numeric(
                $cursor,
            )
                ? max(
                    0,
                    (int) $cursor,
                )
                : 0;

        $transactions =
            collect();

        do {
            $response = $this->client
                ->accountInformation
                ->getAccountActivities(
                    account_id:
                        $providerAccountId,

                    user_id:
                        $providerUser
                            ->provider_user_id,

                    user_secret:
                        $providerUser
                            ->provider_user_secret,

                    offset:
                        $offset,

                    limit:
                        self::TRANSACTION_PAGE_SIZE,
                );

            $data =
                $this->toArray(
                    $response,
                );

            $page =
                $this->listFromArray(
                    $data,
                    preferredKeys: [
                        'data',
                        'results',
                        'activities',
                    ],
                );

            foreach ($page as $activity) {
                $transactions->push(
                    $this->mapTransaction(
                        $activity,
                        $providerAccountId,
                    ),
                );
            }

            $offset +=
                count(
                    $page,
                );

            $hasMore =
                count(
                    $page,
                )
                === self::TRANSACTION_PAGE_SIZE;

            $next =
                data_get(
                    $data,
                    'pagination.next',
                )
                ?? data_get(
                    $data,
                    'next',
                );

            if (
                $next === null
                && ! $hasMore
            ) {
                break;
            }
        } while (true);

        return $transactions
            ->values();
    }

    public function requestRefresh(
        BrokerageConnection $connection,
    ): void {
        if (
            ! $connection
                ->provider_connection_id
        ) {
            throw new RuntimeException(
                'The SnapTrade connection ID is missing.',
            );
        }

        $providerUser =
            $this->providerUser(
                $connection->user,
            );

        $this->client
            ->connections
            ->refreshBrokerageAuthorization(
                authorization_id:
                    $connection
                        ->provider_connection_id,

                user_id:
                    $providerUser
                        ->provider_user_id,

                user_secret:
                    $providerUser
                        ->provider_user_secret,
            );

        $connection->update([
            'status' =>
                BrokerageConnection::STATUS_SYNCING,

            'last_sync_started_at' =>
                now(),

            'last_error' =>
                null,
        ]);
    }

    public function disconnect(
        BrokerageConnection $connection,
    ): void {
        if (
            $connection
                ->provider_connection_id
        ) {
            $providerUser =
                $this->providerUser(
                    $connection->user,
                );

            $this->client
                ->connections
                ->removeBrokerageAuthorization(
                    authorization_id:
                        $connection
                            ->provider_connection_id,

                    user_id:
                        $providerUser
                            ->provider_user_id,

                    user_secret:
                        $providerUser
                            ->provider_user_secret,
                );
        }

        $connection->update([
            'status' =>
                BrokerageConnection::STATUS_DISCONNECTED,

            'disabled_at' =>
                now(),
        ]);
    }

    private function providerUser(
        ?User $user,
    ): BrokerageProviderUser {
        if ($user === null) {
            throw new RuntimeException(
                'The brokerage connection has no Helmio user.',
            );
        }

        return BrokerageProviderUser::query()
            ->where(
                'user_id',
                $user->id,
            )
            ->where(
                'provider',
                self::PROVIDER,
            )
            ->firstOrFail();
    }

    private function mapTransaction(
        array $activity,
        string $providerAccountId,
    ): BrokerageTransactionData {
        $tradeDate =
            $this->stringValue(
                data_get(
                    $activity,
                    'trade_date',
                )
                ?? data_get(
                    $activity,
                    'date',
                )
                ?? data_get(
                    $activity,
                    'created_date',
                ),
            );

        if ($tradeDate === null) {
            throw new RuntimeException(
                'A SnapTrade activity was missing its trade date.',
            );
        }

        $symbolId =
            $this->stringValue(
                data_get(
                    $activity,
                    'symbol.id',
                )
                ?? data_get(
                    $activity,
                    'symbol.symbol.id',
                )
                ?? data_get(
                    $activity,
                    'universal_symbol_id',
                ),
            );

        $type =
            strtoupper(
                $this->stringValue(
                    data_get(
                        $activity,
                        'type',
                    ),
                )
                ?: 'OTHER',
            );

        $amount =
            $this->floatValue(
                data_get(
                    $activity,
                    'amount',
                )
                ?? data_get(
                    $activity,
                    'net_amount',
                ),
            );

        $fee =
            abs(
                $this->floatValue(
                    data_get(
                        $activity,
                        'fee',
                    )
                    ?? data_get(
                        $activity,
                        'fees',
                    ),
                ),
            );

        $price =
            $this->nullableFloat(
                data_get(
                    $activity,
                    'price',
                ),
            );

        $quantity =
            $this->nullableFloat(
                data_get(
                    $activity,
                    'units',
                )
                ?? data_get(
                    $activity,
                    'quantity',
                ),
            );

        $grossAmount =
            $price !== null
            && $quantity !== null
                ? abs(
                    $price
                    * $quantity,
                )
                : abs(
                    $amount,
                ) + $fee;

        $providerTransactionId =
            $this->stringValue(
                data_get(
                    $activity,
                    'id',
                )
                ?? data_get(
                    $activity,
                    'external_reference_id',
                )
                ?? data_get(
                    $activity,
                    'trade_id',
                ),
            )
            ?: hash(
                'sha256',
                json_encode(
                    $activity,
                    JSON_THROW_ON_ERROR,
                ),
            );

        return new BrokerageTransactionData(
            providerTransactionId:
                $providerTransactionId,

            providerAccountId:
                $providerAccountId,

            providerSecurityId:
                $symbolId,

            transactionType:
                $this->normalizeTransactionType(
                    $type,
                ),

            transactionDate:
                CarbonImmutable::parse(
                    $tradeDate,
                ),

            settlementDate:
                (
                    $settlement =
                        $this->stringValue(
                            data_get(
                                $activity,
                                'settlement_date',
                            ),
                        )
                )
                    ? CarbonImmutable::parse(
                        $settlement,
                    )
                    : null,

            quantity:
                $quantity,

            price:
                $price,

            grossAmount:
                $grossAmount,

            fees:
                $fee,

            netAmount:
                $amount,

            description:
                $this->stringValue(
                    data_get(
                        $activity,
                        'description',
                    ),
                ),

            metadata:
                $activity,
        );
    }

    private function normalizeTransactionType(
        string $type,
    ): string {
        return match ($type) {
            'BUY' =>
                'buy',

            'SELL' =>
                'sell',

            'DIVIDEND',
            'SUBSTITUTE_DIVIDEND',
            'STOCK_DIVIDEND' =>
                'dividend',

            'REI' =>
                'reinvestment',

            'INTEREST' =>
                'interest',

            'CONTRIBUTION',
            'DEPOSIT' =>
                'deposit',

            'WITHDRAWAL' =>
                'withdrawal',

            'FEE' =>
                'fee',

            'TAX' =>
                'tax',

            'TRANSFER' =>
                'transfer',

            'SPLIT' =>
                'split',

            'OPTIONEXPIRATION' =>
                'option_expiration',

            'OPTIONASSIGNMENT' =>
                'option_assignment',

            'OPTIONEXERCISE' =>
                'option_exercise',

            default =>
                strtolower(
                    $type,
                ),
        };
    }

    /**
     * Normalize SnapTrade position type information.
     */
    private function normalizePositionSecurityType(
        ?string $code,
        ?string $description,
        bool $cashEquivalent,
    ): string {
        if ($cashEquivalent) {
            return 'cash';
        }

        $code =
            strtolower(
                trim(
                    (string) $code,
                ),
            );

        $description =
            strtolower(
                trim(
                    (string) $description,
                ),
            );

        if (
            $code === 'et'
            || str_contains(
                $description,
                'etf',
            )
            || str_contains(
                $description,
                'exchange traded',
            )
        ) {
            return 'etf';
        }

        if (
            in_array(
                $code,
                [
                    'oef',
                    'mf',
                ],
                true,
            )
            || str_contains(
                $description,
                'mutual fund',
            )
            || str_contains(
                $description,
                'open ended fund',
            )
            || str_contains(
                $description,
                'open-end fund',
            )
        ) {
            return 'mutual_fund';
        }

        if (
            str_contains(
                $description,
                'option',
            )
        ) {
            return 'option';
        }

        if (
            str_contains(
                $description,
                'crypto',
            )
            || str_contains(
                $description,
                'digital asset',
            )
        ) {
            return 'crypto';
        }

        if (
            str_contains(
                $description,
                'future',
            )
        ) {
            return 'future';
        }

        if (
            str_contains(
                $description,
                'bond',
            )
            || str_contains(
                $description,
                'fixed income',
            )
        ) {
            return 'bond';
        }

        if (
            str_contains(
                $description,
                'stock',
            )
            || str_contains(
                $description,
                'equity',
            )
            || str_contains(
                $description,
                'adr',
            )
            || $code === 'cs'
        ) {
            return 'stock';
        }

        return 'other';
    }

    /**
     * General security-type normalizer used for other SnapTrade shapes.
     */
    private function normalizeSecurityType(
        ?string $type,
    ): string {
        $value =
            strtolower(
                trim(
                    (string) $type,
                ),
            );

        return match (true) {
            str_contains(
                $value,
                'etf',
            ) =>
                'etf',

            str_contains(
                $value,
                'mutual',
            ) =>
                'mutual_fund',

            str_contains(
                $value,
                'option',
            ) =>
                'option',

            str_contains(
                $value,
                'crypto',
            ) =>
                'crypto',

            str_contains(
                $value,
                'future',
            ) =>
                'future',

            str_contains(
                $value,
                'cash',
            ) =>
                'cash',

            str_contains(
                $value,
                'bond',
            ),
            str_contains(
                $value,
                'fixed',
            ) =>
                'bond',

            str_contains(
                $value,
                'stock',
            ),
            str_contains(
                $value,
                'equity',
            ),
            str_contains(
                $value,
                'adr',
            ) =>
                'stock',

            default =>
                'other',
        };
    }

    /**
     * Convert SDK models and responses into arrays without tying the
     * provider to generated model classes.
     *
     * @return array<string, mixed>
     */
    private function toArray(
        mixed $value,
    ): array {
        if (is_array($value)) {
            return $value;
        }

        if (
            is_object($value)
            && method_exists(
                $value,
                'getData',
            )
        ) {
            $value =
                $value->getData();
        }

        if (
            is_object($value)
            && method_exists(
                $value,
                'jsonSerialize',
            )
        ) {
            $value =
                $value->jsonSerialize();
        }

        $encoded =
            json_encode(
                $value,
                JSON_THROW_ON_ERROR,
            );

        $decoded =
            json_decode(
                $encoded,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );

        return is_array(
            $decoded,
        )
            ? $decoded
            : [];
    }

    /**
     * @param array<int, string> $preferredKeys
     * @return array<int, array<string, mixed>>
     */
    private function listFromResponse(
        mixed $response,
        array $preferredKeys = [],
    ): array {
        return $this->listFromArray(
            $this->toArray(
                $response,
            ),
            $preferredKeys,
        );
    }

    /**
     * @param array<string, mixed>|array<int, mixed> $data
     * @param array<int, string> $preferredKeys
     * @return array<int, array<string, mixed>>
     */
    private function listFromArray(
        array $data,
        array $preferredKeys = [],
    ): array {
        foreach (
            $preferredKeys as $key
        ) {
            $candidate =
                data_get(
                    $data,
                    $key,
                );

            if (is_array($candidate)) {
                return $this->normalizeList(
                    $candidate,
                );
            }
        }

        if (array_is_list($data)) {
            return $this->normalizeList(
                $data,
            );
        }

        foreach (
            [
                'data',
                'results',
                'items',
            ] as $key
        ) {
            $candidate =
                $data[$key]
                ?? null;

            if (is_array($candidate)) {
                return $this->normalizeList(
                    $candidate,
                );
            }
        }

        return [];
    }

    /**
     * Normalize SDK response items into plain arrays.
     *
     * @param array<int|string, mixed> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeList(
        array $items,
    ): array {
        return collect(
            $items,
        )
            ->map(
                function (
                    mixed $item,
                ): array {
                    if (is_array($item)) {
                        return $item;
                    }

                    if (is_object($item)) {
                        return $this->toArray(
                            $item,
                        );
                    }

                    return [];
                },
            )
            ->filter(
                fn (
                    array $item,
                ): bool =>
                    $item !== [],
            )
            ->values()
            ->all();
    }

    private function requiredString(
        mixed $value,
        string $field,
    ): string {
        return $this->stringValue(
            $value,
        )
            ?? throw new RuntimeException(
                "{$field} was missing.",
            );
    }

    private function stringValue(
        mixed $value,
    ): ?string {
        if (
            $value === null
            || is_array(
                $value,
            )
            || is_object(
                $value,
            )
        ) {
            return null;
        }

        $value =
            trim(
                (string) $value,
            );

        return $value !== ''
            ? $value
            : null;
    }

    private function floatValue(
        mixed $value,
    ): float {
        return $this->nullableFloat(
            $value,
        ) ?? 0.0;
    }

    private function nullableFloat(
        mixed $value,
    ): ?float {
        if (is_array($value)) {
            $value =
                $value['amount']
                ?? $value['value']
                ?? null;
        }

        return is_numeric(
            $value,
        )
            ? (float) $value
            : null;
    }

    private function lastFour(
        ?string $accountNumber,
    ): ?string {
        if ($accountNumber === null) {
            return null;
        }

        $digits =
            preg_replace(
                '/\D+/',
                '',
                $accountNumber,
            );

        if (
            $digits === null
            || $digits === ''
        ) {
            return Str::substr(
                $accountNumber,
                -4,
            );
        }

        return Str::substr(
            $digits,
            -4,
        );
    }
}