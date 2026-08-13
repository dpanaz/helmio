<?php

namespace App\Services\AdvisorAudit;

class AdvisorAuditScoringService
{
    public const FORMULA_VERSION =
        'advisor-audit-score-0.3.0';

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

    private const MINIMUM_AVAILABLE_CATEGORY_COUNT = 4;
    private const MINIMUM_AVAILABLE_WEIGHT = 0.50;
    private const COMPLETE_AVAILABLE_WEIGHT = 0.80;

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

                /*
                 * Preserve the category-specific readiness explanation so
                 * the Advisor Audit Blade can display meaningful messages
                 * such as "building risk history" instead of a generic
                 * "more data is required."
                 */
                'message' =>
                    $categoryData['message'] ?? null,

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
         * Reweight available categories so unavailable categories do not
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

        $meetsMinimumCoverage =
            $availableCategoryCount
                >= self::MINIMUM_AVAILABLE_CATEGORY_COUNT
            && $availableWeight
                >= self::MINIMUM_AVAILABLE_WEIGHT;

        if (! $meetsMinimumCoverage) {
            $overallScore = null;
        }

        $status = match (true) {
            $overallScore === null =>
                'insufficient_data',

            $availableWeight
                < self::COMPLETE_AVAILABLE_WEIGHT =>
                    'provisional',

            default =>
                'complete',
        };

        $confidenceLevel = match ($status) {
            'complete' => 'established',
            'provisional' => 'provisional',
            default => 'insufficient',
        };

        return [
            'status' =>
                $status,

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

            'confidence' => [
                'level' =>
                    $confidenceLevel,

                'is_provisional' =>
                    $status === 'provisional',

                'is_complete' =>
                    $status === 'complete',

                'minimum_available_category_count' =>
                    self::MINIMUM_AVAILABLE_CATEGORY_COUNT,

                'minimum_available_weight' =>
                    self::MINIMUM_AVAILABLE_WEIGHT,

                'complete_available_weight' =>
                    self::COMPLETE_AVAILABLE_WEIGHT,

                'available_category_count' =>
                    $availableCategoryCount,

                'total_category_count' =>
                    $totalCategoryCount,

                'available_weight' =>
                    round($availableWeight, 8),

                'data_completeness' =>
                    round($dataCompleteness, 8),
            ],

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