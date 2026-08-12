<?php

namespace App\Services\Dashboard;

use App\Models\AiInsightRun;
use App\Models\AuditFinding;
use App\Models\AuditRun;
use App\Models\HelmScoreSnapshot;
use App\Models\InvestmentAccount;
use App\Models\User;
use App\Services\Audit\AuditHistoryComparisonService;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly AuditHistoryComparisonService $comparisonService,
    ) {
    }

    /**
     * Build the dashboard using persisted data only.
     *
     * IMPORTANT:
     * This method must remain read-only and fast.
     * Do not calculate Helm Score or Advisor Audit here.
     *
     * @return array<string, mixed>
     */
    public function build(
        int $userId,
    ): array {
        $user = User::query()
            ->with('investorProfile')
            ->findOrFail($userId);

        /*
         * Dashboard account data only.
         *
         * Do not eager-load transactions, portfolio snapshots,
         * benchmark history, or other analytics-only relationships.
         */
        $accounts = InvestmentAccount::query()
            ->where('user_id', $userId)
            ->with([
                'institution',
                'profile',
                'holdings.security',
            ])
            ->withCount('holdings')
            ->orderBy('name')
            ->get();

        /*
         * Read the latest completed Helm Score snapshot.
         * No score calculation occurs during the dashboard request.
         */
        $helm = $this->latestHelmScore(
            $userId,
        );

        /*
         * Load only the two latest persisted audit runs.
         */
        $auditRuns = AuditRun::query()
            ->where('user_id', $userId)
            ->with('findings')
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        $currentAuditRun = $auditRuns->first();

        $previousAuditRun = $auditRuns
            ->skip(1)
            ->first();

        $auditComparison =
            $currentAuditRun !== null
                ? $this->comparisonService->compare(
                    $currentAuditRun,
                    $previousAuditRun,
                )
                : null;

        /*
         * Reconstruct the lightweight Advisor Audit payload needed by
         * dashboard.blade.php from already-persisted records.
         *
         * This deliberately does NOT call AdvisorAuditService::analyze().
         */
        $advisorAudit = $this->persistedAdvisorAudit(
            currentAuditRun: $currentAuditRun,
            helm: $helm,
        );

        /*
         * Finding status counts.
         */
        $findingStatusCounts = AuditFinding::query()
            ->where('user_id', $userId)
            ->selectRaw(
                'status, COUNT(*) as aggregate',
            )
            ->groupBy('status')
            ->pluck(
                'aggregate',
                'status',
            );

        /*
         * Only the five most important open findings are needed
         * for the dashboard.
         */
        $openFindings = AuditFinding::query()
            ->where('user_id', $userId)
            ->where(
                'status',
                AuditFinding::STATUS_OPEN,
            )
            ->orderByRaw(
                "CASE severity
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'important' THEN 3
                    WHEN 'medium' THEN 4
                    WHEN 'moderate' THEN 4
                    WHEN 'low' THEN 5
                    WHEN 'informational' THEN 6
                    WHEN 'information' THEN 6
                    WHEN 'positive' THEN 7
                    ELSE 8
                END",
            )
            ->orderByDesc('last_detected_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        /*
         * Latest persisted AI insight only.
         * No AI generation is performed here.
         */
        $latestAiInsight = AiInsightRun::query()
            ->where('user_id', $userId)
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->first();

        $portfolioValue = (float) $accounts->sum(
            fn (
                InvestmentAccount $account,
            ): float =>
                (float) (
                    $account->current_value
                    ?? 0
                ),
        );

        $cashValue = (float) $accounts->sum(
            fn (
                InvestmentAccount $account,
            ): float =>
                (float) (
                    $account->cash_value
                    ?? 0
                ),
        );

        return [
            'accounts' =>
                $accounts,

            'portfolioValue' =>
                $portfolioValue,

            'cashValue' =>
                $cashValue,

            'accountCount' =>
                $accounts->count(),

            'helm' =>
                $helm,

            'largestAccount' =>
                $accounts
                    ->sortByDesc(
                        fn (
                            InvestmentAccount $account,
                        ): float =>
                            (float) (
                                $account->current_value
                                ?? 0
                            ),
                    )
                    ->first(),

            'advisorAudit' =>
                $advisorAudit,

            'investorProfile' =>
                $user->investorProfile,

            'suitability' =>
                data_get(
                    $advisorAudit,
                    'categories.suitability',
                    [],
                ),

            'currentAuditRun' =>
                $currentAuditRun,

            'previousAuditRun' =>
                $previousAuditRun,

            'auditComparison' =>
                $auditComparison,

            'openFindings' =>
                $openFindings,

            'latestAiInsight' =>
                $latestAiInsight,

            'findingCounts' => [
                'open' =>
                    (int) (
                        $findingStatusCounts[
                            AuditFinding::STATUS_OPEN
                        ]
                        ?? 0
                    ),

                'reviewed' =>
                    (int) (
                        $findingStatusCounts[
                            AuditFinding::STATUS_REVIEWED
                        ]
                        ?? 0
                    ),

                'dismissed' =>
                    (int) (
                        $findingStatusCounts[
                            AuditFinding::STATUS_DISMISSED
                        ]
                        ?? 0
                    ),

                'resolved' =>
                    (int) (
                        $findingStatusCounts[
                            AuditFinding::STATUS_RESOLVED
                        ]
                        ?? 0
                    ),

                'critical' =>
                    (int) data_get(
                        $advisorAudit,
                        'findings.summary.critical_count',
                        0,
                    ),

                'important' =>
                    (int) data_get(
                        $advisorAudit,
                        'findings.summary.important_count',
                        0,
                    ),

                'opportunity' =>
                    (int) data_get(
                        $advisorAudit,
                        'findings.summary.opportunity_count',
                        0,
                    ),
            ],
        ];
    }

    /**
     * Load the latest successfully persisted Helm Score.
     *
     * @return array<string, mixed>|null
     */
    private function latestHelmScore(
        int $userId,
    ): ?array {
        $snapshot = HelmScoreSnapshot::query()
            ->where('user_id', $userId)
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->first();

        if ($snapshot === null) {
            return null;
        }

        $details = $snapshot->score_details;

        if (is_string($details)) {
            $decoded = json_decode(
                $details,
                true,
            );

            $details = is_array($decoded)
                ? $decoded
                : [];
        }

        if (! is_array($details)) {
            $details = [];
        }

        $details['overall_score'] =
            $details['overall_score']
            ?? (
                $snapshot->overall_score !== null
                    ? (int) $snapshot->overall_score
                    : null
            );

        $details['data_completeness'] =
            $details['data_completeness']
            ?? (
                $snapshot->data_completeness !== null
                    ? (float) $snapshot->data_completeness
                    : 0.0
            );

        $details['calculated_for_date'] =
            $details['calculated_for_date']
            ?? $snapshot
                ->calculated_for_date
                ?->toDateString();

        $details['formula_version'] =
            $details['formula_version']
            ?? $snapshot->formula_version;

        $details['snapshot_id'] =
            $snapshot->id;

        $details['snapshot_created_at'] =
            $snapshot->created_at
                ?->toIso8601String();

        return $details;
    }

    /**
     * Build only the small Advisor Audit structure required by
     * the dashboard using persisted data.
     *
     * Helm Score categories are used as the category source because
     * they are already stored and contain the metrics used by the
     * dashboard cards.
     *
     * @param array<string, mixed>|null $helm
     * @return array<string, mixed>
     */
    private function persistedAdvisorAudit(
        ?AuditRun $currentAuditRun,
        ?array $helm,
    ): array {
        $categories = is_array(
            data_get(
                $helm,
                'categories',
            ),
        )
            ? data_get(
                $helm,
                'categories',
                [],
            )
            : [];

        if ($currentAuditRun === null) {
            return [
                'status' =>
                    'pending',

                'message' =>
                    'Advisor Audit has not been calculated yet.',

                'overall_score' =>
                    null,

                'overall_label' =>
                    'Not yet calculated',

                'advisor_rating' =>
                    null,

                'data_completeness' =>
                    (float) data_get(
                        $helm,
                        'data_completeness',
                        0.0,
                    ),

                'categories' =>
                    $categories,

                'findings' =>
                    $this->emptyFindingSummary(),
            ];
        }

        $findings = collect(
            $currentAuditRun->findings
            ?? [],
        );

        $critical = $findings
            ->filter(
                fn ($finding): bool =>
                    strtolower(
                        (string) data_get(
                            $finding,
                            'severity',
                            '',
                        ),
                    ) === 'critical',
            )
            ->values();

        $important = $findings
            ->filter(
                fn ($finding): bool =>
                    in_array(
                        strtolower(
                            (string) data_get(
                                $finding,
                                'severity',
                                '',
                            ),
                        ),
                        [
                            'high',
                            'important',
                            'medium',
                            'moderate',
                        ],
                        true,
                    ),
            )
            ->values();

        $opportunities = $findings
            ->filter(
                fn ($finding): bool =>
                    in_array(
                        strtolower(
                            (string) data_get(
                                $finding,
                                'severity',
                                '',
                            ),
                        ),
                        [
                            'low',
                            'informational',
                            'information',
                            'positive',
                            'opportunity',
                        ],
                        true,
                    ),
            )
            ->values();

        $auditScore = data_get(
            $currentAuditRun,
            'audit_score',
        );

        $auditScore = $auditScore !== null
            ? (int) $auditScore
            : null;

        return [
            'status' =>
                'complete',

            'message' =>
                null,

            'overall_score' =>
                $auditScore,

            'overall_label' =>
                $this->auditLabel(
                    $auditScore,
                ),

            'advisor_rating' =>
                null,

            'data_completeness' =>
                (float) (
                    data_get(
                        $currentAuditRun,
                        'data_completeness',
                    )
                    ?? data_get(
                        $helm,
                        'data_completeness',
                        0.0,
                    )
                ),

            'calculated_for_date' =>
                $currentAuditRun
                    ->calculated_for_date
                    ?->toDateString(),

            'categories' =>
                $categories,

            'findings' => [
                'critical' =>
                    $critical->all(),

                'important' =>
                    $important->all(),

                'opportunities' =>
                    $opportunities->all(),

                'recommendations' =>
                    [],

                'all' =>
                    $findings->values()->all(),

                'summary' => [
                    'critical_count' =>
                        $critical->count(),

                    'important_count' =>
                        $important->count(),

                    'opportunity_count' =>
                        $opportunities->count(),

                    'recommendation_count' =>
                        0,

                    'total_finding_count' =>
                        $findings->count(),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyFindingSummary(): array
    {
        return [
            'critical' =>
                [],

            'important' =>
                [],

            'opportunities' =>
                [],

            'recommendations' =>
                [],

            'all' =>
                [],

            'summary' => [
                'critical_count' =>
                    0,

                'important_count' =>
                    0,

                'opportunity_count' =>
                    0,

                'recommendation_count' =>
                    0,

                'total_finding_count' =>
                    0,
            ],
        ];
    }

    private function auditLabel(
        ?int $score,
    ): string {
        return match (true) {
            $score === null =>
                'Not yet calculated',

            $score >= 90 =>
                'Excellent',

            $score >= 80 =>
                'Very good',

            $score >= 70 =>
                'Good',

            $score >= 60 =>
                'Fair',

            $score >= 40 =>
                'Needs review',

            default =>
                'Action recommended',
        };
    }
}