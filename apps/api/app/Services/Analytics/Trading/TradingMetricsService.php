<?php

namespace App\Services\Analytics\Trading;

class TradingMetricsService
{
    public function analyze(
        array $transactions,
        float $averagePortfolioValue
    ): array {
        if ($transactions === []) {
            return $this->emptyResult();
        }

        $buyAmount = 0.0;
        $sellAmount = 0.0;
        $fees = 0.0;
        $tradeCount = 0;

        foreach ($transactions as $transaction) {
            $type = $this->normalizeType(
                $transaction['transaction_type'] ?? null
            );

            $grossAmount = abs(
                (float) (
                    $transaction['gross_amount']
                    ?? $transaction['net_amount']
                    ?? 0
                )
            );

            $fees += abs(
                (float) ($transaction['fees'] ?? 0)
            );

            if (in_array($type, [
                'buy',
                'purchase',
            ], true)) {
                $buyAmount += $grossAmount;
                $tradeCount++;
            }

            if (in_array($type, [
                'sell',
                'sale',
            ], true)) {
                $sellAmount += $grossAmount;
                $tradeCount++;
            }
        }

        $turnoverAmount = min(
            $buyAmount,
            $sellAmount
        );

        $turnoverRate = $averagePortfolioValue > 0
            ? $turnoverAmount / $averagePortfolioValue
            : null;

        return [
            'status' => 'complete',

            'metrics' => [
                'buy_amount' =>
                    round($buyAmount, 2),

                'sell_amount' =>
                    round($sellAmount, 2),

                'turnover_amount' =>
                    round($turnoverAmount, 2),

                'turnover_rate' =>
                    $turnoverRate === null
                        ? null
                        : round($turnoverRate, 8),

                'trade_count' =>
                    $tradeCount,

                'fees' =>
                    round($fees, 2),

                'fee_rate' =>
                    $averagePortfolioValue > 0
                        ? round(
                            $fees
                            / $averagePortfolioValue,
                            8
                        )
                        : null,
            ],

            'risk_level' =>
                $this->riskLevel(
                    turnoverRate: $turnoverRate,
                    tradeCount: $tradeCount,
                ),

            'flags' =>
                $this->buildFlags(
                    turnoverRate: $turnoverRate,
                    tradeCount: $tradeCount,
                    fees: $fees,
                    averagePortfolioValue:
                        $averagePortfolioValue,
                ),

            'formula_version' =>
                'trading-0.1.0',
        ];
    }

    private function riskLevel(
        ?float $turnoverRate,
        int $tradeCount
    ): string {
        return match (true) {
            $turnoverRate !== null
                && $turnoverRate >= 2.0
                    => 'very_high',

            $turnoverRate !== null
                && $turnoverRate >= 1.0
                    => 'high',

            $turnoverRate !== null
                && $turnoverRate >= 0.5
                    => 'moderate',

            $tradeCount >= 50
                    => 'moderate',

            default => 'low',
        };
    }

    private function buildFlags(
        ?float $turnoverRate,
        int $tradeCount,
        float $fees,
        float $averagePortfolioValue
    ): array {
        $flags = [];

        if (
            $turnoverRate !== null
            && $turnoverRate >= 1.0
        ) {
            $flags[] = [
                'code' =>
                    'high_portfolio_turnover',

                'severity' => 'high',

                'title' =>
                    'High portfolio turnover',

                'message' =>
                    'Trading activity replaced the equivalent of the portfolio value at least once during the period.',
            ];
        }

        if ($tradeCount >= 50) {
            $flags[] = [
                'code' =>
                    'high_trade_frequency',

                'severity' => 'moderate',

                'title' =>
                    'Frequent trading detected',

                'message' =>
                    'The portfolio had a high number of purchase and sale transactions.',
            ];
        }

        $feeRate = $averagePortfolioValue > 0
            ? $fees / $averagePortfolioValue
            : null;

        if (
            $feeRate !== null
            && $feeRate >= 0.01
        ) {
            $flags[] = [
                'code' =>
                    'high_trading_costs',

                'severity' => 'high',

                'title' =>
                    'High trading costs',

                'message' =>
                    'Trading fees exceeded 1% of the average portfolio value.',
            ];
        }

        if ($flags === []) {
            $flags[] = [
                'code' =>
                    'no_major_trading_flags',

                'severity' =>
                    'informational',

                'title' =>
                    'No major trading concerns detected',

                'message' =>
                    'Trading activity did not exceed Helmio’s current warning thresholds.',
            ];
        }

        return $flags;
    }

    private function normalizeType(
        ?string $type
    ): string {
        return strtolower(
            trim(
                str_replace(
                    ['-', ' '],
                    '_',
                    $type ?? ''
                )
            )
        );
    }

    private function emptyResult(): array
    {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                'No trading transactions were found.',

            'metrics' => [
                'buy_amount' => 0,
                'sell_amount' => 0,
                'turnover_amount' => 0,
                'turnover_rate' => null,
                'trade_count' => 0,
                'fees' => 0,
                'fee_rate' => null,
            ],

            'risk_level' => null,
            'flags' => [],

            'formula_version' =>
                'trading-0.1.0',
        ];
    }
}