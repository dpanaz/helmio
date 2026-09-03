<?php

namespace App\Http\Controllers;

use App\Services\Billing\SubscriptionAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Cashier\Checkout;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends Controller
{
    public function index(
        Request $request,
        SubscriptionAccessService $accessService,
    ): View {
        $user = $request->user();

        return view('billing.index', [
            'billingStatus' =>
                $accessService->status($user),

            'subscription' =>
                $user->subscription('default'),

            'invoices' =>
                $user->stripe_id
                    ? $user->invoices()
                    : collect(),
        ]);
    }

    public function status(
        Request $request,
        SubscriptionAccessService $accessService,
    ): JsonResponse {
        return response()->json(
            $accessService->status(
                $request->user(),
            ),
        );
    }

    public function pricing(
        Request $request,
        SubscriptionAccessService $accessService,
    ): View {
        $user = $request->user();

        $billingStatus = $user
            ? $accessService->status($user)
            : [
                'has_access' => false,
                'subscribed' => false,
                'on_trial' => false,
                'on_grace_period' => false,
            ];

        return view('billing.pricing', [
            'billingStatus' =>
                $billingStatus,

            'monthlyPriceId' => config(
                'services.stripe.prices.monthly',
            ),

            'annualPriceId' => config(
                'services.stripe.prices.annual',
            ),

            'trialDays' => (int) config(
                'services.stripe.trial_days',
                14,
            ),
        ]);
    }

    public function checkout(
        Request $request,
        SubscriptionAccessService $accessService,
    ): Checkout|RedirectResponse {
        $validated = $request->validate([
            'billing_period' => [
                'required',
                'in:monthly,annual',
            ],
        ]);

        $user = $request->user();

        if ($accessService->hasPremiumAccess($user)) {
            return redirect()
                ->route('billing.index')
                ->with(
                    'success',
                    'Your Helmio subscription is already active.',
                );
        }

        $priceId = config(
            'services.stripe.prices.'
            .$validated['billing_period'],
        );

        abort_if(
            blank($priceId),
            500,
            'The selected Stripe price is not configured.',
        );

        return $user
            ->newSubscription(
                'default',
                $priceId,
            )
            ->trialDays(
                (int) config(
                    'services.stripe.trial_days',
                    14,
                ),
            )
            ->checkout([
                'success_url' =>
                    route('billing.success')
                    .'?session_id={CHECKOUT_SESSION_ID}',

                'cancel_url' =>
                    route('billing.pricing'),
            ]);
    }

    public function success(
        Request $request,
    ): View {
        return view('billing.success', [
            'sessionId' =>
                $request
                    ->string('session_id')
                    ->toString(),
        ]);
    }

    public function portal(
        Request $request,
    ): RedirectResponse {
        return $request
            ->user()
            ->redirectToBillingPortal(
                route('billing.index'),
            );
    }

    public function downloadInvoice(
        Request $request,
        string $invoice,
    ): Response {
        return $request
            ->user()
            ->downloadInvoice(
                $invoice,
                [
                    'vendor' =>
                        'Helmio',

                    'product' =>
                        'Helmio Premium',

                    'email' =>
                        config('mail.from.address'),

                    'url' =>
                        config('app.url'),
                ],
            );
    }
}