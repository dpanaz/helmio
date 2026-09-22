<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Middleware\VerifyStripeWebhookSignature;
use Laravel\Cashier\Http\Controllers\WebhookController;

class StripeWebhookController extends WebhookController
{
    public function __construct()
    {
        $this->middleware(
            VerifyStripeWebhookSignature::class,
        );
    }
}
