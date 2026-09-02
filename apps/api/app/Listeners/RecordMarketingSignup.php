<?php

namespace App\Listeners;

use App\Services\Marketing\MarketingConversionService;
use Illuminate\Auth\Events\Registered;

class RecordMarketingSignup
{
    public function __construct(
        private readonly MarketingConversionService $conversions,
    ) {
    }

    public function handle(Registered $event): void
    {
        $this->conversions->record(
            type: 'SIGN_UP',
            user: $event->user,
        );
    }
}