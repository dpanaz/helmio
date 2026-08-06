<?php

namespace Tests\Unit;

use App\Models\InvestmentTransaction;
use App\Services\Analytics\Tax\WashSaleDetector;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class WashSaleDetectorTest extends TestCase
{
    public function test_it_detects_a_repurchase_after_a_loss_sale(): void
    {
        $transactions = new Collection([
            $this->transaction(
                id: 1,
                type: 'sell',
                date: '2026-03-10',
                quantity: 100,
                realizedGainLoss: -1000,
            ),

            $this->transaction(
                id: 2,
                type: 'buy',
                date: '2026-03-21',
                quantity: 100,
            ),
        ]);

        $result = (
            new WashSaleDetector()
        )->analyze($transactions);

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertSame(
            1,
            $result['metrics'][
                'wash_sale_count'
            ]
        );

        $this->assertEqualsWithDelta(
            1000,
            $result['metrics'][
                'estimated_disallowed_loss'
            ],
            0.01
        );

        $this->assertSame(
            'likely',
            $result['wash_sales'][0][
                'confidence'
            ]
        );
    }

    public function test_it_allocates_partial_repurchase_loss(): void
    {
        $transactions = new Collection([
            $this->transaction(
                id: 1,
                type: 'sell',
                date: '2026-03-10',
                quantity: 100,
                realizedGainLoss: -1000,
            ),

            $this->transaction(
                id: 2,
                type: 'buy',
                date: '2026-03-21',
                quantity: 40,
            ),
        ]);

        $result = (
            new WashSaleDetector()
        )->analyze($transactions);

        $this->assertEqualsWithDelta(
            400,
            $result['metrics'][
                'estimated_disallowed_loss'
            ],
            0.01
        );
    }

    public function test_it_ignores_purchases_outside_the_window(): void
    {
        $transactions = new Collection([
            $this->transaction(
                id: 1,
                type: 'sell',
                date: '2026-03-10',
                quantity: 100,
                realizedGainLoss: -1000,
            ),

            $this->transaction(
                id: 2,
                type: 'buy',
                date: '2026-04-15',
                quantity: 100,
            ),
        ]);

        $result = (
            new WashSaleDetector()
        )->analyze($transactions);

        $this->assertSame(
            0,
            $result['metrics'][
                'wash_sale_count'
            ]
        );
    }

    public function test_it_detects_a_purchase_before_the_loss_sale(): void
    {
        $transactions = new Collection([
            $this->transaction(
                id: 1,
                type: 'buy',
                date: '2026-03-01',
                quantity: 50,
            ),

            $this->transaction(
                id: 2,
                type: 'sell',
                date: '2026-03-10',
                quantity: 50,
                realizedGainLoss: -500,
            ),
        ]);

        $result = (
            new WashSaleDetector()
        )->analyze($transactions);

        $this->assertSame(
            1,
            $result['metrics'][
                'wash_sale_count'
            ]
        );

        $this->assertTrue(
            $result['wash_sales'][0][
                'purchase_before_sale'
            ]
        );
    }

    private function transaction(
        int $id,
        string $type,
        string $date,
        float $quantity,
        float $realizedGainLoss = 0
    ): InvestmentTransaction {
        $transaction =
            new InvestmentTransaction();

        $transaction->forceFill([
            'investment_account_id' => 1,
            'security_id' => 1,
            'transaction_type' => $type,
            'transaction_date' =>
                Carbon::parse($date),
            'quantity' => $quantity,
            'realized_gain_loss' =>
                $realizedGainLoss,
        ]);

        $transaction->setRawAttributes([
            ...$transaction->getAttributes(),
            'id' => $id,
        ], true);

        return $transaction;
    }
}