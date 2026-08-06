<?php

namespace App\Jobs;

use App\Models\BrokerageConnection;
use App\Notifications\BrokerageSyncFailedNotification;
use App\Services\Brokerage\BrokerageProviderManager;
use App\Services\Brokerage\BrokerageSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use RuntimeException;
use Throwable;

class SyncBrokerageConnection implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 300;

    public int $timeout;

    public function __construct(
        public readonly int $brokerageConnectionId,
        public readonly string $trigger = 'scheduled',
    ) {
        $this->timeout = (int) config(
            'brokerage.sync_timeout_seconds',
            300,
        );

        $this->onQueue(
            'brokerage-sync'
        );
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'brokerage-connection:'
                .$this->brokerageConnectionId,
            ))
                ->releaseAfter(60)
                ->expireAfter(
                    $this->timeout + 60,
                ),
        ];
    }

    public function handle(
        BrokerageProviderManager $providerManager,
        BrokerageSyncService $syncService,
    ): void {
        $connection = BrokerageConnection::query()
            ->with('user')
            ->find(
                $this->brokerageConnectionId
            );

        if ($connection === null) {
            return;
        }

        if (
            in_array(
                $connection->status,
                [
                    BrokerageConnection
                        ::STATUS_DISABLED,

                    BrokerageConnection
                        ::STATUS_DISCONNECTED,
                ],
                true,
            )
        ) {
            return;
        }

        /*
         * SnapTrade refreshes provider data asynchronously. Requesting
         * the refresh here and immediately running the local import can
         * read the provider's previous cached data. The SnapTrade
         * webhook will run BrokerageSyncService after refreshed data is
         * available.
         */
        if (
            $connection->provider
                === 'snaptrade'
        ) {
            if (
                $connection
                    ->provider_connection_id
                === null
            ) {
                throw new RuntimeException(
                    'The SnapTrade connection is missing its provider connection ID.'
                );
            }

            $providerManager
                ->driver('snaptrade')
                ->requestRefresh(
                    $connection
                );

            $connection->update([
                'status' =>
                    BrokerageConnection
                        ::STATUS_SYNCING,

                'last_sync_started_at' =>
                    now(),

                'last_error' =>
                    null,

                'metadata' => array_merge(
                    $connection->metadata
                    ?? [],
                    [
                        'last_refresh_trigger' =>
                            $this->trigger,

                        'last_refresh_requested_at' =>
                            now()
                                ->toIso8601String(),
                    ],
                ),
            ]);

            return;
        }

        /*
         * Fake and other synchronous providers can be imported
         * immediately.
         */
        $syncService->sync(
            $connection,
            $this->trigger,
        );
    }

    public function failed(
        ?Throwable $exception,
    ): void {
        $connection = BrokerageConnection::query()
            ->with('user')
            ->find(
                $this->brokerageConnectionId
            );

        if ($connection === null) {
            return;
        }

        $message =
            $exception?->getMessage()
            ?: 'The synchronization job failed unexpectedly.';

        $connection->update([
            'status' =>
                BrokerageConnection
                    ::STATUS_ERROR,

            'last_error' =>
                $message,
        ]);

        if ($connection->user === null) {
            return;
        }

        /*
         * This callback runs only after all attempts are exhausted, so
         * the user receives one failure notification rather than one
         * notification per retry.
         */
        $connection->user->notify(
            new BrokerageSyncFailedNotification(
                $connection,
                $message,
            ),
        );
    }
}