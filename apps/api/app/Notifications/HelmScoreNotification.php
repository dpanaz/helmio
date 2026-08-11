<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HelmScoreNotification extends Notification
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
                $this->data['type']
                ?? 'helm_score',

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
                ?? route('dashboard'),

            'category' =>
                'helm_score',

            'helm_score' =>
                $this->data['helm_score']
                ?? null,

            'previous_helm_score' =>
                $this->data['previous_helm_score']
                ?? null,

            'calculated_for_date' =>
                $this->data['calculated_for_date']
                ?? now()->toDateString(),
        ];
    }
}