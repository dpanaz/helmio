<?php

namespace App\Services\Audit;

use App\Models\AuditRun;
use App\Models\User;
use App\Notifications\AdvisorAuditChangeNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class AuditNotificationService
{
    public function __construct(
        private readonly AuditHistoryComparisonService $comparisonService,
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
                    'event_key' => $this->eventKey(
                        'new',
                        $currentRun,
                        $finding->fingerprint,
                    ),

                    'type' => 'finding_new',
                    'severity' => $finding->severity,
                    'title' => 'New audit finding',
                    'message' => $finding->title,
                    'action_label' => 'Review finding',
                    'action_url' =>
                        route('advisor-audit.index'),

                    'category' => $finding->category,
                    'audit_run_id' => $currentRun->id,

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
                    'event_key' => $this->eventKey(
                        'worsened',
                        $currentRun,
                        $finding->fingerprint,
                    ),

                    'type' => 'finding_worsened',
                    'severity' => $finding->severity,
                    'title' => 'Audit finding worsened',
                    'message' => $finding->title,
                    'action_label' => 'Review change',
                    'action_url' => route(
                        'advisor-audit.history.show',
                        $currentRun,
                    ),

                    'category' => $finding->category,
                    'audit_run_id' => $currentRun->id,

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
                    'event_key' => $this->eventKey(
                        'improved',
                        $currentRun,
                        $finding->fingerprint,
                    ),

                    'type' => 'finding_improved',
                    'severity' => 'positive',
                    'title' => 'Audit finding improved',
                    'message' => $finding->title,
                    'action_label' => 'View improvement',
                    'action_url' => route(
                        'advisor-audit.history.show',
                        $currentRun,
                    ),

                    'category' => $finding->category,
                    'audit_run_id' => $currentRun->id,

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
                    'event_key' => $this->eventKey(
                        'resolved',
                        $currentRun,
                        $finding->fingerprint,
                    ),

                    'type' => 'finding_resolved',
                    'severity' => 'positive',
                    'title' => 'Audit finding resolved',
                    'message' => $finding->title,
                    'action_label' => 'View audit history',
                    'action_url' =>
                        route('advisor-audit.history'),

                    'category' => $finding->category,
                    'audit_run_id' => $currentRun->id,

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

    private function createScoreNotification(
        User $user,
        AuditRun $run,
        ?int $scoreChange,
    ): void {
        if ($scoreChange === null || $scoreChange === 0) {
            return;
        }

        $direction = $scoreChange > 0
            ? 'improved'
            : 'declined';

        $this->sendOnce(
            $user,
            [
                'event_key' => $this->eventKey(
                    'score-'.$direction,
                    $run,
                ),

                'type' => 'audit_score_changed',

                'severity' => $scoreChange > 0
                    ? 'positive'
                    : 'high',

                'title' =>
                    'Advisor Audit score '.$direction,

                'message' => sprintf(
                    'Your audit score %s by %d point%s to %s.',
                    $direction,
                    abs($scoreChange),
                    abs($scoreChange) === 1 ? '' : 's',
                    $run->audit_score ?? '—',
                ),

                'action_label' => 'View audit history',

                'action_url' => route(
                    'advisor-audit.history.show',
                    $run,
                ),

                'category' => 'audit',
                'audit_run_id' => $run->id,

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
        $alreadyExists = DatabaseNotification::query()
            ->where(
                'notifiable_type',
                $user->getMorphClass(),
            )
            ->where('notifiable_id', $user->getKey())
            ->where(
                'data->event_key',
                $data['event_key'],
            )
            ->exists();

        if ($alreadyExists) {
            return;
        }

        $user->notify(
            new AdvisorAuditChangeNotification($data),
        );
    }

    private function eventKey(
        string $type,
        AuditRun $run,
        ?string $fingerprint = null,
    ): string {
        return Str::of(
            implode(':', [
                'audit',
                $run->id,
                $type,
                $fingerprint ?: 'summary',
            ]),
        )
            ->lower()
            ->toString();
    }
}