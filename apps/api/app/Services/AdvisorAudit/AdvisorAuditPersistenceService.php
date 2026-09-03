<?php

namespace App\Services\AdvisorAudit;

use App\Models\AuditFinding;
use App\Models\AuditRun;
use App\Models\AuditRunFinding;
use App\Models\Benchmark;
use App\Models\User;
use App\Services\Marketing\MarketingConversionService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdvisorAuditPersistenceService
{
    public function __construct(
        private readonly AdvisorAuditService $advisorAuditService,
        private readonly MarketingConversionService $marketingConversions,
        private readonly AdvisorAuditNotificationService $notificationService,
    ) {
    }

    /**
     * Run and persist an Advisor Audit.
     *
     * @return array<string, mixed>
     */
    public function runAndPersist(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?Benchmark $benchmark = null
    ): array {
        $audit = $this->advisorAuditService->analyze(
            user: $user,
            startDate: $startDate,
            endDate: $endDate,
            benchmark: $benchmark,
        );

        $formulaVersion = (string) (
            $audit['formula_version']
            ?? AdvisorAuditService::FORMULA_VERSION
        );

        $calculatedForDate =
            $endDate->toDateString();

        /*
         * Capture the prior audit before persisting this one so the
         * notification layer can compare score and finding changes.
         * Exclude the row that runAndPersist() will updateOrCreate for
         * this user/date/formula combination.
         */
        $previousRun = AuditRun::query()
            ->where('user_id', $user->id)
            ->where(function ($query) use (
                $calculatedForDate,
                $formulaVersion
            ): void {
                $query
                    ->whereDate(
                        'calculated_for_date',
                        '!=',
                        $calculatedForDate
                    )
                    ->orWhere(
                        'formula_version',
                        '!=',
                        $formulaVersion
                    );
            })
            ->orderByDesc('id')
            ->first();

        /*
         * Persist both the immutable audit run and the current
         * AuditFinding state in one transaction. The Action Center
         * and dashboard read AuditFinding rows, so this is the
         * source-of-truth write path for a completed Advisor Audit.
         */
        $currentRun = DB::transaction(function () use (
            $user,
            $audit,
            $endDate
        ): AuditRun {
            $auditRun = $this->persistAuditRun(
                user: $user,
                audit: $audit,
                calculatedForDate: $endDate,
            );

            $activeFingerprints = [];

            foreach (
                $this->persistableFindings($audit)
                as $finding
            ) {
                $auditFinding = $this->upsertFinding(
                    user: $user,
                    finding: $finding,
                );

                $activeFingerprints[] =
                    $auditFinding->fingerprint;

                $this->persistRunFinding(
                    auditRun: $auditRun,
                    auditFinding: $auditFinding,
                    finding: $finding,
                );
            }

            $this->resolveMissingFindings(
                user: $user,
                activeFingerprints:
                    $activeFingerprints,
            );

            return $auditRun;
        });

        /*
         * In-app notifications are part of every successful Advisor
         * Audit, regardless of whether monthly audit scheduling is
         * enabled. Notification failures must never invalidate a
         * successfully persisted audit.
         */
        try {
            $this->notificationService->sendInApp(
                user: $user,
                currentRun: $currentRun,
                previousRun: $previousRun,
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        /*
         * Record the conversion only after the audit transaction
         * completes. Marketing failures must never cause a saved
         * advisor audit to be treated as failed.
         */
        try {
            $this->marketingConversions->record(
                type: 'AuditCompleted',
                user: $user,
                metadata: [
                    'formula_version' =>
                        $audit['formula_version']
                        ?? null,

                    'overall_score' =>
                        isset($audit['overall_score'])
                        && is_numeric(
                            $audit['overall_score'],
                        )
                            ? (int) $audit['overall_score']
                            : null,

                    'critical_count' =>
                        (int) data_get(
                            $audit,
                            'findings.summary.critical_count',
                            0,
                        ),

                    'important_count' =>
                        (int) data_get(
                            $audit,
                            'findings.summary.important_count',
                            0,
                        ),

                    'opportunity_count' =>
                        (int) data_get(
                            $audit,
                            'findings.summary.opportunity_count',
                            0,
                        ),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        return $audit;
    }

    /**
     * @param array<string, mixed> $audit
     */
    private function persistAuditRun(
        User $user,
        array $audit,
        CarbonInterface $calculatedForDate
    ): AuditRun {
        $categories =
            $audit['categories'] ?? [];

        $findings =
            $audit['findings'] ?? [];

        $summary =
            $findings['summary'] ?? [];

        $criticalCount = (int) (
            $summary['critical_count'] ?? 0
        );

        $importantCount = (int) (
            $summary['important_count'] ?? 0
        );

        $opportunityCount = (int) (
            $summary['opportunity_count'] ?? 0
        );

        $issueCount =
            $criticalCount
            + $importantCount;

        $portfolioValue = (float) data_get(
            $audit,
            'raw_analytics.cost.data.cost_analytics.portfolio_value',
            data_get(
                $audit,
                'raw_analytics.cost.metrics.portfolio_value',
                0
            )
        );

        $annualCost = (float) data_get(
            $audit,
            'categories.cost.metrics.annual_cost',
            0
        );

        $potentialSavings = (float) data_get(
            $audit,
            'categories.cost.metrics.potential_savings',
            0
        );

        $score = isset($audit['overall_score'])
            && is_numeric($audit['overall_score'])
                ? (int) $audit['overall_score']
                : null;

        return AuditRun::query()->updateOrCreate(
            [
                'user_id' =>
                    $user->id,

                'calculated_for_date' =>
                    $calculatedForDate
                        ->toDateString(),

                'formula_version' =>
                    (string) (
                        $audit['formula_version']
                        ?? AdvisorAuditService::FORMULA_VERSION
                    ),
            ],
            [
                'audit_score' =>
                    $score,

                'audit_grade' =>
                    $this->gradeForScore($score),

                'audit_label' =>
                    $audit['overall_label']
                    ?? null,

                'portfolio_value' =>
                    round(
                        $portfolioValue,
                        2
                    ),

                'annual_cost' =>
                    round(
                        $annualCost,
                        2
                    ),

                'potential_savings' =>
                    round(
                        $potentialSavings,
                        2
                    ),

                'issue_count' =>
                    $issueCount,

                'critical_count' =>
                    $criticalCount,

                'high_count' =>
                    $importantCount,

                'medium_count' =>
                    0,

                'positive_count' =>
                    $opportunityCount,

                'category_scores' =>
                    collect($categories)
                        ->map(
                            fn (
                                array $category
                            ): ?int =>
                                isset($category['score'])
                                && is_numeric(
                                    $category['score']
                                )
                                    ? (int) $category['score']
                                    : null
                        )
                        ->all(),

                'audit_details' =>
                    $audit,
            ],
        );
    }

    /**
     * @param array<string, mixed> $audit
     * @return array<int, array<string, mixed>>
     */
    private function persistableFindings(
        array $audit
    ): array {
        $findings = [];

        foreach (
            [
                'critical',
                'important',
                'opportunities',
                'recommendations',
            ] as $group
        ) {
            foreach (
                data_get(
                    $audit,
                    "findings.{$group}",
                    []
                ) as $finding
            ) {
                if (! is_array($finding)) {
                    continue;
                }

                $finding['group'] = $group;
                $findings[] = $finding;
            }
        }

        return $findings;
    }

    /**
     * @param array<string, mixed> $finding
     */
    private function upsertFinding(
        User $user,
        array $finding
    ): AuditFinding {
        $fingerprint =
            $this->fingerprint($finding);

        $existing = AuditFinding::query()
            ->where('user_id', $user->id)
            ->where(
                'fingerprint',
                $fingerprint
            )
            ->first();

        $status = $existing?->status;

        if (
            $status === null
            || $status === AuditFinding::STATUS_RESOLVED
        ) {
            $status =
                AuditFinding::STATUS_OPEN;
        }

        $findingType =
            $finding['type']
            ?? null;

        $recommendation =
            $findingType === 'recommendation'
                ? (
                    $finding['message']
                    ?? $finding['recommendation']
                    ?? null
                )
                : (
                    $finding['recommendation']
                    ?? null
                );

        $auditFinding = AuditFinding::query()
            ->updateOrCreate(
                [
                    'user_id' =>
                        $user->id,

                    'fingerprint' =>
                        $fingerprint,
                ],
                [
                    'category' =>
                        $finding['category']
                        ?? 'general',

                    'title' =>
                        $finding['title']
                        ?? 'Advisor audit finding',

                    'description' =>
                        $finding['message']
                        ?? 'An advisor audit finding was detected.',

                    'recommendation' =>
                        $recommendation,

                    'severity' =>
                        $this->databaseSeverity(
                            $finding
                        ),

                    'status' =>
                        $status,

                    'score' =>
                        $this->normalizePriority(
                            $finding['priority']
                            ?? null
                        ),

                    'route_name' =>
                        $this->routeForCategory(
                            $finding['category']
                            ?? null
                        ),

                    'first_detected_at' =>
                        $existing?->first_detected_at
                        ?? now(),

                    'last_detected_at' =>
                        now(),

                    'resolved_at' =>
                        null,

                    'metadata' => [
                        'finding_id' =>
                            $finding['id']
                            ?? null,

                        'code' =>
                            $finding['code']
                            ?? null,

                        'type' =>
                            $finding['type']
                            ?? null,

                        'group' =>
                            $finding['group']
                            ?? null,

                        'category_label' =>
                            $finding[
                                'category_label'
                            ] ?? null,

                        'financial_impact' =>
                            $finding[
                                'financial_impact'
                            ] ?? null,

                        'confidence' =>
                            $finding[
                                'confidence'
                            ] ?? null,

                        'source' =>
                            $finding['source']
                            ?? null,

                        'priority' =>
                            $finding['priority']
                            ?? null,
                    ],
                ],
            );

        return $auditFinding;
    }

    /**
     * @param array<string, mixed> $finding
     */
    private function persistRunFinding(
        AuditRun $auditRun,
        AuditFinding $auditFinding,
        array $finding
    ): void {
        AuditRunFinding::query()
            ->updateOrCreate(
                [
                    'audit_run_id' =>
                        $auditRun->id,

                    'fingerprint' =>
                        $auditFinding->fingerprint,
                ],
                [
                    'audit_finding_id' =>
                        $auditFinding->id,

                    'category' =>
                        $auditFinding->category,

                    'title' =>
                        $auditFinding->title,

                    'description' =>
                        $auditFinding->description,

                    'recommendation' =>
                        $auditFinding->recommendation,

                    'severity' =>
                        $auditFinding->severity,

                    'status' =>
                        $auditFinding->status,

                    'score' =>
                        $auditFinding->score,

                    'route_name' =>
                        $auditFinding->route_name,

                    'metadata' =>
                        array_merge(
                            $auditFinding->metadata
                            ?? [],
                            [
                                'snapshot_at' =>
                                    now()
                                        ->toIso8601String(),

                                'finding' =>
                                    $finding,
                            ]
                        ),
                ],
            );
    }

    /**
     * @param array<int, string> $activeFingerprints
     */
    private function resolveMissingFindings(
        User $user,
        array $activeFingerprints
    ): void {
        $query = AuditFinding::query()
            ->where('user_id', $user->id)
            ->whereIn(
                'status',
                [
                    AuditFinding::STATUS_OPEN,
                    AuditFinding::STATUS_REVIEWED,
                ]
            );

        if ($activeFingerprints !== []) {
            $query->whereNotIn(
                'fingerprint',
                array_values(
                    array_unique(
                        $activeFingerprints
                    )
                )
            );
        }

        $query->update([
            'status' =>
                AuditFinding::STATUS_RESOLVED,

            'resolved_at' =>
                now(),
        ]);
    }

    /**
     * @param array<string, mixed> $finding
     */
    private function fingerprint(
        array $finding
    ): string {
        if (
            isset($finding['id'])
            && is_string($finding['id'])
            && $finding['id'] !== ''
        ) {
            return hash(
                'sha256',
                $finding['id']
            );
        }

        return hash(
            'sha256',
            implode('|', [
                (string) (
                    $finding['category']
                    ?? 'general'
                ),

                (string) (
                    $finding['code']
                    ?? 'finding'
                ),

                (string) (
                    $finding['title']
                    ?? ''
                ),

                (string) (
                    $finding['message']
                    ?? ''
                ),
            ])
        );
    }

    /**
     * @param array<string, mixed> $finding
     */
    private function databaseSeverity(
        array $finding
    ): string {
        if (
            ($finding['type'] ?? null)
            === 'opportunity'
        ) {
            return 'positive';
        }

        if (
            ($finding['type'] ?? null)
            === 'recommendation'
        ) {
            return 'information';
        }

        return match (
            strtolower(
                (string) (
                    $finding['severity']
                    ?? ''
                )
            )
        ) {
            'critical' =>
                'critical',

            'high',
            'important' =>
                'high',

            'moderate',
            'medium' =>
                'medium',

            'low' =>
                'low',

            'informational',
            'information' =>
                'information',

            'positive',
            'opportunity' =>
                'positive',

            default =>
                'medium',
        };
    }

    private function normalizePriority(
        mixed $priority
    ): ?int {
        if (! is_numeric($priority)) {
            return null;
        }

        return (int) max(
            0,
            min(
                100,
                round(
                    (float) $priority
                )
            )
        );
    }

    private function routeForCategory(
        ?string $category
    ): ?string {
        return match ($category) {
            'cost' =>
                'analytics.costs',

            'diversification' =>
                'analytics.diversification',

            'performance' =>
                'analytics.performance',

            'risk' =>
                'analytics.risk',

            'trading' =>
                'analytics.trading-discipline',

            'cash' =>
                'analytics.cash-drag',

            'tax' =>
                'analytics.tax-efficiency',

            default =>
                'advisor-audit.index',
        };
    }

    private function gradeForScore(
        ?int $score
    ): ?string {
        if ($score === null) {
            return null;
        }

        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }
}