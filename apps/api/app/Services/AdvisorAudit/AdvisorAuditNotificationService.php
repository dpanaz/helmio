<?php

namespace App\Services\AdvisorAudit;

use App\Models\AuditRun;
use App\Models\MonthlyAuditSetting;
use App\Models\User;
use App\Notifications\AdvisorAuditChangeNotification;
use App\Notifications\MonthlyAdvisorAuditNotification;
use Illuminate\Support\Collection;

class AdvisorAuditNotificationService
{
    private const SCORE_CHANGE_THRESHOLD = 2;

    /**
     * Send always-on in-app Advisor Audit notifications.
     *
     * These notifications do NOT depend on MonthlyAuditSetting.
     */
    public function sendInApp(
        User $user,
        AuditRun $currentRun,
        ?AuditRun $previousRun = null
    ): void {
        $currentRun->loadMissing('findings');
        $previousRun?->loadMissing('findings');

        $scoreChange = $this->scoreChange(
            currentRun: $currentRun,
            previousRun: $previousRun,
        );

        /*
         * Always record one completion event for a new audit run.
         *
         * notifyOnce() prevents rerunning the same audit from creating
         * duplicate Notification Center entries.
         */
        $this->notifyOnce(
            user: $user,
            eventKey: sprintf(
                'advisor-audit-completed:%d',
                $currentRun->id
            ),
            data: [
                'event_key' =>
                    sprintf(
                        'advisor-audit-completed:%d',
                        $currentRun->id
                    ),

                'type' =>
                    'advisor_audit_completed',

                'severity' =>
                    'information',

                'title' =>
                    'Advisor Audit complete',

                'message' =>
                    $this->completionMessage(
                        currentRun: $currentRun,
                        scoreChange: $scoreChange,
                    ),

                'action_label' =>
                    'View Advisor Audit',

                'action_url' =>
                    route(
                        'advisor-audit.history.show',
                        $currentRun
                    ),

                'category' =>
                    'audit',

                'audit_run_id' =>
                    $currentRun->id,

                'created_for_date' =>
                    $currentRun
                        ->calculated_for_date
                        ->toDateString(),
            ],
        );

        /*
         * Surface the CURRENT actionable state.
         *
         * This deliberately uses persisted run-finding severity rather
         * than metadata.group because Action Center priority is based on
         * the normalized database severity.
         */
        $criticalCount =
            $currentRun->findings
                ->where('severity', 'critical')
                ->count();

        $importantCount =
            $currentRun->findings
                ->filter(
                    fn ($finding): bool =>
                        in_array(
                            $finding->severity,
                            [
                                'high',
                                'medium',
                            ],
                            true
                        )
                )
                ->count();

        if (
            $criticalCount > 0
            || $importantCount > 0
        ) {
            $this->notifyOnce(
                user: $user,
                eventKey: sprintf(
                    'advisor-audit-attention:%d',
                    $currentRun->id
                ),
                data: [
                    'event_key' =>
                        sprintf(
                            'advisor-audit-attention:%d',
                            $currentRun->id
                        ),

                    'type' =>
                        'advisor_audit_attention',

                    'severity' =>
                        $criticalCount > 0
                            ? 'critical'
                            : 'high',

                    'title' =>
                        $criticalCount > 0
                            ? 'Advisor Audit findings require attention'
                            : 'Important Advisor Audit findings',

                    'message' =>
                        $this->attentionMessage(
                            criticalCount:
                                $criticalCount,

                            importantCount:
                                $importantCount,
                        ),

                    'action_label' =>
                        'Open Action Center',

                    'action_url' =>
                        route(
                            'advisor-action-center.index'
                        ),

                    'category' =>
                        'audit',

                    'audit_run_id' =>
                        $currentRun->id,

                    'created_for_date' =>
                        $currentRun
                            ->calculated_for_date
                            ->toDateString(),
                ],
            );
        }

        /*
         * Notify when genuinely NEW critical findings appear compared
         * with the previous audit.
         */
        $newCriticalFindings =
            $this->newFindingsBySeverity(
                currentRun: $currentRun,
                previousRun: $previousRun,
                severities: [
                    'critical',
                ],
            );

        if ($newCriticalFindings->isNotEmpty()) {
            $this->notifyOnce(
                user: $user,
                eventKey: sprintf(
                    'advisor-audit-new-critical:%d',
                    $currentRun->id
                ),
                data: [
                    'event_key' =>
                        sprintf(
                            'advisor-audit-new-critical:%d',
                            $currentRun->id
                        ),

                    'type' =>
                        'advisor_audit_new_critical',

                    'severity' =>
                        'critical',

                    'title' =>
                        'New critical Advisor Audit finding',

                    'message' =>
                        sprintf(
                            '%d new critical finding%s require review.',
                            $newCriticalFindings->count(),
                            $newCriticalFindings->count() === 1
                                ? ''
                                : 's'
                        ),

                    'action_label' =>
                        'Open Action Center',

                    'action_url' =>
                        route(
                            'advisor-action-center.index'
                        ),

                    'category' =>
                        'audit',

                    'audit_run_id' =>
                        $currentRun->id,

                    'finding_fingerprint' =>
                        $newCriticalFindings
                            ->first()
                            ?->fingerprint,

                    'created_for_date' =>
                        $currentRun
                            ->calculated_for_date
                            ->toDateString(),
                ],
            );
        }

        /*
         * Important findings in the current Action Center correspond
         * to normalized high / medium severity findings.
         */
        $newImportantFindings =
            $this->newFindingsBySeverity(
                currentRun: $currentRun,
                previousRun: $previousRun,
                severities: [
                    'high',
                    'medium',
                ],
            );

        if ($newImportantFindings->isNotEmpty()) {
            $this->notifyOnce(
                user: $user,
                eventKey: sprintf(
                    'advisor-audit-new-important:%d',
                    $currentRun->id
                ),
                data: [
                    'event_key' =>
                        sprintf(
                            'advisor-audit-new-important:%d',
                            $currentRun->id
                        ),

                    'type' =>
                        'advisor_audit_new_important',

                    'severity' =>
                        'high',

                    'title' =>
                        'New important Advisor Audit finding',

                    'message' =>
                        sprintf(
                            '%d new important finding%s require review.',
                            $newImportantFindings->count(),
                            $newImportantFindings->count() === 1
                                ? ''
                                : 's'
                        ),

                    'action_label' =>
                        'Open Action Center',

                    'action_url' =>
                        route(
                            'advisor-action-center.index'
                        ),

                    'category' =>
                        'audit',

                    'audit_run_id' =>
                        $currentRun->id,

                    'finding_fingerprint' =>
                        $newImportantFindings
                            ->first()
                            ?->fingerprint,

                    'created_for_date' =>
                        $currentRun
                            ->calculated_for_date
                            ->toDateString(),
                ],
            );
        }

        /*
         * Significant score movement.
         */
        if (
            $scoreChange !== null
            && abs($scoreChange)
                >= self::SCORE_CHANGE_THRESHOLD
        ) {
            $this->notifyOnce(
                user: $user,
                eventKey: sprintf(
                    'advisor-audit-score-change:%d',
                    $currentRun->id
                ),
                data: [
                    'event_key' =>
                        sprintf(
                            'advisor-audit-score-change:%d',
                            $currentRun->id
                        ),

                    'type' =>
                        'advisor_audit_score_change',

                    'severity' =>
                        $scoreChange < 0
                            ? 'high'
                            : 'information',

                    'title' =>
                        $scoreChange < 0
                            ? 'Advisor Audit score decreased'
                            : 'Advisor Audit score improved',

                    'message' =>
                        sprintf(
                            'Your Advisor Audit score changed from %d to %d (%+d points).',
                            $previousRun->audit_score,
                            $currentRun->audit_score,
                            $scoreChange
                        ),

                    'action_label' =>
                        'Compare Audits',

                    'action_url' =>
                        route(
                            'advisor-audit.history.show',
                            $currentRun
                        ),

                    'category' =>
                        'audit',

                    'audit_run_id' =>
                        $currentRun->id,

                    'created_for_date' =>
                        $currentRun
                            ->calculated_for_date
                            ->toDateString(),
                ],
            );
        }
    }

    /**
     * Monthly delivery.
     *
     * In-app notifications are always generated independently of
     * MonthlyAuditSetting. This method now handles the scheduled
     * monthly delivery layer.
     */
    public function send(
        MonthlyAuditSetting $setting,
        AuditRun $currentRun,
        ?AuditRun $previousRun = null
    ): void {
        $user = $setting->user;

        if ($user === null) {
            return;
        }

        /*
         * Safe to call again because sendInApp() deduplicates by
         * event_key.
         */
        $this->sendInApp(
            user: $user,
            currentRun: $currentRun,
            previousRun: $previousRun,
        );

        /*
         * Keep monthly email controlled by the user's monthly
         * notification preference.
         */
        if ($setting->notify_on_completion) {
            $user->notify(
                new MonthlyAdvisorAuditNotification(
                    $this->mailReportData(
                        currentRun: $currentRun,
                    )
                )
            );
        }
    }

    private function scoreChange(
        AuditRun $currentRun,
        ?AuditRun $previousRun
    ): ?int {
        if (
            $currentRun->audit_score === null
            || $previousRun?->audit_score === null
        ) {
            return null;
        }

        return (int) (
            $currentRun->audit_score
            - $previousRun->audit_score
        );
    }

    /**
     * @param array<int, string> $severities
     * @return Collection<int, \App\Models\AuditRunFinding>
     */
    private function newFindingsBySeverity(
        AuditRun $currentRun,
        ?AuditRun $previousRun,
        array $severities
    ): Collection {
        $currentRun->loadMissing('findings');
        $previousRun?->loadMissing('findings');

        $previousFingerprints =
            $previousRun?->findings
                ->pluck('fingerprint')
                ->all()
            ?? [];

        return $currentRun->findings
            ->filter(
                fn ($finding): bool =>
                    in_array(
                        $finding->severity,
                        $severities,
                        true
                    )
            )
            ->reject(
                fn ($finding): bool =>
                    in_array(
                        $finding->fingerprint,
                        $previousFingerprints,
                        true
                    )
            )
            ->values();
    }

    /**
     * Create one database notification for each unique Helmio event.
     *
     * This keeps recalculations, retries, queue retries, and monthly
     * delivery from flooding the Notification Center.
     *
     * @param array<string, mixed> $data
     */
    private function notifyOnce(
        User $user,
        string $eventKey,
        array $data
    ): void {
        $alreadyExists =
            $user->notifications()
                ->where(
                    'data->event_key',
                    $eventKey
                )
                ->exists();

        if ($alreadyExists) {
            return;
        }

        $user->notify(
            new AdvisorAuditChangeNotification(
                $data
            )
        );
    }

    private function attentionMessage(
        int $criticalCount,
        int $importantCount
    ): string {
        if (
            $criticalCount > 0
            && $importantCount > 0
        ) {
            return sprintf(
                '%d critical and %d important findings require review.',
                $criticalCount,
                $importantCount
            );
        }

        if ($criticalCount > 0) {
            return sprintf(
                '%d critical finding%s require review.',
                $criticalCount,
                $criticalCount === 1
                    ? ''
                    : 's'
            );
        }

        return sprintf(
            '%d important finding%s require review.',
            $importantCount,
            $importantCount === 1
                ? ''
                : 's'
        );
    }

    private function completionMessage(
        AuditRun $currentRun,
        ?int $scoreChange
    ): string {
        if ($currentRun->audit_score === null) {
            return 'Your Advisor Audit finished, but more portfolio data is needed for a complete score.';
        }

        if ($scoreChange === null) {
            return sprintf(
                'Your Advisor Audit score is %d out of 100.',
                $currentRun->audit_score
            );
        }

        return sprintf(
            'Your Advisor Audit score is %d out of 100 (%+d points from the previous audit).',
            $currentRun->audit_score,
            $scoreChange
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function mailReportData(
        AuditRun $currentRun
    ): array {
        return [
            'audit' => [
                'audit_grade' =>
                    $currentRun->audit_grade
                    ?? 'N/A',

                'audit_score' =>
                    $currentRun->audit_score,

                'issue_count' =>
                    $currentRun->issue_count,

                'annual_cost' =>
                    (float) $currentRun->annual_cost,

                'potential_savings' =>
                    (float) $currentRun
                        ->potential_savings,

                'portfolio_value' =>
                    (float) $currentRun
                        ->portfolio_value,

                'category_scores' =>
                    $currentRun
                        ->category_scores
                    ?? [],

                'audit_details' =>
                    $currentRun
                        ->audit_details
                    ?? [],
            ],

            'auditRun' =>
                $currentRun,

            'generatedAt' =>
                $currentRun->created_at
                ?? now(),
        ];
    }
}