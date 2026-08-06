<?php

namespace App\Services\Analytics\Trading;

use App\Data\Analytics\AnalyticsResult;
use App\Models\InvestmentTransaction;
use App\Models\PortfolioValuation;
use App\Models\User;
use Carbon\CarbonInterface;

class TradingAnalyticsService
{
    public const FORMULA_VERSION = 'trading-0.3.0';

    public function __construct(
        private readonly TradingMetricsService $tradingMetricsService,
        private readonly RoundTripTradeDetector $roundTripTradeDetector
    ) {
    }

    /**
     * Analyze trading activity for one user and date range.
     *
     * @return array<string, mixed>
     */
    public function analyze(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        $transactions = InvestmentTransaction::query()
            ->whereHas(
                'investmentAccount',
                fn ($query) => $query->where(
                    'user_id',
                    $user->id
                )
            )
            ->whereBetween(
                'transaction_date',
                [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ]
            )
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $valuations = PortfolioValuation::query()
            ->where('user_id', $user->id)
            ->whereNull('investment_account_id')
            ->whereBetween(
                'valuation_date',
                [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ]
            )
            ->orderBy('valuation_date')
            ->get();

        $period = [
            'start_date' =>
                $startDate->toDateString(),

            'end_date' =>
                $endDate->toDateString(),
        ];

        $averagePortfolioValue = $valuations->isEmpty()
            ? 0.0
            : (float) $valuations->avg(
                fn (
                    PortfolioValuation $valuation
                ): float => $valuation->total_value
            );

        $summary = [
            'transaction_count' =>
                $transactions->count(),

            'average_portfolio_value' =>
                round($averagePortfolioValue, 2),

            'valuation_count' =>
                $valuations->count(),
        ];

        if ($transactions->isEmpty()) {
            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message:
                        'No investment transactions were found for the selected period.',

                    metrics: [
                        'buy_amount' => 0.0,
                        'sell_amount' => 0.0,
                        'turnover_amount' => 0.0,
                        'turnover_rate' => null,
                        'trade_count' => 0,
                        'fees' => 0.0,
                        'fee_rate' => null,
                    ],

                    warnings: [
                        [
                            'code' =>
                                'no_trading_transactions',

                            'message' =>
                                'No qualifying trading activity was available for analysis.',
                        ],
                    ],

                    data: [
                        'period' => $period,
                        'summary' => $summary,
                        'risk_level' => null,

                        'round_trip_analysis' => [
                            'status' =>
                                'insufficient_data',

                            'metrics' => [
                                'round_trip_count' => 0,
                                'short_term_round_trip_count' => 0,
                                'very_short_round_trip_count' => 0,
                                'average_holding_period_days' => null,
                                'total_round_trip_fees' => 0.0,
                                'total_realized_gain_loss' => 0.0,
                            ],

                            'round_trips' => [],
                            'flags' => [],

                            'formula_version' =>
                                'round-trip-0.1.0',
                        ],
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        $tradingMetricsResult =
            $this->tradingMetricsService->analyze(
                transactions: $transactions
                    ->map(
                        fn (
                            InvestmentTransaction $transaction
                        ): array => [
                            'transaction_type' =>
                                $transaction->transaction_type,

                            'gross_amount' =>
                                (float) (
                                    $transaction->gross_amount
                                    ?? 0
                                ),

                            'net_amount' =>
                                (float) (
                                    $transaction->net_amount
                                    ?? 0
                                ),

                            'fees' =>
                                (float) (
                                    $transaction->fees
                                    ?? 0
                                ),
                        ]
                    )
                    ->all(),

                averagePortfolioValue:
                    $averagePortfolioValue,
            );

        $roundTripResult =
            $this->roundTripTradeDetector->analyze(
                $transactions
            );

        $flags = array_values(
            array_merge(
                $tradingMetricsResult['flags'] ?? [],
                $roundTripResult['flags'] ?? [],
            )
        );

        $warnings = $this->buildWarnings(
            valuations:
                $valuations->count(),

            averagePortfolioValue:
                $averagePortfolioValue,

            roundTripResult:
                $roundTripResult,
        );

        $score = $this->calculateScore(
            metrics:
                $tradingMetricsResult['metrics'] ?? [],

            roundTripResult:
                $roundTripResult,
        );

        $result = AnalyticsResult::complete(
            metrics:
                $tradingMetricsResult['metrics'] ?? [],

            flags:
                $flags,

            warnings:
                $warnings,

            data: [
                'period' =>
                    $period,

                'summary' =>
                    $summary,

                'risk_level' =>
                    $tradingMetricsResult[
                        'risk_level'
                    ] ?? null,

                'round_trip_analysis' =>
                    $roundTripResult,
            ],

            score:
                $score,

            label:
                $score === null
                    ? null
                    : $this->scoreLabel($score),

            formulaVersion:
                self::FORMULA_VERSION,
        );

        return $this->legacyCompatibleResult(
            $result
        );
    }

    /**
     * Calculate a consumer-facing trading score.
     *
     * Higher scores represent more disciplined trading.
     *
     * @param array<string, mixed> $metrics
     * @param array<string, mixed> $roundTripResult
     */
    private function calculateScore(
        array $metrics,
        array $roundTripResult
    ): ?int {
        $turnoverRate =
            $metrics['turnover_rate'] ?? null;

        if ($turnoverRate === null) {
            return null;
        }

        $tradeCount = (int) (
            $metrics['trade_count'] ?? 0
        );

        $feeRate = $metrics['fee_rate'] ?? null;

        $roundTripCount = (int) data_get(
            $roundTripResult,
            'metrics.round_trip_count',
            0
        );

        $shortTermRoundTripCount =
            (int) data_get(
                $roundTripResult,
                'metrics.short_term_round_trip_count',
                0
            );

        $veryShortRoundTripCount =
            (int) data_get(
                $roundTripResult,
                'metrics.very_short_round_trip_count',
                0
            );

        $score = match (true) {
            $turnoverRate <= 0.20 => 100,
            $turnoverRate <= 0.50 => 90,
            $turnoverRate <= 1.00 => 75,
            $turnoverRate <= 2.00 => 50,
            default => 25,
        };

        if ($tradeCount >= 100) {
            $score -= 15;
        } elseif ($tradeCount >= 50) {
            $score -= 10;
        }

        if (
            $feeRate !== null
            && $feeRate >= 0.01
        ) {
            $score -= 15;
        } elseif (
            $feeRate !== null
            && $feeRate >= 0.005
        ) {
            $score -= 8;
        }

        if ($roundTripCount >= 10) {
            $score -= 15;
        } elseif ($roundTripCount >= 5) {
            $score -= 8;
        }

        if ($shortTermRoundTripCount >= 5) {
            $score -= 15;
        } elseif ($shortTermRoundTripCount >= 2) {
            $score -= 8;
        }

        if ($veryShortRoundTripCount >= 3) {
            $score -= 20;
        } elseif ($veryShortRoundTripCount >= 1) {
            $score -= 8;
        }

        return max(
            0,
            min(100, $score)
        );
    }

    /**
     * @param array<string, mixed> $roundTripResult
     * @return array<int, array<string, mixed>>
     */
    private function buildWarnings(
        int $valuations,
        float $averagePortfolioValue,
        array $roundTripResult
    ): array {
        $warnings = [];

        if ($valuations === 0) {
            $warnings[] = [
                'code' =>
                    'portfolio_valuations_missing',

                'message' =>
                    'Portfolio valuation history was unavailable, so turnover and fee rates may not be calculated.',
            ];
        }

        if ($averagePortfolioValue <= 0) {
            $warnings[] = [
                'code' =>
                    'average_portfolio_value_unavailable',

                'message' =>
                    'Average portfolio value was unavailable or zero.',
            ];
        }

        if (
            ($roundTripResult['status'] ?? null)
            === 'insufficient_data'
        ) {
            $warnings[] = [
                'code' =>
                    'round_trip_analysis_limited',

                'message' =>
                    'No completed buy-and-sell round trips were available for analysis.',
            ];
        }

        return $warnings;
    }

    /**
     * Preserve the fields currently consumed by the controller and Blade
     * page while also returning the new shared AnalyticsResult structure.
     *
     * The duplicated top-level fields can be removed after all consumers
     * have migrated to the nested "data" element.
     *
     * @return array<string, mixed>
     */
    private function legacyCompatibleResult(
        AnalyticsResult $result
    ): array {
        $shared = $result->toArray();

        return array_merge(
            $shared,
            $result->data
        );
    }

    private function scoreLabel(
        int $score
    ): string {
        return match (true) {
            $score >= 90 =>
                'Excellent',

            $score >= 80 =>
                'Very good',

            $score >= 70 =>
                'Good',

            $score >= 60 =>
                'Fair',

            $score >= 40 =>
                'Needs attention',

            default =>
                'Action recommended',
        };
    }
}