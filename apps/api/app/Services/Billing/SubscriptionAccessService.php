<?php

namespace App\Services\Billing;

use App\Models\User;
use Laravel\Cashier\Subscription;

class SubscriptionAccessService
{
    public function status(User $user): array
    {
        if ($this->isDemoUser($user)) {
            return [
                'has_access' => true,
                'subscribed' => false,
                'on_trial' => false,
                'on_grace_period' => false,
                'cancelled' => false,
                'status' => 'demo',
                'plan' => 'demo',
                'price_id' => null,
                'trial_ends_at' => null,
                'ends_at' => null,
            ];
        }

        $subscription = $user->subscription('default');
        $subscribed = $user->subscribed('default');
        $onTrial = $subscription?->onTrial() ?? false;
        $onGracePeriod = $subscription?->onGracePeriod() ?? false;

        return [
            'has_access' => $subscribed || $onTrial || $onGracePeriod,
            'subscribed' => $subscribed,
            'on_trial' => $onTrial,
            'on_grace_period' => $onGracePeriod,
            'cancelled' => $subscription?->canceled() ?? false,
            'status' => $subscription?->stripe_status ?? 'none',
            'plan' => $this->resolvePlan($subscription),
            'price_id' => $this->resolvePriceId($subscription),
            'trial_ends_at' => $subscription?->trial_ends_at,
            'ends_at' => $subscription?->ends_at,
        ];
    }

    public function hasPremiumAccess(User $user): bool
    {
        return (bool) $this->status($user)['has_access'];
    }

    private function isDemoUser(User $user): bool
    {
        return strtolower((string) $user->email) === 'demo@myhelmio.com';
    }

    private function resolvePlan(?Subscription $subscription): ?string
    {
        return match ($this->resolvePriceId($subscription)) {
            config('services.stripe.prices.monthly') => 'monthly',
            config('services.stripe.prices.annual') => 'annual',
            default => null,
        };
    }

    private function resolvePriceId(?Subscription $subscription): ?string
    {
        if ($subscription === null) {
            return null;
        }

        return $subscription->items()->value('stripe_price');
    }
}