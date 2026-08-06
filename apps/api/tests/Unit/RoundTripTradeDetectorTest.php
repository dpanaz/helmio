<?php

namespace Tests\Unit;

use App\Models\InvestmentTransaction;
use App\Services\Analytics\Trading\RoundTripTradeDetector;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RoundTripTradeDetectorTest extends TestCase
{
    public function test_it_matches_a_buy_and_sell(): void
    {
        $transactions = new Collection([
            $this->transaction(
                id: 1,
                type: 'buy',
                date: '2026-01-01',
                quantity: 100,
                price: 10,
                fees: 5,
            ),

            $this->transaction(
                id: 2,
                type: 'sell',
                date: '2026-01-11',
                quantity: 100,
                price: 12,
                fees: 5,
            ),
        ]);

        $result = app(
            RoundTripTradeDetector::class
        )->analyze($transactions);

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertSame(
            1,
            $result['metrics']['round_trip_count']
        );

        $this->assertEqualsWithDelta(
    10,
    $result['round_trips'][0]['holding_period_days'],
    0.000001
);

        $this->assertEqualsWithDelta(
            190,
            $result['round_trips'][0]['realized_gain_loss'],
            0.01
        );
    }

    public function test_it_matches_partial_sales_using_fifo(): void
    {
        $transactions = new Collection([
            $this->transaction(
                id: 1,
                type: 'buy',
                date: '2026-01-01',
                quantity: 100,
                price: 10,
            ),

            $this->transaction(
                id: 2,
                type: 'sell',
                date: '2026-02-01',
                quantity: 40,
                price: 12,
            ),
        ]);

        $result = app(
            RoundTripTradeDetector::class
        )->analyze($transactions);

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertSame(
            1,
            $result['metrics']['round_trip_count']
        );

        $this->assertEqualsWithDelta(
            40,
            $result['round_trips'][0]['quantity'],
            0.000001
        );

        $this->assertEqualsWithDelta(
            80,
            $result['round_trips'][0]['realized_gain_loss'],
            0.01
        );
    }

    public function test_it_matches_multiple_buy_lots_using_fifo(): void
    {
        $transactions = new Collection([
            $this->transaction(
                id: 1,
                type: 'buy',
                date: '2026-01-01',
                quantity: 50,
                price: 10,
            ),

            $this->transaction(
                id: 2,
                type: 'buy',
                date: '2026-01-10',
                quantity: 50,
                price: 11,
            ),

            $this->transaction(
                id: 3,
                type: 'sell',
                date: '2026-02-01',
                quantity: 75,
                price: 12,
            ),
        ]);

        $result = app(
            RoundTripTradeDetector::class
        )->analyze($transactions);

        $this->assertSame(
            2,
            $result['metrics']['round_trip_count']
        );

        $this->assertEqualsWithDelta(
            50,
            $result['round_trips'][0]['quantity'],
            0.000001
        );

        $this->assertEqualsWithDelta(
            25,
            $result['round_trips'][1]['quantity'],
            0.000001
        );
    }

    public function test_it_returns_insufficient_data_without_completed_round_trips(): void
    {
        $transactions = new Collection([
            $this->transaction(
                id: 1,
                type: 'buy',
                date: '2026-01-01',
                quantity: 100,
                price: 10,
            ),
        ]);

        $result = app(
            RoundTripTradeDetector::class
        )->analyze($transactions);

        $this->assertSame(
            'insufficient_data',
            $result['status']
        );

        $this->assertSame(
            0,
            $result['metrics']['round_trip_count']
        );
    }

    private function transaction(
        int $id,
        string $type,
        string $date,
        float $quantity,
        float $price,
        float $fees = 0
    ): InvestmentTransaction {
        /*
         * Use forceFill so test-only model attributes are assigned
         * without relying on mass-assignment configuration.
         */
        $transaction = new InvestmentTransaction();

        $transaction->forceFill([
            'investment_account_id' => 1,
            'security_id' => 1,
            'transaction_type' => $type,
            'transaction_date' => Carbon::parse($date),
            'quantity' => $quantity,
            'price' => $price,
            'gross_amount' => $quantity * $price,
            'net_amount' => $type === 'sell'
                ? $quantity * $price
                : -($quantity * $price),
            'fees' => $fees,
        ]);

        /*
         * setAttribute('id') can trigger model internals depending on
         * configuration. Set the raw primary key directly for this
         * unsaved test model.
         */
        $transaction->setRawAttributes([
            ...$transaction->getAttributes(),
            'id' => $id,
        ], true);

        return $transaction;
    }
}