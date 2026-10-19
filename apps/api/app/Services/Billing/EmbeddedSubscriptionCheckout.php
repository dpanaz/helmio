<?php

namespace App\Services\Billing;

use App\Models\User;
use Laravel\Cashier\Checkout;

class EmbeddedSubscriptionCheckout
{
    public function create(User $user, string $priceId): Checkout
    {
        return $user->newSubscription('default', $priceId)
            ->trialDays((int) config('services.stripe.trial_days', 14))
            ->checkout([
                'ui_mode' => 'embedded',
                'payment_method_types' => ['card'],
                'redirect_on_completion' => 'if_required',
                'return_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
            ]);
    }
}
