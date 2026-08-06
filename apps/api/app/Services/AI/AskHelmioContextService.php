<?php

namespace App\Services\AI;

use App\Models\AiInsightRun;
use App\Models\AuditFinding;
use App\Models\AuditRun;
use App\Models\BrokerageConnection;
use App\Models\MonthlyPortfolioReview;
use App\Models\PortfolioStateSnapshot;
use App\Models\TimelineEvent;
use App\Models\User;

class AskHelmioContextService
{
    public const CONTEXT_VERSION =
        'ask-helmio-context-0.1.0';

    /**
     * @return array<string, mixed>
     */
    public function build(
        User $user,
        string $question,
    ): array {
        $latestSnapshot = PortfolioStateSnapshot::query()
            ->where('user_id', $user->id)
            ->with('holdings')
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->first();

        $latestAudit = AuditRun::query()
            ->where('user_id', $user->id)
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->first();

        $latestReview = MonthlyPortfolioReview::query()
            ->where('user_id', $user->id)
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->first();

        $latestInsight = AiInsightRun::query()
            ->where('user_id', $user->id)
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->first();

        $timelineEvents = TimelineEvent::query()
            ->where('user_id', $user->id)
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $findings = AuditFinding::query()
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
            ->limit(12)
            ->get();

        $connections = BrokerageConnection::query()
            ->where('user_id', $user->id)
            ->get();

        $holdings = $latestSnapshot?->holdings
            ?->sortByDesc('market_value')
            ->take(15)
            ->map(
                fn ($holding): array => [
                    'symbol' => $holding->symbol,
                    'name' => $holding->name,
                    'market_value' =>
                        (float) $holding->market_value,
                    'portfolio_weight' =>
                        $holding->portfolio_weight !== null
                            ? (float) $holding->portfolio_weight
                            : null,
                    'asset_class' =>
                        $holding->asset_class,
                    'sector' =>
                        $holding->sector,
                ],
            )
            ->values()
            ->all() ?? [];

        return [
            'context_version' =>
                self::CONTEXT_VERSION,

            'generated_at' =>
                now()->toIso8601String(),

            'question' => $question,

            'portfolio_snapshot' =>
                $latestSnapshot === null
                    ? null
                    : [
                        'id' => $latestSnapshot->id,
                        'captured_at' =>
                            $latestSnapshot
                                ->captured_at
                                ->toIso8601String(),
                        'portfolio_value' =>
                            (float) $latestSnapshot
                                ->portfolio_value,
                        'cash_value' =>
                            (float) $latestSnapshot
                                ->cash_value,
                        'invested_value' =>
                            (float) $latestSnapshot
                                ->invested_value,
                        'account_count' =>
                            $latestSnapshot
                                ->account_count,
                        'holding_count' =>
                            $latestSnapshot
                                ->holding_count,
                        'top_holdings' => $holdings,
                    ],

            'latest_audit' =>
                $latestAudit === null
                    ? null
                    : [
                        'id' => $latestAudit->id,
                        'date' =>
                            $latestAudit
                                ->calculated_for_date
                                ->toDateString(),
                        'score' =>
                            $latestAudit->audit_score,
                        'grade' =>
                            $latestAudit->audit_grade,
                        'annual_cost' =>
                            (float) $latestAudit
                                ->annual_cost,
                        'potential_savings' =>
                            (float) $latestAudit
                                ->potential_savings,
                        'issue_count' =>
                            $latestAudit->issue_count,
                        'category_scores' =>
                            $latestAudit
                                ->category_scores,
                    ],

            'open_findings' => $findings
                ->map(
                    fn (AuditFinding $finding): array => [
                        'id' => $finding->id,
                        'severity' =>
                            $finding->severity,
                        'category' =>
                            $finding->category,
                        'title' =>
                            $finding->title,
                        'description' =>
                            $finding->description,
                        'recommendation' =>
                            $finding->recommendation,
                        'route_name' =>
                            $finding->route_name,
                    ],
                )
                ->values()
                ->all(),

            'recent_timeline_events' =>
                $timelineEvents
                    ->map(
                        fn (TimelineEvent $event): array => [
                            'id' => $event->id,
                            'event_date' =>
                                $event
                                    ->event_date
                                    ->toDateString(),
                            'type' => $event->type,
                            'severity' =>
                                $event->severity,
                            'category' =>
                                $event->category,
                            'headline' =>
                                $event->headline,
                            'summary' =>
                                $event->summary,
                            'metrics' =>
                                $event->metrics,
                            'route_name' =>
                                $event->route_name,
                            'source_id' =>
                                $event->source_id,
                        ],
                    )
                    ->values()
                    ->all(),

            'latest_monthly_review' =>
                $latestReview === null
                    ? null
                    : [
                        'id' => $latestReview->id,
                        'period' =>
                            $latestReview
                                ->period_start
                                ->format('F Y'),
                        'headline' =>
                            $latestReview->headline,
                        'summary' =>
                            $latestReview->summary,
                        'key_changes' =>
                            $latestReview->key_changes,
                        'positive_changes' =>
                            $latestReview
                                ->positive_changes,
                        'review_items' =>
                            $latestReview->review_items,
                        'limitations' =>
                            $latestReview->limitations,
                    ],

            'latest_ai_insight' =>
                $latestInsight === null
                    ? null
                    : [
                        'id' => $latestInsight->id,
                        'headline' =>
                            $latestInsight->headline,
                        'summary' =>
                            $latestInsight->summary,
                        'priorities' =>
                            $latestInsight->priorities,
                        'positive_changes' =>
                            $latestInsight
                                ->positive_changes,
                        'limitations' =>
                            $latestInsight->limitations,
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
                    $connections
                        ->filter(
                            fn (
                                BrokerageConnection $connection,
                            ): bool =>
                                $connection->isStale(),
                        )
                        ->count(),

                'latest_successful_sync_at' =>
                    $connections
                        ->pluck(
                            'last_successful_sync_at',
                        )
                        ->filter()
                        ->sortDesc()
                        ->first()
                        ?->toIso8601String(),
            ],

            'limitations' =>
                $this->limitations(
                    $latestSnapshot,
                    $latestAudit,
                    $connections,
                ),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function limitations(
        ?PortfolioStateSnapshot $snapshot,
        ?AuditRun $audit,
        $connections,
    ): array {
        $limitations = collect();

        if ($snapshot === null) {
            $limitations->push(
                'No saved portfolio snapshot is available.',
            );
        }

        if ($audit === null) {
            $limitations->push(
                'No recorded Advisor Audit is available.',
            );
        }

        if ($connections->isEmpty()) {
            $limitations->push(
                'Portfolio data may rely on manual entry.',
            );
        }

        if (
            $connections->contains(
                fn (
                    BrokerageConnection $connection,
                ): bool =>
                    $connection->isStale(),
            )
        ) {
            $limitations->push(
                'One or more brokerage connections have stale data.',
            );
        }

        return $limitations->values()->all();
    }
}