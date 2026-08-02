<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdvisorAuditChangeNotification extends Notification
{
    use Queueable;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly array $data,
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
            'event_key' =>
                $this->data['event_key'],

            'type' =>
                $this->data['type'],

            'severity' =>
                $this->data['severity']
                ?? 'information',

            'title' =>
                $this->data['title'],

            'message' =>
                $this->data['message'],

            'action_label' =>
                $this->data['action_label']
                ?? 'Review',

            'action_url' =>
                $this->data['action_url']
                ?? route('advisor-audit.index'),

            'category' =>
                $this->data['category']
                ?? 'audit',

            'audit_run_id' =>
                $this->data['audit_run_id']
                ?? null,

            'finding_fingerprint' =>
                $this->data['finding_fingerprint']
                ?? null,

            'created_for_date' =>
                $this->data['created_for_date']
                ?? now()->toDateString(),
        ];
    }
}