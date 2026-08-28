<?php

namespace App\Services\MarketData;

use App\Models\Holding;
use App\Models\Security;
use App\Models\SecurityPrice;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class SecurityPriceResolver
{
    public function resolve(
        Security $security,
        bool $refreshIfMissing = true,
    ): float {
        $price =
            $this->priceFromSecurity(
                $security
            );

        if ($price > 0) {
            return $price;
        }

        $price =
            $this->priceFromHolding(
                $security
            );

        if ($price > 0) {
            $this->persistResolvedPrice(
                $security,
                $price
            );

            return $price;
        }

        $price =
            $this->priceFromHistory(
                $security
            );

        if ($price > 0) {
            $this->persistResolvedPrice(
                $security,
                $price
            );

            return $price;
        }

        if (! $refreshIfMissing) {
            return 0.0;
        }

        $remote =
            $this->priceFromAlphaVantage(
                $security
            );

        $price =
            (float) (
                $remote['price']
                ?? 0
            );

        if ($price > 0) {
            $this->persistResolvedPrice(
                security: $security,
                price: $price,
                asOf: $remote['as_of'] ?? null,
            );

            return $price;
        }

        return 0.0;
    }

    private function priceFromSecurity(
        Security $security,
    ): float {
        foreach (
            [
                'last_price',
                'price',
                'current_price',
                'latest_price',
            ]
            as $field
        ) {
            $value =
                data_get(
                    $security,
                    $field
                );

            if (
                $value !== null
                && (float) $value > 0
            ) {
                return (float) $value;
            }
        }

        return 0.0;
    }

    private function priceFromHolding(
        Security $security,
    ): float {
        $holding = Holding::query()
            ->where(
                'security_id',
                $security->id
            )
            ->where(
                'quantity',
                '>',
                0
            )
            ->where(
                'market_value',
                '>',
                0
            )
            ->latest(
                'updated_at'
            )
            ->first();

        if (! $holding) {
            return 0.0;
        }

        $quantity =
            (float) $holding->quantity;

        $marketValue =
            (float) $holding->market_value;

        if (
            $quantity <= 0
            || $marketValue <= 0
        ) {
            return 0.0;
        }

        return
            $marketValue
            / $quantity;
    }

    private function priceFromHistory(
        Security $security,
    ): float {
        $securityPrice =
            SecurityPrice::query()
                ->where(
                    'security_id',
                    $security->id
                )
                ->latest(
                    'date'
                )
                ->first();

        if (! $securityPrice) {
            return 0.0;
        }

        foreach (
            [
                'close',
                'close_price',
                'price',
                'adjusted_close',
                'adj_close',
            ]
            as $field
        ) {
            $value =
                data_get(
                    $securityPrice,
                    $field
                );

            if (
                $value !== null
                && (float) $value > 0
            ) {
                return (float) $value;
            }
        }

        return 0.0;
    }

    /**
     * @return array{
     *     price: float,
     *     as_of: CarbonInterface|null
     * }
     */
    private function priceFromAlphaVantage(
        Security $security,
    ): array {
        $apiKey =
            (string) (
                config(
                    'services.alpha_vantage.key'
                )
                ?: env(
                    'ALPHA_VANTAGE_API_KEY'
                )
            );

        if ($apiKey === '') {
            return $this->emptyRemotePrice();
        }

        $symbol =
            strtoupper(
                trim(
                    (string) $security->symbol
                )
            );

        if ($symbol === '') {
            return $this->emptyRemotePrice();
        }

        try {
            /*
             * First choice:
             * Alpha Vantage GLOBAL_QUOTE.
             */
            $quoteResponse =
                Http::acceptJson()
                    ->connectTimeout(8)
                    ->timeout(15)
                    ->retry(
                        2,
                        250,
                        throw: false,
                    )
                    ->get(
                        'https://www.alphavantage.co/query',
                        [
                            'function' =>
                                'GLOBAL_QUOTE',

                            'symbol' =>
                                $symbol,

                            'apikey' =>
                                $apiKey,
                        ]
                    );

            if ($quoteResponse->successful()) {
                $payload =
                    $quoteResponse->json();

                $quote =
                    is_array($payload)
                        ? (
                            $payload[
                                'Global Quote'
                            ]
                            ?? null
                        )
                        : null;

                if (is_array($quote)) {
                    /*
                     * IMPORTANT:
                     *
                     * Alpha Vantage uses literal keys containing
                     * periods, such as "05. price".
                     *
                     * Do NOT use data_get($quote, '05. price')
                     * because Laravel interprets "." as nesting.
                     */
                    $price =
                        (float) (
                            $quote[
                                '05. price'
                            ]
                            ?? 0
                        );

                    if ($price > 0) {
                        return [
                            'price' =>
                                $price,

                            'as_of' =>
                                $this->parseDate(
                                    $quote[
                                        '07. latest trading day'
                                    ] ?? null
                                ),
                        ];
                    }
                }
            }

            /*
             * Second choice:
             * latest daily close.
             */
            $dailyResponse =
                Http::acceptJson()
                    ->connectTimeout(8)
                    ->timeout(15)
                    ->retry(
                        2,
                        250,
                        throw: false,
                    )
                    ->get(
                        'https://www.alphavantage.co/query',
                        [
                            'function' =>
                                'TIME_SERIES_DAILY',

                            'symbol' =>
                                $symbol,

                            'outputsize' =>
                                'compact',

                            'apikey' =>
                                $apiKey,
                        ]
                    );

            if (! $dailyResponse->successful()) {
                return $this->emptyRemotePrice();
            }

            $payload =
                $dailyResponse->json();

            $series =
                is_array($payload)
                    ? (
                        $payload[
                            'Time Series (Daily)'
                        ]
                        ?? null
                    )
                    : null;

            if (
                ! is_array($series)
                || $series === []
            ) {
                return $this->emptyRemotePrice();
            }

            /*
             * Alpha Vantage returns the newest trading day first.
             */
            $date =
                array_key_first(
                    $series
                );

            $latest =
                $date !== null
                    ? $series[$date]
                    : null;

            if (! is_array($latest)) {
                return $this->emptyRemotePrice();
            }

            /*
             * Same issue here:
             * "4. close" is a literal array key.
             */
            $price =
                (float) (
                    $latest[
                        '4. close'
                    ]
                    ?? 0
                );

            if ($price <= 0) {
                return $this->emptyRemotePrice();
            }

            return [
                'price' =>
                    $price,

                'as_of' =>
                    $this->parseDate(
                        $date
                    ),
            ];
        } catch (Throwable $e) {
            report($e);

            return $this->emptyRemotePrice();
        }
    }

    private function persistResolvedPrice(
        Security $security,
        float $price,
        ?CarbonInterface $asOf = null,
    ): void {
        if ($price <= 0) {
            return;
        }

        $security->forceFill([
            'last_price' =>
                $price,

            'price_as_of' =>
                $asOf
                ?? Carbon::now(),
        ])->save();
    }

    private function parseDate(
        mixed $value,
    ): ?CarbonInterface {
        if (
            $value === null
            || trim(
                (string) $value
            ) === ''
        ) {
            return null;
        }

        try {
            return Carbon::parse(
                (string) $value
            );
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{
     *     price: float,
     *     as_of: null
     * }
     */
    private function emptyRemotePrice(): array
    {
        return [
            'price' =>
                0.0,

            'as_of' =>
                null,
        ];
    }
}