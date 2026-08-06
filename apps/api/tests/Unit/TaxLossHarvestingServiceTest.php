<?php

namespace Tests\Unit;

use App\Models\Holding;
use App\Models\InvestmentTransaction;
use App\Services\Analytics\Tax\TaxLossHarvestingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TaxLossHarvestingServiceTest extends TestCase
{
    public function test_it_detects_a_harvestable_loss(): void
    {
        $holdings = new Collection([
            $this->holding(
                id: 1,
                currentValue: 8000,
                costBasis: 10000,
            ),
        ]);

        $result = (
            new TaxLossHarvestingService()
        )->analyze(
            holdings: $holdings,
            transactions: new Collection(),
            asOfDate: Carbon::parse(
                '2026-08-04'
            ),
        );

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertSame(
            1,
            $result['metrics'][
                'opportunity_count'
            ]
        );

        $this->assertEqualsWithDelta(
            2000,
            $result['metrics'][
                'estimated_harvestable_loss'
            ],
            0.01
        );
    }

    public function test_it_flags_recent_purchase_wash_sale_risk(): void
    {
        $holdings = new Collection([
            $this->holding(
                id: 1,
                currentValue: 8000,
                costBasis: 10000,
            ),
        ]);

        $transactions = new Collection([
            $this->transaction(
                date: '2026-07-20',
                type: 'buy',
            ),
        ]);

        $result = (
            new TaxLossHarvestingService()
        )->analyze(
            holdings: $holdings,
            transactions: $transactions,
            asOfDate: Carbon::parse(
                '2026-08-04'
            ),
        );

        $this->assertSame(
            1,
            $result['metrics'][
                'wash_sale_risk_count'
            ]
        );

        $this->assertEqualsWithDelta(
            0,
            $result['metrics'][
                'estimated_harvestable_loss'
            ],
            0.01
        );
    }

    public function test_it_ignores_small_losses(): void
    {
        $holdings = new Collection([
            $this->holding(
                id: 1,
                currentValue: 9700,
                costBasis: 10000,
            ),
        ]);

        $result = (
            new TaxLossHarvestingService()
        )->analyze(
            holdings: $holdings,
            transactions: new Collection(),
            asOfDate: Carbon::parse(
                '2026-08-04'
            ),
        );

        $this->assertSame(
            0,
            $result['metrics'][
                'opportunity_count'
            ]
        );
    }

    private function holding(
        int $id,
        float $currentValue,
        float $costBasis
    ): Holding {
        $holding = new Holding();

        $holding->forceFill([
            'investment_account_id' => 1,
            'security_id' => 1,
            'quantity' => 100,
            'market_value' =>
                $currentValue,
            'cost_basis' =>
                $costBasis,
        ]);

        $holding->setRawAttributes([
            ...$holding->getAttributes(),
            'id' => $id,
        ], true);

        return $holding;
    }

    private function transaction(
        string $date,
        string $type
    ): InvestmentTransaction {
        $transaction =
            new InvestmentTransaction();

        $transaction->forceFill([
            'investment_account_id' => 1,
            'security_id' => 1,
            'transaction_type' => $type,
            'transaction_date' =>
                Carbon::parse($date),
            'quantity' => 10,
        ]);

        return $transaction;
    }
}