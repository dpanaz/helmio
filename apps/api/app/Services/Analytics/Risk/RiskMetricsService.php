<?php

namespace App\Services\Analytics\Risk;

use InvalidArgumentException;

class RiskMetricsService
{
    private const TRADING_DAYS_PER_YEAR = 252;

    /**
     * Calculate portfolio risk metrics from daily decimal returns.
     *
     * Example daily return:
     * 0.01 = positive 1%
     * -0.02 = negative 2%
     *
     * @param array<int, float|int|string|null> $portfolioReturns
     * @param array<int, float|int|string|null> $benchmarkReturns
     */
    public function analyze(
        array $portfolioReturns,
        array $benchmarkReturns = [],
        float $annualRiskFreeRate = 0.0,
        float $minimumAcceptableAnnualReturn = 0.0
    ): array {
        $portfolioReturns = $this->normalizeReturns(
            $portfolioReturns
        );

        $benchmarkReturns = $this->normalizeReturns(
            $benchmarkReturns
        );

        if (count($portfolioReturns) < 2) {
            return $this->insufficientDataResult(
                'At least two valid portfolio returns are required.'
            );
        }

        $dailyRiskFreeRate = $this->annualRateToDaily(
            $annualRiskFreeRate
        );

        $dailyMinimumAcceptableReturn =
            $this->annualRateToDaily(
                $minimumAcceptableAnnualReturn
            );

        $dailyMean = $this->mean($portfolioReturns);

        $dailyStandardDeviation =
            $this->sampleStandardDeviation(
                $portfolioReturns
            );

        $annualizedVolatility =
            $dailyStandardDeviation
            * sqrt(self::TRADING_DAYS_PER_YEAR);

        $annualizedReturn =
            $this->annualizedGeometricReturn(
                $portfolioReturns
            );

        $maximumDrawdown =
            $this->maximumDrawdown(
                $portfolioReturns
            );

        $dailyDownsideDeviation =
            $this->downsideDeviation(
                returns: $portfolioReturns,
                dailyMinimumAcceptableReturn:
                    $dailyMinimumAcceptableReturn,
            );

        $annualizedDownsideDeviation =
            $dailyDownsideDeviation
            * sqrt(self::TRADING_DAYS_PER_YEAR);

        $sharpeRatio = $this->sharpeRatio(
            returns: $portfolioReturns,
            dailyRiskFreeRate: $dailyRiskFreeRate,
        );

        $sortinoRatio = $this->sortinoRatio(
            returns: $portfolioReturns,
            dailyMinimumAcceptableReturn:
                $dailyMinimumAcceptableReturn,
        );

        $beta = $this->beta(
            portfolioReturns: $portfolioReturns,
            benchmarkReturns: $benchmarkReturns,
        );

        $positiveDays = count(
            array_filter(
                $portfolioReturns,
                fn (float $return): bool =>
                    $return > 0
            )
        );

        $negativeDays = count(
            array_filter(
                $portfolioReturns,
                fn (float $return): bool =>
                    $return < 0
            )
        );

        $flatDays =
            count($portfolioReturns)
            - $positiveDays
            - $negativeDays;

        $warnings = $this->buildWarnings(
            portfolioReturns: $portfolioReturns,
            benchmarkReturns: $benchmarkReturns,
            beta: $beta,
        );

        return [
            'status' => 'complete',

            'metrics' => [
                'annualized_return' =>
                    $this->roundMetric(
                        $annualizedReturn
                    ),

                'annualized_volatility' =>
                    $this->roundMetric(
                        $annualizedVolatility
                    ),

                'maximum_drawdown' =>
                    $this->roundMetric(
                        $maximumDrawdown
                    ),

                'downside_deviation' =>
                    $this->roundMetric(
                        $annualizedDownsideDeviation
                    ),

                'sharpe_ratio' =>
                    $this->roundMetric(
                        $sharpeRatio
                    ),

                'sortino_ratio' =>
                    $this->roundMetric(
                        $sortinoRatio
                    ),

                'beta' =>
                    $this->roundMetric(
                        $beta
                    ),

                'average_daily_return' =>
                    $this->roundMetric(
                        $dailyMean
                    ),

                'daily_standard_deviation' =>
                    $this->roundMetric(
                        $dailyStandardDeviation
                    ),
            ],

            'observations' => [
                'portfolio_return_count' =>
                    count($portfolioReturns),

                'benchmark_return_count' =>
                    count($benchmarkReturns),

                'positive_days' =>
                    $positiveDays,

                'negative_days' =>
                    $negativeDays,

                'flat_days' =>
                    $flatDays,
            ],

            'assumptions' => [
                'trading_days_per_year' =>
                    self::TRADING_DAYS_PER_YEAR,

                'annual_risk_free_rate' =>
                    $annualRiskFreeRate,

                'minimum_acceptable_annual_return' =>
                    $minimumAcceptableAnnualReturn,
            ],

            'risk_level' =>
                $this->riskLevel(
                    annualizedVolatility:
                        $annualizedVolatility,

                    maximumDrawdown:
                        $maximumDrawdown,
                ),

            'warnings' => $warnings,

            'formula_version' => 'risk-0.1.0',
        ];
    }

    /**
     * @param array<int, float|int|string|null> $returns
     * @return array<int, float>
     */
    private function normalizeReturns(
        array $returns
    ): array {
        $normalized = [];

        foreach ($returns as $return) {
            if (
                $return === null
                || $return === ''
            ) {
                continue;
            }

            if (! is_numeric($return)) {
                throw new InvalidArgumentException(
                    'Every return must be numeric or null.'
                );
            }

            $return = (float) $return;

            if ($return < -1) {
                throw new InvalidArgumentException(
                    'A return cannot be less than -100%.'
                );
            }

            $normalized[] = $return;
        }

        return array_values($normalized);
    }

    /**
     * @param array<int, float> $values
     */
    private function mean(
        array $values
    ): float {
        if ($values === []) {
            return 0.0;
        }

        return array_sum($values)
            / count($values);
    }

    /**
     * Uses sample standard deviation with n - 1.
     *
     * @param array<int, float> $values
     */
    private function sampleStandardDeviation(
        array $values
    ): float {
        $count = count($values);

        if ($count < 2) {
            return 0.0;
        }

        $mean = $this->mean($values);

        $squaredDifferences = array_map(
            fn (float $value): float =>
                ($value - $mean) ** 2,
            $values
        );

        return sqrt(
            array_sum($squaredDifferences)
            / ($count - 1)
        );
    }

    /**
     * Compounds the observed daily returns and annualizes them.
     *
     * @param array<int, float> $returns
     */
    private function annualizedGeometricReturn(
        array $returns
    ): ?float {
        if ($returns === []) {
            return null;
        }

        $growthFactor = 1.0;

        foreach ($returns as $return) {
            $growthFactor *= 1 + $return;
        }

        if ($growthFactor <= 0) {
            return -1.0;
        }

        return pow(
            $growthFactor,
            self::TRADING_DAYS_PER_YEAR
                / count($returns)
        ) - 1;
    }

    /**
     * Calculates peak-to-trough drawdown from compounded returns.
     *
     * @param array<int, float> $returns
     */
    private function maximumDrawdown(
        array $returns
    ): float {
        $wealthIndex = 1.0;
        $peak = 1.0;
        $maximumDrawdown = 0.0;

        foreach ($returns as $return) {
            $wealthIndex *= 1 + $return;

            $peak = max(
                $peak,
                $wealthIndex
            );

            if ($peak <= 0) {
                continue;
            }

            $drawdown =
                ($wealthIndex / $peak) - 1;

            $maximumDrawdown = min(
                $maximumDrawdown,
                $drawdown
            );
        }

        return $maximumDrawdown;
    }

    /**
     * Calculates downside deviation using all observations.
     *
     * Returns above the minimum acceptable return contribute zero.
     *
     * @param array<int, float> $returns
     */
    private function downsideDeviation(
        array $returns,
        float $dailyMinimumAcceptableReturn
    ): float {
        if ($returns === []) {
            return 0.0;
        }

        $squaredDownsideDifferences =
            array_map(
                function (
                    float $return
                ) use (
                    $dailyMinimumAcceptableReturn
                ): float {
                    $difference =
                        min(
                            0.0,
                            $return
                            - $dailyMinimumAcceptableReturn
                        );

                    return $difference ** 2;
                },
                $returns
            );

        return sqrt(
            array_sum(
                $squaredDownsideDifferences
            ) / count($returns)
        );
    }

    /**
     * @param array<int, float> $returns
     */
    private function sharpeRatio(
        array $returns,
        float $dailyRiskFreeRate
    ): ?float {
        $excessReturns = array_map(
            fn (float $return): float =>
                $return - $dailyRiskFreeRate,
            $returns
        );

        $standardDeviation =
            $this->sampleStandardDeviation(
                $excessReturns
            );

        if ($standardDeviation <= 0) {
            return null;
        }

        return (
            $this->mean($excessReturns)
            / $standardDeviation
        ) * sqrt(self::TRADING_DAYS_PER_YEAR);
    }

    /**
     * @param array<int, float> $returns
     */
    private function sortinoRatio(
        array $returns,
        float $dailyMinimumAcceptableReturn
    ): ?float {
        $downsideDeviation =
            $this->downsideDeviation(
                returns: $returns,
                dailyMinimumAcceptableReturn:
                    $dailyMinimumAcceptableReturn,
            );

        if ($downsideDeviation <= 0) {
            return null;
        }

        $averageExcessReturn =
            $this->mean($returns)
            - $dailyMinimumAcceptableReturn;

        return (
            $averageExcessReturn
            / $downsideDeviation
        ) * sqrt(self::TRADING_DAYS_PER_YEAR);
    }

    /**
     * Beta = covariance(portfolio, benchmark)
     *        / variance(benchmark)
     *
     * Arrays are paired by position and truncated to the shorter length.
     *
     * @param array<int, float> $portfolioReturns
     * @param array<int, float> $benchmarkReturns
     */
    private function beta(
        array $portfolioReturns,
        array $benchmarkReturns
    ): ?float {
        $observationCount = min(
            count($portfolioReturns),
            count($benchmarkReturns)
        );

        if ($observationCount < 2) {
            return null;
        }

        $portfolioReturns = array_slice(
            $portfolioReturns,
            0,
            $observationCount
        );

        $benchmarkReturns = array_slice(
            $benchmarkReturns,
            0,
            $observationCount
        );

        $portfolioMean =
            $this->mean($portfolioReturns);

        $benchmarkMean =
            $this->mean($benchmarkReturns);

        $covarianceSum = 0.0;
        $benchmarkVarianceSum = 0.0;

        for (
            $index = 0;
            $index < $observationCount;
            $index++
        ) {
            $portfolioDifference =
                $portfolioReturns[$index]
                - $portfolioMean;

            $benchmarkDifference =
                $benchmarkReturns[$index]
                - $benchmarkMean;

            $covarianceSum +=
                $portfolioDifference
                * $benchmarkDifference;

            $benchmarkVarianceSum +=
                $benchmarkDifference ** 2;
        }

        if ($benchmarkVarianceSum <= 0) {
            return null;
        }

        return $covarianceSum
            / $benchmarkVarianceSum;
    }

    private function annualRateToDaily(
        float $annualRate
    ): float {
        if ($annualRate <= -1) {
            throw new InvalidArgumentException(
                'An annual rate cannot be less than or equal to -100%.'
            );
        }

        return pow(
            1 + $annualRate,
            1 / self::TRADING_DAYS_PER_YEAR
        ) - 1;
    }

    private function riskLevel(
        float $annualizedVolatility,
        float $maximumDrawdown
    ): string {
        $drawdownMagnitude =
            abs($maximumDrawdown);

        return match (true) {
            $annualizedVolatility >= 0.30
                || $drawdownMagnitude >= 0.35
                    => 'very_high',

            $annualizedVolatility >= 0.22
                || $drawdownMagnitude >= 0.25
                    => 'high',

            $annualizedVolatility >= 0.14
                || $drawdownMagnitude >= 0.15
                    => 'moderate',

            $annualizedVolatility >= 0.08
                || $drawdownMagnitude >= 0.08
                    => 'low',

            default => 'very_low',
        };
    }

    /**
     * @param array<int, float> $portfolioReturns
     * @param array<int, float> $benchmarkReturns
     */
    private function buildWarnings(
        array $portfolioReturns,
        array $benchmarkReturns,
        ?float $beta
    ): array {
        $warnings = [];

        if (count($portfolioReturns) < 30) {
            $warnings[] = [
                'code' =>
                    'limited_risk_history',

                'message' =>
                    'Risk metrics are based on fewer than 30 return observations.',
            ];
        }

        if ($benchmarkReturns === []) {
            $warnings[] = [
                'code' =>
                    'benchmark_returns_missing',

                'message' =>
                    'Benchmark returns were unavailable, so beta could not be calculated.',
            ];
        } elseif ($beta === null) {
            $warnings[] = [
                'code' =>
                    'beta_unavailable',

                'message' =>
                    'Beta could not be calculated from the available benchmark returns.',
            ];
        }

        return $warnings;
    }

    private function insufficientDataResult(
        string $message
    ): array {
        return [
            'status' => 'insufficient_data',

            'message' => $message,

            'metrics' => [
                'annualized_return' => null,
                'annualized_volatility' => null,
                'maximum_drawdown' => null,
                'downside_deviation' => null,
                'sharpe_ratio' => null,
                'sortino_ratio' => null,
                'beta' => null,
                'average_daily_return' => null,
                'daily_standard_deviation' => null,
            ],

            'observations' => [
                'portfolio_return_count' => 0,
                'benchmark_return_count' => 0,
                'positive_days' => 0,
                'negative_days' => 0,
                'flat_days' => 0,
            ],

            'assumptions' => [
                'trading_days_per_year' =>
                    self::TRADING_DAYS_PER_YEAR,
            ],

            'risk_level' => null,

            'warnings' => [
                [
                    'code' =>
                        'insufficient_risk_history',

                    'message' => $message,
                ],
            ],

            'formula_version' => 'risk-0.1.0',
        ];
    }

    private function roundMetric(
        ?float $value
    ): ?float {
        return $value === null
            ? null
            : round($value, 10);
    }
}