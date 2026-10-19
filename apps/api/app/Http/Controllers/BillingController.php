<?php

namespace App\Http\Controllers;

use App\Services\Billing\SubscriptionAccessService;
use App\Services\Billing\BillingPlanService;
use App\Services\Billing\EmbeddedSubscriptionCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
        BillingPlanService $plans,
        OnboardingController $onboarding,
    ): View|RedirectResponse {
        $user = $request->user();

        if ($user && ! $accessService->hasPremiumAccess($user)) {
            $next = $onboarding->redirectToNextStep($user);
            if ($next->getTargetUrl() !== route('billing.pricing')) {
                return $next;
            }
        }

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

            'monthlyPriceId' => $plans->priceId('monthly'),
            'annualPriceId' => $plans->priceId('annual'),
            'monthlyAmount' => $plans->amount('monthly'),
            'annualAmount' => $plans->amount('annual'),

            'trialDays' => (int) config(
                'services.stripe.trial_days',
                14,
            ),
        ]);
    }

    public function checkout(
        Request $request,
        SubscriptionAccessService $accessService,
        BillingPlanService $plans,
        EmbeddedSubscriptionCheckout $checkout,
        OnboardingController $onboarding,
    ): View|RedirectResponse {
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

        if ($redirect = $onboarding->prerequisiteRedirect($user, false)) {
            return $redirect;
        }
        // Do not collect payment until the customer has linked an account.
        $next = $onboarding->redirectToNextStep($user);
        if ($next->getTargetUrl() !== route('billing.pricing')) {
            return $next;
        }

        $priceId = $plans->priceId($validated['billing_period']);

        abort_if(
            blank($priceId),
            500,
            'The selected Stripe price is not configured.',
        );

        abort_if(blank(config('services.stripe.key')), 503, 'Payment is temporarily unavailable. Please try again later.');

        return view('billing.checkout', [
            'clientSecret' => $checkout->create($user, $priceId)->asStripeCheckoutSession()->client_secret,
            'stripeKey' => config('services.stripe.key'),
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
