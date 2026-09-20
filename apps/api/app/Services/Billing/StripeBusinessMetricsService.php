<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Throwable;

class StripeBusinessMetricsService
{
    private const CACHE_KEY = 'admin.stripe-business-metrics.v2';

    public function __construct(
        private readonly BillingPlanService $plans,
    ) {
    }

    public function metrics(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(5),
            fn (): array => $this->retrieve(),
        );
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function retrieve(): array
    {
        $secret = config('services.stripe.secret');

        if (blank($secret)) {
            return $this->localFallback('Stripe secret is not configured.');
        }

        try {
            $stripe = new StripeClient($secret);
            $active = 0;
            $trials = 0;
            $pastDue = 0;
            $monthly = 0;
            $annual = 0;
            $newSubscriptions = 0;
            $cancellations = 0;
            $mrrCents = 0;
            $since = now()->subDays(30)->timestamp;

            foreach ($stripe->subscriptions->all([
                'status' => 'all',
                'limit' => 100,
            ])->autoPagingIterator() as $subscription) {
                if (in_array($subscription->status, ['active', 'trialing'], true)) {
                    $active++;
                    $trials += $subscription->status === 'trialing' ? 1 : 0;

                    foreach ($subscription->items->data as $item) {
                        $price = $item->price;
                        $quantity = max(1, (int) ($item->quantity ?? 1));
                        $amount = (int) ($price->unit_amount ?? 0) * $quantity;
                        $interval = $price->recurring?->interval;
                        $intervalCount = max(1, (int) ($price->recurring?->interval_count ?? 1));

                        $mrrCents += match ($interval) {
                            'year' => (int) round($amount / (12 * $intervalCount)),
                            'week' => (int) round(($amount * 52) / (12 * $intervalCount)),
                            'day' => (int) round(($amount * 365) / (12 * $intervalCount)),
                            default => (int) round($amount / $intervalCount),
                        };

                        $monthly += $interval === 'month' ? 1 : 0;
                        $annual += $interval === 'year' ? 1 : 0;
                    }
                }

                $pastDue += in_array($subscription->status, ['past_due', 'unpaid'], true) ? 1 : 0;
                $newSubscriptions += $subscription->created >= $since ? 1 : 0;
                $cancellations += $subscription->status === 'canceled'
                    && ($subscription->ended_at ?? 0) >= $since ? 1 : 0;
            }

            $refundCents = 0;
            $refunds = 0;
            foreach ($stripe->refunds->all([
                'created' => ['gte' => $since],
                'limit' => 100,
            ])->autoPagingIterator() as $refund) {
                if ($refund->status === 'succeeded') {
                    $refunds++;
                    $refundCents += (int) $refund->amount;
                }
            }

            return [
                'source' => 'stripe',
                'available' => true,
                'error' => null,
                'refreshed_at' => now()->toIso8601String(),
                'active_subscriptions' => $active,
                'trials' => $trials,
                'past_due' => $pastDue,
                'monthly_plans' => $monthly,
                'annual_plans' => $annual,
                'new_subscriptions_30d' => $newSubscriptions,
                'cancellations_30d' => $cancellations,
                'refunds_30d' => $refunds,
                'refund_amount_30d' => $refundCents / 100,
                'mrr' => $mrrCents / 100,
                'arr' => ($mrrCents / 100) * 12,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return $this->localFallback($exception->getMessage());
        }
    }

    private function localFallback(string $error): array
    {
        $active = DB::table('subscriptions')
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where(fn ($query) => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>', now()));

        $monthly = $this->plans->priceId('monthly')
            ? (clone $active)->where('stripe_price', $this->plans->priceId('monthly'))->count()
            : 0;
        $annual = $this->plans->priceId('annual')
            ? (clone $active)->where('stripe_price', $this->plans->priceId('annual'))->count()
            : 0;
        $mrr = ($monthly * $this->plans->amount('monthly'))
            + (($annual * $this->plans->amount('annual')) / 12);

        return [
            'source' => 'local',
            'available' => false,
            'error' => $error,
            'refreshed_at' => now()->toIso8601String(),
            'active_subscriptions' => (clone $active)->count(),
            'trials' => (clone $active)->where('stripe_status', 'trialing')->count(),
            'past_due' => DB::table('subscriptions')->whereIn('stripe_status', ['past_due', 'unpaid'])->count(),
            'monthly_plans' => $monthly,
            'annual_plans' => $annual,
            'new_subscriptions_30d' => DB::table('subscriptions')->where('created_at', '>=', now()->subDays(30))->count(),
            'cancellations_30d' => DB::table('subscriptions')->where('stripe_status', 'canceled')->where('updated_at', '>=', now()->subDays(30))->count(),
            'refunds_30d' => 0,
            'refund_amount_30d' => 0.0,
            'mrr' => $mrr,
            'arr' => $mrr * 12,
        ];
    }
}
