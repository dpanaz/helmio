<?php

namespace Tests\Unit;

use App\Models\InvestmentTransaction;
use App\Services\Analytics\Tax\TaxLotAnalyticsService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TaxLotAnalyticsServiceTest extends TestCase
{
    public function test_it_summarizes_realized_gains_and_dividends(): void
    {
        $transactions = new Collection([
            $this->transaction([
                'transaction_type' => 'sell',
                'realized_gain_loss' => 1000,
                'holding_period_days' => 200,
            ]),

            $this->transaction([
                'transaction_type' => 'sell',
                'realized_gain_loss' => 2500,
                'holding_period_days' => 500,
            ]),

            $this->transaction([
                'transaction_type' => 'dividend',
                'net_amount' => 400,
                'is_qualified_dividend' => true,
            ]),

            $this->transaction([
                'transaction_type' => 'dividend',
                'net_amount' => 250,
                'is_qualified_dividend' => false,
                'tax_withheld' => 25,
            ]),
        ]);

        $result = (
            new TaxLotAnalyticsService()
        )->analyze($transactions);

        $this->assertSame(
            'complete',
            $result['status']
        );

        $this->assertEqualsWithDelta(
            1000,
            $result['metrics'][
                'realized_short_term_gain_loss'
            ],
            0.01
        );

        $this->assertEqualsWithDelta(
            2500,
            $result['metrics'][
                'realized_long_term_gain_loss'
            ],
            0.01
        );

        $this->assertEqualsWithDelta(
            400,
            $result['metrics'][
                'qualified_dividends'
            ],
            0.01
        );

        $this->assertEqualsWithDelta(
            250,
            $result['metrics'][
                'non_qualified_dividends'
            ],
            0.01
        );

        $this->assertEqualsWithDelta(
            25,
            $result['metrics'][
                'tax_withheld'
            ],
            0.01
        );
    }

    public function test_it_identifies_tax_exempt_income(): void
    {
        $transactions = new Collection([
            $this->transaction([
                'transaction_type' => 'dividend',
                'net_amount' => 500,
                'is_tax_exempt' => true,
            ]),
        ]);

        $result = (
            new TaxLotAnalyticsService()
        )->analyze($transactions);

        $this->assertEqualsWithDelta(
            500,
            $result['metrics'][
                'tax_exempt_income'
            ],
            0.01
        );
    }

    public function test_it_reports_missing_holding_periods(): void
    {
        $transactions = new Collection([
            $this->transaction([
                'transaction_type' => 'sell',
                'realized_gain_loss' => 1000,
                'holding_period_days' => null,
            ]),
        ]);

        $result = (
            new TaxLotAnalyticsService()
        )->analyze($transactions);

        $this->assertSame(
            1,
            $result['counts'][
                'unknown_holding_period_count'
            ]
        );
    }

    private function transaction(
        array $attributes
    ): InvestmentTransaction {
        $transaction =
            new InvestmentTransaction();

        $transaction->forceFill(
            array_merge(
                [
                    'transaction_type' => 'buy',
                    'gross_amount' => 0,
                    'net_amount' => 0,
                    'realized_gain_loss' => 0,
                    'holding_period_days' => null,
                    'is_qualified_dividend' => false,
                    'is_tax_exempt' => false,
                    'tax_withheld' => 0,
                ],
                $attributes
            )
        );

        return $transaction;
    }
}