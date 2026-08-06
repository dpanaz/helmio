<?php

namespace Tests\Unit;

use App\Services\Analytics\Risk\RiskMetricsService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RiskMetricsServiceTest extends TestCase
{
    private RiskMetricsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service =
            new RiskMetricsService();
    }

    public function test_it_requires_two_portfolio_returns(): void
    {
        $result = $this->service->analyze([
            0.01,
        ]);

        $this->assertSame(
            'insufficient_data',
            $result['status']
        );

        $this->assertNull(
            $result['metrics'][
                'annualized_volatility'
            ]
        );
    }

    public function test_it_calculates_maximum_drawdown(): void
    {
        $result = $this->service->analyze([
            0.10,
            -0.20,
            0.05,
        ]);

        /*
         * Wealth:
         * 1.00 → 1.10 → 0.88 → 0.924
         *
         * Largest drawdown:
         * 0.88 / 1.10 - 1 = -20%
         */
        $this->assertEqualsWithDelta(
            -0.20,
            $result['metrics'][
                'maximum_drawdown'
            ],
            0.0000001
        );
    }

    public function test_zero_volatility_returns_null_sharpe(): void
    {
        $result = $this->service->analyze([
            0.01,
            0.01,
            0.01,
        ]);

        $this->assertNull(
            $result['metrics'][
                'sharpe_ratio'
            ]
        );
    }

    public function test_it_calculates_beta(): void
    {
        $benchmarkReturns = [
            0.01,
            -0.01,
            0.02,
            -0.02,
        ];

        $portfolioReturns = [
            0.02,
            -0.02,
            0.04,
            -0.04,
        ];

        $result = $this->service->analyze(
            portfolioReturns:
                $portfolioReturns,

            benchmarkReturns:
                $benchmarkReturns,
        );

        $this->assertEqualsWithDelta(
            2.0,
            $result['metrics']['beta'],
            0.0000001
        );
    }

    public function test_it_calculates_positive_volatility(): void
    {
        $result = $this->service->analyze([
            0.01,
            -0.01,
            0.02,
            -0.02,
        ]);

        $this->assertGreaterThan(
            0,
            $result['metrics'][
                'annualized_volatility'
            ]
        );
    }

    public function test_it_calculates_downside_deviation(): void
    {
        $result = $this->service->analyze([
            0.01,
            -0.01,
            0.02,
            -0.02,
        ]);

        $this->assertGreaterThan(
            0,
            $result['metrics'][
                'downside_deviation'
            ]
        );
    }

    public function test_it_returns_complete_result(): void
    {
        $result = $this->service->analyze(
            portfolioReturns: [
                0.01,
                -0.005,
                0.007,
                -0.002,
            ],

            benchmarkReturns: [
                0.008,
                -0.004,
                0.006,
                -0.001,
            ],
        );

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertSame(
            'risk-0.1.0',
            $result['formula_version']
        );

        $this->assertNotNull(
            $result['metrics']['beta']
        );
    }

    public function test_it_rejects_returns_below_negative_one(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->service->analyze([
            -1.01,
            0.01,
        ]);
    }

    public function test_it_ignores_null_return_values(): void
    {
        $result = $this->service->analyze([
            0.01,
            null,
            -0.01,
        ]);

        $this->assertSame(
            2,
            $result['observations'][
                'portfolio_return_count'
            ]
        );
    }

    public function test_it_assigns_a_risk_level(): void
    {
        $result = $this->service->analyze([
            0.01,
            -0.01,
            0.02,
            -0.02,
        ]);

        $this->assertContains(
            $result['risk_level'],
            [
                'very_low',
                'low',
                'moderate',
                'high',
                'very_high',
            ]
        );
    }
}