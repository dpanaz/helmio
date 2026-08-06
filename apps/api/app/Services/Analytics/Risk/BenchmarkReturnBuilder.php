<?php

namespace App\Services\Analytics\Risk;

use App\Models\Benchmark;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BenchmarkReturnBuilder
{
    /**
     * Build benchmark returns for the requested dates.
     *
     * The most recent available price on or before each requested date
     * is used to handle weekends and market holidays.
     *
     * @param array<int, string> $dates
     */
    public function build(
        Benchmark $benchmark,
        array $dates,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        if ($dates === []) {
            return [];
        }

        $prices = $benchmark->prices()
            ->whereDate(
                'price_date',
                '<=',
                $endDate->toDateString()
            )
            ->orderBy('price_date')
            ->get();

        if ($prices->isEmpty()) {
            return [];
        }

        $requestedDates = collect($dates)
            ->unique()
            ->sort()
            ->values();

        $priceByDate = $requestedDates
            ->mapWithKeys(function (string $date) use ($prices): array {
                $price = $prices
                    ->filter(
                        fn ($candidate): bool =>
                            $candidate->price_date
                                ->toDateString() <= $date
                    )
                    ->last();

                return [
                    $date => $price?->performance_price,
                ];
            });

        return $this->calculateReturns($priceByDate);
    }

    /**
     * @param Collection<string, float|null> $prices
     */
    private function calculateReturns(
        Collection $prices
    ): array {
        $returns = [];
        $previousDate = null;
        $previousPrice = null;

        foreach ($prices as $date => $price) {
            if ($price === null || $price <= 0) {
                continue;
            }

            if (
                $previousDate !== null
                && $previousPrice !== null
                && $previousPrice > 0
            ) {
                $periodReturn = (
                    (float) $price
                    / (float) $previousPrice
                ) - 1;

                if ($periodReturn >= -1) {
                    $returns[] = [
                        'date' => $date,
                        'start_date' => $previousDate,
                        'return' => round(
                            $periodReturn,
                            10
                        ),

                        'beginning_price' => round(
                            (float) $previousPrice,
                            6
                        ),

                        'ending_price' => round(
                            (float) $price,
                            6
                        ),
                    ];
                }
            }

            $previousDate = $date;
            $previousPrice = (float) $price;
        }

        return $returns;
    }
}