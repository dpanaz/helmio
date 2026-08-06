<?php

namespace App\Console\Commands;

use App\Jobs\SyncBrokerageConnection;
use App\Models\BrokerageConnection;
use Illuminate\Console\Command;

class DispatchDueBrokerageSyncs extends Command
{
    protected $signature = 'helmio:dispatch-brokerage-syncs
        {--connection= : Dispatch one brokerage connection ID}
        {--force : Dispatch even when the connection is not due}';

    protected $description =
        'Dispatch synchronization or refresh jobs for due brokerage connections';

    public function handle(): int
    {
        $intervalHours = (int) config(
            'brokerage.sync_interval_hours',
            6,
        );

        $query = BrokerageConnection::query()
            ->whereNotIn('status', [
                BrokerageConnection::STATUS_DISABLED,
                BrokerageConnection::STATUS_DISCONNECTED,
                BrokerageConnection::STATUS_SYNCING,
            ]);

        if ($this->option('connection')) {
            $query->where(
                'id',
                (int) $this->option('connection'),
            );
        }

        if (! $this->option('force')) {
            $query
                ->where(
                    'status',
                    BrokerageConnection::STATUS_ACTIVE,
                )
                ->where(
                    function ($query) use (
                        $intervalHours,
                    ): void {
                        $query
                            ->whereNull(
                                'last_successful_sync_at'
                            )
                            ->orWhere(
                                'last_successful_sync_at',
                                '<=',
                                now()->subHours(
                                    $intervalHours
                                ),
                            );
                    },
                );
        }

        $dispatched = 0;

        $query
            ->orderBy('id')
            ->chunkById(
                100,
                function ($connections) use (
                    &$dispatched,
                ): void {
                    foreach ($connections as $connection) {
                        SyncBrokerageConnection::dispatch(
                            $connection->id,
                            'scheduled',
                        );

                        $this->line(
                            sprintf(
                                'Dispatched connection %d (%s, provider: %s).',
                                $connection->id,
                                $connection->brokerage_name
                                    ?: $connection->provider,
                                $connection->provider,
                            ),
                        );

                        $dispatched++;
                    }
                },
            );

        $this->info(
            sprintf(
                '%d brokerage job%s dispatched.',
                $dispatched,
                $dispatched === 1
                    ? ''
                    : 's',
            ),
        );

        return self::SUCCESS;
    }
}