<?php

namespace App\Services\Analytics\Performance;

use App\Models\PortfolioValuation;
use Illuminate\Support\Collection;

class TimeWeightedReturnService
{
    /**
     * Calculate a time-weighted return from ordered valuation records.
     *
     * Each valuation's net_cash_flow represents external cash flow
     * occurring during the period ending on that valuation date.
     */
    public function calculate(
        Collection $valuations
    ): array {
        $valuations = $valuations
            ->sortBy('valuation_date')
            ->values();

        if ($valuations->count() < 2) {
            return $this->insufficientData();
        }

        $subperiods = [];
        $growthFactor = 1.0;

        for (
            $index = 1;
            $index < $valuations->count();
            $index++
        ) {
            /** @var PortfolioValuation $beginning */
            $beginning = $valuations->get($index - 1);

            /** @var PortfolioValuation $ending */
            $ending = $valuations->get($index);

            $beginningValue = $beginning->total_value;
            $endingValue = $ending->total_value;

            /*
             * The external cash flow recorded on the ending valuation
             * is removed from investment performance.
             */
            $netCashFlow = (float) $ending->net_cash_flow;

            $periodReturn = $this->subperiodReturn(
                beginningValue: $beginningValue,
                endingValue: $endingValue,
                netCashFlow: $netCashFlow,
            );

            if ($periodReturn === null) {
                $subperiods[] = [
                    'start_date' =>
                        $beginning->valuation_date->toDateString(),

                    'end_date' =>
                        $ending->valuation_date->toDateString(),

                    'beginning_value' =>
                        round($beginningValue, 2),

                    'ending_value' =>
                        round($endingValue, 2),

                    'net_cash_flow' =>
                        round($netCashFlow, 2),

                    'return' => null,

                    'status' =>
                        'invalid_beginning_value',
                ];

                continue;
            }

            $growthFactor *= 1 + $periodReturn;

            $subperiods[] = [
                'start_date' =>
                    $beginning->valuation_date->toDateString(),

                'end_date' =>
                    $ending->valuation_date->toDateString(),

                'beginning_value' =>
                    round($beginningValue, 2),

                'ending_value' =>
                    round($endingValue, 2),

                'net_cash_flow' =>
                    round($netCashFlow, 2),

                'return' =>
                    round($periodReturn, 10),

                'status' => 'complete',
            ];
        }

        $validSubperiods = collect($subperiods)
            ->where('status', 'complete');

        if ($validSubperiods->isEmpty()) {
            return $this->insufficientData();
        }

        return [
            'status' => 'complete',

            'return' =>
                round($growthFactor - 1, 10),

            'subperiod_count' =>
                $validSubperiods->count(),

            'skipped_subperiod_count' =>
                count($subperiods)
                - $validSubperiods->count(),

            'subperiods' => $subperiods,
        ];
    }

    /**
     * Modified Dietz-style daily subperiod return.
     *
     * This assumes the cash flow occurs at the end of the period:
     *
     * (ending value - net cash flow) / beginning value - 1
     */
    private function subperiodReturn(
        float $beginningValue,
        float $endingValue,
        float $netCashFlow
    ): ?float {
        if ($beginningValue <= 0) {
            return null;
        }

        return (
            ($endingValue - $netCashFlow)
            / $beginningValue
        ) - 1;
    }

    private function insufficientData(): array
    {
        return [
            'status' => 'insufficient_data',
            'return' => null,
            'subperiod_count' => 0,
            'skipped_subperiod_count' => 0,
            'subperiods' => [],
        ];
    }
}