<?php

namespace App\Services\Analytics\Trading;

use App\Data\Analytics\AnalyticsResult;
use App\Models\InvestmentTransaction;
use App\Models\PortfolioValuation;
use App\Models\User;
use Carbon\CarbonInterface;

class TradingAnalyticsService
{
    public const FORMULA_VERSION = 'trading-0.5.0';

    private const MINIMUM_SCORABLE_VALUATION_COUNT = 20;
    private const ESTABLISHED_VALUATION_COUNT = 60;

    public function __construct(
        private readonly TradingMetricsService $tradingMetricsService,
        private readonly RoundTripTradeDetector $roundTripTradeDetector
    ) {
    }

    /**
     * Analyze trading activity for one user and date range.
     *
     * Turnover is calculated only over the period for which consolidated
     * portfolio valuations actually exist. This prevents a full year of
     * transactions from being divided by a portfolio-value denominator
     * based on only a few recent valuation dates.
     *
     * @return array<string, mixed>
     */
    public function analyze(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        $requestedPeriod = [
            'start_date' =>
                $startDate->toDateString(),

            'end_date' =>
                $endDate->toDateString(),
        ];

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

        if ($valuations->isEmpty()) {
            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message:
                        'Portfolio valuation history is required before trading turnover can be calculated.',

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
                                'portfolio_valuations_missing',

                            'message' =>
                                'No consolidated portfolio valuations were available for the requested period.',
                        ],
                    ],

                    data: [
                        'period' =>
                            $requestedPeriod,

                        'requested_period' =>
                            $requestedPeriod,

                        'effective_period' =>
                            null,

                        'summary' => [
                            'transaction_count' => 0,
                            'requested_transaction_count' => 0,
                            'excluded_transaction_count' => 0,
                            'average_portfolio_value' => 0.0,
                            'valuation_count' => 0,
                        ],

                        'risk_level' =>
                            null,

                        'round_trip_analysis' =>
                            $this->emptyRoundTripResult(),
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        $firstValuationDate =
            $valuations
                ->first()
                ->valuation_date
                ->copy()
                ->startOfDay();

        $lastValuationDate =
            $valuations
                ->last()
                ->valuation_date
                ->copy()
                ->endOfDay();

        $effectiveStartDate =
            $firstValuationDate->greaterThan($startDate)
                ? $firstValuationDate
                : $startDate;

        $effectiveEndDate =
            $lastValuationDate->lessThan($endDate)
                ? $lastValuationDate
                : $endDate;

        $effectivePeriod = [
            'start_date' =>
                $effectiveStartDate->toDateString(),

            'end_date' =>
                $effectiveEndDate->toDateString(),
        ];

        /*
         * Count all transactions in the originally requested period so the
         * response can disclose how many were excluded from turnover scoring
         * because matching valuation history was unavailable.
         */
        $requestedTransactionCount =
            InvestmentTransaction::query()
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
                ->count();

        /*
         * Only transactions inside the effective valuation-backed period
         * participate in turnover and round-trip scoring.
         */
        $transactions = InvestmentTransaction::query()
            ->with('security')
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
                    $effectiveStartDate->toDateString(),
                    $effectiveEndDate->toDateString(),
                ]
            )
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $averagePortfolioValue =
            (float) $valuations->avg(
                fn (
                    PortfolioValuation $valuation
                ): float =>
                    $valuation->total_value
            );

        $excludedTransactionCount =
            max(
                0,
                $requestedTransactionCount
                - $transactions->count()
            );

        $summary = [
            'transaction_count' =>
                $transactions->count(),

            'requested_transaction_count' =>
                $requestedTransactionCount,

            'excluded_transaction_count' =>
                $excludedTransactionCount,

            'average_portfolio_value' =>
                round($averagePortfolioValue, 2),

            'valuation_count' =>
                $valuations->count(),
        ];

        if ($transactions->isEmpty()) {
            $warnings = [
                [
                    'code' =>
                        'no_trading_transactions',

                    'message' =>
                        'No qualifying trading activity was available inside the valuation-backed analysis period.',
                ],
            ];

            $warnings = array_values(
                array_merge(
                    $warnings,
                    $this->periodWarnings(
                        requestedPeriod:
                            $requestedPeriod,

                        effectivePeriod:
                            $effectivePeriod,

                        valuationCount:
                            $valuations->count(),

                        excludedTransactionCount:
                            $excludedTransactionCount,
                    )
                )
            );

            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message:
                        'No investment transactions were found inside the valuation-backed analysis period.',

                    metrics: [
                        'buy_amount' => 0.0,
                        'sell_amount' => 0.0,
                        'turnover_amount' => 0.0,
                        'turnover_rate' => null,
                        'trade_count' => 0,
                        'fees' => 0.0,
                        'fee_rate' => null,
                    ],

                    warnings:
                        $warnings,

                    data: [
                        'period' =>
                            $effectivePeriod,

                        'requested_period' =>
                            $requestedPeriod,

                        'effective_period' =>
                            $effectivePeriod,

                        'summary' =>
                            $summary,

                        'risk_level' =>
                            null,

                        'round_trip_analysis' =>
                            $this->emptyRoundTripResult(),
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        $tradingMetricsResult =
            $this->tradingMetricsService->analyze(
                transactions:
                    $transactions
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
            $this->roundTripTradeDetector
                ->analyze(
                    $transactions
                );

        $flags = array_values(
            array_merge(
                $tradingMetricsResult['flags']
                    ?? [],

                $roundTripResult['flags']
                    ?? [],
            )
        );

        $warnings = array_values(
            array_merge(
                $this->buildWarnings(
                    valuations:
                        $valuations->count(),

                    averagePortfolioValue:
                        $averagePortfolioValue,

                    roundTripResult:
                        $roundTripResult,
                ),

                $this->periodWarnings(
                    requestedPeriod:
                        $requestedPeriod,

                    effectivePeriod:
                        $effectivePeriod,

                    valuationCount:
                        $valuations->count(),

                    excludedTransactionCount:
                        $excludedTransactionCount,
                )
            )
        );

        $valuationCount =
            $valuations->count();

        $confidence =
            $this->confidenceData(
                valuationCount:
                    $valuationCount,
            );

        /*
         * Turnover can be calculated with a very short valuation history,
         * but Helmio should not promote that estimate into an established
         * consumer-facing trading score until enough valuation points exist.
         */
        if (
            $valuationCount
            < self::MINIMUM_SCORABLE_VALUATION_COUNT
        ) {
            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message:
                        sprintf(
                            'Helmio is still building trading history. At least %d consolidated portfolio valuation points are required before a trading score is assigned.',
                            self::MINIMUM_SCORABLE_VALUATION_COUNT
                        ),

                    metrics:
                        $tradingMetricsResult['metrics']
                        ?? [],

                    warnings:
                        $warnings,

                    data: [
                        'period' =>
                            $effectivePeriod,

                        'requested_period' =>
                            $requestedPeriod,

                        'effective_period' =>
                            $effectivePeriod,

                        'summary' =>
                            $summary,

                        'risk_level' =>
                            null,

                        'provisional_risk_level' =>
                            $tradingMetricsResult[
                                'risk_level'
                            ] ?? null,

                        'round_trip_analysis' =>
                            $roundTripResult,

                        'confidence' =>
                            $confidence,
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        $score =
            $this->calculateScore(
                metrics:
                    $tradingMetricsResult['metrics']
                    ?? [],

                roundTripResult:
                    $roundTripResult,

                valuationCount:
                    $valuationCount,
            );

        $result =
            AnalyticsResult::complete(
                metrics:
                    $tradingMetricsResult['metrics']
                    ?? [],

                flags:
                    $flags,

                warnings:
                    $warnings,

                data: [
                    /*
                     * Keep "period" as the effective scored period for
                     * existing consumers while also exposing both periods
                     * explicitly.
                     */
                    'period' =>
                        $effectivePeriod,

                    'requested_period' =>
                        $requestedPeriod,

                    'effective_period' =>
                        $effectivePeriod,

                    'summary' =>
                        $summary,

                    'risk_level' =>
                        $tradingMetricsResult[
                            'risk_level'
                        ] ?? null,

                    'round_trip_analysis' =>
                        $roundTripResult,

                    'confidence' =>
                        $confidence,
                ],

                score:
                    $score,

                label:
                    $score === null
                        ? null
                        : $this->scoreLabel(
                            $score
                        ),

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
        array $roundTripResult,
        int $valuationCount
    ): ?int {
        $turnoverRate =
            $metrics['turnover_rate']
            ?? null;

        if ($turnoverRate === null) {
            return null;
        }

        $tradeCount = (int) (
            $metrics['trade_count']
            ?? 0
        );

        $feeRate =
            $metrics['fee_rate']
            ?? null;

        $roundTripCount =
            (int) data_get(
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

        if (
            $valuationCount
            < self::ESTABLISHED_VALUATION_COUNT
        ) {
            $score -= 5;
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
            ($roundTripResult['status']
                ?? null)
            === 'insufficient_data'
        ) {
            $warnings[] = [
                'code' =>
                    'round_trip_analysis_limited',

                'message' =>
                    'No completed non-cash buy-and-sell round trips were available for analysis.',
            ];
        }

        return $warnings;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function periodWarnings(
        array $requestedPeriod,
        array $effectivePeriod,
        int $valuationCount,
        int $excludedTransactionCount
    ): array {
        $warnings = [];

        if (
            $requestedPeriod['start_date']
            !== $effectivePeriod['start_date']
            || $requestedPeriod['end_date']
            !== $effectivePeriod['end_date']
        ) {
            $warnings[] = [
                'code' =>
                    'trading_period_aligned_to_valuation_history',

                'message' =>
                    sprintf(
                        'Trading turnover was scored from %s through %s because consolidated portfolio valuations were not available for the entire requested period.',
                        $effectivePeriod['start_date'],
                        $effectivePeriod['end_date'],
                    ),
            ];
        }

        if ($excludedTransactionCount > 0) {
            $warnings[] = [
                'code' =>
                    'transactions_excluded_without_matching_valuation_history',

                'message' =>
                    sprintf(
                        '%d transaction(s) outside the valuation-backed period were excluded from turnover and round-trip scoring.',
                        $excludedTransactionCount
                    ),
            ];
        }

        if (
            $valuationCount
            < self::MINIMUM_SCORABLE_VALUATION_COUNT
        ) {
            $warnings[] = [
                'code' =>
                    'insufficient_trading_history',

                'message' =>
                    sprintf(
                        'Trading turnover is based on only %d consolidated portfolio valuation point(s); at least %d are required before Helmio assigns a trading score.',
                        $valuationCount,
                        self::MINIMUM_SCORABLE_VALUATION_COUNT
                    ),
            ];
        } elseif (
            $valuationCount
            < self::ESTABLISHED_VALUATION_COUNT
        ) {
            $warnings[] = [
                'code' =>
                    'limited_turnover_history',

                'message' =>
                    sprintf(
                        'Trading turnover is based on %d consolidated portfolio valuation point(s) and should be interpreted with additional caution until at least %d are available.',
                        $valuationCount,
                        self::ESTABLISHED_VALUATION_COUNT
                    ),
            ];
        }

        return $warnings;
    }

    /**
     * @return array<string, mixed>
     */
    private function confidenceData(
        int $valuationCount
    ): array {
        $level = match (true) {
            $valuationCount
                < self::MINIMUM_SCORABLE_VALUATION_COUNT =>
                    'insufficient',

            $valuationCount
                < self::ESTABLISHED_VALUATION_COUNT =>
                    'limited',

            default =>
                'established',
        };

        return [
            'level' =>
                $level,

            'valuation_count' =>
                $valuationCount,

            'minimum_scorable_valuation_count' =>
                self::MINIMUM_SCORABLE_VALUATION_COUNT,

            'established_valuation_count' =>
                self::ESTABLISHED_VALUATION_COUNT,

            'is_scorable' =>
                $valuationCount
                >= self::MINIMUM_SCORABLE_VALUATION_COUNT,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRoundTripResult(): array
    {
        return [
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

            'round_trips' =>
                [],

            'flags' =>
                [],

            'warnings' =>
                [],

            'formula_version' =>
                'round-trip-0.2.0',
        ];
    }

    /**
     * Preserve the fields currently consumed by the controller and Blade
     * page while also returning the shared AnalyticsResult structure.
     *
     * @return array<string, mixed>
     */
    private function legacyCompatibleResult(
        AnalyticsResult $result
    ): array {
        $shared =
            $result->toArray();

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