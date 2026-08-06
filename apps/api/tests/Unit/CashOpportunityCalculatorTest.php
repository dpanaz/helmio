<?php

namespace Tests\Unit;

use App\Services\Analytics\Cash\CashOpportunityCalculator;
use PHPUnit\Framework\TestCase;

class CashOpportunityCalculatorTest extends TestCase
{
    public function test_it_calculates_excess_cash_and_opportunity_cost(): void
    {
        $service = new CashOpportunityCalculator();

        $result = $service->calculate(
            averageCash: 40000,
            averagePortfolioValue: 200000,
            benchmarkReturn: 0.12,
            targetCashPercent: 0.05,
        );

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertEqualsWithDelta(
            30000,
            $result['metrics']['excess_cash'],
            0.01
        );

        $this->assertEqualsWithDelta(
            3600,
            $result['metrics'][
                'estimated_opportunity_cost'
            ],
            0.01
        );
    }

    public function test_it_returns_zero_when_cash_is_below_target(): void
    {
        $service = new CashOpportunityCalculator();

        $result = $service->calculate(
            averageCash: 5000,
            averagePortfolioValue: 200000,
            benchmarkReturn: 0.12,
            targetCashPercent: 0.05,
        );

        $this->assertSame(
            0.0,
            $result['metrics']['excess_cash']
        );

        $this->assertSame(
            0.0,
            $result['metrics'][
                'estimated_opportunity_cost'
            ]
        );
    }

    public function test_it_requires_a_benchmark_return(): void
    {
        $service = new CashOpportunityCalculator();

        $result = $service->calculate(
            averageCash: 40000,
            averagePortfolioValue: 200000,
            benchmarkReturn: null,
        );

        $this->assertSame(
            'insufficient_data',
            $result['status']
        );
    }
}