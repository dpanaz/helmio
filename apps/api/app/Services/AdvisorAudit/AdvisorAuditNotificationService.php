<?php

namespace App\Services\AdvisorAudit;

use App\Models\AuditRun;
use App\Models\MonthlyAuditSetting;
use App\Notifications\AdvisorAuditChangeNotification;
use App\Notifications\MonthlyAdvisorAuditNotification;
use Illuminate\Support\Collection;

class AdvisorAuditNotificationService
{
    public function send(
        MonthlyAuditSetting $setting,
        AuditRun $currentRun,
        ?AuditRun $previousRun = null
    ): void {
        $user = $setting->user;

        if ($user === null) {
            return;
        }

        $scoreChange = (
            $currentRun->audit_score !== null
            && $previousRun?->audit_score !== null
        )
            ? $currentRun->audit_score
                - $previousRun->audit_score
            : null;

        $newCriticalFindings =
            $this->newCriticalFindings(
                currentRun: $currentRun,
                previousRun: $previousRun,
            );

        if ($setting->notify_on_completion) {
            $user->notify(
                new AdvisorAuditChangeNotification([
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
                        'Monthly Advisor Audit complete',

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
                ])
            );

            $user->notify(
                new MonthlyAdvisorAuditNotification(
                    $this->mailReportData(
                        currentRun: $currentRun,
                    )
                )
            );
        }

        if (
            $setting->notify_on_new_critical
            && $newCriticalFindings->isNotEmpty()
        ) {
            $user->notify(
                new AdvisorAuditChangeNotification([
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
                            '%d new critical finding(s) require review.',
                            $newCriticalFindings->count()
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
                ])
            );
        }

        if (
            $setting->notify_on_score_change
            && $scoreChange !== null
            && abs($scoreChange)
                >= $setting->score_change_threshold
        ) {
            $user->notify(
                new AdvisorAuditChangeNotification([
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
                        'Advisor Audit score changed',

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
                ])
            );
        }
    }

    /**
     * @return Collection<int, \App\Models\AuditRunFinding>
     */
    private function newCriticalFindings(
        AuditRun $currentRun,
        ?AuditRun $previousRun
    ): Collection {
        $currentRun->loadMissing('findings');
        $previousRun?->loadMissing('findings');

        $previousFingerprints =
            $previousRun?->findings
                ->pluck('fingerprint')
                ->all()
            ?? [];

        return $currentRun->findings
            ->where('severity', 'critical')
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

    private function completionMessage(
        AuditRun $currentRun,
        ?int $scoreChange
    ): string {
        if ($currentRun->audit_score === null) {
            return 'Your monthly Advisor Audit finished, but more portfolio data is needed for a complete score.';
        }

        if ($scoreChange === null) {
            return sprintf(
                'Your monthly Advisor Audit score is %d out of 100.',
                $currentRun->audit_score
            );
        }

        return sprintf(
            'Your monthly Advisor Audit score is %d out of 100 (%+d points from the previous audit).',
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