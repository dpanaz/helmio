<?php

namespace Tests\Feature;

use App\Models\Security;
use App\Services\Analytics\Performance\HistoricalPriceService;
use App\Services\MarketData\HistoricalSecurityPriceImporter;
use App\Services\MarketData\TwelveDataInvalidSymbol;
use App\Services\MarketData\TwelveDataMarketDataService;
use Carbon\CarbonImmutable;
use Mockery;
use Tests\TestCase;

class HistoricalSecurityPriceImporterInvalidSymbolTest extends TestCase
{
    public function test_invalid_symbol_is_skipped_without_storing_prices(): void
    {
        $marketData = Mockery::mock(TwelveDataMarketDataService::class);
        $marketData->shouldReceive('historicalDailyPrices')->once()
            ->andThrow(new TwelveDataInvalidSymbol('Invalid symbol.'));
        $prices = Mockery::mock(HistoricalPriceService::class);
        $prices->shouldNotReceive('store');
        $security = new Security;
        $security->id = 1;
        $security->symbol = 'INVALID';
        $security->security_type = 'stock';

        $result = (new HistoricalSecurityPriceImporter($marketData, $prices))->import(
            $security, CarbonImmutable::parse('2026-09-01'), CarbonImmutable::parse('2026-09-26'),
        );

        $this->assertSame('skipped_invalid_symbol', $result['status']);
        $this->assertSame(0, $result['imported']);
    }
}
