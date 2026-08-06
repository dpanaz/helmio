<?php

namespace App\Services\AI;

use App\Models\AuditFinding;
use App\Models\AuditRun;
use App\Models\BrokerageConnection;
use App\Models\InvestmentAccount;
use App\Models\User;
use App\Services\Analytics\HelmScoreService;
use App\Services\Audit\AdvisorAuditService;
use Illuminate\Support\Collection;

class AiPortfolioContextService
{
    public const CONTEXT_VERSION = 'ai-portfolio-context-0.1.0';

    public function __construct(
        private readonly HelmScoreService $helmScoreService,
        private readonly AdvisorAuditService $advisorAuditService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with([
                'institution',
                'holdings.security',
                'transactions.security',
                'brokerageConnection',
            ])
            ->orderBy('name')
            ->get();

        $connections = BrokerageConnection::query()
            ->where('user_id', $user->id)
            ->orderByDesc('last_successful_sync_at')
            ->get();

        $helm = $this->helmScoreService->calculate(
            $accounts,
        );

        $audit = $this->advisorAuditService->build(
            $accounts,
        );

        $latestAuditRun = AuditRun::query()
            ->where('user_id', $user->id)
            ->with('findings')
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->first();

        $previousAuditRun = AuditRun::query()
            ->where('user_id', $user->id)
            ->when(
                $latestAuditRun !== null,
                fn ($query) => $query->where(
                    'id',
                    '!=',
                    $latestAuditRun->id,
                ),
            )
            ->with('findings')
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->first();

        $openFindings = AuditFinding::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                AuditFinding::STATUS_OPEN,
                AuditFinding::STATUS_REVIEWED,
            ])
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
            ->limit(10)
            ->get();

        $holdings = $accounts
            ->flatMap(
                fn (InvestmentAccount $account): Collection =>
                    $account->holdings->map(
                        fn ($holding): array => [
                            'account_id' => $account->id,
                            'account_name' => $account->name,
                            'symbol' =>
                                $holding->security?->symbol,
                            'name' =>
                                $holding->security?->name
                                ?? 'Unknown security',
                            'security_type' =>
                                $holding->security?->security_type,
                            'asset_class' =>
                                $holding->security?->asset_class,
                            'sector' =>
                                $holding->security?->sector,
                            'quantity' =>
                                (float) $holding->quantity,
                            'market_value' =>
                                (float) $holding->market_value,
                            'portfolio_weight' =>
                                $holding->portfolio_weight !== null
                                    ? (float) $holding->portfolio_weight
                                    : null,
                        ],
                    ),
            )
            ->sortByDesc('market_value')
            ->values();

        $portfolioValue = (float) $accounts->sum(
            'current_value',
        );

        $cashValue = (float) $accounts->sum(
            'cash_value',
        );

        $latestSuccessfulSync = $connections
            ->pluck('last_successful_sync_at')
            ->filter()
            ->sortDesc()
            ->first();

        $staleConnections = $connections
            ->filter(
                fn (BrokerageConnection $connection): bool =>
                    $connection->isStale(),
            )
            ->values();

        return [
            'context_version' => self::CONTEXT_VERSION,
            'generated_at' => now()->toIso8601String(),

            'portfolio' => [
                'total_value' => round(
                    $portfolioValue,
                    2,
                ),

                'cash_value' => round(
                    $cashValue,
                    2,
                ),

                'invested_value' => round(
                    max(
                        0,
                        $portfolioValue - $cashValue,
                    ),
                    2,
                ),

                'account_count' =>
                    $accounts->count(),

                'holding_count' =>
                    $holdings->count(),

                'cash_weight' =>
                    $portfolioValue > 0
                        ? round(
                            $cashValue / $portfolioValue,
                            6,
                        )
                        : null,
            ],

            'helm_score' => [
                'overall_score' =>
                    $helm['overall_score'] ?? null,

                'overall_label' =>
                    $helm['overall_label']
                    ?? 'Not calculated',

                'data_completeness' =>
                    $helm['data_completeness']
                    ?? null,

                'categories' =>
                    $this->normalizeCategories(
                        $helm['categories'] ?? [],
                    ),

                'formula_version' =>
                    $helm['formula_version']
                    ?? null,
            ],

            'advisor_audit' => [
                'score' =>
                    $audit['audit_score'] ?? null,

                'grade' =>
                    $audit['audit_grade'] ?? null,

                'label' =>
                    $audit['audit_label'] ?? null,

                'annual_cost' =>
                    $audit['annual_cost'] ?? 0,

                'potential_savings' =>
                    $audit['potential_savings'] ?? 0,

                'issue_count' =>
                    $audit['issue_count'] ?? 0,

                'review_recommended' =>
                    $audit['review_recommended']
                    ?? false,

                'formula_version' =>
                    $audit['formula_version']
                    ?? null,
            ],

            'top_holdings' => $holdings
                ->take(10)
                ->values()
                ->all(),

            'open_findings' => $openFindings
                ->map(
                    fn (AuditFinding $finding): array => [
                        'category' =>
                            $finding->category,

                        'severity' =>
                            $finding->severity,

                        'status' =>
                            $finding->status,

                        'title' =>
                            $finding->title,

                        'description' =>
                            $finding->description,

                        'recommendation' =>
                            $finding->recommendation,

                        'score' =>
                            $finding->score,

                        'route_name' =>
                            $finding->route_name,
                    ],
                )
                ->values()
                ->all(),

            'audit_history' => [
                'latest' =>
                    $this->normalizeAuditRun(
                        $latestAuditRun,
                    ),

                'previous' =>
                    $this->normalizeAuditRun(
                        $previousAuditRun,
                    ),
            ],

            'data_freshness' => [
                'connection_count' =>
                    $connections->count(),

                'active_connection_count' =>
                    $connections
                        ->where(
                            'status',
                            BrokerageConnection::STATUS_ACTIVE,
                        )
                        ->count(),

                'stale_connection_count' =>
                    $staleConnections->count(),

                'latest_successful_sync_at' =>
                    $latestSuccessfulSync
                        ?->toIso8601String(),

                'status' =>
                    $this->freshnessStatus(
                        $connections,
                        $staleConnections,
                    ),
            ],

            'limitations' =>
                $this->buildLimitations(
                    $accounts,
                    $connections,
                    $helm,
                ),
        ];
    }

    /**
     * @param array<string, mixed> $categories
     * @return array<string, mixed>
     */
    private function normalizeCategories(
        array $categories,
    ): array {
        return collect($categories)
            ->map(
                fn (array $category): array => [
                    'score' =>
                        $category['score'] ?? null,

                    'label' =>
                        $category['label']
                        ?? 'Not calculated',

                    'reasons' =>
                        $category['reasons'] ?? [],

                    'recommendations' =>
                        $category['recommendations']
                        ?? [],

                    'metrics' =>
                        $category['metrics'] ?? [],
                ],
            )
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeAuditRun(
        ?AuditRun $run,
    ): ?array {
        if ($run === null) {
            return null;
        }

        return [
            'id' => $run->id,
            'calculated_for_date' =>
                $run
                    ->calculated_for_date
                    ->toDateString(),

            'audit_score' =>
                $run->audit_score,

            'audit_grade' =>
                $run->audit_grade,

            'annual_cost' =>
                (float) $run->annual_cost,

            'potential_savings' =>
                (float) $run->potential_savings,

            'issue_count' =>
                $run->issue_count,
        ];
    }

    /**
     * @param Collection<int, BrokerageConnection> $connections
     * @param Collection<int, BrokerageConnection> $staleConnections
     */
    private function freshnessStatus(
        Collection $connections,
        Collection $staleConnections,
    ): string {
        if ($connections->isEmpty()) {
            return 'manual_only';
        }

        if (
            $connections->contains(
                fn (BrokerageConnection $connection): bool =>
                    $connection->status
                    === BrokerageConnection::STATUS_ERROR,
            )
        ) {
            return 'attention';
        }

        if ($staleConnections->isNotEmpty()) {
            return 'stale';
        }

        return 'current';
    }

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @param Collection<int, BrokerageConnection> $connections
     * @param array<string, mixed> $helm
     * @return array<int, string>
     */
    private function buildLimitations(
        Collection $accounts,
        Collection $connections,
        array $helm,
    ): array {
        $limitations = collect();

        if ($accounts->isEmpty()) {
            $limitations->push(
                'No investment accounts are available.',
            );
        }

        if ($connections->isEmpty()) {
            $limitations->push(
                'Portfolio data is manually entered and may not reflect current brokerage balances.',
            );
        }

        if (
            $connections->contains(
                fn (BrokerageConnection $connection): bool =>
                    $connection->isStale(),
            )
        ) {
            $limitations->push(
                'One or more brokerage connections have stale data.',
            );
        }

        $completeness = (float) (
            $helm['data_completeness'] ?? 0
        );

        if ($completeness < 0.8) {
            $limitations->push(
                'Analytics data completeness is below 80 percent.',
            );
        }

        return $limitations->values()->all();
    }
}