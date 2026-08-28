<?php

namespace App\Services\Simulation;

use App\Data\Simulation\SimulatedHoldingData;
use App\Data\Simulation\SimulatedPortfolioData;

class SimulationComparisonService
{
    public function compare(
        SimulatedPortfolioData $current,
        SimulatedPortfolioData $simulated,
    ): array {
        $currentValue = $current->totalValue();
        $simulatedValue = $simulated->totalValue();

        $currentLargest = $this->largestPosition($current);
        $simulatedLargest = $this->largestPosition($simulated);

        $currentExpense = $this->estimatedFundExpense($current);
        $simulatedExpense = $this->estimatedFundExpense($simulated);

        $currentCashWeight = $this->cashWeight($current);
        $simulatedCashWeight = $this->cashWeight($simulated);

        $currentHhi = $this->concentrationHhi($current);
        $simulatedHhi = $this->concentrationHhi($simulated);

        return [
            'portfolio' => [
                'current_value' => $currentValue,
                'simulated_value' => $simulatedValue,
                'difference' => $simulatedValue - $currentValue,
            ],

            'cash' => [
                'current' => $current->cash,
                'simulated' => $simulated->cash,
                'difference' => $simulated->cash - $current->cash,
                'current_weight' => $currentCashWeight,
                'simulated_weight' => $simulatedCashWeight,
                'weight_change' => $simulatedCashWeight - $currentCashWeight,
            ],

            'holdings_count' => [
                'current' => $current->holdings->count(),
                'simulated' => $simulated->holdings->count(),
                'difference' =>
                    $simulated->holdings->count()
                    - $current->holdings->count(),
            ],

            'largest_position' => [
                'current' => $currentLargest,
                'simulated' => $simulatedLargest,
                'weight_change' =>
                    ($simulatedLargest['weight'] ?? 0)
                    - ($currentLargest['weight'] ?? 0),
            ],

            'fund_expenses' => [
                'current_annual_estimate' => $currentExpense,
                'simulated_annual_estimate' => $simulatedExpense,
                'difference' => $simulatedExpense - $currentExpense,
            ],

            'concentration' => [
                'current_hhi' => $currentHhi,
                'simulated_hhi' => $simulatedHhi,
                'difference' => $simulatedHhi - $currentHhi,
                'current_label' => $this->concentrationLabel($currentHhi),
                'simulated_label' => $this->concentrationLabel($simulatedHhi),
            ],

            'impact_summary' => $this->impactSummary(
                current: $current,
                simulated: $simulated,
                currentLargest: $currentLargest,
                simulatedLargest: $simulatedLargest,
                currentExpense: $currentExpense,
                simulatedExpense: $simulatedExpense,
                currentHhi: $currentHhi,
                simulatedHhi: $simulatedHhi,
            ),

            'holdings' => $this->compareHoldings(
                $current,
                $simulated,
            ),
        ];
    }

    private function largestPosition(
        SimulatedPortfolioData $portfolio,
    ): ?array {
        $total = $portfolio->totalValue();

        if (
            $total <= 0
            || $portfolio->holdings->isEmpty()
        ) {
            return null;
        }

        /** @var SimulatedHoldingData $holding */
        $holding = $portfolio->holdings
            ->sortByDesc('marketValue')
            ->first();

        return [
            'symbol' => $holding->symbol,
            'name' => $holding->name,
            'market_value' => $holding->marketValue,
            'weight' => $holding->weight($total),
        ];
    }

    private function cashWeight(
        SimulatedPortfolioData $portfolio,
    ): float {
        $total = $portfolio->totalValue();

        if ($total <= 0) {
            return 0.0;
        }

        return $portfolio->cash / $total;
    }

    private function estimatedFundExpense(
        SimulatedPortfolioData $portfolio,
    ): float {
        return (float) $portfolio->holdings->sum(
            function (SimulatedHoldingData $holding) {
                $expenseRatio = $holding->expenseRatio;

                if (
                    $expenseRatio === null
                    || $expenseRatio <= 0
                ) {
                    return 0.0;
                }

                return $holding->marketValue * $expenseRatio;
            }
        );
    }

    /**
     * Herfindahl-Hirschman style concentration index.
     * Returns 0-1, where a larger number indicates more concentration.
     */
    private function concentrationHhi(
        SimulatedPortfolioData $portfolio,
    ): float {
        $total = $portfolio->holdingsValue();

        if ($total <= 0) {
            return 0.0;
        }

        return (float) $portfolio->holdings->sum(
            function (SimulatedHoldingData $holding) use ($total) {
                $weight = $holding->marketValue / $total;

                return $weight * $weight;
            }
        );
    }

    private function concentrationLabel(
        float $hhi,
    ): string {
        return match (true) {
            $hhi >= 0.25 => 'High concentration',
            $hhi >= 0.15 => 'Moderate concentration',
            default => 'Lower concentration',
        };
    }

    private function impactSummary(
        SimulatedPortfolioData $current,
        SimulatedPortfolioData $simulated,
        ?array $currentLargest,
        ?array $simulatedLargest,
        float $currentExpense,
        float $simulatedExpense,
        float $currentHhi,
        float $simulatedHhi,
    ): array {
        $items = [];

        $currentLargestWeight =
            $currentLargest['weight'] ?? 0.0;

        $simulatedLargestWeight =
            $simulatedLargest['weight'] ?? 0.0;

        $largestDifference =
            $simulatedLargestWeight
            - $currentLargestWeight;

        if (abs($largestDifference) >= 0.001) {
            $items[] = [
                'type' =>
                    $largestDifference < 0
                        ? 'positive'
                        : 'warning',

                'title' =>
                    $largestDifference < 0
                        ? 'Largest position decreased'
                        : 'Largest position increased',

                'message' => sprintf(
                    '%s changed from %.1f%% to %.1f%% of the portfolio.',
                    $simulatedLargest['symbol']
                        ?? $currentLargest['symbol']
                        ?? 'Largest position',
                    $currentLargestWeight * 100,
                    $simulatedLargestWeight * 100,
                ),
            ];
        }

        $expenseDifference =
            $simulatedExpense - $currentExpense;

        if (abs($expenseDifference) >= 1.0) {
            $items[] = [
                'type' =>
                    $expenseDifference < 0
                        ? 'positive'
                        : 'warning',

                'title' =>
                    $expenseDifference < 0
                        ? 'Estimated fund costs decreased'
                        : 'Estimated fund costs increased',

                'message' => sprintf(
                    'Estimated annual fund expenses changed by %s$%s.',
                    $expenseDifference > 0 ? '+' : '-',
                    number_format(abs($expenseDifference), 0),
                ),
            ];
        }

        $hhiDifference =
            $simulatedHhi - $currentHhi;

        if (abs($hhiDifference) >= 0.005) {
            $items[] = [
                'type' =>
                    $hhiDifference < 0
                        ? 'positive'
                        : 'warning',

                'title' =>
                    $hhiDifference < 0
                        ? 'Concentration improved'
                        : 'Concentration increased',

                'message' =>
                    $hhiDifference < 0
                        ? 'The hypothetical portfolio is less concentrated across its invested holdings.'
                        : 'The hypothetical portfolio is more concentrated across its invested holdings.',
            ];
        }

        $currentCashWeight =
            $this->cashWeight($current);

        $simulatedCashWeight =
            $this->cashWeight($simulated);

        $cashDifference =
            $simulatedCashWeight
            - $currentCashWeight;

        if (abs($cashDifference) >= 0.001) {
            $items[] = [
                'type' => 'neutral',
                'title' => 'Cash allocation changed',
                'message' => sprintf(
                    'Cash changed from %.1f%% to %.1f%% of portfolio value.',
                    $currentCashWeight * 100,
                    $simulatedCashWeight * 100,
                ),
            ];
        }

        return $items;
    }

    private function compareHoldings(
        SimulatedPortfolioData $current,
        SimulatedPortfolioData $simulated,
    ): array {
        $symbols = $current->holdings
            ->pluck('symbol')
            ->merge(
                $simulated->holdings->pluck('symbol')
            )
            ->map(
                fn ($symbol) =>
                    strtoupper((string) $symbol)
            )
            ->unique()
            ->sort()
            ->values();

        $currentTotal = $current->totalValue();
        $simulatedTotal = $simulated->totalValue();

        return $symbols
            ->map(function ($symbol) use (
                $current,
                $simulated,
                $currentTotal,
                $simulatedTotal,
            ) {
                $before =
                    $current->findHolding($symbol);

                $after =
                    $simulated->findHolding($symbol);

                $beforeValue =
                    $before?->marketValue ?? 0;

                $afterValue =
                    $after?->marketValue ?? 0;

                $currentWeight =
                    $currentTotal > 0
                        ? $beforeValue / $currentTotal
                        : 0;

                $simulatedWeight =
                    $simulatedTotal > 0
                        ? $afterValue / $simulatedTotal
                        : 0;

                return [
                    'symbol' => $symbol,

                    'name' =>
                        $after?->name
                        ?? $before?->name
                        ?? $symbol,

                    'current_value' =>
                        $beforeValue,

                    'simulated_value' =>
                        $afterValue,

                    'value_change' =>
                        $afterValue - $beforeValue,

                    'current_weight' =>
                        $currentWeight,

                    'simulated_weight' =>
                        $simulatedWeight,

                    'weight_change' =>
                        $simulatedWeight
                        - $currentWeight,
                ];
            })
            ->values()
            ->all();
    }
}