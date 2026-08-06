<?php

namespace App\Services\AdvisorAudit;

class AdvisorAuditScoringService
{
    public const FORMULA_VERSION =
        'advisor-audit-score-0.2.0';

    /**
     * Category weights total 100%.
     *
     * Suitability receives the largest single weight because Helmio
     * should judge whether the portfolio fits the investor, not only
     * whether the investments look good in isolation.
     *
     * @var array<string, float>
     */
    private const CATEGORY_WEIGHTS = [
        'cost' => 0.15,
        'diversification' => 0.10,
        'performance' => 0.15,
        'risk' => 0.10,
        'suitability' => 0.20,
        'trading' => 0.15,
        'cash' => 0.05,
        'tax' => 0.10,
    ];

    /**
     * @param array<string, array<string, mixed>> $categories
     * @return array<string, mixed>
     */
    public function calculate(array $categories): array
    {
        $normalizedCategories = [];
        $availableWeight = 0.0;
        $weightedScoreTotal = 0.0;

        foreach (self::CATEGORY_WEIGHTS as $category => $weight) {
            $categoryData = $categories[$category] ?? [];

            $score = $this->normalizeScore(
                $categoryData['score'] ?? null
            );

            $isAvailable = $score !== null;

            if ($isAvailable) {
                $availableWeight += $weight;
                $weightedScoreTotal += $score * $weight;
            }

            $normalizedCategories[$category] = [
                'score' => $score,

                'label' =>
                    $categoryData['label']
                    ?? (
                        $isAvailable
                            ? $this->scoreLabel($score)
                            : 'Insufficient data'
                    ),

                'status' =>
                    $categoryData['status']
                    ?? (
                        $isAvailable
                            ? 'complete'
                            : 'insufficient_data'
                    ),

                'weight' => $weight,
                'available' => $isAvailable,

                'reasons' =>
                    array_values(
                        $categoryData['reasons'] ?? []
                    ),

                'recommendations' =>
                    array_values(
                        $categoryData['recommendations'] ?? []
                    ),

                'metrics' =>
                    $categoryData['metrics'] ?? [],

                'flags' =>
                    array_values(
                        $categoryData['flags'] ?? []
                    ),

                'warnings' =>
                    array_values(
                        $categoryData['warnings'] ?? []
                    ),

                'formula_version' =>
                    $categoryData['formula_version'] ?? null,
            ];
        }

        $availableCategoryCount = collect(
            $normalizedCategories
        )
            ->where('available', true)
            ->count();

        $totalCategoryCount =
            count(self::CATEGORY_WEIGHTS);

        $dataCompleteness =
            $totalCategoryCount > 0
                ? $availableCategoryCount
                    / $totalCategoryCount
                : 0.0;

        /*
         * Reweight available categories so missing categories do not
         * automatically reduce the Advisor Audit score.
         */
        $overallScore =
            $availableWeight > 0
                ? (int) round(
                    (
                        $weightedScoreTotal
                        / $availableWeight
                    ) + 0.000000001,
                    0,
                    PHP_ROUND_HALF_UP
                )
                : null;

        /*
         * Require at least four categories and 50% of configured
         * weighting before presenting an overall score.
         */
        if (
            $availableCategoryCount < 4
            || $availableWeight < 0.50
        ) {
            $overallScore = null;
        }

        return [
            'status' =>
                $overallScore === null
                    ? 'insufficient_data'
                    : 'complete',

            'overall_score' =>
                $overallScore,

            'overall_label' =>
                $overallScore === null
                    ? 'Building advisor audit'
                    : $this->scoreLabel($overallScore),

            'advisor_rating' =>
                $overallScore === null
                    ? null
                    : $this->advisorRating(
                        $overallScore
                    ),

            'data_completeness' =>
                round($dataCompleteness, 8),

            'available_weight' =>
                round($availableWeight, 8),

            'available_category_count' =>
                $availableCategoryCount,

            'total_category_count' =>
                $totalCategoryCount,

            'categories' =>
                $normalizedCategories,

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function normalizeScore(
        mixed $score
    ): ?int {
        if (
            $score === null
            || ! is_numeric($score)
        ) {
            return null;
        }

        return (int) round(
            max(
                0,
                min(
                    100,
                    (float) $score
                )
            )
        );
    }

    private function scoreLabel(
        int $score
    ): string {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Very good',
            $score >= 70 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Needs attention',
            default => 'Action recommended',
        };
    }

    private function advisorRating(
        int $score
    ): string {
        return match (true) {
            $score >= 90 => 'strong',
            $score >= 75 => 'generally_sound',
            $score >= 60 => 'mixed',
            $score >= 40 => 'concerning',
            default => 'high_concern',
        };
    }
}