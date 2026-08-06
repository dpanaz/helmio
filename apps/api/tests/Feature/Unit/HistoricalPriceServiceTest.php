<?php

namespace Tests\Feature;

use App\Models\Security;
use App\Models\SecurityPrice;
use App\Services\Analytics\Performance\HistoricalPriceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalPriceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_latest_price_on_or_before_date(): void
    {
        $security = Security::query()->create([
    'symbol' => 'TEST',
    'name' => 'Test Security',
    'security_type' => 'stock',
    'asset_class' => 'equity',
    'sector' => 'technology',
    'category' => 'large_cap',
    'expense_ratio' => 0,
    'last_price' => 100,
    'price_as_of' => '2026-08-01',
    'metadata' => [],
]);

        SecurityPrice::query()->create([
            'security_id' => $security->id,
            'price_date' => '2026-08-01',
            'close_price' => 100,
            'adjusted_close_price' => 100,
            'currency' => 'USD',
            'source' => 'test',
        ]);

        SecurityPrice::query()->create([
            'security_id' => $security->id,
            'price_date' => '2026-08-04',
            'close_price' => 105,
            'adjusted_close_price' => 105,
            'currency' => 'USD',
            'source' => 'test',
        ]);

        $service = app(HistoricalPriceService::class);

        $price = $service->priceOnOrBefore(
            security: $security,
            date: Carbon::parse('2026-08-03'),
        );

        $this->assertNotNull($price);
        $this->assertSame(
            '2026-08-01',
            $price->price_date->format('Y-m-d')
        );

        $this->assertSame(
            100.0,
            $price->performance_price
        );
    }
}