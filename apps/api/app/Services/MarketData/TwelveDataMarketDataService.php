<?php

namespace App\Services\MarketData;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TwelveDataMarketDataService
{
    public function historicalDailyPrices(
        string $symbol,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
    ): Collection {
        $apiKey = (string) config(
            'services.twelve_data.key',
        );

        $baseUrl = rtrim(
            (string) config(
                'services.twelve_data.base_url',
                'https://api.twelvedata.com',
            ),
            '/',
        );

        if ($apiKey === '') {
            throw new RuntimeException(
                'Twelve Data API key is not configured.',
            );
        }

        $response = Http::timeout(30)
            ->retry(
                3,
                500,
            )
            ->get(
                $baseUrl.'/time_series',
                [
                    'symbol' =>
                        $symbol,

                    'interval' =>
                        '1day',

                    'start_date' =>
                        $startDate->toDateString(),

                    'end_date' =>
                        $endDate->toDateString(),

                    'outputsize' =>
                        5000,

                    'format' =>
                        'JSON',

                    'apikey' =>
                        $apiKey,
                ],
            );

        if (! $response->successful()) {
            throw new RuntimeException(
                sprintf(
                    'Twelve Data request failed for %s with HTTP %d: %s',
                    $symbol,
                    $response->status(),
                    $response->body(),
                ),
            );
        }

        $data = $response->json();

        if (
            ($data['status'] ?? null)
            === 'error'
        ) {
            throw new RuntimeException(
                sprintf(
                    'Twelve Data returned an error for %s: %s',
                    $symbol,
                    $data['message']
                        ?? 'Unknown error',
                ),
            );
        }

        $values =
            $data['values']
            ?? [];

        return collect(
            $values,
        )
            ->filter(
                fn (
                    mixed $value,
                ): bool =>
                    is_array($value)
                    && isset(
                        $value['datetime'],
                        $value['close'],
                    ),
            )
            ->map(
                fn (
                    array $value,
                ): array => [
                    'date' =>
                        $value['datetime'],

                    'open' =>
                        $this->nullableFloat(
                            $value['open']
                            ?? null,
                        ),

                    'high' =>
                        $this->nullableFloat(
                            $value['high']
                            ?? null,
                        ),

                    'low' =>
                        $this->nullableFloat(
                            $value['low']
                            ?? null,
                        ),

                    'close' =>
                        (float) $value['close'],

                    'volume' =>
                        $this->nullableInt(
                            $value['volume']
                            ?? null,
                        ),
                ],
            )
            ->sortBy(
                'date',
            )
            ->values();
    }

    private function nullableFloat(
        mixed $value,
    ): ?float {
        return is_numeric(
            $value,
        )
            ? (float) $value
            : null;
    }

    private function nullableInt(
        mixed $value,
    ): ?int {
        return is_numeric(
            $value,
        )
            ? (int) $value
            : null;
    }
}