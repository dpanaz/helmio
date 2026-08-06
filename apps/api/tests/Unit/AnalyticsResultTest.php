<?php

namespace Tests\Unit;

use App\Data\Analytics\AnalyticsResult;
use PHPUnit\Framework\TestCase;

class AnalyticsResultTest extends TestCase
{
    public function test_it_creates_a_complete_result(): void
    {
        $result = AnalyticsResult::complete(
            metrics: [
                'turnover_rate' => 0.45,
            ],
            flags: [
                [
                    'code' => 'example_flag',
                    'severity' => 'moderate',
                ],
            ],
            formulaVersion: 'test-1.0.0',
        );

        $this->assertTrue(
            $result->isComplete()
        );

        $this->assertSame(
            'complete',
            $result->status
        );

        $this->assertSame(
            0.45,
            $result->metrics['turnover_rate']
        );

        $this->assertSame(
            'test-1.0.0',
            $result->formulaVersion
        );
    }

    public function test_it_creates_an_insufficient_data_result(): void
    {
        $result = AnalyticsResult::insufficientData(
            message: 'More history is required.',
            formulaVersion: 'test-1.0.0',
        );

        $this->assertFalse(
            $result->isComplete()
        );

        $this->assertSame(
            'insufficient_data',
            $result->status
        );

        $this->assertSame(
            'More history is required.',
            $result->message
        );

        $this->assertNull(
            $result->score
        );
    }

    public function test_it_clamps_scores(): void
    {
        $result = AnalyticsResult::complete()
            ->withScore(150);

        $this->assertSame(
            100,
            $result->score
        );

        $this->assertSame(
            'Excellent',
            $result->label
        );
    }

    public function test_it_converts_to_the_shared_array_shape(): void
    {
        $result = AnalyticsResult::complete(
            metrics: [
                'value' => 100,
            ],
            score: 85,
            label: 'Very good',
            formulaVersion: 'test-1.0.0',
        );

        $array = $result->toArray();

        $this->assertSame(
            [
                'status',
                'message',
                'score',
                'label',
                'metrics',
                'flags',
                'warnings',
                'data',
                'formula_version',
            ],
            array_keys($array)
        );

        $this->assertSame(
            85,
            $array['score']
        );
    }
}