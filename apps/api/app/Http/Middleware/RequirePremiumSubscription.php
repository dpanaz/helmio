<?php

namespace App\Http\Middleware;

use App\Services\Billing\SubscriptionAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePremiumSubscription
{
    public function __construct(
        private readonly SubscriptionAccessService $accessService,
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $user = $request->user();

        if (
            $user === null
            || ! $this->accessService
                ->hasPremiumAccess($user)
        ) {
            return redirect()
                ->route('billing.pricing')
                ->with(
                    'subscription_required',
                    'Start your Helmio trial before connecting an investment account.',
                );
        }

        return $next($request);
    }
}