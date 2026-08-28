<?php

namespace App\Services\Simulation;

use App\Data\Simulation\SimulatedHoldingData;
use App\Data\Simulation\SimulatedPortfolioData;

class PortfolioSimulationAnalyticsService
{
    public function analyze(
        SimulatedPortfolioData $portfolio,
    ): array {
        return [
            'portfolio_value' =>
                $portfolio->totalValue(),

            'holdings_value' =>
                $portfolio->holdingsValue(),

            'cash_value' =>
                $portfolio->cash,

            'cash_weight' =>
                $this->cashWeight($portfolio),

            'holdings_count' =>
                $portfolio->holdings->count(),

            'largest_position' =>
                $this->largestPosition($portfolio),

            'top_five_weight' =>
                $this->topFiveWeight($portfolio),

            'concentration_hhi' =>
                $this->concentrationHhi($portfolio),

            'concentration_label' =>
                $this->concentrationLabel(
                    $this->concentrationHhi($portfolio)
                ),

            'estimated_annual_fund_expenses' =>
                $this->estimatedFundExpenses($portfolio),

            'weighted_expense_ratio' =>
                $this->weightedExpenseRatio($portfolio),

            'asset_classes' =>
                $this->assetClassBreakdown($portfolio),

            'sectors' =>
                $this->sectorBreakdown($portfolio),

            'diversification_score' =>
                $this->diversificationScore($portfolio),

            'cash_drag_score' =>
                $this->cashDragScore($portfolio),

            'cost_score' =>
                $this->costScore($portfolio),
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

    private function largestPosition(
        SimulatedPortfolioData $portfolio,
    ): ?array {
        if ($portfolio->holdings->isEmpty()) {
            return null;
        }

        $total = $portfolio->totalValue();

        /** @var SimulatedHoldingData $holding */
        $holding = $portfolio->holdings
            ->sortByDesc('marketValue')
            ->first();

        return [
            'symbol' =>
                $holding->symbol,

            'name' =>
                $holding->name,

            'market_value' =>
                $holding->marketValue,

            'weight' =>
                $total > 0
                    ? $holding->marketValue / $total
                    : 0,
        ];
    }

    private function topFiveWeight(
        SimulatedPortfolioData $portfolio,
    ): float {
        $total = $portfolio->totalValue();

        if ($total <= 0) {
            return 0.0;
        }

        return $portfolio->holdings
            ->sortByDesc('marketValue')
            ->take(5)
            ->sum('marketValue')
            / $total;
    }

    private function concentrationHhi(
        SimulatedPortfolioData $portfolio,
    ): float {
        $investedValue =
            $portfolio->holdingsValue();

        if ($investedValue <= 0) {
            return 0.0;
        }

        return (float) $portfolio->holdings
            ->sum(
                function (
                    SimulatedHoldingData $holding
                ) use ($investedValue) {
                    $weight =
                        $holding->marketValue
                        / $investedValue;

                    return $weight * $weight;
                }
            );
    }

    private function concentrationLabel(
        float $hhi,
    ): string {
        return match (true) {
            $hhi >= 0.25 =>
                'High concentration',

            $hhi >= 0.15 =>
                'Moderate concentration',

            default =>
                'Lower concentration',
        };
    }

    private function estimatedFundExpenses(
        SimulatedPortfolioData $portfolio,
    ): float {
        return (float) $portfolio->holdings
            ->sum(
                function (
                    SimulatedHoldingData $holding
                ) {
                    if (
                        $holding->expenseRatio === null
                        || $holding->expenseRatio <= 0
                    ) {
                        return 0.0;
                    }

                    return
                        $holding->marketValue
                        * $holding->expenseRatio;
                }
            );
    }

    private function weightedExpenseRatio(
        SimulatedPortfolioData $portfolio,
    ): float {
        $investedValue =
            $portfolio->holdingsValue();

        if ($investedValue <= 0) {
            return 0.0;
        }

        $weightedExpense =
            $portfolio->holdings->sum(
                function (
                    SimulatedHoldingData $holding
                ) {
                    return
                        $holding->marketValue
                        * (
                            $holding->expenseRatio
                            ?? 0
                        );
                }
            );

        return
            $weightedExpense
            / $investedValue;
    }

    private function assetClassBreakdown(
        SimulatedPortfolioData $portfolio,
    ): array {
        $total =
            $portfolio->holdingsValue();

        if ($total <= 0) {
            return [];
        }

        return $portfolio->holdings
            ->groupBy(
                fn (
                    SimulatedHoldingData $holding
                ) =>
                    $holding->assetClass
                    ?: 'Unknown'
            )
            ->map(
                function ($holdings) use ($total) {
                    $value =
                        $holdings->sum('marketValue');

                    return [
                        'market_value' =>
                            $value,

                        'weight' =>
                            $value / $total,
                    ];
                }
            )
            ->sortByDesc('market_value')
            ->toArray();
    }

    private function sectorBreakdown(
        SimulatedPortfolioData $portfolio,
    ): array {
        $total =
            $portfolio->holdingsValue();

        if ($total <= 0) {
            return [];
        }

        return $portfolio->holdings
            ->groupBy(
                fn (
                    SimulatedHoldingData $holding
                ) =>
                    $holding->sector
                    ?: 'Unknown'
            )
            ->map(
                function ($holdings) use ($total) {
                    $value =
                        $holdings->sum('marketValue');

                    return [
                        'market_value' =>
                            $value,

                        'weight' =>
                            $value / $total,
                    ];
                }
            )
            ->sortByDesc('market_value')
            ->toArray();
    }

    private function diversificationScore(
        SimulatedPortfolioData $portfolio,
    ): int {
        $count =
            $portfolio->holdings->count();

        $largest =
            $this->largestPosition($portfolio);

        $largestWeight =
            $largest['weight']
            ?? 0;

        $hhi =
            $this->concentrationHhi(
                $portfolio
            );

        $score = 100;

        if ($count < 5) {
            $score -= 35;
        } elseif ($count < 10) {
            $score -= 20;
        } elseif ($count < 15) {
            $score -= 10;
        }

        if ($largestWeight > 0.40) {
            $score -= 35;
        } elseif ($largestWeight > 0.25) {
            $score -= 20;
        } elseif ($largestWeight > 0.15) {
            $score -= 10;
        }

        if ($hhi >= 0.25) {
            $score -= 20;
        } elseif ($hhi >= 0.15) {
            $score -= 10;
        }

        return max(
            0,
            min(100, $score)
        );
    }

    private function cashDragScore(
        SimulatedPortfolioData $portfolio,
    ): int {
        $cashWeight =
            $this->cashWeight(
                $portfolio
            );

        return match (true) {
            $cashWeight <= 0.05 =>
                100,

            $cashWeight <= 0.10 =>
                90,

            $cashWeight <= 0.20 =>
                75,

            $cashWeight <= 0.30 =>
                55,

            default =>
                30,
        };
    }

    private function costScore(
        SimulatedPortfolioData $portfolio,
    ): int {
        $expenseRatio =
            $this->weightedExpenseRatio(
                $portfolio
            );

        return match (true) {
            $expenseRatio <= 0.001 =>
                100,

            $expenseRatio <= 0.003 =>
                90,

            $expenseRatio <= 0.005 =>
                80,

            $expenseRatio <= 0.010 =>
                65,

            default =>
                40,
        };
    }
}