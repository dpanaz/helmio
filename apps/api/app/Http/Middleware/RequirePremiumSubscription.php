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
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Complete checkout to unlock your Helmio results.',
                    'code' => 'subscription_required',
                ], 402);
            }
            return redirect()
                ->route('billing.pricing')
                ->with(
                    'subscription_required',
                    'Complete checkout to unlock your Helmio results.',
                );
        }

        return $next($request);
    }
}
