<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class VerifyStripeWebhookSignature
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $payload = $request->getContent();
        $decoded = json_decode(
            $payload,
            true,
        );

        if (
            ! is_array($decoded)
            || ! array_key_exists('livemode', $decoded)
            || ! is_bool($decoded['livemode'])
        ) {
            abort(
                Response::HTTP_BAD_REQUEST,
                'Invalid Stripe webhook payload.',
            );
        }

        $mode = $decoded['livemode']
            ? 'live'
            : 'test';

        $secret = config(
            "services.stripe.webhook_secrets.{$mode}",
        );

        if (! is_string($secret) || $secret === '') {
            abort(
                Response::HTTP_SERVICE_UNAVAILABLE,
                "Stripe {$mode}-mode webhook secret is not configured.",
            );
        }

        try {
            Webhook::constructEvent(
                $payload,
                (string) $request->header(
                    'Stripe-Signature',
                    '',
                ),
                $secret,
            );
        } catch (
            UnexpectedValueException
            | SignatureVerificationException
        ) {
            abort(
                Response::HTTP_BAD_REQUEST,
                'Invalid Stripe webhook signature.',
            );
        }

        return $next($request);
    }
}
