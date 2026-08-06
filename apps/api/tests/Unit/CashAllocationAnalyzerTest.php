<?php

namespace Tests\Unit;

use App\Models\PortfolioValuation;
use App\Services\Analytics\Cash\CashAllocationAnalyzer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CashAllocationAnalyzerTest extends TestCase
{
    public function test_it_calculates_cash_percentages(): void
    {
        $service = new CashAllocationAnalyzer();

        $valuations = new Collection([
            $this->valuation(
                '2026-01-01',
                100000,
                10000
            ),

            $this->valuation(
                '2026-02-01',
                110000,
                22000
            ),
        ]);

        $result = $service->analyze($valuations);

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertEqualsWithDelta(
            0.10,
            $result['metrics']['minimum_cash_percent'],
            0.000001
        );

        $this->assertEqualsWithDelta(
            0.20,
            $result['metrics']['maximum_cash_percent'],
            0.000001
        );

        $this->assertEqualsWithDelta(
            0.15,
            $result['metrics']['average_cash_percent'],
            0.000001
        );
    }

    private function valuation(
        string $date,
        float $portfolio,
        float $cash
    ): PortfolioValuation {
        $valuation = new PortfolioValuation();

        $valuation->forceFill([
            'valuation_date' => Carbon::parse($date),
            'market_value' => $portfolio - $cash,
            'cash_value' => $cash,
        ]);

        return $valuation;
    }
}