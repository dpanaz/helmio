<?php

namespace Tests\Unit;

use App\Services\AdvisorAudit\AdvisorAuditFindingBuilder;
use PHPUnit\Framework\TestCase;

class AdvisorAuditFindingBuilderTest extends TestCase
{
    private AdvisorAuditFindingBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder =
            new AdvisorAuditFindingBuilder();
    }

    public function test_it_ranks_high_severity_findings_first(): void
    {
        $result = $this->builder->build([
            'trading' => [
                'score' => 35,

                'flags' => [
                    [
                        'code' =>
                            'possible_churning_pattern',

                        'severity' => 'high',

                        'title' =>
                            'Possible churning',

                        'message' =>
                            'Repeated trading generated $12,000 in costs.',
                    ],
                ],
            ],

            'cash' => [
                'score' => 80,

                'flags' => [
                    [
                        'code' =>
                            'elevated_average_cash',

                        'severity' =>
                            'moderate',

                        'title' =>
                            'Elevated cash',

                        'message' =>
                            'Average cash was elevated.',
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            'possible_churning_pattern',
            $result['all'][0]['code']
        );
    }

    public function test_it_separates_opportunities_and_concerns(): void
    {
        $result = $this->builder->build([
            'performance' => [
                'score' => 90,

                'flags' => [
                    [
                        'code' =>
                            'positive_alpha',

                        'severity' =>
                            'informational',

                        'title' =>
                            'Positive alpha',

                        'message' =>
                            'Portfolio outperformed its benchmark.',
                    ],
                ],
            ],

            'risk' => [
                'score' => 45,

                'flags' => [
                    [
                        'code' =>
                            'high_volatility',

                        'severity' => 'high',

                        'title' =>
                            'High volatility',

                        'message' =>
                            'Portfolio volatility was elevated.',
                    ],
                ],
            ],
        ]);

        $this->assertCount(
            1,
            $result['opportunities']
        );

        $this->assertCount(
            1,
            $result['important']
        );
    }

    public function test_it_extracts_financial_impact(): void
    {
        $result = $this->builder->build([
            'performance' => [
                'score' => 40,

                'flags' => [
                    [
                        'code' =>
                            'meaningful_performance_opportunity_cost',

                        'severity' =>
                            'high',

                        'title' =>
                            'Missed growth',

                        'message' =>
                            'Benchmark underperformance may represent approximately $18,500.00 in missed growth.',
                    ],
                ],
            ],
        ]);

        $this->assertEqualsWithDelta(
            18500,
            $result['all'][0][
                'financial_impact'
            ],
            0.01
        );
    }

    public function test_it_deduplicates_identical_messages(): void
    {
        $result = $this->builder->build([
            'risk' => [
                'score' => 50,

                'flags' => [
                    [
                        'code' =>
                            'high_volatility',

                        'severity' => 'high',

                        'title' =>
                            'High volatility',

                        'message' =>
                            'Portfolio risk is elevated.',
                    ],
                ],

                'reasons' => [
                    'Portfolio risk is elevated.',
                ],
            ],
        ]);

        $this->assertSame(
            1,
            collect($result['all'])
                ->where(
                    'message',
                    'Portfolio risk is elevated.'
                )
                ->count()
        );
    }

    public function test_it_returns_summary_counts(): void
    {
        $result = $this->builder->build([
            'risk' => [
                'score' => 30,

                'flags' => [
                    [
                        'code' =>
                            'severe_drawdown',

                        'severity' =>
                            'critical',

                        'title' =>
                            'Severe drawdown',

                        'message' =>
                            'A severe drawdown was detected.',
                    ],
                ],

                'recommendations' => [
                    'Review portfolio risk.',
                ],
            ],
        ]);

        $this->assertSame(
            1,
            $result['summary'][
                'critical_count'
            ]
        );

        $this->assertSame(
            1,
            $result['summary'][
                'recommendation_count'
            ]
        );
    }
}