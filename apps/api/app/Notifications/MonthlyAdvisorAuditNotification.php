<?php

namespace App\Notifications;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyAdvisorAuditNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<string, mixed> $reportData
     */
    public function __construct(
        private readonly array $reportData,
    ) {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(
        object $notifiable,
    ): MailMessage {
        $audit = $this->reportData['audit'];
        $generatedAt = $this->reportData['generatedAt'];

        $pdf = Pdf::loadView(
            'audit.report-pdf',
            $this->reportData,
        )
            ->setPaper('letter', 'portrait')
            ->output();

        return (new MailMessage)
            ->subject(
                'Your monthly Helmio Advisor Audit — '
                .$audit['audit_grade']
            )
            ->greeting(
                'Hello '.$notifiable->name.','
            )
            ->line(
                'Your monthly Advisor Audit has been calculated.'
            )
            ->line(
                sprintf(
                    'Current grade: %s (%s/100).',
                    $audit['audit_grade'],
                    $audit['audit_score'] ?? 'Not available',
                ),
            )
            ->line(
                sprintf(
                    'Review items: %d. Estimated annual cost: $%s.',
                    $audit['issue_count'],
                    number_format(
                        $audit['annual_cost'],
                        2,
                    ),
                ),
            )
            ->action(
                'Open Advisor Audit',
                route('advisor-audit.index'),
            )
            ->line(
                'Your complete PDF report is attached.'
            )
            ->attachData(
                $pdf,
                sprintf(
                    'helmio-advisor-audit-%s.pdf',
                    $generatedAt->format('Y-m-d'),
                ),
                [
                    'mime' => 'application/pdf',
                ],
            );
    }
}
