<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Marketing\MarketingConversionService;
use Laravel\Cashier\Events\WebhookHandled;

class RecordStripePurchase
{
    public function __construct(
        private readonly MarketingConversionService $conversions,
    ) {
    }

    public function handle(
        WebhookHandled $event,
    ): void {
        $payload = $event->payload;

        if (
            data_get($payload, 'type')
            !== 'invoice.payment_succeeded'
        ) {
            return;
        }

        $invoice = data_get(
            $payload,
            'data.object',
            [],
        );

        $invoiceId = data_get(
            $invoice,
            'id',
        );

        $stripeCustomerId = data_get(
            $invoice,
            'customer',
        );

        $amountPaidInCents = (int) data_get(
            $invoice,
            'amount_paid',
            0,
        );

        /*
         * Stripe may generate a $0 invoice when a trial
         * begins. That is not a paid conversion.
         */
        if (
            ! is_string($invoiceId)
            || $invoiceId === ''
            || ! is_string($stripeCustomerId)
            || $stripeCustomerId === ''
            || $amountPaidInCents <= 0
        ) {
            return;
        }

        $user = User::query()
            ->where(
                'stripe_id',
                $stripeCustomerId,
            )
            ->first();

        if (! $user) {
            return;
        }

        $this->conversions->record(
            type: 'PURCHASE',
            user: $user,
            value: $amountPaidInCents / 100,
            metadata: [
                'stripe_invoice_id' =>
                    $invoiceId,

                'stripe_event_id' =>
                    data_get($payload, 'id'),

                'billing_reason' =>
                    data_get(
                        $invoice,
                        'billing_reason',
                    ),

                'currency' =>
                    strtoupper(
                        (string) data_get(
                            $invoice,
                            'currency',
                            'usd',
                        ),
                    ),
            ],
            conversionId:
                'stripe_invoice_'.$invoiceId,
        );
    }
}