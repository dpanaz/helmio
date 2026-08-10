<?php

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
            | External Webhooks
            |--------------------------------------------------------------------------
            |
            | SnapTrade sends POST requests from outside the Helmio application
            | and therefore cannot provide a Laravel CSRF token.
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