<?php

namespace App\Services\Dashboard;

use App\Models\AuditFinding;
use App\Models\AuditRun;
use App\Models\InvestmentAccount;
use App\Services\Analytics\HelmScoreService;
use App\Services\Audit\AdvisorAuditService;
use App\Services\Audit\AuditHistoryComparisonService;

class DashboardService
{
    public function __construct(
        private readonly HelmScoreService $helmScoreService,
        private readonly AdvisorAuditService $advisorAuditService,
        private readonly AuditHistoryComparisonService $comparisonService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(int $userId): array
    {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $userId)
            ->with([
                'institution',
                'holdings.security',
                'transactions.security',
                'portfolioSnapshots',
                'benchmark.returns',
            ])
            ->orderBy('name')
            ->get();

        $helm = $this->helmScoreService->calculate(
            $accounts,
        );

        $advisorAudit = $this->advisorAuditService->build(
            $accounts,
        );

        $auditRuns = AuditRun::query()
            ->where('user_id', $userId)
            ->with('findings')
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        $currentAuditRun = $auditRuns->first();
        $previousAuditRun = $auditRuns->skip(1)->first();

        $auditComparison = $currentAuditRun !== null
            ? $this->comparisonService->compare(
                $currentAuditRun,
                $previousAuditRun,
            )
            : null;

        $findingCounts = AuditFinding::query()
            ->where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $openFindings = AuditFinding::query()
            ->where('user_id', $userId)
            ->where('status', AuditFinding::STATUS_OPEN)
            ->orderByRaw(
                "CASE severity
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                    WHEN 'information' THEN 5
                    WHEN 'positive' THEN 6
                    ELSE 7
                END",
            )
            ->orderByDesc('last_detected_at')
            ->limit(5)
            ->get();

        return [
            'accounts' => $accounts,

            'portfolioValue' =>
                (float) $accounts->sum('current_value'),

            'cashValue' =>
                (float) $accounts->sum('cash_value'),

            'accountCount' =>
                $accounts->count(),

            'helm' => $helm,

            'largestAccount' => $accounts
                ->sortByDesc('current_value')
                ->first(),

            'advisorAudit' => $advisorAudit,

            'currentAuditRun' =>
                $currentAuditRun,

            'previousAuditRun' =>
                $previousAuditRun,

            'auditComparison' =>
                $auditComparison,

            'openFindings' =>
                $openFindings,

            'findingCounts' => [
                'open' => (int) (
                    $findingCounts[
                        AuditFinding::STATUS_OPEN
                    ] ?? 0
                ),

                'reviewed' => (int) (
                    $findingCounts[
                        AuditFinding::STATUS_REVIEWED
                    ] ?? 0
                ),

                'dismissed' => (int) (
                    $findingCounts[
                        AuditFinding::STATUS_DISMISSED
                    ] ?? 0
                ),

                'resolved' => (int) (
                    $findingCounts[
                        AuditFinding::STATUS_RESOLVED
                    ] ?? 0
                ),
            ],
        ];
    }
}