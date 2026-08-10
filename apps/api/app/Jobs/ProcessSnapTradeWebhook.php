<?php

namespace App\Jobs;

use App\Models\BrokerageConnection;
use App\Models\BrokerageProviderUser;
use App\Services\Analytics\Pipeline\PortfolioAnalyticsDispatcher;
use App\Services\Brokerage\BrokerageSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class ProcessSnapTradeWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload,
    ) {
        $this->onQueue(
            'brokerage-sync'
        );
    }

    public function handle(
        BrokerageSyncService $syncService,
        PortfolioAnalyticsDispatcher $analyticsDispatcher,
    ): void {
        $eventType = strtoupper(
            (string) (
                $this->payload[
                    'eventType'
                ] ?? ''
            )
        );

        $providerUserId =
            $this->payload['userId']
            ?? null;

        if (
            ! is_string($providerUserId)
            || $providerUserId === ''
        ) {
            throw new RuntimeException(
                'SnapTrade webhook did not contain a userId.'
            );
        }

        $providerUser =
            BrokerageProviderUser::query()
                ->where(
                    'provider',
                    'snaptrade'
                )
                ->where(
                    'provider_user_id',
                    $providerUserId
                )
                ->first();

        if ($providerUser === null) {
            throw new RuntimeException(
                'No Helmio user matches the SnapTrade webhook user.'
            );
        }

        $authorizationId =
            $this->payload[
                'brokerageAuthorizationId'
            ] ?? null;

        $connection = null;

        if (
            is_string($authorizationId)
            && $authorizationId !== ''
        ) {
            $connection =
                BrokerageConnection::query()
                    ->where(
                        'user_id',
                        $providerUser->user_id
                    )
                    ->where(
                        'provider',
                        'snaptrade'
                    )
                    ->where(
                        'provider_connection_id',
                        $authorizationId
                    )
                    ->first();
        }

        /*
         * CONNECTION_FAILED may not contain an authorization ID.
         * Use the newest pending SnapTrade connection for that user.
         */
        $connection ??=
            BrokerageConnection::query()
                ->where(
                    'user_id',
                    $providerUser->user_id
                )
                ->where(
                    'provider',
                    'snaptrade'
                )
                ->whereIn(
                    'status',
                    [
                        BrokerageConnection
                            ::STATUS_PENDING,

                        BrokerageConnection
                            ::STATUS_SYNCING,
                    ]
                )
                ->latest('id')
                ->first();

        if ($connection === null) {
            /*
             * A webhook can arrive before the redirect callback has
             * reconciled the connection. Let the queue retry.
             */
            throw new RuntimeException(
                'No local SnapTrade connection matches this webhook.'
            );
        }

        $connection->loadMissing(
            'user'
        );

        $metadata = array_merge(
            $connection->metadata ?? [],
            [
                'last_webhook' =>
                    $this->payload,

                'last_webhook_received_at' =>
                    now()->toIso8601String(),
            ]
        );

        match ($eventType) {
            'CONNECTION_BROKEN' =>
                $connection->update([
                    'status' =>
                        BrokerageConnection
                            ::STATUS_DISABLED,

                    'disabled_at' =>
                        now(),

                    'last_error' =>
                        'The brokerage connection requires reconnection.',

                    'metadata' =>
                        $metadata,
                ]),

            'CONNECTION_DELETED' =>
                $connection->update([
                    'status' =>
                        BrokerageConnection
                            ::STATUS_DISCONNECTED,

                    'disabled_at' =>
                        now(),

                    'metadata' =>
                        $metadata,
                ]),

            'CONNECTION_FAILED' =>
                $connection->update([
                    'status' =>
                        BrokerageConnection
                            ::STATUS_ERROR,

                    'last_error' =>
                        (string) (
                            $this->payload[
                                'connectionAttemptedResult'
                            ]
                            ?? 'SnapTrade connection failed.'
                        ),

                    'metadata' =>
                        $metadata,
                ]),

            'CONNECTION_ADDED',
            'CONNECTION_FIXED',
            'CONNECTION_UPDATED' =>
                $connection->update([
                    'status' =>
                        BrokerageConnection
                            ::STATUS_ACTIVE,

                    'disabled_at' =>
                        null,

                    'last_error' =>
                        null,

                    'connected_at' =>
                        $connection->connected_at
                        ?: now(),

                    'metadata' =>
                        $metadata,
                ]),

            /*
             * These events indicate that SnapTrade has fresh account
             * data available. Import it into Helmio and then launch
             * portfolio analytics.
             */
            'ACCOUNT_HOLDINGS_UPDATED',
            'ACCOUNT_TRANSACTIONS_INITIAL_UPDATE',
            'ACCOUNT_TRANSACTIONS_UPDATED',
            'NEW_ACCOUNT_AVAILABLE',
            'ACCOUNT_REMOVED' =>
                $this->syncConnection(
                    connection:
                        $connection,

                    syncService:
                        $syncService,

                    analyticsDispatcher:
                        $analyticsDispatcher,

                    metadata:
                        $metadata,

                    eventType:
                        $eventType,
                ),

            default =>
                $connection->update([
                    'metadata' =>
                        $metadata,
                ]),
        };
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function syncConnection(
        BrokerageConnection $connection,
        BrokerageSyncService $syncService,
        PortfolioAnalyticsDispatcher $analyticsDispatcher,
        array $metadata,
        string $eventType,
    ): void {
        $connection->update([
            'metadata' =>
                $metadata,
        ]);

        /*
         * Import the latest accounts, holdings and transactions first.
         *
         * If this throws, analytics will NOT be dispatched. The queue
         * retry will retry the brokerage import first.
         */
        $syncService->sync(
            $connection->fresh(),
            'webhook:'.strtolower(
                $eventType
            )
        );

        $connection =
            $connection
                ->fresh([
                    'user',
                ]);

        if (
            $connection === null
            || $connection->user === null
        ) {
            throw new RuntimeException(
                'Unable to resolve the Helmio user after brokerage synchronization.'
            );
        }

        /*
         * Queue portfolio history, analytics and Helm Score generation.
         *
         * PortfolioAnalyticsDispatcher prevents duplicate active runs
         * for the same user, so multiple SnapTrade events arriving
         * close together will reuse the existing analysis run.
         */
        $analyticsDispatcher->dispatch(
            user:
                $connection->user,

            trigger:
                'snaptrade_webhook:'
                .strtolower(
                    $eventType
                ),

            connection:
                $connection,
        );
    }

    public function backoff(): array
    {
        return [
            30,
            120,
            300,
        ];
    }

    public function failed(
        Throwable $exception
    ): void {
        report(
            $exception
        );
    }
}