<?php

namespace App\Services\MarketData;

use App\Models\Security;
use App\Services\Analytics\Performance\HistoricalPriceService;
use Carbon\CarbonInterface;

class HistoricalSecurityPriceImporter
{
    public function __construct(
        private readonly TwelveDataMarketDataService $marketData,
        private readonly HistoricalPriceService $historicalPriceService,
    ) {
    }

    public function import(
        Security $security,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
    ): array {
        $symbol = trim(
            (string) $security->symbol,
        );

        if ($symbol === '') {
            return [
                'security_id' =>
                    $security->id,

                'symbol' =>
                    null,

                'imported' =>
                    0,

                'status' =>
                    'skipped',

                'reason' =>
                    'Security has no symbol.',
            ];
        }

        /*
         * Money-market/cash-equivalent positions do not need
         * equity-style market-price history for portfolio valuation.
         */
        if (
            in_array(
                $security->security_type,
                [
                    'cash',
                ],
                true,
            )
        ) {
            return [
                'security_id' =>
                    $security->id,

                'symbol' =>
                    $symbol,

                'imported' =>
                    0,

                'status' =>
                    'skipped',

                'reason' =>
                    'Cash-equivalent security.',
            ];
        }

        $prices =
            $this->marketData
                ->historicalDailyPrices(
                    symbol:
                        $symbol,

                    startDate:
                        $startDate,

                    endDate:
                        $endDate,
                );

        foreach ($prices as $price) {
            $this->historicalPriceService
                ->store(
                    security:
                        $security,

                    date:
                        now()
                            ->parse(
                                $price['date'],
                            ),

                    closePrice:
                        $price['close'],

                    adjustedClosePrice:
                        $price['close'],

                    openPrice:
                        $price['open'],

                    highPrice:
                        $price['high'],

                    lowPrice:
                        $price['low'],

                    volume:
                        $price['volume'],

                    currency:
                        'USD',

                    source:
                        'twelve_data',

                    metadata: [
                        'symbol' =>
                            $symbol,

                        'provider' =>
                            'twelve_data',
                    ],
                );
        }

        return [
            'security_id' =>
                $security->id,

            'symbol' =>
                $symbol,

            'imported' =>
                $prices->count(),

            'status' =>
                'complete',
        ];
    }
}