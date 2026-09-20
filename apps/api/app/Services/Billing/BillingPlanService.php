<?php

namespace App\Services\Billing;

use App\Models\BillingSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class BillingPlanService
{
    public function amount(string $period): float
    {
        return (float) $this->value(
            "{$period}_amount",
            $period === 'annual' ? '199.95' : '19.95',
        );
    }

    public function priceId(string $period): ?string
    {
        $value = $this->value(
            "{$period}_price_id",
            config("services.stripe.prices.{$period}"),
        );

        return filled($value) ? (string) $value : null;
    }

    public function update(array $values): void
    {
        foreach ($values as $key => $value) {
            BillingSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        Cache::forget('helmio.billing-settings');
    }

    private function value(string $key, mixed $fallback = null): mixed
    {
        if (! Schema::hasTable('billing_settings')) {
            return $fallback;
        }

        $settings = Cache::remember(
            'helmio.billing-settings',
            now()->addMinutes(10),
            fn () => BillingSetting::query()->pluck('value', 'key')->all(),
        );

        return $settings[$key] ?? $fallback;
    }
}
