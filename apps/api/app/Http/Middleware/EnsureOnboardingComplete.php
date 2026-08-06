<?php

namespace App\Http\Middleware;

use App\Http\Controllers\OnboardingController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function __construct(
        private readonly OnboardingController $onboarding,
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (! $this->onboarding->isComplete($user)) {
            return $this->onboarding
                ->redirectToNextStep($user);
        }

        return $next($request);
    }
}
