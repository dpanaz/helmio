<?php

namespace Tests\Feature;

use App\Models\InvestorProfile;
use App\Models\User;
use App\Services\Billing\BillingPlanService;
use App\Services\Billing\EmbeddedSubscriptionCheckout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\SubscriptionBuilder;
use Mockery;
use Stripe\Checkout\Session;
use Tests\TestCase;

class EmbeddedCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_embedded_subscription_with_existing_trial_and_no_external_redirect(): void
    {
        config(['services.stripe.trial_days' => 14]);
        $user = Mockery::mock(User::class)->makePartial();
        $builder = Mockery::mock(SubscriptionBuilder::class);
        $checkout = new Checkout($user, Session::constructFrom(['id' => 'cs_test', 'client_secret' => 'secret_test']));
        $user->shouldReceive('newSubscription')->once()->with('default', 'price_monthly')->andReturn($builder);
        $builder->shouldReceive('trialDays')->once()->with(14)->andReturnSelf();
        $builder->shouldReceive('checkout')->once()->with(Mockery::on(fn ($options) =>
            $options['ui_mode'] === 'embedded'
            && $options['payment_method_types'] === ['card']
            && $options['redirect_on_completion'] === 'if_required'
            && $options['return_url'] === route('billing.success').'?session_id={CHECKOUT_SESSION_ID}'
            && ! isset($options['success_url'], $options['cancel_url'])
        ))->andReturn($checkout);

        $this->assertSame($checkout, app(EmbeddedSubscriptionCheckout::class)->create($user, 'price_monthly'));
    }

    public function test_checkout_renders_payment_inside_helmio_without_unlocking_results(): void
    {
        $this->withoutVite();
        config(['services.stripe.key' => 'pk_test_example']);
        $user = User::factory()->create();
        InvestorProfile::query()->create(['user_id' => $user->id, 'primary_objective' => 'growth', 'time_horizon_years' => 10,
            'investment_experience' => 'beginner', 'liquidity_needs' => 'low', 'risk_tolerance' => 'moderate']);
        $account = $user->investmentAccounts()->create(['name' => 'Linked account', 'account_type' => 'brokerage', 'currency' => 'USD']);
        $account->profile()->create(['purpose' => 'retirement', 'time_horizon_years_override' => 10, 'liquidity_needs_override' => 'low']);
        $this->mock(BillingPlanService::class, fn ($mock) => $mock->shouldReceive('priceId')->with('monthly')->andReturn('price_monthly'));
        $checkout = new Checkout($user, Session::constructFrom(['id' => 'cs_test', 'client_secret' => 'secret_test']));
        $this->mock(EmbeddedSubscriptionCheckout::class, fn ($mock) => $mock->shouldReceive('create')->once()
            ->with(Mockery::on(fn ($customer) => $customer->is($user)), 'price_monthly')->andReturn($checkout));

        $this->actingAs($user)->post(route('billing.checkout'), ['billing_period' => 'monthly'])
            ->assertOk()->assertViewIs('billing.checkout')->assertSee('initEmbeddedCheckout', false)->assertSee('secret_test');
        $this->get(route('onboarding.complete'))->assertRedirect(route('billing.pricing'));
    }
}
