<?php

namespace App\Services\MarketData;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\Response;
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
        $data = $this->get(
            endpoint: '/time_series',
            parameters: [
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
            ],
            context:
                "historical prices for {$symbol}",
        );

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

    /**
     * Retrieve ETF composition / top holdings.
     *
     * Twelve Data endpoint:
     * /etfs/world/composition
     *
     * @return array<string, mixed>
     */
    public function etfComposition(
        string $symbol,
    ): array {
        $symbol = strtoupper(
            trim($symbol),
        );

        if ($symbol === '') {
            throw new RuntimeException(
                'ETF symbol is required.',
            );
        }

        $data = $this->get(
            endpoint:
                '/etfs/world/composition',

            parameters: [
                'symbol' =>
                    $symbol,

                'format' =>
                    'JSON',
            ],

            context:
                "ETF composition for {$symbol}",
        );

        $composition =
            data_get(
                $data,
                'etf.composition',
            );

        if (! is_array($composition)) {
            throw new RuntimeException(
                "Twelve Data returned no ETF composition for {$symbol}.",
            );
        }

        return [
            'symbol' =>
                $symbol,

            'top_holdings' =>
                $this->normalizeTopHoldings(
                    $composition[
                        'top_holdings'
                    ] ?? [],
                ),

            'sectors' =>
                collect(
                    $composition[
                        'major_market_sectors'
                    ] ?? [],
                )
                    ->filter(
                        fn (
                            mixed $row,
                        ): bool =>
                            is_array($row)
                            && filled(
                                $row['sector']
                                ?? null
                            ),
                    )
                    ->map(
                        fn (
                            array $row,
                        ): array => [
                            'sector' =>
                                $row['sector'],

                            'weight' =>
                                $this->nullableFloat(
                                    $row['weight']
                                    ?? null,
                                ),
                        ],
                    )
                    ->values(),

            'countries' =>
                collect(
                    $composition[
                        'country_allocation'
                    ] ?? [],
                )
                    ->filter(
                        fn (
                            mixed $row,
                        ): bool =>
                            is_array($row)
                            && filled(
                                $row['country']
                                ?? null
                            ),
                    )
                    ->map(
                        fn (
                            array $row,
                        ): array => [
                            'country' =>
                                $row['country'],

                            'weight' =>
                                $this->nullableFloat(
                                    $row['allocation']
                                    ?? null,
                                ),
                        ],
                    )
                    ->values(),

            'asset_allocation' =>
                $composition[
                    'asset_allocation'
                ] ?? [],

            'raw' =>
                $composition,
        ];
    }

    /**
     * Retrieve mutual-fund composition.
     *
     * Twelve Data exposes fund composition within its
     * mutual-fund data family.
     *
     * @return array<string, mixed>
     */
    public function mutualFundComposition(
        string $symbol,
    ): array {
        $symbol = trim(
            $symbol,
        );

        if ($symbol === '') {
            throw new RuntimeException(
                'Mutual fund symbol is required.',
            );
        }

        $data = $this->get(
            endpoint:
                '/mutual_funds/world/composition',

            parameters: [
                'symbol' =>
                    $symbol,

                'format' =>
                    'JSON',
            ],

            context:
                "mutual fund composition for {$symbol}",
        );

        $composition =
            data_get(
                $data,
                'mutual_fund.composition',
            );

        if (! is_array($composition)) {
            throw new RuntimeException(
                "Twelve Data returned no mutual fund composition for {$symbol}.",
            );
        }

        return [
            'symbol' =>
                $symbol,

            'top_holdings' =>
                $this->normalizeTopHoldings(
                    $composition[
                        'top_holdings'
                    ] ?? [],
                ),

            'sectors' =>
                collect(
                    $composition[
                        'major_market_sectors'
                    ] ?? [],
                )
                    ->filter(
                        fn (
                            mixed $row,
                        ): bool =>
                            is_array($row)
                            && filled(
                                $row['sector']
                                ?? null
                            ),
                    )
                    ->map(
                        fn (
                            array $row,
                        ): array => [
                            'sector' =>
                                $row['sector'],

                            'weight' =>
                                $this->nullableFloat(
                                    $row['weight']
                                    ?? null,
                                ),
                        ],
                    )
                    ->values(),

            'countries' =>
                collect(
                    $composition[
                        'country_allocation'
                    ] ?? [],
                )
                    ->filter(
                        fn (
                            mixed $row,
                        ): bool =>
                            is_array($row)
                            && filled(
                                $row['country']
                                ?? null
                            ),
                    )
                    ->map(
                        fn (
                            array $row,
                        ): array => [
                            'country' =>
                                $row['country'],

                            'weight' =>
                                $this->nullableFloat(
                                    $row['allocation']
                                    ?? null,
                                ),
                        ],
                    )
                    ->values(),

            'asset_allocation' =>
                $composition[
                    'asset_allocation'
                ] ?? [],

            'raw' =>
                $composition,
        ];
    }

    /**
     * Retrieve composition based on Helmio's security type.
     *
     * @return array<string, mixed>
     */
    public function fundComposition(
        string $symbol,
        string $securityType,
    ): array {
        return match (
            strtolower(
                trim(
                    $securityType
                )
            )
        ) {
            'etf' =>
                $this->etfComposition(
                    $symbol
                ),

            'mutual_fund' =>
                $this->mutualFundComposition(
                    $symbol
                ),

            default =>
                throw new RuntimeException(
                    sprintf(
                        'Fund composition is not supported for security type "%s".',
                        $securityType,
                    ),
                ),
        };
    }

    /**
     * @param array<int, mixed> $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function normalizeTopHoldings(
        array $rows,
    ): Collection {
        return collect(
            $rows,
        )
            ->filter(
                fn (
                    mixed $row,
                ): bool =>
                    is_array($row)
                    && filled(
                        $row['symbol']
                        ?? null
                    )
                    && is_numeric(
                        $row['weight']
                        ?? null
                    ),
            )
            ->map(
                function (
                    array $row,
                ): array {
                    return [
                        'symbol' =>
                            strtoupper(
                                trim(
                                    (string) $row[
                                        'symbol'
                                    ]
                                )
                            ),

                        'name' =>
                            filled(
                                $row['name']
                                ?? null
                            )
                                ? trim(
                                    (string) $row[
                                        'name'
                                    ]
                                )
                                : null,

                        'exchange' =>
                            filled(
                                $row['exchange']
                                ?? null
                            )
                                ? trim(
                                    (string) $row[
                                        'exchange'
                                    ]
                                )
                                : null,

                        'mic_code' =>
                            filled(
                                $row['mic_code']
                                ?? null
                            )
                                ? trim(
                                    (string) $row[
                                        'mic_code'
                                    ]
                                )
                                : null,

                        'weight' =>
                            max(
                                0,
                                (float) $row[
                                    'weight'
                                ],
                            ),
                    ];
                },
            )
            ->filter(
                fn (
                    array $row,
                ): bool =>
                    $row['weight'] > 0,
            )
            ->sortByDesc(
                'weight',
            )
            ->values();
    }

    /**
     * Centralized Twelve Data request handling.
     *
     * @return array<string, mixed>
     */
    private function get(
        string $endpoint,
        array $parameters,
        string $context,
    ): array {
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

        $response =
            Http::timeout(30)
                ->retry(
                    3,
                    500,
                )
                ->get(
                    $baseUrl
                    . '/'
                    . ltrim(
                        $endpoint,
                        '/'
                    ),
                    [
                        ...$parameters,

                        'apikey' =>
                            $apiKey,
                    ],
                );

        $this->assertSuccessfulResponse(
            response:
                $response,

            context:
                $context,
        );

        $data =
            $response->json();

        if (! is_array($data)) {
            throw new RuntimeException(
                "Twelve Data returned an invalid JSON response for {$context}.",
            );
        }

        if (
            ($data['status'] ?? null)
            === 'error'
        ) {
            throw new RuntimeException(
                sprintf(
                    'Twelve Data returned an error for %s: %s',
                    $context,
                    $data['message']
                        ?? 'Unknown error',
                ),
            );
        }

        return $data;
    }

    private function assertSuccessfulResponse(
        Response $response,
        string $context,
    ): void {
        if ($response->successful()) {
            return;
        }

        $message =
            data_get(
                $response->json(),
                'message',
            );

        if (
            in_array(
                $response->status(),
                [
                    401,
                    402,
                    403,
                ],
                true,
            )
        ) {
            throw new RuntimeException(
                sprintf(
                    'Twelve Data access denied for %s (HTTP %d). This endpoint may require a higher Twelve Data plan. %s',
                    $context,
                    $response->status(),
                    $message
                        ?: $response->body(),
                ),
            );
        }

        throw new RuntimeException(
            sprintf(
                'Twelve Data request failed for %s with HTTP %d: %s',
                $context,
                $response->status(),
                $response->body(),
            ),
        );
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