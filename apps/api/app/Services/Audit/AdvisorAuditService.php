<?php

namespace App\Services\Audit;

use App\Models\InvestmentAccount;
use App\Services\Analytics\HelmScoreService;
use Illuminate\Support\Collection;

class AdvisorAuditService
{
    public const FORMULA_VERSION = 'advisor-audit-0.1.0';

    public function __construct(
        private readonly HelmScoreService $helmScoreService,
    ) {
    }

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function build(Collection $accounts): array
    {
        $helm = $this->helmScoreService->calculate($accounts);

        $categories = collect($helm['categories']);

        $numericScores = $categories
            ->pluck('score')
            ->filter(
                fn ($score): bool => $score !== null,
            )
            ->map(
                fn ($score): int => (int) $score,
            );

        $auditScore = $numericScores->isNotEmpty()
            ? (int) round($numericScores->average())
            : null;

        $findings = $this->buildFindings($helm);

        $reviewFindings = $findings
            ->whereIn('severity', [
                'critical',
                'high',
                'medium',
            ])
            ->values();

        $positiveFindings = $findings
            ->where('severity', 'positive')
            ->values();

        $annualCost = (float) (
            $helm['cost_analytics']['total_annual_cost']
            ?? 0
        );

        $potentialSavings = (float) (
            $helm['fund_analytics']['estimated_savings']
            ?? 0
        );

        return [
            'audit_score' => $auditScore,
            'audit_grade' => $this->grade($auditScore),
            'audit_label' => $this->label($auditScore),

            'portfolio_value' => round(
                (float) $accounts->sum('current_value'),
                2,
            ),

            'annual_cost' => round($annualCost, 2),
            'potential_savings' => round($potentialSavings, 2),

            'issue_count' => $reviewFindings->count(),

            'critical_count' => $reviewFindings
                ->where('severity', 'critical')
                ->count(),

            'high_count' => $reviewFindings
                ->where('severity', 'high')
                ->count(),

            'medium_count' => $reviewFindings
                ->where('severity', 'medium')
                ->count(),

            'positive_count' => $positiveFindings->count(),

            'review_recommended' =>
                $reviewFindings->isNotEmpty(),

            'findings' => $findings,
            'review_findings' => $reviewFindings,
            'positive_findings' => $positiveFindings,

            'category_scores' => $categories,
            'helm_score' => $helm,

            'formula_version' => self::FORMULA_VERSION,
            'calculated_for_date' => now()->toDateString(),
        ];
    }

    /**
     * @param array<string, mixed> $helm
     * @return Collection<int, array<string, mixed>>
     */
    private function buildFindings(array $helm): Collection
    {
        $findings = collect();

        foreach ($helm['categories'] as $categoryKey => $category) {
            $score = $category['score'];

            if ($score === null) {
                $findings->push([
                    'category' => $categoryKey,
                    'title' => $this->categoryTitle($categoryKey)
                        .' data is incomplete',
                    'description' =>
                        $category['reasons'][0]
                        ?? 'Additional data is required.',
                    'recommendation' =>
                        $category['recommendations'][0]
                        ?? null,
                    'severity' => 'information',
                    'score' => null,
                    'route' => $this->categoryRoute($categoryKey),
                ]);

                continue;
            }

            $severity = $this->severityFromScore(
                (int) $score,
            );

            $reasons = collect(
                $category['reasons'] ?? [],
            );

            if ($reasons->isEmpty()) {
                $reasons->push(
                    'No material concerns were identified.',
                );
            }

            foreach ($reasons as $index => $reason) {
                $findings->push([
                    'category' => $categoryKey,
                    'title' => $index === 0
                        ? $this->findingTitle(
                            $categoryKey,
                            (int) $score,
                        )
                        : $this->categoryTitle($categoryKey)
                            .' observation',

                    'description' => $reason,

                    'recommendation' =>
                        $category['recommendations'][$index]
                        ?? $category['recommendations'][0]
                        ?? null,

                    'severity' => $severity,
                    'score' => (int) $score,
                    'route' =>
                        $this->categoryRoute($categoryKey),
                ]);
            }
        }

        $potentialSavings = (float) (
            $helm['fund_analytics']['estimated_savings']
            ?? 0
        );

        if ($potentialSavings > 0) {
            $findings->push([
                'category' => 'cost',
                'title' => 'Potential lower-cost alternatives',
                'description' => sprintf(
                    'Current comparison data indicates approximately $%s in potential annual savings.',
                    number_format($potentialSavings, 2),
                ),
                'recommendation' =>
                    'Review lower-cost candidates alongside tax consequences, risk, availability and investment objectives.',
                'severity' => $potentialSavings >= 1000
                    ? 'high'
                    : 'medium',
                'score' =>
                    $helm['categories']['cost']['score']
                    ?? null,
                'route' => 'analytics.fund-expenses',
            ]);
        }

        return $findings
            ->sortBy(
                fn (array $finding): int =>
                    $this->severityRank(
                        $finding['severity'],
                    ),
            )
            ->values();
    }

    private function findingTitle(
        string $category,
        int $score,
    ): string {
        $categoryTitle = $this->categoryTitle($category);

        return match (true) {
            $score < 40 =>
                $categoryTitle.' requires immediate review',

            $score < 60 =>
                $categoryTitle.' needs attention',

            $score < 80 =>
                $categoryTitle.' has review opportunities',

            default =>
                $categoryTitle.' appears healthy',
        };
    }

    private function severityFromScore(
        int $score,
    ): string {
        return match (true) {
            $score < 25 => 'critical',
            $score < 40 => 'high',
            $score < 60 => 'medium',
            $score < 80 => 'low',
            default => 'positive',
        };
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

    private function categoryTitle(
        string $category,
    ): string {
        return match ($category) {
            'cost' => 'Costs',
            'diversification' => 'Diversification',
            'performance' => 'Performance',
            'risk' => 'Risk',
            'trading' => 'Trading discipline',
            'tax' => 'Tax efficiency',
            default => str($category)
                ->replace('_', ' ')
                ->title()
                ->toString(),
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

    private function grade(
        ?int $score,
    ): string {
        if ($score === null) {
            return '—';
        }

        return match (true) {
            $score >= 97 => 'A+',
            $score >= 93 => 'A',
            $score >= 90 => 'A−',
            $score >= 87 => 'B+',
            $score >= 83 => 'B',
            $score >= 80 => 'B−',
            $score >= 77 => 'C+',
            $score >= 73 => 'C',
            $score >= 70 => 'C−',
            $score >= 67 => 'D+',
            $score >= 63 => 'D',
            $score >= 60 => 'D−',
            default => 'F',
        };
    }

    private function label(
        ?int $score,
    ): string {
        if ($score === null) {
            return 'Insufficient data';
        }

        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Strong',
            $score >= 70 => 'Generally sound',
            $score >= 60 => 'Review suggested',
            $score >= 40 => 'Needs attention',
            default => 'Priority review recommended',
        };
    }
}
