<?php

namespace Tests\Unit;

use App\Data\Analytics\AnalyticsResult;
use PHPUnit\Framework\TestCase;

class TradingAnalyticsContractTest extends TestCase
{
    public function test_shared_contract_contains_required_fields(): void
    {
        $result = AnalyticsResult::complete(
            metrics: [
                'turnover_rate' => 0.25,
            ],

            flags: [],

            warnings: [],

            data: [
                'period' => [
                    'start_date' => '2026-01-01',
                    'end_date' => '2026-12-31',
                ],

                'summary' => [
                    'transaction_count' => 10,
                ],

                'risk_level' => 'low',

                'round_trip_analysis' => [],
            ],

            score: 90,
            label: 'Excellent',
            formulaVersion: 'trading-0.3.0',
        )->toArray();

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertSame(
            90,
            $result['score']
        );

        $this->assertArrayHasKey(
            'metrics',
            $result
        );

        $this->assertArrayHasKey(
            'flags',
            $result
        );

        $this->assertArrayHasKey(
            'warnings',
            $result
        );

        $this->assertArrayHasKey(
            'data',
            $result
        );

        $this->assertSame(
            'trading-0.3.0',
            $result['formula_version']
        );
    }
}