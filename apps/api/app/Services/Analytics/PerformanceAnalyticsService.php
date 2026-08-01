<?php

namespace App\Services\Analytics;

use App\Models\InvestmentAccount;
use App\Models\PortfolioSnapshot;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PerformanceAnalyticsService
{
    public const FORMULA_VERSION = 'performance-1.0.0';

    /**
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function calculate(
        Collection $accounts,
        ?CarbonInterface $asOf = null,
    ): array {
        $asOf ??= now();

        $accountResults = $accounts
            ->map(
                fn (InvestmentAccount $account): array =>
                    $this->calculateAccount($account, $asOf),
            )
            ->values();

        $validAccounts = $accountResults
            ->filter(
                fn (array $account): bool =>
                    $account['time_weighted_return'] !== null,
            );

        $totalBeginningValue = (float) $validAccounts->sum(
            'beginning_value',
        );

        $totalEndingValue = (float) $validAccounts->sum(
            'ending_value',
        );

        $totalExternalFlows = (float) $validAccounts->sum(
            'external_cash_flows',
        );

        $weightedPortfolioReturn = $totalBeginningValue > 0
            ? (float) $validAccounts->sum(
                fn (array $account): float =>
                    $account['time_weighted_return']
                    * $account['beginning_value'],
            ) / $totalBeginningValue
            : null;

        $benchmarkAccounts = $validAccounts
            ->filter(
                fn (array $account): bool =>
                    $account['benchmark_return'] !== null,
            );

        $benchmarkBeginningValue = (float) $benchmarkAccounts->sum(
            'beginning_value',
        );

        $weightedBenchmarkReturn = $benchmarkBeginningValue > 0
            ? (float) $benchmarkAccounts->sum(
                fn (array $account): float =>
                    $account['benchmark_return']
                    * $account['beginning_value'],
            ) / $benchmarkBeginningValue
            : null;

        $excessReturn = (
            $weightedPortfolioReturn !== null
            && $weightedBenchmarkReturn !== null
        )
            ? $weightedPortfolioReturn - $weightedBenchmarkReturn
            : null;

        $dataCompleteness = $accounts->count() > 0
            ? $validAccounts->count() / $accounts->count()
            : 0;

        $benchmarkCoverage = $validAccounts->count() > 0
            ? $benchmarkAccounts->count() / $validAccounts->count()
            : 0;

        $scoreResult = $this->calculateScore(
            portfolioReturn: $weightedPortfolioReturn,
            benchmarkReturn: $weightedBenchmarkReturn,
            excessReturn: $excessReturn,
            dataCompleteness: $dataCompleteness,
            benchmarkCoverage: $benchmarkCoverage,
            validAccountCount: $validAccounts->count(),
        );

        return [
            'score' => $scoreResult['score'],
            'label' => $scoreResult['label'],
            'reasons' => $scoreResult['reasons'],
            'recommendations' =>
                $scoreResult['recommendations'],

            'metrics' => [
                'beginning_value' =>
                    round($totalBeginningValue, 2),
                'ending_value' =>
                    round($totalEndingValue, 2),
                'external_cash_flows' =>
                    round($totalExternalFlows, 2),
                'net_growth' => round(
                    $totalEndingValue
                    - $totalBeginningValue
                    - $totalExternalFlows,
                    2,
                ),
                'portfolio_return' =>
                    $weightedPortfolioReturn,
                'benchmark_return' =>
                    $weightedBenchmarkReturn,
                'excess_return' => $excessReturn,
                'data_completeness' =>
                    $dataCompleteness,
                'benchmark_coverage' =>
                    $benchmarkCoverage,
                'valid_account_count' =>
                    $validAccounts->count(),
                'account_count' =>
                    $accounts->count(),
            ],

            'accounts' => $accountResults,
            'calculated_for_date' =>
                $asOf->toDateString(),
            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateAccount(
        InvestmentAccount $account,
        CarbonInterface $asOf,
    ): array {
        $account->loadMissing([
            'portfolioSnapshots',
            'benchmark.returns',
        ]);

        $snapshots = $account->portfolioSnapshots
            ->filter(
                fn (PortfolioSnapshot $snapshot): bool =>
                    $snapshot->snapshot_date->lte($asOf),
            )
            ->sortBy(
                fn (PortfolioSnapshot $snapshot): int =>
                    $snapshot->snapshot_date->timestamp,
            )
            ->values();

        if ($snapshots->count() < 2) {
            return $this->insufficientAccountResult(
                $account,
                'At least two portfolio snapshots are required.',
                $snapshots->count(),
            );
        }

        $beginningSnapshot = $snapshots->first();
        $endingSnapshot = $snapshots->last();

        $beginningValue =
            (float) $beginningSnapshot->ending_value;

        $endingValue =
            (float) $endingSnapshot->ending_value;

        if ($beginningValue <= 0) {
            return $this->insufficientAccountResult(
                $account,
                'The beginning portfolio value must be greater than zero.',
                $snapshots->count(),
            );
        }

        $subperiodReturns = collect();

        for ($index = 1; $index < $snapshots->count(); $index++) {
            $previous = $snapshots[$index - 1];
            $current = $snapshots[$index];

            $previousValue =
                (float) $previous->ending_value;

            if ($previousValue <= 0) {
                continue;
            }

            $currentValue =
                (float) $current->ending_value;

            $externalFlow =
                (float) $current->external_cash_flow;

            /*
             * Assumption:
             * External cash flow occurs at the end of the period.
             *
             * Return =
             * (Ending Value - External Flow - Beginning Value)
             * / Beginning Value
             */
            $periodReturn = (
                $currentValue
                - $externalFlow
                - $previousValue
            ) / $previousValue;

            $subperiodReturns->push([
                'start_date' =>
                    $previous->snapshot_date->toDateString(),
                'end_date' =>
                    $current->snapshot_date->toDateString(),
                'beginning_value' =>
                    round($previousValue, 2),
                'ending_value' =>
                    round($currentValue, 2),
                'external_cash_flow' =>
                    round($externalFlow, 2),
                'period_return' =>
                    $periodReturn,
            ]);
        }

        if ($subperiodReturns->isEmpty()) {
            return $this->insufficientAccountResult(
                $account,
                'No valid performance periods could be calculated.',
                $snapshots->count(),
            );
        }

        $timeWeightedReturn = $subperiodReturns
            ->reduce(
                fn (
                    float $compound,
                    array $period,
                ): float =>
                    $compound
                    * (1 + $period['period_return']),
                1.0,
            ) - 1;

        $externalCashFlows = (float) $snapshots
            ->slice(1)
            ->sum(
                fn (PortfolioSnapshot $snapshot): float =>
                    (float) $snapshot->external_cash_flow,
            );

        $simpleReturn = (
            $endingValue
            - $externalCashFlows
            - $beginningValue
        ) / $beginningValue;

        $benchmarkReturn = $this->calculateBenchmarkReturn(
            $account,
            $beginningSnapshot->snapshot_date,
            $endingSnapshot->snapshot_date,
        );

        return [
            'account_id' => $account->id,
            'account_name' => $account->name,
            'benchmark_name' =>
                $account->benchmark?->name,
            'snapshot_count' => $snapshots->count(),
            'period_start' =>
                $beginningSnapshot
                    ->snapshot_date
                    ->toDateString(),
            'period_end' =>
                $endingSnapshot
                    ->snapshot_date
                    ->toDateString(),
            'beginning_value' =>
                round($beginningValue, 2),
            'ending_value' =>
                round($endingValue, 2),
            'external_cash_flows' =>
                round($externalCashFlows, 2),
            'net_growth' => round(
                $endingValue
                - $beginningValue
                - $externalCashFlows,
                2,
            ),
            'simple_return' => $simpleReturn,
            'time_weighted_return' =>
                $timeWeightedReturn,
            'benchmark_return' =>
                $benchmarkReturn,
            'excess_return' => (
                $benchmarkReturn !== null
            )
                ? $timeWeightedReturn
                    - $benchmarkReturn
                : null,
            'subperiod_returns' =>
                $subperiodReturns,
            'data_status' => 'complete',
            'data_warning' => null,
        ];
    }

    private function calculateBenchmarkReturn(
        InvestmentAccount $account,
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd,
    ): ?float {
        $benchmark = $account->benchmark;

        if ($benchmark === null) {
            return null;
        }

        $returns = $benchmark->returns
            ->filter(
                fn ($return): bool =>
                    $return->return_date->gt($periodStart)
                    && $return->return_date->lte($periodEnd),
            )
            ->sortBy(
                fn ($return): int =>
                    $return->return_date->timestamp,
            )
            ->values();

        if ($returns->isEmpty()) {
            return null;
        }

        return $returns->reduce(
            fn (float $compound, $return): float =>
                $compound
                * (1 + (float) $return->period_return),
            1.0,
        ) - 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function insufficientAccountResult(
        InvestmentAccount $account,
        string $warning,
        int $snapshotCount,
    ): array {
        return [
            'account_id' => $account->id,
            'account_name' => $account->name,
            'benchmark_name' =>
                $account->benchmark?->name,
            'snapshot_count' =>
                $snapshotCount,
            'period_start' => null,
            'period_end' => null,
            'beginning_value' => 0,
            'ending_value' =>
                (float) $account->current_value,
            'external_cash_flows' => 0,
            'net_growth' => null,
            'simple_return' => null,
            'time_weighted_return' => null,
            'benchmark_return' => null,
            'excess_return' => null,
            'subperiod_returns' => collect(),
            'data_status' => 'insufficient',
            'data_warning' => $warning,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateScore(
        ?float $portfolioReturn,
        ?float $benchmarkReturn,
        ?float $excessReturn,
        float $dataCompleteness,
        float $benchmarkCoverage,
        int $validAccountCount,
    ): array {
        if (
            $portfolioReturn === null
            || $validAccountCount === 0
        ) {
            return [
                'score' => null,
                'label' => 'Insufficient data',
                'reasons' => [
                    'At least two valid snapshots are required for one account.',
                ],
                'recommendations' => [
                    'Add beginning and ending portfolio snapshots.',
                ],
            ];
        }

        $score = 70;
        $reasons = [];
        $recommendations = [];

        if ($benchmarkReturn !== null && $excessReturn !== null) {
            if ($excessReturn >= 0.03) {
                $score += 25;
            } elseif ($excessReturn >= 0.01) {
                $score += 18;
            } elseif ($excessReturn >= 0) {
                $score += 10;
            } elseif ($excessReturn >= -0.02) {
                $score -= 5;
            } elseif ($excessReturn >= -0.05) {
                $score -= 20;
            } else {
                $score -= 35;
            }

            $reasons[] = sprintf(
                'Portfolio return was %.2f%% versus %.2f%% for the selected benchmark.',
                $portfolioReturn * 100,
                $benchmarkReturn * 100,
            );

            $reasons[] = sprintf(
                'Benchmark-relative return was %s%.2f%%.',
                $excessReturn >= 0 ? '+' : '',
                $excessReturn * 100,
            );

            if ($excessReturn < -0.02) {
                $recommendations[] =
                    'Review whether fees, cash levels, asset allocation or security selection explain the benchmark shortfall.';
            }
        } else {
            $score -= 15;

            $reasons[] = sprintf(
                'Portfolio return was %.2f%%, but benchmark comparison data is incomplete.',
                $portfolioReturn * 100,
            );

            $recommendations[] =
                'Assign benchmarks and enter benchmark returns for each account.';
        }

        if ($portfolioReturn < -0.20) {
            $score -= 15;

            $reasons[] =
                'The portfolio experienced a loss greater than 20% during the measured period.';
        } elseif ($portfolioReturn < -0.10) {
            $score -= 8;
        }

        if ($dataCompleteness < 0.50) {
            $score -= 20;

            $reasons[] = sprintf(
                'Performance data covers only %.0f%% of accounts.',
                $dataCompleteness * 100,
            );

            $recommendations[] =
                'Add snapshots for accounts missing performance history.';
        } elseif ($dataCompleteness < 1.00) {
            $score -= 8;

            $reasons[] = sprintf(
                'Performance data covers %.0f%% of accounts.',
                $dataCompleteness * 100,
            );
        }

        if ($benchmarkCoverage < 0.50) {
            $score -= 10;
        }

        $score = max(0, min(100, $score));

        if ($recommendations === []) {
            $recommendations[] =
                'Continue monitoring net performance against an appropriate benchmark.';
        }

        return [
            'score' => $score,
            'label' => $this->scoreLabel($score),
            'reasons' =>
                array_values(array_unique($reasons)),
            'recommendations' =>
                array_values(array_unique($recommendations)),
        ];
    }

    private function scoreLabel(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Very good',
            $score >= 70 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Needs attention',
            default => 'Action recommended',
        };
    }
}
