<?php

namespace App\Services\Analytics\Cash;

use App\Models\PortfolioValuation;
use Illuminate\Support\Collection;

class CashAllocationAnalyzer
{
    /**
     * Analyze historical cash allocations.
     *
     * @param Collection<int, PortfolioValuation> $valuations
     */
    public function analyze(Collection $valuations): array
    {
        if ($valuations->isEmpty()) {
            return [
                'status' => 'insufficient_data',

                'metrics' => [
                    'current_cash' => null,
                    'current_cash_percent' => null,
                    'average_cash' => null,
                    'average_cash_percent' => null,
                    'minimum_cash_percent' => null,
                    'maximum_cash_percent' => null,
                ],

                'history' => [],
            ];
        }

        $history = [];

        foreach ($valuations as $valuation) {
            $portfolioValue = (float) $valuation->total_value;
            $cash = (float) $valuation->cash_value;

            $cashPercent = $portfolioValue > 0
                ? $cash / $portfolioValue
                : 0;

            $history[] = [
                'date' => $valuation->valuation_date->toDateString(),

                'portfolio_value' => round(
                    $portfolioValue,
                    2
                ),

                'cash_value' => round(
                    $cash,
                    2
                ),

                'cash_percent' => round(
                    $cashPercent,
                    8
                ),
            ];
        }

        $cashPercents = collect($history)
            ->pluck('cash_percent');

        $latest = end($history);

        return [
            'status' => 'complete',

            'metrics' => [
                'current_cash' =>
                    $latest['cash_value'],

                'current_cash_percent' =>
                    $latest['cash_percent'],

                'average_cash' =>
                    round(
                        collect($history)
                            ->avg('cash_value'),
                        2
                    ),

                'average_cash_percent' =>
                    round(
                        $cashPercents->avg(),
                        8
                    ),

                'minimum_cash_percent' =>
                    round(
                        $cashPercents->min(),
                        8
                    ),

                'maximum_cash_percent' =>
                    round(
                        $cashPercents->max(),
                        8
                    ),
            ],

            'history' => $history,

            'formula_version' => 'cash-allocation-0.1.0',
        ];
    }
}