<?php

use App\Http\Middleware\CaptureMarketingAttribution;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\RequirePremiumSubscription;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(
    basePath: dirname(__DIR__),
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(
        function (Middleware $middleware): void {
            $middleware->trustProxies(
                at: '*',
            );

            /*
            |--------------------------------------------------------------------------
            | Marketing Attribution
            |--------------------------------------------------------------------------
            |
            | Capture campaign parameters and Reddit click IDs on web requests.
            | This must run within the web middleware group so sessions and
            | cookies are available.
            |
            */

            $middleware->web(
                append: [
                    CaptureMarketingAttribution::class,
                ],
            );

            /*
            |--------------------------------------------------------------------------
            | External Webhooks
            |--------------------------------------------------------------------------
            |
            | SnapTrade and Stripe send requests from outside Helmio and
            | therefore cannot provide a Laravel CSRF token.
            |
            */

            $middleware->preventRequestForgery(
                except: [
                    'webhooks/snaptrade',
                    'stripe/*',
                ],
            );

            $middleware->alias([
                'subscribed' =>
                    RequirePremiumSubscription::class,

                'onboarding.complete' =>
                    EnsureOnboardingComplete::class,
            ]);
        },
    )
    ->withExceptions(
        function (Exceptions $exceptions): void {
            $exceptions->shouldRenderJsonWhen(
                fn (Request $request): bool =>
                    $request->is('api/*'),
            );
        },
    )
    ->create();