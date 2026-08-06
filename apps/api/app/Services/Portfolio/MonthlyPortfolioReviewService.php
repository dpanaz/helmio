<?php

namespace App\Services\Portfolio;

use App\Models\AiInsightRun;
use App\Models\AuditRun;
use App\Models\MonthlyPortfolioReview;
use App\Models\PortfolioStateSnapshot;
use App\Models\TimelineEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MonthlyPortfolioReviewService
{
    public function generate(
        User $user,
        ?CarbonImmutable $month = null,
    ): MonthlyPortfolioReview {
        $month ??= CarbonImmutable::now();

        $periodStart = $month->startOfMonth();
        $periodEnd = $month->endOfMonth();

        $events = TimelineEvent::query()
            ->where('user_id', $user->id)
            ->whereBetween('event_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->get();

        $snapshots = PortfolioStateSnapshot::query()
            ->where('user_id', $user->id)
            ->whereBetween('captured_at', [
                $periodStart->startOfDay(),
                $periodEnd->endOfDay(),
            ])
            ->orderBy('captured_at')
            ->orderBy('id')
            ->get();

        $auditRuns = AuditRun::query()
            ->where('user_id', $user->id)
            ->whereBetween('calculated_for_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderBy('calculated_for_date')
            ->orderBy('id')
            ->get();

        $startingSnapshot = $snapshots->first();
        $endingSnapshot = $snapshots->last();

        $startingAudit = $auditRuns->first();
        $endingAudit = $auditRuns->last();

        $startingValue = $startingSnapshot !== null
            ? (float) $startingSnapshot->portfolio_value
            : null;

        $endingValue = $endingSnapshot !== null
            ? (float) $endingSnapshot->portfolio_value
            : null;

        $valueChange = $startingValue !== null
            && $endingValue !== null
                ? $endingValue - $startingValue
                : null;

        $valueChangeRate = $valueChange !== null
            && $startingValue > 0
                ? $valueChange / $startingValue
                : null;

        $startingScore = $startingAudit?->audit_score;
        $endingScore = $endingAudit?->audit_score;

        $scoreChange = $startingScore !== null
            && $endingScore !== null
                ? $endingScore - $startingScore
                : null;

        $startingCost = $startingAudit !== null
            ? (float) $startingAudit->annual_cost
            : null;

        $endingCost = $endingAudit !== null
            ? (float) $endingAudit->annual_cost
            : null;

        $costChange = $startingCost !== null
            && $endingCost !== null
                ? $endingCost - $startingCost
                : null;

        $positiveEvents = $events
            ->where('severity', 'positive')
            ->values();

        $attentionEvents = $events
            ->whereIn('severity', [
                'critical',
                'high',
                'medium',
            ])
            ->values();

        $keyChanges = $this->keyChanges($events);
        $positiveChanges = $this->positiveChanges(
            $positiveEvents,
        );

        $reviewItems = $this->reviewItems(
            $attentionEvents,
        );

        $limitations = $this->limitations(
            $snapshots,
            $auditRuns,
            $events,
        );

        $headline = $this->headline(
            $events,
            $scoreChange,
            $valueChangeRate,
        );

        $summary = $this->summary(
            $periodStart,
            $endingValue,
            $valueChange,
            $valueChangeRate,
            $scoreChange,
            $events,
            $attentionEvents,
        );

        $latestAiInsight = AiInsightRun::query()
            ->where('user_id', $user->id)
            ->whereBetween('generated_at', [
                $periodStart->startOfDay(),
                $periodEnd->endOfDay(),
            ])
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->first();

        $status = $snapshots->isEmpty()
            && $auditRuns->isEmpty()
            && $events->isEmpty()
                ? MonthlyPortfolioReview::STATUS_BLOCKED
                : MonthlyPortfolioReview::STATUS_COMPLETED;

        $review = MonthlyPortfolioReview::query()
            ->where('user_id', $user->id)
            ->whereDate(
                'period_start',
                $periodStart->toDateString(),
            )
            ->whereDate(
                'period_end',
                $periodEnd->toDateString(),
            )
            ->first();

        if ($review === null) {
            $review = new MonthlyPortfolioReview([
                'user_id' => $user->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ]);
        }

        $review->fill([
            'status' => $status,
            'headline' => $headline,
            'summary' => $summary,

            'starting_portfolio_value' => $startingValue,
            'ending_portfolio_value' => $endingValue,
            'portfolio_value_change' => $valueChange,
            'portfolio_value_change_rate' => $valueChangeRate,

            'starting_helm_score' => $startingScore,
            'ending_helm_score' => $endingScore,
            'helm_score_change' => $scoreChange,

            'starting_audit_grade' => $startingAudit?->audit_grade,
            'ending_audit_grade' => $endingAudit?->audit_grade,

            'starting_annual_cost' => $startingCost,
            'ending_annual_cost' => $endingCost,
            'annual_cost_change' => $costChange,

            'event_count' => $events->count(),
            'positive_event_count' => $positiveEvents->count(),
            'attention_event_count' => $attentionEvents->count(),

            'key_changes' => $keyChanges,
            'positive_changes' => $positiveChanges,
            'review_items' => $reviewItems,
            'limitations' => $limitations,

            'data_snapshot' => [
                'portfolio_snapshot_ids' => $snapshots
                    ->pluck('id')
                    ->all(),

                'audit_run_ids' => $auditRuns
                    ->pluck('id')
                    ->all(),

                'timeline_event_ids' => $events
                    ->pluck('id')
                    ->all(),

                'ai_insight_run_id' => $latestAiInsight?->id,
                'latest_ai_headline' => $latestAiInsight?->headline,
            ],

            'generated_at' => now(),
        ]);

        $review->save();

        return $review;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function keyChanges(
        Collection $events,
    ): array {
        return $events
            ->sortBy(
                fn (TimelineEvent $event): int =>
                    $this->severityRank(
                        $event->severity,
                    ),
            )
            ->take(8)
            ->map(
                fn (TimelineEvent $event): array => [
                    'id' => $event->id,
                    'headline' => $event->headline,
                    'summary' => $event->summary,
                    'severity' => $event->severity,
                    'category' => $event->category,
                    'event_date' => $event
                        ->event_date
                        ->toDateString(),
                    'route_name' => $event->route_name,
                    'source_id' => $event->source_id,
                ],
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function positiveChanges(
        Collection $events,
    ): array {
        return $events
            ->take(6)
            ->map(
                fn (TimelineEvent $event): string =>
                    $event->summary
                    ?: $event->headline,
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function reviewItems(
        Collection $events,
    ): array {
        return $events
            ->take(6)
            ->map(
                fn (TimelineEvent $event): array => [
                    'headline' => $event->headline,
                    'summary' => $event->summary,
                    'severity' => $event->severity,
                    'category' => $event->category,
                    'route_name' => $event->route_name,
                    'source_id' => $event->source_id,
                ],
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function limitations(
        Collection $snapshots,
        Collection $auditRuns,
        Collection $events,
    ): array {
        $limitations = collect();

        if ($snapshots->count() < 2) {
            $limitations->push(
                'Fewer than two portfolio snapshots were recorded during this month.',
            );
        }

        if ($auditRuns->count() < 2) {
            $limitations->push(
                'Fewer than two Advisor Audit runs were recorded during this month.',
            );
        }

        if ($events->isEmpty()) {
            $limitations->push(
                'No material timeline events were detected during this month.',
            );
        }

        return $limitations->values()->all();
    }

    private function headline(
        Collection $events,
        ?int $scoreChange,
        ?float $valueChangeRate,
    ): string {
        $priorityEvent = $events
            ->sortBy(
                fn (TimelineEvent $event): int =>
                    $this->severityRank(
                        $event->severity,
                    ),
            )
            ->first();

        if ($priorityEvent !== null) {
            return $priorityEvent->headline;
        }

        if ($scoreChange !== null && $scoreChange > 0) {
            return 'Portfolio oversight scores improved';
        }

        if ($scoreChange !== null && $scoreChange < 0) {
            return 'Portfolio oversight scores declined';
        }

        if (
            $valueChangeRate !== null
            && abs($valueChangeRate) >= 0.01
        ) {
            return $valueChangeRate > 0
                ? 'Portfolio value increased'
                : 'Portfolio value decreased';
        }

        return 'Monthly portfolio review completed';
    }

    private function summary(
        CarbonImmutable $periodStart,
        ?float $endingValue,
        ?float $valueChange,
        ?float $valueChangeRate,
        ?int $scoreChange,
        Collection $events,
        Collection $attentionEvents,
    ): string {
        $parts = collect([
            sprintf(
                'This review summarizes portfolio activity for %s.',
                $periodStart->format('F Y'),
            ),
        ]);

        if ($endingValue !== null) {
            $parts->push(
                sprintf(
                    'The ending recorded portfolio value was $%s.',
                    number_format($endingValue, 2),
                ),
            );
        }

        if ($valueChange !== null) {
            $parts->push(
                sprintf(
                    'Recorded value changed by %s$%s%s.',
                    $valueChange > 0 ? '+' : '-',
                    number_format(abs($valueChange), 2),
                    $valueChangeRate !== null
                        ? sprintf(
                            ' (%s%s%%)',
                            $valueChangeRate > 0
                                ? '+'
                                : '',
                            number_format(
                                $valueChangeRate * 100,
                                2,
                            ),
                        )
                        : '',
                ),
            );
        }

        if ($scoreChange !== null) {
            $parts->push(
                sprintf(
                    'The Advisor Audit score changed by %s%d point%s.',
                    $scoreChange > 0 ? '+' : '',
                    $scoreChange,
                    abs($scoreChange) === 1
                        ? ''
                        : 's',
                ),
            );
        }

        $parts->push(
            sprintf(
                '%d material timeline event%s were recorded, including %d item%s requiring review.',
                $events->count(),
                $events->count() === 1 ? '' : 's',
                $attentionEvents->count(),
                $attentionEvents->count() === 1
                    ? ''
                    : 's',
            ),
        );

        return $parts->implode(' ');
    }

    private function severityRank(
        string $severity,
    ): int {
        return match ($severity) {
            'critical' => 1,
            'high' => 2,
            'medium' => 3,
            'low' => 4,
            'information' => 5,
            'positive' => 6,
            default => 7,
        };
    }
}