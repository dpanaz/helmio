<?php

namespace App\Notifications;

use App\Models\BrokerageConnection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BrokerageSyncFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly BrokerageConnection $connection,
        private readonly string $errorMessage,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event_key' => sprintf(
                'brokerage-sync-failed:%d:%s',
                $this->connection->id,
                now()->format('Y-m-d-H'),
            ),

            'type' => 'brokerage_sync_failed',
            'severity' => 'high',
            'category' => 'brokerage',

            'title' => 'Brokerage synchronization failed',

            'message' => sprintf(
                '%s could not be synchronized. %s',
                $this->connection->brokerage_name
                    ?: str($this->connection->provider)->title(),
                $this->errorMessage,
            ),

            'action_label' => 'Review connection',

            'action_url' => route(
                'brokerage-connections.index',
            ),

            'brokerage_connection_id' =>
                $this->connection->id,

            'created_for_date' =>
                now()->toDateString(),
        ];
    }
}