<?php

namespace App\Services\Audit;

use App\Models\AuditFinding;
use App\Models\AuditRun;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuditRunRecorderService
{
    /**
     * @param array<string, mixed> $audit
     * @param Collection<int, AuditFinding> $findings
     */
    public function record(
        User $user,
        array $audit,
        Collection $findings,
    ): AuditRun {
        return DB::transaction(
            function () use ($user, $audit, $findings): AuditRun {
                $run = AuditRun::query()
                    ->where('user_id', $user->id)
                    ->whereDate(
                        'calculated_for_date',
                        $audit['calculated_for_date'],
                    )
                    ->where(
                        'formula_version',
                        $audit['formula_version'],
                    )
                    ->first();

                if ($run === null) {
                    $run = new AuditRun([
                        'user_id' => $user->id,
                        'calculated_for_date' =>
                            $audit['calculated_for_date'],
                        'formula_version' =>
                            $audit['formula_version'],
                    ]);
                }

                $run->fill([
                    'audit_score' => $audit['audit_score'],
                    'audit_grade' => $audit['audit_grade'],
                    'audit_label' => $audit['audit_label'],

                    'portfolio_value' =>
                        $audit['portfolio_value'],

                    'annual_cost' =>
                        $audit['annual_cost'],

                    'potential_savings' =>
                        $audit['potential_savings'],

                    'issue_count' =>
                        $audit['issue_count'],

                    'critical_count' =>
                        $audit['critical_count'],

                    'high_count' =>
                        $audit['high_count'],

                    'medium_count' =>
                        $audit['medium_count'],

                    'positive_count' =>
                        $audit['positive_count'],

                    'category_scores' =>
                        $audit['category_scores']->toArray(),

                    'audit_details' => [
                        'review_recommended' =>
                            $audit['review_recommended'],

                        'helm_score' =>
                            $audit['helm_score'],
                    ],
                ]);

                $run->save();

                /*
                 * Rebuild today’s snapshot so it always reflects the
                 * latest findings and statuses.
                 */
                $run->findings()->delete();

                foreach ($findings as $finding) {
                    $run->findings()->create([
                        'audit_finding_id' =>
                            $finding->id,

                        'fingerprint' =>
                            $finding->fingerprint,

                        'category' =>
                            $finding->category,

                        'title' =>
                            $finding->title,

                        'description' =>
                            $finding->description,

                        'recommendation' =>
                            $finding->recommendation,

                        'severity' =>
                            $finding->severity,

                        'status' =>
                            $finding->status,

                        'score' =>
                            $finding->score,

                        'route_name' =>
                            $finding->route_name,

                        'metadata' => [
                            'first_detected_at' =>
                                $finding
                                    ->first_detected_at
                                    ?->toIso8601String(),

                            'last_detected_at' =>
                                $finding
                                    ->last_detected_at
                                    ?->toIso8601String(),
                        ],
                    ]);
                }

                return $run->load('findings');
            },
        );
    }
}
