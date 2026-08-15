<?php

namespace App\Services\MarketData;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AlphaVantageFundDataService
{
    /**
     * Fetch an ETF profile and normalize its holdings / sector data.
     *
     * Alpha Vantage function:
     * ETF_PROFILE
     *
     * @return array<string, mixed>
     */
    public function etfProfile(
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

        $data = $this->request(
            parameters: [
                'function' =>
                    'ETF_PROFILE',

                'symbol' =>
                    $symbol,
            ],
            context:
                "ETF profile for {$symbol}",
        );

        $holdings =
            $this->normalizeHoldings(
                $data['holdings']
                ?? $data['top_holdings']
                ?? [],
            );

        $sectors =
            $this->normalizeSectors(
                $data['sectors']
                ?? $data['sector_allocation']
                ?? [],
            );

        return [
            'symbol' =>
                $symbol,

            'net_assets' =>
                $this->nullableFloat(
                    $data['net_assets']
                    ?? null,
                ),

            'expense_ratio' =>
                $this->nullableRate(
                    $data['net_expense_ratio']
                    ?? $data['expense_ratio']
                    ?? null,
                ),

            'portfolio_turnover' =>
                $this->nullableRate(
                    $data['portfolio_turnover']
                    ?? null,
                ),

            'dividend_yield' =>
                $this->nullableRate(
                    $data['dividend_yield']
                    ?? null,
                ),

            'inception_date' =>
                $this->nullableString(
                    $data['inception_date']
                    ?? null,
                ),

            'leveraged' =>
                $this->nullableBoolean(
                    $data['leveraged']
                    ?? null,
                ),

            'holdings' =>
                $holdings,

            'sectors' =>
                $sectors,

            'holding_count' =>
                $holdings->count(),

            'holding_weight_coverage' =>
                round(
                    (float) $holdings->sum(
                        'weight',
                    ),
                    10,
                ),

            'raw' =>
                $data,
        ];
    }

    /**
     * Convenience wrapper used by fund-import services.
     *
     * For the first Helmio implementation, Alpha Vantage look-through
     * supports ETFs only. Mutual-fund providers can implement the same
     * normalized contract later.
     *
     * @return array<string, mixed>
     */
    public function fundComposition(
        string $symbol,
        string $securityType,
    ): array {
        $securityType = strtolower(
            trim($securityType),
        );

        if ($securityType !== 'etf') {
            throw new RuntimeException(
                sprintf(
                    'Alpha Vantage fund composition currently supports ETFs only; received security type "%s".',
                    $securityType,
                ),
            );
        }

        return $this->etfProfile(
            $symbol,
        );
    }

    /**
     * @param array<int, mixed> $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function normalizeHoldings(
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
                        ?? null,
                    )
                    && $this->normalizeWeight(
                        $row['weight']
                        ?? $row['allocation']
                        ?? null,
                    ) !== null,
            )
            ->map(
                function (
                    array $row,
                ): array {
                    $weight =
                        $this->normalizeWeight(
                            $row['weight']
                            ?? $row['allocation']
                            ?? null,
                        );

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
                            $this->nullableString(
                                $row['description']
                                ?? $row['name']
                                ?? null,
                            ),

                        'weight' =>
                            $weight,

                        'metadata' => [
                            'description' =>
                                $this->nullableString(
                                    $row['description']
                                    ?? null,
                                ),
                        ],
                    ];
                },
            )
            ->filter(
                fn (
                    array $row,
                ): bool =>
                    $row['weight'] !== null
                    && $row['weight'] > 0,
            )
            ->sortByDesc(
                'weight',
            )
            ->values();
    }

    /**
     * @param array<int|string, mixed> $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function normalizeSectors(
        array $rows,
    ): Collection {
        /*
         * Alpha Vantage responses may represent sector allocation as
         * an array of rows or, depending on provider evolution, an
         * associative object. Normalize both forms.
         */
        if (
            ! array_is_list($rows)
        ) {
            $rows = collect(
                $rows,
            )
                ->map(
                    fn (
                        mixed $weight,
                        string $sector,
                    ): array => [
                        'sector' =>
                            $sector,

                        'weight' =>
                            $weight,
                    ],
                )
                ->values()
                ->all();
        }

        return collect(
            $rows,
        )
            ->filter(
                fn (
                    mixed $row,
                ): bool =>
                    is_array($row)
                    && filled(
                        $row['sector']
                        ?? $row['name']
                        ?? null,
                    )
                    && $this->normalizeWeight(
                        $row['weight']
                        ?? $row['allocation']
                        ?? null,
                    ) !== null,
            )
            ->map(
                function (
                    array $row,
                ): array {
                    return [
                        'sector' =>
                            trim(
                                (string) (
                                    $row['sector']
                                    ?? $row['name']
                                )
                            ),

                        'weight' =>
                            $this->normalizeWeight(
                                $row['weight']
                                ?? $row['allocation']
                                ?? null,
                            ),
                    ];
                },
            )
            ->filter(
                fn (
                    array $row,
                ): bool =>
                    $row['weight'] !== null
                    && $row['weight'] > 0,
            )
            ->sortByDesc(
                'weight',
            )
            ->values();
    }

    /**
     * Centralized Alpha Vantage request handling.
     *
     * @return array<string, mixed>
     */
    private function request(
        array $parameters,
        string $context,
    ): array {
        $apiKey = (string) config(
            'services.alpha_vantage.key',
        );

        $baseUrl = rtrim(
            (string) config(
                'services.alpha_vantage.base_url',
                'https://www.alphavantage.co/query',
            ),
            '/',
        );

        if ($apiKey === '') {
            throw new RuntimeException(
                'Alpha Vantage API key is not configured.',
            );
        }

        $response =
            Http::timeout(30)
                ->retry(
                    3,
                    750,
                    throw: false,
                )
                ->get(
                    $baseUrl,
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
                "Alpha Vantage returned invalid JSON for {$context}.",
            );
        }

        /*
         * Alpha Vantage can return HTTP 200 with an error or
         * informational payload, so inspect these fields explicitly.
         */
        $errorMessage =
            $data['Error Message']
            ?? null;

        if (
            is_string($errorMessage)
            && $errorMessage !== ''
        ) {
            throw new RuntimeException(
                sprintf(
                    'Alpha Vantage returned an error for %s: %s',
                    $context,
                    $errorMessage,
                ),
            );
        }

        $note =
            $data['Note']
            ?? null;

        if (
            is_string($note)
            && $note !== ''
        ) {
            throw new RuntimeException(
                sprintf(
                    'Alpha Vantage rate limit reached for %s: %s',
                    $context,
                    $note,
                ),
            );
        }

        $information =
            $data['Information']
            ?? null;

        if (
            is_string($information)
            && $information !== ''
        ) {
            throw new RuntimeException(
                sprintf(
                    'Alpha Vantage returned an informational response for %s: %s',
                    $context,
                    $information,
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

        throw new RuntimeException(
            sprintf(
                'Alpha Vantage request failed for %s with HTTP %d: %s',
                $context,
                $response->status(),
                $response->body(),
            ),
        );
    }

    /**
     * Normalize provider percentage-like values into Helmio decimal rates.
     *
     * Examples:
     * "7.12%" -> 0.0712
     * "0.0712" -> 0.0712
     * 0.0712 -> 0.0712
     * 7.12 -> 0.0712
     */
    private function normalizeWeight(
        mixed $value,
    ): ?float {
        return $this->normalizeRate(
            $value,
        );
    }

    private function nullableRate(
        mixed $value,
    ): ?float {
        return $this->normalizeRate(
            $value,
        );
    }

    private function normalizeRate(
        mixed $value,
    ): ?float {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim(
                $value,
            );

            if ($value === '') {
                return null;
            }

            $hasPercentSign =
                str_ends_with(
                    $value,
                    '%',
                );

            $value = str_replace(
                [
                    '%',
                    ',',
                ],
                '',
                $value,
            );

            if (! is_numeric($value)) {
                return null;
            }

            $number =
                (float) $value;

            if ($hasPercentSign) {
                return max(
                    0,
                    $number / 100,
                );
            }

            return $number > 1
                ? max(
                    0,
                    $number / 100,
                )
                : max(
                    0,
                    $number,
                );
        }

        if (! is_numeric($value)) {
            return null;
        }

        $number =
            (float) $value;

        return $number > 1
            ? max(
                0,
                $number / 100,
            )
            : max(
                0,
                $number,
            );
    }

    private function nullableFloat(
        mixed $value,
    ): ?float {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(
                [
                    ',',
                    '$',
                ],
                '',
                trim($value),
            );
        }

        return is_numeric(
            $value,
        )
            ? (float) $value
            : null;
    }

    private function nullableString(
        mixed $value,
    ): ?string {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim(
            (string) $value,
        );

        return $value !== ''
            ? $value
            : null;
    }

    private function nullableBoolean(
        mixed $value,
    ): ?bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value !== 0;
        }

        if (is_string($value)) {
            return match (
                strtolower(
                    trim($value)
                )
            ) {
                'true',
                'yes',
                'y',
                '1' =>
                    true,

                'false',
                'no',
                'n',
                '0' =>
                    false,

                default =>
                    null,
            };
        }

        return null;
    }
}