<?php

namespace Tests\Feature;

use Stripe\Webhook;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    public function test_test_mode_webhook_uses_test_signing_secret(): void
    {
        config()->set(
            'services.stripe.webhook_secrets.test',
            'whsec_test_helmio',
        );
        config()->set(
            'services.stripe.webhook_secrets.live',
            'whsec_live_helmio',
        );

        $payload = $this->payload(
            liveMode: false,
        );

        $this->postWebhook(
            $payload,
            'whsec_test_helmio',
        )->assertOk();
    }

    public function test_live_mode_webhook_uses_live_signing_secret(): void
    {
        config()->set(
            'services.stripe.webhook_secrets.test',
            'whsec_test_helmio',
        );
        config()->set(
            'services.stripe.webhook_secrets.live',
            'whsec_live_helmio',
        );

        $payload = $this->payload(
            liveMode: true,
        );

        $this->postWebhook(
            $payload,
            'whsec_live_helmio',
        )->assertOk();
    }

    public function test_webhook_with_wrong_mode_secret_is_rejected(): void
    {
        config()->set(
            'services.stripe.webhook_secrets.test',
            'whsec_test_helmio',
        );
        config()->set(
            'services.stripe.webhook_secrets.live',
            'whsec_live_helmio',
        );

        $payload = $this->payload(
            liveMode: false,
        );

        $this->postWebhook(
            $payload,
            'whsec_live_helmio',
        )->assertBadRequest();
    }

    private function payload(
        bool $liveMode,
    ): string {
        return (string) json_encode(
            [
                'id' => 'evt_helmio_test',
                'object' => 'event',
                'type' => 'helmio.webhook_test',
                'livemode' => $liveMode,
                'data' => [
                    'object' => [],
                ],
            ],
            JSON_THROW_ON_ERROR,
        );
    }

    private function postWebhook(
        string $payload,
        string $secret,
    ) {
        $signature = Webhook::generateTestHeaderString(
            [
                'payload' => $payload,
                'secret' => $secret,
            ],
        );

        return $this->call(
            'POST',
            '/stripe/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => $signature,
            ],
            $payload,
        );
    }
}
