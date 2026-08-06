<?php

namespace App\Services\Audit;

use App\Models\AuditRun;
use Illuminate\Support\Collection;

class AuditHistoryComparisonService
{
    /**
     * Compare the current audit run with the previous audit run.
     *
     * @return array<string, mixed>
     */
    public function compare(
        AuditRun $current,
        ?AuditRun $previous,
    ): array {
        if ($previous === null) {
            return [
                'has_previous' =>
                    false,

                'score_change' =>
                    null,

                'grade_change' =>
                    null,

                'portfolio_value_change' =>
                    null,

                'annual_cost_change' =>
                    null,

                'potential_savings_change' =>
                    null,

                'category_changes' =>
                    collect(),

                'new_findings' =>
                    collect(),

                'resolved_findings' =>
                    collect(),

                'improved_findings' =>
                    collect(),

                'worsened_findings' =>
                    collect(),

                'unchanged_findings' =>
                    collect(),
            ];
        }

        $current->loadMissing('findings');
        $previous->loadMissing('findings');

        $currentByFingerprint =
            $current->findings->keyBy(
                'fingerprint'
            );

        $previousByFingerprint =
            $previous->findings->keyBy(
                'fingerprint'
            );

        $newFindings =
            $currentByFingerprint
                ->filter(
                    fn (
                        $finding,
                        string $fingerprint
                    ): bool =>
                        ! $previousByFingerprint
                            ->has($fingerprint)
                        && $finding->status
                            !== 'resolved'
                )
                ->values();

        $resolvedFindings =
            $previousByFingerprint
                ->filter(
                    function (
                        $finding,
                        string $fingerprint,
                    ) use (
                        $currentByFingerprint
                    ): bool {
                        $currentFinding =
                            $currentByFingerprint
                                ->get(
                                    $fingerprint
                                );

                        return $currentFinding
                            === null
                            || $currentFinding
                                ->status
                                === 'resolved';
                    }
                )
                ->values();

        $improved =
            collect();

        $worsened =
            collect();

        $unchanged =
            collect();

        foreach (
            $currentByFingerprint
                as $fingerprint => $finding
        ) {
            $previousFinding =
                $previousByFingerprint
                    ->get($fingerprint);

            if ($previousFinding === null) {
                continue;
            }

            $currentRank =
                $this->severityRank(
                    $finding->severity
                );

            $previousRank =
                $this->severityRank(
                    $previousFinding->severity
                );

            /*
             * A larger rank means the severity became less serious.
             *
             * Example:
             * critical = 1
             * high = 2
             * medium = 3
             */
            if ($currentRank > $previousRank) {
                $improved->push($finding);
            } elseif (
                $currentRank < $previousRank
            ) {
                $worsened->push($finding);
            } else {
                $unchanged->push($finding);
            }
        }

        return [
            'has_previous' =>
                true,

            'score_change' => (
                $current->audit_score !== null
                && $previous->audit_score !== null
            )
                ? $current->audit_score
                    - $previous->audit_score
                : null,

            'grade_change' =>
                $current->audit_grade
                !== $previous->audit_grade,

            'portfolio_value_change' =>
                (float) $current
                    ->portfolio_value
                - (float) $previous
                    ->portfolio_value,

            'annual_cost_change' =>
                (float) $current
                    ->annual_cost
                - (float) $previous
                    ->annual_cost,

            'potential_savings_change' =>
                (float) $current
                    ->potential_savings
                - (float) $previous
                    ->potential_savings,

            'category_changes' =>
                $this->categoryChanges(
                    $current,
                    $previous,
                ),

            'new_findings' =>
                $newFindings,

            'resolved_findings' =>
                $resolvedFindings,

            'improved_findings' =>
                $improved,

            'worsened_findings' =>
                $worsened,

            'unchanged_findings' =>
                $unchanged,
        ];
    }

    /**
     * Compare category scores across audit versions.
     *
     * Supports both formats:
     *
     * Legacy:
     * [
     *     'risk' => [
     *         'score' => 75,
     *     ],
     * ]
     *
     * Current:
     * [
     *     'risk' => 75,
     * ]
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function categoryChanges(
        AuditRun $current,
        AuditRun $previous,
    ): Collection {
        $currentScores = collect(
            $current->category_scores
            ?? []
        );

        $previousScores = collect(
            $previous->category_scores
            ?? []
        );

        $categoryKeys = $currentScores
            ->keys()
            ->merge(
                $previousScores->keys()
            )
            ->unique()
            ->values();

        return $categoryKeys
            ->map(
                function (
                    mixed $key
                ) use (
                    $currentScores,
                    $previousScores,
                ): array {
                    $category =
                        (string) $key;

                    $currentCategory =
                        $currentScores->get(
                            $category
                        );

                    $previousCategory =
                        $previousScores->get(
                            $category
                        );

                    $currentScore =
                        $this->categoryScore(
                            $currentCategory
                        );

                    $previousScore =
                        $this->categoryScore(
                            $previousCategory
                        );

                    $change = (
                        $currentScore !== null
                        && $previousScore !== null
                    )
                        ? $currentScore
                            - $previousScore
                        : null;

                    return [
                        'category' =>
                            $category,

                        'label' =>
                            $this->categoryLabel(
                                $category
                            ),

                        'current_score' =>
                            $currentScore,

                        'previous_score' =>
                            $previousScore,

                        'change' =>
                            $change,

                        'direction' =>
                            $this->changeDirection(
                                $change
                            ),
                    ];
                }
            )
            ->values();
    }

    /**
     * Accept a scalar score or a legacy category array.
     */
    private function categoryScore(
        mixed $category
    ): ?int {
        if (is_numeric($category)) {
            return (int) round(
                (float) $category
            );
        }

        if (! is_array($category)) {
            return null;
        }

        $score =
            $category['score']
            ?? $category['overall_score']
            ?? null;

        if (! is_numeric($score)) {
            return null;
        }

        return (int) round(
            (float) $score
        );
    }

    private function changeDirection(
        ?int $change
    ): string {
        return match (true) {
            $change === null =>
                'unavailable',

            $change > 0 =>
                'improved',

            $change < 0 =>
                'declined',

            default =>
                'unchanged',
        };
    }

    private function categoryLabel(
        string $category
    ): string {
        return match ($category) {
            'cost' =>
                'Cost',

            'diversification' =>
                'Diversification',

            'performance' =>
                'Performance',

            'risk' =>
                'Risk',

            'trading' =>
                'Trading Discipline',

            'cash' =>
                'Cash Drag',

            'tax' =>
                'Tax Efficiency',

            default =>
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $category
                    )
                ),
        };
    }

    private function severityRank(
        string $severity,
    ): int {
        return match ($severity) {
            'critical' =>
                1,

            'high' =>
                2,

            'medium',
            'moderate' =>
                3,

            'low' =>
                4,

            'information',
            'informational' =>
                5,

            'positive' =>
                6,

            default =>
                5,
        };
    }
}