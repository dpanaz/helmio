<?php

namespace Tests\Unit;

use App\Services\AdvisorAudit\AdvisorAuditScoringService;
use PHPUnit\Framework\TestCase;

class AdvisorAuditScoringServiceTest extends TestCase
{
    private AdvisorAuditScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service =
            new AdvisorAuditScoringService();
    }

    public function test_it_calculates_a_weighted_score(): void
    {
        $result = $this->service->calculate([
            'cost' => [
                'score' => 80,
            ],

            'diversification' => [
                'score' => 70,
            ],

            'performance' => [
                'score' => 60,
            ],

            'risk' => [
                'score' => 50,
            ],

            'suitability' => [
                'score' => 90,
            ],

            'trading' => [
                'score' => 75,
            ],

            'cash' => [
                'score' => 85,
            ],

            'tax' => [
                'score' => 65,
            ],
        ]);

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertSame(
            73,
            $result['overall_score']
        );

        $this->assertSame(
            'Good',
            $result['overall_label']
        );

        $this->assertSame(
            'mixed',
            $result['advisor_rating']
        );

        $this->assertSame(
            8,
            $result['available_category_count']
        );

        $this->assertSame(
            8,
            $result['total_category_count']
        );

        $this->assertEqualsWithDelta(
            1.0,
            $result['data_completeness'],
            0.000001
        );

        $this->assertEqualsWithDelta(
            1.0,
            $result['available_weight'],
            0.000001
        );
    }

    public function test_it_reweights_available_categories(): void
    {
        $result = $this->service->calculate([
            'cost' => [
                'score' => 100,
            ],

            'performance' => [
                'score' => 50,
            ],

            'suitability' => [
                'score' => 75,
            ],

            'trading' => [
                'score' => 75,
            ],
        ]);

        $expected = (int) round(
            (
                (100 * 0.15)
                + (50 * 0.15)
                + (75 * 0.20)
                + (75 * 0.15)
            ) / 0.65,
            0,
            PHP_ROUND_HALF_UP
        );

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertSame(
            $expected,
            $result['overall_score']
        );

        $this->assertEqualsWithDelta(
            0.65,
            $result['available_weight'],
            0.000001
        );
    }

    public function test_it_requires_at_least_four_categories(): void
    {
        $result = $this->service->calculate([
            'cost' => [
                'score' => 90,
            ],

            'performance' => [
                'score' => 90,
            ],

            'suitability' => [
                'score' => 90,
            ],
        ]);

        $this->assertSame(
            'insufficient_data',
            $result['status']
        );

        $this->assertNull(
            $result['overall_score']
        );
    }

    public function test_it_clamps_scores_between_zero_and_one_hundred(): void
    {
        $result = $this->service->calculate([
            'cost' => [
                'score' => 150,
            ],

            'diversification' => [
                'score' => -25,
            ],

            'performance' => [
                'score' => 100,
            ],

            'suitability' => [
                'score' => 100,
            ],
        ]);

        $this->assertSame(
            100,
            $result['categories']['cost']['score']
        );

        $this->assertSame(
            0,
            $result['categories']['diversification']['score']
        );

        $this->assertSame(
            100,
            $result['categories']['performance']['score']
        );

        $this->assertSame(
            100,
            $result['categories']['suitability']['score']
        );
    }

    public function test_it_preserves_reasons_and_recommendations(): void
    {
        $result = $this->service->calculate([
            'cost' => [
                'score' => 80,

                'reasons' => [
                    'Costs are elevated.',
                ],

                'recommendations' => [
                    'Review advisory fees.',
                ],

                'flags' => [
                    [
                        'code' =>
                            'high_investment_cost',
                    ],
                ],

                'warnings' => [
                    [
                        'code' =>
                            'limited_data',
                    ],
                ],

                'formula_version' =>
                    'cost-test-1.0.0',
            ],
        ]);

        $category =
            $result['categories']['cost'];

        $this->assertSame(
            [
                'Costs are elevated.',
            ],
            $category['reasons']
        );

        $this->assertSame(
            [
                'Review advisory fees.',
            ],
            $category['recommendations']
        );

        $this->assertSame(
            'high_investment_cost',
            $category['flags'][0]['code']
        );

        $this->assertSame(
            'limited_data',
            $category['warnings'][0]['code']
        );

        $this->assertSame(
            'cost-test-1.0.0',
            $category['formula_version']
        );
    }

    public function test_it_reports_data_completeness(): void
    {
        $result = $this->service->calculate([
            'cost' => [
                'score' => 80,
            ],

            'performance' => [
                'score' => 80,
            ],

            'risk' => [
                'score' => 80,
            ],

            'suitability' => [
                'score' => 80,
            ],
        ]);

        $this->assertEqualsWithDelta(
            4 / 8,
            $result['data_completeness'],
            0.000001
        );

        $this->assertSame(
            4,
            $result['available_category_count']
        );

        $this->assertSame(
            8,
            $result['total_category_count']
        );
    }
}