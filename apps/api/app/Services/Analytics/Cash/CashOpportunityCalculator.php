<?php

namespace App\Services\Analytics\Cash;

class CashOpportunityCalculator
{
    /**
     * Estimate missed growth from excess cash.
     *
     * Example:
     * Average cash: $40,000
     * Target cash rate: 5%
     * Average portfolio value: $200,000
     * Excess cash: $30,000
     * Benchmark return: 12%
     * Estimated opportunity cost: $3,600
     */
    public function calculate(
        float $averageCash,
        float $averagePortfolioValue,
        ?float $benchmarkReturn,
        float $targetCashPercent = 0.05
    ): array {
        if ($averagePortfolioValue <= 0) {
            return $this->insufficientData(
                'Average portfolio value must be greater than zero.'
            );
        }

        if ($benchmarkReturn === null) {
            return $this->insufficientData(
                'Benchmark return is required.'
            );
        }

        $targetCashAmount =
            $averagePortfolioValue
            * max(0, $targetCashPercent);

        $excessCash = max(
            0,
            $averageCash - $targetCashAmount
        );

        $opportunityCost = $benchmarkReturn > 0
            ? $excessCash * $benchmarkReturn
            : 0.0;

        return [
            'status' => 'complete',

            'metrics' => [
                'average_cash' =>
                    round($averageCash, 2),

                'average_portfolio_value' =>
                    round($averagePortfolioValue, 2),

                'target_cash_percent' =>
                    round($targetCashPercent, 8),

                'target_cash_amount' =>
                    round($targetCashAmount, 2),

                'excess_cash' =>
                    round($excessCash, 2),

                'benchmark_return' =>
                    round($benchmarkReturn, 10),

                'estimated_opportunity_cost' =>
                    round($opportunityCost, 2),
            ],

            'formula_version' =>
                'cash-opportunity-0.1.0',
        ];
    }

    private function insufficientData(
        string $message
    ): array {
        return [
            'status' => 'insufficient_data',
            'message' => $message,

            'metrics' => [
                'average_cash' => null,
                'average_portfolio_value' => null,
                'target_cash_percent' => null,
                'target_cash_amount' => null,
                'excess_cash' => null,
                'benchmark_return' => null,
                'estimated_opportunity_cost' => null,
            ],

            'formula_version' =>
                'cash-opportunity-0.1.0',
        ];
    }
}