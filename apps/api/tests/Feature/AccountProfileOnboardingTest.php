<?php

namespace Tests\Feature;

use App\Models\BrokerageConnection;
use App\Models\InvestorProfile;
use App\Models\User;
use App\Services\Analytics\Risk\SuitabilityProfileService;
use App\Services\Billing\SubscriptionAccessService;
use App\Services\Brokerage\BrokerageSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccountProfileOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Queue::fake();
        $this->mock(SubscriptionAccessService::class, fn ($mock) => $mock->shouldReceive('hasPremiumAccess')->andReturn(false));
    }

    private function investor(): User
    {
        $user = User::factory()->create();
        InvestorProfile::query()->create(['user_id' => $user->id, 'primary_objective' => 'growth',
            'investment_experience' => 'beginner', 'risk_tolerance' => 'moderate']);
        return $user;
    }

    private function answers(int $years): array
    {
        return ['purpose' => 'retirement', 'time_horizon_years_override' => $years, 'liquidity_needs_override' => 'low'];
    }

    public function test_each_imported_account_gets_its_own_profile_before_payment(): void
    {
        $user = $this->investor();
        $first = $user->investmentAccounts()->create(['name' => 'House deposit', 'account_type' => 'brokerage', 'currency' => 'USD']);
        $second = $user->investmentAccounts()->create(['name' => 'Retirement IRA', 'account_type' => 'traditional_ira', 'currency' => 'USD']);
        $this->actingAs($user)->get(route('onboarding.index'))->assertRedirect(route('onboarding.accounts'));
        $this->get(route('billing.pricing'))->assertRedirect(route('onboarding.accounts'));
        $this->post(route('billing.checkout'), ['billing_period' => 'monthly'])->assertRedirect(route('onboarding.accounts'));
        $this->get(route('onboarding.accounts'))->assertOk()->assertSee('House deposit')->assertSee('Account 1 of 2');
        $this->put(route('onboarding.accounts.update', $first), ['purpose' => 'invented'])
            ->assertSessionHasErrors(['purpose', 'time_horizon_years_override', 'liquidity_needs_override']);
        $this->put(route('onboarding.accounts.update', $first), ['purpose' => 'short_term_goal', 'time_horizon_years_override' => 1, 'liquidity_needs_override' => 'high'])
            ->assertRedirect(route('onboarding.accounts'));
        $this->get(route('onboarding.accounts'))->assertSee('Retirement IRA')->assertSee('Account 2 of 2');
        $this->put(route('onboarding.accounts.update', $second), $this->answers(20))->assertRedirect(route('onboarding.accounts'));
        $this->get(route('onboarding.accounts'))->assertRedirect(route('onboarding.index'));
        $this->get(route('onboarding.index'))->assertRedirect(route('billing.pricing'));
        $this->assertNull($user->investorProfile->time_horizon_years);
        $service = app(SuitabilityProfileService::class);
        $this->assertSame(1, $service->forAccount($first->fresh())['time_horizon_years']);
        $this->assertSame(20, $service->forAccount($second->fresh())['time_horizon_years']);
        $this->assertSame('high', $service->forAccount($first->fresh())['liquidity_needs']);
        $this->assertSame('moderate', $service->forAccount($second->fresh())['risk_tolerance']);
    }

    public function test_customer_cannot_update_another_investors_account_profile(): void
    {
        $user = $this->investor();
        $other = $this->investor();
        $account = $other->investmentAccounts()->create(['name' => 'Private account', 'account_type' => 'brokerage', 'currency' => 'USD']);
        $this->actingAs($user)->put(route('onboarding.accounts.update', $account), $this->answers(5))->assertForbidden();
        $this->assertDatabaseCount('investment_account_profiles', 0);
    }

    public function test_account_import_waits_before_payment_and_then_prompts_for_profile(): void
    {
        $user = $this->investor();
        $user->brokerageConnections()->create(['provider' => 'snaptrade', 'status' => 'active', 'read_only' => true]);
        $this->actingAs($user)->get(route('onboarding.index'))->assertRedirect(route('onboarding.accounts'));
        $this->get(route('onboarding.accounts'))->assertOk()->assertSee('Bringing in your accounts');
        $this->getJson(route('onboarding.accounts'))->assertJsonPath('next_url', null);
        $user->investmentAccounts()->create(['name' => 'Imported IRA', 'account_type' => 'traditional_ira', 'currency' => 'USD']);
        $this->getJson(route('onboarding.accounts'))->assertJsonPath('next_url', route('onboarding.accounts'));
        $this->get(route('onboarding.accounts'))->assertSee('Imported IRA');
    }

    public function test_existing_customer_is_prompted_after_connecting_another_account(): void
    {
        $user = $this->investor();
        $user->update(['onboarding_completed_at' => now()]);
        $connection = $user->brokerageConnections()->create(['provider' => 'fake', 'status' => 'pending', 'read_only' => true]);
        $this->mock(BrokerageSyncService::class, function ($mock) use ($user, $connection): void {
            $mock->shouldReceive('sync')->once()->andReturnUsing(function () use ($user, $connection): array {
                $user->investmentAccounts()->create(['name' => 'New linked account', 'account_type' => 'brokerage', 'currency' => 'USD', 'brokerage_connection_id' => $connection->id]);
                return ['accounts' => 1, 'positions' => 0, 'transactions' => 0];
            });
        });
        $this->actingAs($user)->get(route('brokerage-connections.fake-complete', $connection))->assertRedirect(route('onboarding.accounts'));
        $this->get(route('onboarding.accounts'))->assertSee('New linked account');
    }
}
