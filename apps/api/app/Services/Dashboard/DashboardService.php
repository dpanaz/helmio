<?php

namespace App\Services\Dashboard;

use App\Models\AiInsightRun;
use App\Models\AuditFinding;
use App\Models\AuditRun;
use App\Models\Benchmark;
use App\Models\InvestmentAccount;
use App\Models\User;
use App\Services\AdvisorAudit\AdvisorAuditService;
use App\Services\Analytics\HelmScoreService;
use App\Services\Audit\AuditHistoryComparisonService;
use Illuminate\Support\Facades\Cache;
use Throwable;

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
        $user = User::query()
            ->with('investorProfile')
            ->findOrFail($userId);

        $accounts = InvestmentAccount::query()
            ->where('user_id', $userId)
            ->with([
                'institution',
                'profile',
                'user.investorProfile',
                'holdings.security',
                'transactions.security',
                'portfolioSnapshots',
                'benchmark.returns',
            ])
            ->withCount('holdings')
            ->orderBy('name')
            ->get();

        $helm = $this->helmScoreService->calculate(
            $accounts
        );

        $benchmark = Benchmark::query()
            ->where('is_active', true)
            ->where('symbol', 'SPY')
            ->first();

        try {
            $advisorAudit = Cache::remember(
                key: $this->advisorAuditCacheKey(
                    $userId
                ),

                ttl: now()->addMinutes(15),

                callback: fn (): array =>
                    $this->advisorAuditService->analyze(
                        user: $user,

                        startDate: now()
                            ->subYear()
                            ->startOfDay(),

                        endDate: now()
                            ->endOfDay(),

                        benchmark: $benchmark,
                    ),
            );
        } catch (Throwable $exception) {
            report($exception);

            $advisorAudit =
                $this->failedAdvisorAuditResult();
        }

        $auditRuns = AuditRun::query()
            ->where('user_id', $userId)
            ->with('findings')
            ->orderByDesc(
                'calculated_for_date'
            )
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        $currentAuditRun =
            $auditRuns->first();

        $previousAuditRun =
            $auditRuns->skip(1)->first();

        $auditComparison =
            $currentAuditRun !== null
                ? $this->comparisonService->compare(
                    $currentAuditRun,
                    $previousAuditRun,
                )
                : null;

        $findingStatusCounts =
            AuditFinding::query()
                ->where('user_id', $userId)
                ->selectRaw(
                    'status, COUNT(*) as aggregate'
                )
                ->groupBy('status')
                ->pluck(
                    'aggregate',
                    'status'
                );

        $openFindings =
            AuditFinding::query()
                ->where('user_id', $userId)
                ->where(
                    'status',
                    AuditFinding::STATUS_OPEN
                )
                ->orderByRaw(
                    "CASE severity
                        WHEN 'critical' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'moderate' THEN 3
                        WHEN 'low' THEN 4
                        WHEN 'informational' THEN 5
                        WHEN 'information' THEN 5
                        WHEN 'positive' THEN 6
                        ELSE 7
                    END"
                )
                ->orderByDesc(
                    'last_detected_at'
                )
                ->limit(5)
                ->get();

        $latestAiInsight =
            AiInsightRun::query()
                ->where('user_id', $userId)
                ->orderByDesc('generated_at')
                ->orderByDesc('id')
                ->first();

        $portfolioValue = (float) $accounts
            ->sum(
                fn (
                    InvestmentAccount $account
                ): float =>
                    (float) (
                        $account->current_value
                        ?? 0
                    )
            );

        $cashValue = (float) $accounts
            ->sum(
                fn (
                    InvestmentAccount $account
                ): float =>
                    (float) (
                        $account->cash_value
                        ?? 0
                    )
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
                            InvestmentAccount $account
                        ): float =>
                            (float) (
                                $account->current_value
                                ?? 0
                            )
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
                    []
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
                'open' => (int) (
                    $findingStatusCounts[
                        AuditFinding::STATUS_OPEN
                    ] ?? 0
                ),

                'reviewed' => (int) (
                    $findingStatusCounts[
                        AuditFinding::STATUS_REVIEWED
                    ] ?? 0
                ),

                'dismissed' => (int) (
                    $findingStatusCounts[
                        AuditFinding::STATUS_DISMISSED
                    ] ?? 0
                ),

                'resolved' => (int) (
                    $findingStatusCounts[
                        AuditFinding::STATUS_RESOLVED
                    ] ?? 0
                ),

                'critical' => (int) data_get(
                    $advisorAudit,
                    'findings.summary.critical_count',
                    0
                ),

                'important' => (int) data_get(
                    $advisorAudit,
                    'findings.summary.important_count',
                    0
                ),

                'opportunity' => (int) data_get(
                    $advisorAudit,
                    'findings.summary.opportunity_count',
                    0
                ),
            ],
        ];
    }

    public function clearAdvisorAuditCache(
        int $userId
    ): void {
        Cache::forget(
            $this->advisorAuditCacheKey(
                $userId
            )
        );
    }

    private function advisorAuditCacheKey(
        int $userId
    ): string {
        return sprintf(
            'dashboard:advisor-audit:user:%d:version:%s',
            $userId,
            'advisor-audit-0.2.0'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function failedAdvisorAuditResult(): array
    {
        return [
            'status' =>
                'failed',

            'message' =>
                'The Advisor Audit could not be calculated.',

            'overall_score' =>
                null,

            'overall_label' =>
                'Unavailable',

            'advisor_rating' =>
                null,

            'data_completeness' =>
                0.0,

            'available_weight' =>
                0.0,

            'available_category_count' =>
                0,

            'total_category_count' =>
                8,

            'period' =>
                null,

            'benchmark' =>
                null,

            'categories' =>
                [],

            'findings' => [
                'critical' => [],
                'important' => [],
                'opportunities' => [],
                'recommendations' => [],
                'all' => [],

                'summary' => [
                    'critical_count' => 0,
                    'important_count' => 0,
                    'opportunity_count' => 0,
                    'recommendation_count' => 0,
                    'total_finding_count' => 0,
                ],
            ],

            'executive_summary' => [
                'headline' =>
                    'Advisor Audit unavailable',

                'summary' =>
                    'The audit could not be calculated from the available account data.',

                'top_concerns' => [],
                'top_opportunities' => [],
                'top_recommendations' => [],
            ],

            'raw_analytics' =>
                [],

            'formula_version' =>
                'advisor-audit-0.2.0',

            'calculated_at' =>
                now()->toIso8601String(),
        ];
    }
}