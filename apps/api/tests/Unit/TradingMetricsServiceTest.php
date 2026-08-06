<?php

namespace Tests\Unit;

use App\Services\Analytics\Trading\TradingMetricsService;
use PHPUnit\Framework\TestCase;

class TradingMetricsServiceTest extends TestCase
{
    public function test_it_calculates_turnover(): void
    {
        $service = new TradingMetricsService();

        $result = $service->analyze(
            transactions: [
                [
                    'transaction_type' => 'buy',
                    'gross_amount' => 50000,
                    'fees' => 100,
                ],
                [
                    'transaction_type' => 'sell',
                    'gross_amount' => 40000,
                    'fees' => 100,
                ],
            ],
            averagePortfolioValue: 100000,
        );

        $this->assertEqualsWithDelta(
            0.40,
            $result['metrics']['turnover_rate'],
            0.000001
        );

        $this->assertSame(
            2,
            $result['metrics']['trade_count']
        );
    }

    public function test_high_turnover_creates_flag(): void
    {
        $service = new TradingMetricsService();

        $result = $service->analyze(
            transactions: [
                [
                    'transaction_type' => 'buy',
                    'gross_amount' => 150000,
                ],
                [
                    'transaction_type' => 'sell',
                    'gross_amount' => 150000,
                ],
            ],
            averagePortfolioValue: 100000,
        );

        $codes = collect(
            $result['flags']
        )->pluck('code');

        $this->assertTrue(
            $codes->contains(
                'high_portfolio_turnover'
            )
        );
    }

    public function test_no_transactions_returns_insufficient_data(): void
    {
        $service = new TradingMetricsService();

        $result = $service->analyze(
            transactions: [],
            averagePortfolioValue: 100000,
        );

        $this->assertSame(
            'insufficient_data',
            $result['status']
        );
    }
}