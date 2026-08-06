<?php

namespace App\Services\Analytics\Risk;

use App\Models\PortfolioValuation;
use Illuminate\Support\Collection;

class DailyReturnBuilder
{
    /**
     * Build dated time-weighted subperiod returns from portfolio valuations.
     *
     * External cash flow recorded on the ending valuation is removed
     * from the period's investment return.
     */
    public function build(Collection $valuations): array
    {
        $valuations = $valuations
            ->sortBy('valuation_date')
            ->values();

        if ($valuations->count() < 2) {
            return [];
        }

        $returns = [];

        for ($index = 1; $index < $valuations->count(); $index++) {
            /** @var PortfolioValuation $beginning */
            $beginning = $valuations->get($index - 1);

            /** @var PortfolioValuation $ending */
            $ending = $valuations->get($index);

            $beginningValue = $beginning->total_value;
            $endingValue = $ending->total_value;
            $netCashFlow = (float) $ending->net_cash_flow;

            if ($beginningValue <= 0) {
                continue;
            }

            $periodReturn = (
                ($endingValue - $netCashFlow)
                / $beginningValue
            ) - 1;

            if ($periodReturn < -1) {
                continue;
            }

            $returns[] = [
                'date' => $ending
                    ->valuation_date
                    ->toDateString(),

                'start_date' => $beginning
                    ->valuation_date
                    ->toDateString(),

                'return' => round($periodReturn, 10),

                'beginning_value' => round(
                    $beginningValue,
                    2
                ),

                'ending_value' => round(
                    $endingValue,
                    2
                ),

                'net_cash_flow' => round(
                    $netCashFlow,
                    2
                ),
            ];
        }

        return $returns;
    }
}