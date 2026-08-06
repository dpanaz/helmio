<?php

namespace App\Services\Analytics\Performance;

use App\Models\Security;
use App\Models\SecurityPrice;
use Carbon\CarbonInterface;

class HistoricalPriceService
{
    /**
     * Get the best available security price on or before a date.
     *
     * Using the most recent prior price handles weekends,
     * holidays, and missing market-data days.
     */
    public function priceOnOrBefore(
        Security|int $security,
        CarbonInterface $date
    ): ?SecurityPrice {
        $securityId = $security instanceof Security
            ? $security->id
            : $security;

        return SecurityPrice::query()
            ->where('security_id', $securityId)
            ->whereDate(
                'price_date',
                '<=',
                $date->toDateString()
            )
            ->orderByDesc('price_date')
            ->first();
    }

    /**
     * Return only the numeric performance price.
     */
    public function valueOnOrBefore(
        Security|int $security,
        CarbonInterface $date
    ): ?float {
        $price = $this->priceOnOrBefore(
            security: $security,
            date: $date,
        );

        return $price?->performance_price;
    }

    /**
     * Get the exact price for a date.
     */
    public function exactPrice(
        Security|int $security,
        CarbonInterface $date
    ): ?SecurityPrice {
        $securityId = $security instanceof Security
            ? $security->id
            : $security;

        return SecurityPrice::query()
            ->where('security_id', $securityId)
            ->whereDate(
                'price_date',
                $date->toDateString()
            )
            ->first();
    }

    public function priceAgeInDays(
    int $securityId,
    CarbonInterface $valuationDate
): ?int {
    $price = $this->priceOnOrBefore(
        security: $securityId,
        date: $valuationDate,
    );

    if ($price === null) {
        return null;
    }

    return $price->price_date->diffInDays(
        $valuationDate
    );
}

public function isStale(
    int $securityId,
    CarbonInterface $valuationDate,
    int $maximumAgeInDays = 7
): bool {
    $age = $this->priceAgeInDays(
        securityId: $securityId,
        valuationDate: $valuationDate,
    );

    return $age === null
        || $age > $maximumAgeInDays;
}

    /**
     * Store or update one price record.
     */
    public function store(
        Security|int $security,
        CarbonInterface $date,
        float $closePrice,
        ?float $adjustedClosePrice = null,
        ?float $openPrice = null,
        ?float $highPrice = null,
        ?float $lowPrice = null,
        ?int $volume = null,
        string $currency = 'USD',
        string $source = 'manual',
        array $metadata = []
    ): SecurityPrice {
        $securityId = $security instanceof Security
            ? $security->id
            : $security;

        $price = SecurityPrice::query()
            ->where('security_id', $securityId)
            ->whereDate(
                'price_date',
                $date->toDateString()
            )
            ->first();

        if ($price === null) {
            $price = new SecurityPrice();

            $price->security_id = $securityId;
            $price->price_date = $date->toDateString();
        }

        $price->fill([
            'open_price' => $openPrice,
            'high_price' => $highPrice,
            'low_price' => $lowPrice,
            'close_price' => $closePrice,
            'adjusted_close_price' =>
                $adjustedClosePrice,
            'volume' => $volume,
            'currency' => $currency,
            'source' => $source,
            'metadata' => $metadata,
        ]);

        $price->save();

        return $price->refresh();
    }
}