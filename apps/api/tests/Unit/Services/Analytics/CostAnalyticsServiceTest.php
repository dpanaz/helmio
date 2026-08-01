<?php

namespace Tests\Unit\Services\Analytics;

use App\Models\Holding;
use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use App\Models\Security;
use App\Services\Analytics\CostAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_all_in_annual_cost(): void
    {
        $user = \App\Models\User::factory()->create();

        $account = InvestmentAccount::create([
            'user_id' => $user->id,
            'name' => 'Test IRA',
            'account_type' => 'traditional_ira',
            'current_value' => 100000,
            'cash_value' => 10000,
            'annual_advisory_fee_rate' => 0.01,
            'annual_account_fee' => 50,
            'advisory_fee_applies_to_cash' => true,
            'status' => 'active',
        ]);

        $fund = Security::create([
            'symbol' => 'TESTX',
            'name' => 'Test Mutual Fund',
            'security_type' => 'mutual_fund',
            'expense_ratio' => 0.005,
        ]);

        Holding::create([
            'investment_account_id' => $account->id,
            'security_id' => $fund->id,
            'quantity' => 1000,
            'price' => 100,
            'market_value' => 100000,
            'as_of_date' => now()->toDateString(),
        ]);

        InvestmentTransaction::create([
            'investment_account_id' => $account->id,
            'transaction_type' => 'buy',
            'transaction_date' => now()->subMonth(),
            'gross_amount' => -10000,
            'fees' => 25,
            'net_amount' => -10025,
        ]);

        $result = app(CostAnalyticsService::class)->calculate(
            collect([$account]),
        );

        $this->assertSame(1000.00, $result['advisory_fees']);
        $this->assertSame(500.00, $result['fund_expenses']);
        $this->assertSame(25.00, $result['transaction_fees']);
        $this->assertSame(50.00, $result['account_fees']);
        $this->assertSame(1575.00, $result['total_annual_cost']);
        $this->assertEqualsWithDelta(
            0.01575,
            $result['all_in_cost_rate'],
            0.000001,
        );
    }
}
