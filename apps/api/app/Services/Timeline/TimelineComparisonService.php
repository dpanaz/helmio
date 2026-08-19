<?php

namespace App\Services\Timeline;

use Illuminate\Support\Facades\DB;

use App\Enums\TimelineEventType;
use App\Models\AuditRun;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Support\Collection;

class TimelineComparisonService
{
    /**
     * Compare all consecutive recorded audit runs and persist events.
     *
     * @return Collection<int, TimelineEvent>
     */
    public function compare(User $user): Collection
    {
        $runs = AuditRun::query()
            ->from(
                DB::raw(
                    'audit_runs FORCE INDEX (audit_runs_user_date_id_index)'
                )
            )
            ->where('user_id', $user->id)
            ->orderBy('calculated_for_date')
            ->orderBy('id')
            ->get();

        if ($runs->count() < 2) {
            return collect();
        }

        $events = collect();

        for ($index = 1; $index < $runs->count(); $index++) {
            $previous = $runs[$index - 1];
            $current = $runs[$index];

            $events->push(
                ...$this->compareRuns(
                    $user,
                    $previous,
                    $current,
                ),
            );
        }

        return $events;
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    public function compareLatest(User $user): Collection
    {
        $runs = AuditRun::query()
            ->where('user_id', $user->id)
            ->orderByDesc('calculated_for_date')
            ->orderByDesc('id')
            ->limit(2)
            ->get();

        $current = $runs->first();
        $previous = $runs->skip(1)->first();

        if ($current === null || $previous === null) {
            return collect();
        }

        return $this->compareRuns(
            $user,
            $previous,
            $current,
        );
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    private function compareRuns(
        User $user,
        AuditRun $previous,
        AuditRun $current,
    ): Collection {
        return collect()
            ->merge(
                $this->scoreEvents(
                    $user,
                    $previous,
                    $current,
                ),
            )
            ->merge(
                $this->auditGradeEvents(
                    $user,
                    $previous,
                    $current,
                ),
            )
            ->merge(
                $this->portfolioValueEvents(
                    $user,
                    $previous,
                    $current,
                ),
            )
            ->merge(
                $this->costEvents(
                    $user,
                    $previous,
                    $current,
                ),
            )
            ->merge(
                $this->potentialSavingsEvents(
                    $user,
                    $previous,
                    $current,
                ),
            )
            ->merge(
                $this->categoryScoreEvents(
                    $user,
                    $previous,
                    $current,
                ),
            );
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    private function scoreEvents(
        User $user,
        AuditRun $previous,
        AuditRun $current,
    ): Collection {
        if (
            $previous->audit_score === null
            || $current->audit_score === null
            || $previous->audit_score === $current->audit_score
        ) {
            return collect();
        }

        $change =
            $current->audit_score
            - $previous->audit_score;

        $improved = $change > 0;

        return collect([
            $this->storeEvent(
                user: $user,
                current: $current,
                type: $improved
                    ? TimelineEventType::HelmScoreImproved
                    : TimelineEventType::HelmScoreDeclined,
                category: 'overall',
                severity: $improved
                    ? 'positive'
                    : $this->declineSeverity(abs($change)),
                headline: $improved
                    ? 'Advisor Audit score improved'
                    : 'Advisor Audit score declined',
                summary: sprintf(
                    'The Advisor Audit score changed from %d to %d, a %s%d-point move.',
                    $previous->audit_score,
                    $current->audit_score,
                    $change > 0 ? '+' : '',
                    $change,
                ),
                before: [
                    'score' => $previous->audit_score,
                ],
                after: [
                    'score' => $current->audit_score,
                ],
                metrics: [
                    'change' => $change,
                ],
                routeName: 'advisor-audit.history.show',
                suffix: 'audit-score',
            ),
        ]);
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    private function auditGradeEvents(
        User $user,
        AuditRun $previous,
        AuditRun $current,
    ): Collection {
        if (
            empty($previous->audit_grade)
            || empty($current->audit_grade)
            || $previous->audit_grade === $current->audit_grade
        ) {
            return collect();
        }

        $previousRank = $this->gradeRank(
            $previous->audit_grade,
        );

        $currentRank = $this->gradeRank(
            $current->audit_grade,
        );

        $improved = $currentRank > $previousRank;

        return collect([
            $this->storeEvent(
                user: $user,
                current: $current,
                type: $improved
                    ? TimelineEventType::AuditGradeImproved
                    : TimelineEventType::AuditGradeDeclined,
                category: 'audit',
                severity: $improved
                    ? 'positive'
                    : 'high',
                headline: $improved
                    ? 'Advisor Audit grade improved'
                    : 'Advisor Audit grade declined',
                summary: sprintf(
                    'The Advisor Audit grade changed from %s to %s.',
                    $previous->audit_grade,
                    $current->audit_grade,
                ),
                before: [
                    'grade' => $previous->audit_grade,
                ],
                after: [
                    'grade' => $current->audit_grade,
                ],
                metrics: [],
                routeName: 'advisor-audit.history.show',
                suffix: 'audit-grade',
            ),
        ]);
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    private function portfolioValueEvents(
        User $user,
        AuditRun $previous,
        AuditRun $current,
    ): Collection {
        $before = (float) $previous->portfolio_value;
        $after = (float) $current->portfolio_value;
        $change = $after - $before;

        if (abs($change) < 0.01) {
            return collect();
        }

        $percentageChange = $before > 0
            ? $change / $before
            : null;

        /*
         * Ignore tiny market-value fluctuations.
         */
        if (
            $percentageChange !== null
            && abs($percentageChange) < 0.01
        ) {
            return collect();
        }

        $increased = $change > 0;

        return collect([
            $this->storeEvent(
                user: $user,
                current: $current,
                type: $increased
                    ? TimelineEventType::PortfolioValueIncrease
                    : TimelineEventType::PortfolioValueDecrease,
                category: 'portfolio',
                severity: 'information',
                headline: $increased
                    ? 'Portfolio value increased'
                    : 'Portfolio value decreased',
                summary: sprintf(
                    'Recorded portfolio value changed from $%s to $%s.',
                    number_format($before, 2),
                    number_format($after, 2),
                ),
                before: [
                    'portfolio_value' => $before,
                ],
                after: [
                    'portfolio_value' => $after,
                ],
                metrics: [
                    'change' => round($change, 2),
                    'percentage_change' =>
                        $percentageChange !== null
                            ? round(
                                $percentageChange,
                                6,
                            )
                            : null,
                ],
                routeName: 'dashboard',
                suffix: 'portfolio-value',
            ),
        ]);
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    private function costEvents(
        User $user,
        AuditRun $previous,
        AuditRun $current,
    ): Collection {
        $before = (float) $previous->annual_cost;
        $after = (float) $current->annual_cost;
        $change = $after - $before;

        if (abs($change) < 1) {
            return collect();
        }

        $increased = $change > 0;

        return collect([
            $this->storeEvent(
                user: $user,
                current: $current,
                type: $increased
                    ? TimelineEventType::CostIncrease
                    : TimelineEventType::CostDecrease,
                category: 'cost',
                severity: $increased
                    ? $this->costSeverity($change)
                    : 'positive',
                headline: $increased
                    ? 'Estimated annual costs increased'
                    : 'Estimated annual costs decreased',
                summary: sprintf(
                    'Estimated annual portfolio costs changed from $%s to $%s.',
                    number_format($before, 2),
                    number_format($after, 2),
                ),
                before: [
                    'annual_cost' => $before,
                ],
                after: [
                    'annual_cost' => $after,
                ],
                metrics: [
                    'change' => round($change, 2),
                ],
                routeName: 'analytics.costs',
                suffix: 'annual-cost',
            ),
        ]);
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    private function potentialSavingsEvents(
        User $user,
        AuditRun $previous,
        AuditRun $current,
    ): Collection {
        $before =
            (float) $previous->potential_savings;

        $after =
            (float) $current->potential_savings;

        $change = $after - $before;

        if (abs($change) < 1) {
            return collect();
        }

        $increased = $change > 0;

        return collect([
            $this->storeEvent(
                user: $user,
                current: $current,
                type: $increased
                    ? TimelineEventType::PotentialSavingsIncrease
                    : TimelineEventType::PotentialSavingsDecrease,
                category: 'cost',
                severity: $increased
                    ? 'medium'
                    : 'positive',
                headline: $increased
                    ? 'Potential savings opportunity increased'
                    : 'Potential savings opportunity decreased',
                summary: sprintf(
                    'Estimated potential savings changed from $%s to $%s.',
                    number_format($before, 2),
                    number_format($after, 2),
                ),
                before: [
                    'potential_savings' => $before,
                ],
                after: [
                    'potential_savings' => $after,
                ],
                metrics: [
                    'change' => round($change, 2),
                ],
                routeName: 'analytics.fund-expenses',
                suffix: 'potential-savings',
            ),
        ]);
    }

    /**
     * @return Collection<int, TimelineEvent>
     */
    private function categoryScoreEvents(
    User $user,
    AuditRun $previous,
    AuditRun $current,
): Collection {
    $previousScores = collect(
        $previous->category_scores ?? [],
    );

    $currentScores = collect(
        $current->category_scores ?? [],
    );

    /*
     * Only these keys represent Advisor Audit categories.
     *
     * category_scores may also contain scalar metadata such as
     * overall_score, completeness, or other summary values.
     * Those values must not be passed into the category callback.
     */
    $validCategories = [
        'cost',
        'diversification',
        'performance',
        'risk',
        'trading',
        'cash',
        'tax',
        'suitability',
    ];

    return $currentScores
        ->filter(
            function (
                mixed $category,
                mixed $key,
            ) use (
                $validCategories,
            ): bool {
                return is_string($key)
                    && in_array(
                        $key,
                        $validCategories,
                        true,
                    )
                    && is_array($category);
            },
        )
        ->map(
            function (
                array $category,
                string $key,
            ) use (
                $user,
                $previous,
                $current,
                $previousScores,
            ): ?TimelineEvent {
                $previousCategory =
                    $previousScores->get(
                        $key,
                    );

                /*
                 * The previous audit may have been generated using
                 * an older payload structure. Ignore the comparison
                 * if the prior category is not an array.
                 */
                if (
                    ! is_array(
                        $previousCategory,
                    )
                ) {
                    return null;
                }

                $before = data_get(
                    $previousCategory,
                    'score',
                );

                $after = data_get(
                    $category,
                    'score',
                );

                if (
                    $before === null
                    || $after === null
                    || ! is_numeric($before)
                    || ! is_numeric($after)
                    || (int) $before === (int) $after
                ) {
                    return null;
                }

                $before =
                    (int) $before;

                $after =
                    (int) $after;

                $change =
                    $after - $before;

                /*
                 * Avoid filling the timeline with negligible changes.
                 */
                if (
                    abs($change) < 3
                ) {
                    return null;
                }

                $improved =
                    $change > 0;

                return $this->storeEvent(
                    user: $user,

                    current: $current,

                    type: $improved
                        ? TimelineEventType::CategoryScoreImproved
                        : TimelineEventType::CategoryScoreDeclined,

                    category: $key,

                    severity: $improved
                        ? 'positive'
                        : $this->declineSeverity(
                            abs($change),
                        ),

                    headline: sprintf(
                        '%s score %s',
                        str($key)
                            ->replace(
                                '_',
                                ' ',
                            )
                            ->title(),
                        $improved
                            ? 'improved'
                            : 'declined',
                    ),

                    summary: sprintf(
                        'The %s score changed from %d to %d.',
                        str($key)
                            ->replace(
                                '_',
                                ' ',
                            )
                            ->lower(),
                        $before,
                        $after,
                    ),

                    before: [
                        'score' =>
                            $before,
                    ],

                    after: [
                        'score' =>
                            $after,
                    ],

                    metrics: [
                        'change' =>
                            $change,
                    ],

                    routeName:
                        $this->categoryRoute(
                            $key,
                        ),

                    suffix:
                        'category-'.$key,
                );
            },
        )
        ->filter()
        ->values();
}

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @param array<string, mixed> $metrics
     */
    private function storeEvent(
        User $user,
        AuditRun $current,
        TimelineEventType $type,
        string $category,
        string $severity,
        string $headline,
        string $summary,
        array $before,
        array $after,
        array $metrics,
        ?string $routeName,
        string $suffix,
    ): TimelineEvent {
        $fingerprint = hash(
            'sha256',
            implode('|', [
                $user->id,
                'audit-run',
                $current->id,
                $type->value,
                $suffix,
            ]),
        );

        return TimelineEvent::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'fingerprint' => $fingerprint,
            ],
            [
                'event_date' =>
                    $current->calculated_for_date,

                'detected_at' => now(),
                'type' => $type->value,
                'category' => $category,
                'severity' => $severity,
                'headline' => $headline,
                'summary' => $summary,
                'before' => $before,
                'after' => $after,
                'metrics' => $metrics,

                'source_type' => AuditRun::class,
                'source_id' => $current->id,
                'route_name' => $routeName,

                'metadata' => [
                    'previous_audit_run_id' =>
                        data_get(
                            $current,
                            'previous_audit_run_id',
                        ),

                    'formula_version' =>
                        $current->formula_version,
                ],
            ],
        );
    }

    private function gradeRank(string $grade): int
    {
        return match (strtoupper($grade)) {
            'A+' => 13,
            'A' => 12,
            'A-' => 11,
            'B+' => 10,
            'B' => 9,
            'B-' => 8,
            'C+' => 7,
            'C' => 6,
            'C-' => 5,
            'D+' => 4,
            'D' => 3,
            'D-' => 2,
            'F' => 1,
            default => 0,
        };
    }

    private function declineSeverity(int $change): string
    {
        return match (true) {
            $change >= 20 => 'critical',
            $change >= 10 => 'high',
            $change >= 5 => 'medium',
            default => 'low',
        };
    }

    private function costSeverity(float $increase): string
    {
        return match (true) {
            $increase >= 5000 => 'critical',
            $increase >= 2000 => 'high',
            $increase >= 500 => 'medium',
            default => 'low',
        };
    }

    private function categoryRoute(
        string $category,
    ): ?string {
        return match ($category) {
            'cost' => 'analytics.costs',
            'diversification' =>
                'analytics.diversification',
            'performance' =>
                'analytics.performance',
            'risk' => 'analytics.risk',
            'trading' =>
                'analytics.trading-discipline',
            'tax' =>
                'analytics.tax-efficiency',
            default => null,
        };
    }
}