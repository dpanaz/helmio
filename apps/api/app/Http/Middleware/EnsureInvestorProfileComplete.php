<?php

namespace App\Http\Middleware;

use App\Http\Controllers\OnboardingController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInvestorProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        return app(OnboardingController::class)->prerequisiteRedirect($request->user(), false)
            ?? $next($request);
    }
}
