<?php

namespace App\Services\Audit;

use App\Models\AuditRun;
use App\Models\User;
use App\Notifications\AdvisorAuditChangeNotification;
use App\Services\Notifications\WebPushService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Throwable;

class AuditNotificationService
{
    public function __construct(
        private readonly AuditHistoryComparisonService $comparisonService,
        private readonly WebPushService $webPushService,
    ) {
    }

    public function generate(
        User $user,
        AuditRun $currentRun,
        ?AuditRun $previousRun,
    ): void {
        $currentRun->loadMissing('findings');
        $previousRun?->loadMissing('findings');

        $comparison = $this->comparisonService->compare(
            $currentRun,
            $previousRun,
        );

        if (! $comparison['has_previous']) {
            $this->createInitialAuditNotification(
                $user,
                $currentRun,
            );

            return;
        }

        $this->createScoreNotification(
            $user,
            $currentRun,
            $comparison['score_change'],
        );

        foreach ($comparison['new_findings'] as $finding) {
            $this->sendOnce(
                $user,
                [
                    'event_key' =>
                        $this->eventKey(
                            'new',
                            $currentRun,
                            $finding->fingerprint,
                        ),

                    'type' =>
                        'finding_new',

                    'severity' =>
                        $finding->severity,

                    'title' =>
                        'New audit finding',

                    'message' =>
                        $finding->title,

                    'action_label' =>
                        'Review finding',

                    'action_url' =>
                        route(
                            'advisor-audit.index',
                        ),

                    'category' =>
                        $finding->category,

                    'audit_run_id' =>
                        $currentRun->id,

                    'finding_fingerprint' =>
                        $finding->fingerprint,

                    'created_for_date' =>
                        $currentRun
                            ->calculated_for_date
                            ->toDateString(),
                ],
            );
        }

        foreach ($comparison['worsened_findings'] as $finding) {
            $this->sendOnce(
                $user,
                [
                    'event_key' =>
                        $this->eventKey(
                            'worsened',
                            $currentRun,
                            $finding->fingerprint,
                        ),

                    'type' =>
                        'finding_worsened',

                    'severity' =>
                        $finding->severity,

                    'title' =>
                        'Audit finding worsened',

                    'message' =>
                        $finding->title,

                    'action_label' =>
                        'Review change',

                    'action_url' =>
                        route(
                            'advisor-audit.history.show',
                            $currentRun,
                        ),

                    'category' =>
                        $finding->category,

                    'audit_run_id' =>
                        $currentRun->id,

                    'finding_fingerprint' =>
                        $finding->fingerprint,

                    'created_for_date' =>
                        $currentRun
                            ->calculated_for_date
                            ->toDateString(),
                ],
            );
        }

        foreach ($comparison['improved_findings'] as $finding) {
            $this->sendOnce(
                $user,
                [
                    'event_key' =>
                        $this->eventKey(
                            'improved',
                            $currentRun,
                            $finding->fingerprint,
                        ),

                    'type' =>
                        'finding_improved',

                    'severity' =>
                        'positive',

                    'title' =>
                        'Audit finding improved',

                    'message' =>
                        $finding->title,

                    'action_label' =>
                        'View improvement',

                    'action_url' =>
                        route(
                            'advisor-audit.history.show',
                            $currentRun,
                        ),

                    'category' =>
                        $finding->category,

                    'audit_run_id' =>
                        $currentRun->id,

                    'finding_fingerprint' =>
                        $finding->fingerprint,

                    'created_for_date' =>
                        $currentRun
                            ->calculated_for_date
                            ->toDateString(),
                ],
            );
        }

        foreach ($comparison['resolved_findings'] as $finding) {
            $this->sendOnce(
                $user,
                [
                    'event_key' =>
                        $this->eventKey(
                            'resolved',
                            $currentRun,
                            $finding->fingerprint,
                        ),

                    'type' =>
                        'finding_resolved',

                    'severity' =>
                        'positive',

                    'title' =>
                        'Audit finding resolved',

                    'message' =>
                        $finding->title,

                    'action_label' =>
                        'View audit history',

                    'action_url' =>
                        route(
                            'advisor-audit.history',
                        ),

                    'category' =>
                        $finding->category,

                    'audit_run_id' =>
                        $currentRun->id,

                    'finding_fingerprint' =>
                        $finding->fingerprint,

                    'created_for_date' =>
                        $currentRun
                            ->calculated_for_date
                            ->toDateString(),
                ],
            );
        }
    }

    private function createInitialAuditNotification(
        User $user,
        AuditRun $run,
    ): void {
        $findings = $run->findings;

        if ($findings->isEmpty()) {
            return;
        }

        $total = $findings->count();

        $criticalCount = $findings
            ->filter(
                fn ($finding): bool =>
                    strtolower(
                        (string) $finding->severity,
                    ) === 'critical',
            )
            ->count();

        $highCount = $findings
            ->filter(
                fn ($finding): bool =>
                    strtolower(
                        (string) $finding->severity,
                    ) === 'high',
            )
            ->count();

        $severity = match (true) {
            $criticalCount > 0 =>
                'critical',

            $highCount > 0 =>
                'high',

            default =>
                'information',
        };

        $messageParts = [
            sprintf(
                'Helmio found %d item%s in your portfolio review.',
                $total,
                $total === 1 ? '' : 's',
            ),
        ];

        if ($criticalCount > 0) {
            $messageParts[] = sprintf(
                '%d critical finding%s require%s attention.',
                $criticalCount,
                $criticalCount === 1 ? '' : 's',
                $criticalCount === 1 ? 's' : '',
            );
        } elseif ($highCount > 0) {
            $messageParts[] = sprintf(
                '%d high-priority finding%s deserve%s review.',
                $highCount,
                $highCount === 1 ? '' : 's',
                $highCount === 1 ? 's' : '',
            );
        }

        $this->sendOnce(
            $user,
            [
                'event_key' =>
                    $this->eventKey(
                        'initial-review',
                        $run,
                    ),

                'type' =>
                    'initial_audit_review',

                'severity' =>
                    $severity,

                'title' =>
                    'Portfolio review needs attention',

                'message' =>
                    implode(
                        ' ',
                        $messageParts,
                    ),

                'action_label' =>
                    'Review findings',

                'action_url' =>
                    route(
                        'advisor-audit.index',
                    ),

                'category' =>
                    'audit',

                'audit_run_id' =>
                    $run->id,

                'created_for_date' =>
                    $run
                        ->calculated_for_date
                        ->toDateString(),
            ],
        );
    }

    private function createScoreNotification(
        User $user,
        AuditRun $run,
        ?int $scoreChange,
    ): void {
        if (
            $scoreChange === null
            || $scoreChange === 0
        ) {
            return;
        }

        $direction =
            $scoreChange > 0
                ? 'improved'
                : 'declined';

        $this->sendOnce(
            $user,
            [
                'event_key' =>
                    $this->eventKey(
                        'score-'.$direction,
                        $run,
                    ),

                'type' =>
                    'audit_score_changed',

                'severity' =>
                    $scoreChange > 0
                        ? 'positive'
                        : 'high',

                'title' =>
                    'Advisor Audit score '
                    .$direction,

                'message' =>
                    sprintf(
                        'Your audit score %s by %d point%s to %s.',
                        $direction,
                        abs($scoreChange),
                        abs($scoreChange) === 1
                            ? ''
                            : 's',
                        $run->audit_score
                        ?? '—',
                    ),

                'action_label' =>
                    'View audit history',

                'action_url' =>
                    route(
                        'advisor-audit.history.show',
                        $run,
                    ),

                'category' =>
                    'audit',

                'audit_run_id' =>
                    $run->id,

                'created_for_date' =>
                    $run
                        ->calculated_for_date
                        ->toDateString(),
            ],
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function sendOnce(
        User $user,
        array $data,
    ): void {
        $alreadyExists =
            DatabaseNotification::query()
                ->where(
                    'notifiable_type',
                    $user->getMorphClass(),
                )
                ->where(
                    'notifiable_id',
                    $user->getKey(),
                )
                ->where(
                    'data->event_key',
                    $data['event_key'],
                )
                ->exists();

        if ($alreadyExists) {
            return;
        }

        /*
         * Store the notification in Helmio first.
         */
        $user->notify(
            new AdvisorAuditChangeNotification(
                $data,
            ),
        );

        /*
         * Get the unread count after the database notification
         * has been created so the device badge is accurate.
         */
        $unreadCount =
            $user
                ->unreadNotifications()
                ->count();

        /*
         * Send the same event as a Web Push notification.
         *
         * Push delivery is deliberately non-fatal. A problem with
         * Apple/Google push delivery must never prevent the in-app
         * notification from being stored.
         */
        try {
            $this->webPushService
                ->sendToUser(
                    $user,
                    [
                        'title' =>
                            $data['title']
                            ?? 'Portfolio update',

                        'body' =>
                            $data['message']
                            ?? 'Helmio found something worth reviewing.',

                        'action_url' =>
                            $data['action_url']
                            ?? '/notifications',

                        'unread_count' =>
                            $unreadCount,

                        'type' =>
                            $data['type']
                            ?? 'advisor_audit',

                        'event_key' =>
                            $data['event_key']
                            ?? null,

                        'severity' =>
                            $data['severity']
                            ?? 'information',

                        'category' =>
                            $data['category']
                            ?? 'audit',
                    ],
                );
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function eventKey(
        string $type,
        AuditRun $run,
        ?string $fingerprint = null,
    ): string {
        return Str::of(
            implode(
                ':',
                [
                    'audit',
                    $run->id,
                    $type,
                    $fingerprint
                    ?: 'summary',
                ],
            ),
        )
            ->lower()
            ->toString();
    }
}