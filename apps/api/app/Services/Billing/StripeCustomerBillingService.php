<?php

namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Stripe\StripeClient;
use Throwable;

class StripeCustomerBillingService
{
    public function details(User $customer): array
    {
        return Cache::remember(
            $this->cacheKey($customer),
            now()->addMinutes(3),
            fn (): array => $this->retrieve($customer),
        );
    }

    public function forget(User $customer): void
    {
        Cache::forget($this->cacheKey($customer));
    }

    private function retrieve(User $customer): array
    {
        $secret = config('services.stripe.secret');

        if (blank($secret) || blank($customer->stripe_id)) {
            return $this->localFallback(
                $customer,
                blank($customer->stripe_id)
                    ? 'This customer is not linked to Stripe.'
                    : 'Stripe is not configured.',
            );
        }

        try {
            $stripe = new StripeClient($secret);
            $stripeCustomer = $stripe->customers->retrieve($customer->stripe_id, []);
            $subscriptions = [];
            $invoices = [];
            $refunds = [];
            $timeline = [];

            foreach ($stripe->subscriptions->all([
                'customer' => $customer->stripe_id,
                'status' => 'all',
                'limit' => 100,
            ])->autoPagingIterator() as $subscription) {
                $item = $subscription->items->data[0] ?? null;
                $price = $item?->price;
                $periodEnd = $subscription->current_period_end
                    ?? $item?->current_period_end;

                $subscriptions[] = [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'plan_name' => $price?->nickname ?: 'Helmio subscription',
                    'price_id' => $price?->id,
                    'amount' => ((int) ($price?->unit_amount ?? 0)) / 100,
                    'currency' => strtoupper((string) ($price?->currency ?? 'usd')),
                    'interval' => $price?->recurring?->interval,
                    'interval_count' => (int) ($price?->recurring?->interval_count ?? 1),
                    'quantity' => (int) ($item?->quantity ?? 1),
                    'created_at' => $this->date($subscription->created),
                    'trial_ends_at' => $this->date($subscription->trial_end),
                    'current_period_ends_at' => $this->date($periodEnd),
                    'cancel_at_period_end' => (bool) $subscription->cancel_at_period_end,
                    'cancel_at' => $this->date($subscription->cancel_at),
                    'canceled_at' => $this->date($subscription->canceled_at),
                    'ended_at' => $this->date($subscription->ended_at),
                ];

                $timeline[] = [
                    'type' => 'subscription',
                    'title' => 'Subscription created',
                    'detail' => ucfirst((string) $subscription->status),
                    'occurred_at' => $this->date($subscription->created),
                ];

                if ($subscription->canceled_at) {
                    $timeline[] = [
                        'type' => 'subscription',
                        'title' => 'Subscription canceled',
                        'detail' => $subscription->cancel_at_period_end
                            ? 'Access continues through the current billing period'
                            : 'Subscription ended',
                        'occurred_at' => $this->date($subscription->canceled_at),
                    ];
                }
            }

            foreach ($stripe->invoices->all([
                'customer' => $customer->stripe_id,
                'limit' => 25,
            ])->data as $invoice) {
                $invoiceData = [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'status' => $invoice->status,
                    'amount_due' => ((int) $invoice->amount_due) / 100,
                    'amount_paid' => ((int) $invoice->amount_paid) / 100,
                    'currency' => strtoupper((string) $invoice->currency),
                    'created_at' => $this->date($invoice->created),
                    'due_at' => $this->date($invoice->due_date),
                    'paid_at' => $this->date($invoice->status_transitions?->paid_at),
                    'hosted_url' => $invoice->hosted_invoice_url,
                    'pdf_url' => $invoice->invoice_pdf,
                ];
                $invoices[] = $invoiceData;
                $timeline[] = [
                    'type' => 'invoice',
                    'title' => 'Invoice '.($invoice->number ?: $invoice->id),
                    'detail' => ucfirst((string) $invoice->status).' · '.number_format($invoiceData['amount_due'], 2).' '.$invoiceData['currency'],
                    'occurred_at' => $invoiceData['paid_at'] ?: $invoiceData['created_at'],
                ];
            }

            foreach ($stripe->charges->all([
                'customer' => $customer->stripe_id,
                'limit' => 25,
            ])->data as $charge) {
                foreach ($charge->refunds?->data ?? [] as $refund) {
                    $refundData = [
                        'id' => $refund->id,
                        'status' => $refund->status,
                        'amount' => ((int) $refund->amount) / 100,
                        'currency' => strtoupper((string) $refund->currency),
                        'reason' => $refund->reason,
                        'created_at' => $this->date($refund->created),
                    ];
                    $refunds[] = $refundData;
                    $timeline[] = [
                        'type' => 'refund',
                        'title' => 'Refund issued',
                        'detail' => number_format($refundData['amount'], 2).' '.$refundData['currency'],
                        'occurred_at' => $refundData['created_at'],
                    ];
                }
            }

            usort($timeline, fn (array $a, array $b): int =>
                strcmp((string) $b['occurred_at'], (string) $a['occurred_at']));

            $testMode = str_starts_with((string) $secret, 'sk_test_');

            return [
                'source' => 'stripe',
                'available' => true,
                'error' => null,
                'refreshed_at' => now()->toIso8601String(),
                'stripe_customer_id' => $customer->stripe_id,
                'stripe_dashboard_url' => 'https://dashboard.stripe.com/'.($testMode ? 'test/' : '').'customers/'.$customer->stripe_id,
                'delinquent' => (bool) $stripeCustomer->delinquent,
                'balance' => ((int) $stripeCustomer->balance) / 100,
                'currency' => strtoupper((string) ($stripeCustomer->currency ?? 'usd')),
                'payment_method' => $customer->pm_type && $customer->pm_last_four
                    ? ucfirst($customer->pm_type).' •••• '.$customer->pm_last_four
                    : null,
                'subscriptions' => $subscriptions,
                'invoices' => $invoices,
                'refunds' => $refunds,
                'timeline' => array_slice($timeline, 0, 30),
            ];
        } catch (Throwable $exception) {
            report($exception);
            return $this->localFallback($customer, $exception->getMessage());
        }
    }

    private function localFallback(User $customer, string $error): array
    {
        $subscriptions = $customer->subscriptions()
            ->with('items')
            ->latest()
            ->get()
            ->map(fn ($subscription): array => [
                'id' => $subscription->stripe_id,
                'status' => $subscription->stripe_status,
                'plan_name' => 'Helmio subscription',
                'price_id' => $subscription->items->first()?->stripe_price
                    ?? $subscription->stripe_price,
                'amount' => null,
                'currency' => 'USD',
                'interval' => null,
                'interval_count' => 1,
                'quantity' => $subscription->quantity ?? 1,
                'created_at' => $subscription->created_at?->toIso8601String(),
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                'current_period_ends_at' => $subscription->ends_at?->toIso8601String(),
                'cancel_at_period_end' => $subscription->onGracePeriod(),
                'cancel_at' => $subscription->ends_at?->toIso8601String(),
                'canceled_at' => null,
                'ended_at' => $subscription->ends_at?->toIso8601String(),
            ])->all();

        return [
            'source' => 'local',
            'available' => false,
            'error' => $error,
            'refreshed_at' => now()->toIso8601String(),
            'stripe_customer_id' => $customer->stripe_id,
            'stripe_dashboard_url' => null,
            'delinquent' => false,
            'balance' => 0.0,
            'currency' => 'USD',
            'payment_method' => $customer->pm_type && $customer->pm_last_four
                ? ucfirst($customer->pm_type).' •••• '.$customer->pm_last_four
                : null,
            'subscriptions' => $subscriptions,
            'invoices' => [],
            'refunds' => [],
            'timeline' => [],
        ];
    }

    private function cacheKey(User $customer): string
    {
        return 'admin.customer-billing.v1.'.$customer->id;
    }

    private function date(mixed $timestamp): ?string
    {
        return $timestamp ? now()->setTimestamp((int) $timestamp)->toIso8601String() : null;
    }
}
