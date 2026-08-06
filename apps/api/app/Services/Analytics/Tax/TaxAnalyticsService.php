<?php

namespace App\Services\Analytics\Tax;

use App\Data\Analytics\AnalyticsResult;
use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class TaxAnalyticsService
{
    public const FORMULA_VERSION = 'tax-0.3.0';

    public function __construct(
        private readonly TaxLotAnalyticsService $taxLotAnalyticsService,
        private readonly WashSaleDetector $washSaleDetector,
        private readonly TaxLossHarvestingService $taxLossHarvestingService
    ) {
    }

    /**
     * Analyze tax activity for one user and date range.
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

        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with('holdings')
            ->get();

        $holdings = $accounts
            ->pluck('holdings')
            ->flatten()
            ->values();

        $period = [
            'start_date' =>
                $startDate->toDateString(),

            'end_date' =>
                $endDate->toDateString(),

            'transaction_count' =>
                $transactions->count(),

            'account_count' =>
                $accounts->count(),

            'holding_count' =>
                $holdings->count(),
        ];

        if (
            $transactions->isEmpty()
            && $holdings->isEmpty()
        ) {
            return $this->legacyCompatibleResult(
                AnalyticsResult::insufficientData(
                    message:
                        'No investment transactions or open holdings were found.',

                    metrics:
                        $this->emptyMetrics(),

                    warnings: [
                        [
                            'code' =>
                                'no_tax_data',

                            'message' =>
                                'No transaction or holding data was available for tax analysis.',
                        ],
                    ],

                    data: [
                        'period' =>
                            $period,

                        'tax_lot_analysis' =>
                            $this->emptyTaxLotResult(),

                        'wash_sale_analysis' =>
                            $this->emptyWashSaleResult(),

                        'tax_loss_harvesting' =>
                            $this->emptyHarvestingResult(),
                    ],

                    formulaVersion:
                        self::FORMULA_VERSION,
                )
            );
        }

        $taxLotResult = $transactions->isEmpty()
            ? $this->emptyTaxLotResult()
            : $this->taxLotAnalyticsService
                ->analyze($transactions);

        $washSaleResult = $transactions->isEmpty()
            ? $this->emptyWashSaleResult()
            : $this->washSaleDetector
                ->analyze($transactions);

        $harvestingResult =
            $this->taxLossHarvestingService->analyze(
                holdings: $holdings,

                transactions: $transactions,

                asOfDate: $endDate,
            );

        $flags = array_values(
            array_merge(
                $taxLotResult['flags'] ?? [],
                $washSaleResult['flags'] ?? [],
                $harvestingResult['flags'] ?? [],
            )
        );

        $warnings = $this->buildWarnings(
            transactions:
                $transactions,

            holdings:
                $holdings,

            taxLotResult:
                $taxLotResult,

            washSaleResult:
                $washSaleResult,

            harvestingResult:
                $harvestingResult,
        );

        $metrics = $this->buildSharedMetrics(
            taxLotResult:
                $taxLotResult,

            washSaleResult:
                $washSaleResult,

            harvestingResult:
                $harvestingResult,
        );

        $score = $this->calculateScore(
            taxLotResult:
                $taxLotResult,

            washSaleResult:
                $washSaleResult,

            harvestingResult:
                $harvestingResult,
        );

        $result = AnalyticsResult::complete(
            metrics:
                $metrics,

            flags:
                $flags,

            warnings:
                $warnings,

            data: [
                'period' =>
                    $period,

                'tax_lot_analysis' =>
                    $taxLotResult,

                'wash_sale_analysis' =>
                    $washSaleResult,

                'tax_loss_harvesting' =>
                    $harvestingResult,
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
     * Flatten the major tax metrics into the shared result.
     *
     * @return array<string, mixed>
     */
    private function buildSharedMetrics(
        array $taxLotResult,
        array $washSaleResult,
        array $harvestingResult
    ): array {
        return [
            'realized_short_term_gain_loss' =>
                data_get(
                    $taxLotResult,
                    'metrics.realized_short_term_gain_loss',
                    0
                ),

            'realized_long_term_gain_loss' =>
                data_get(
                    $taxLotResult,
                    'metrics.realized_long_term_gain_loss',
                    0
                ),

            'total_realized_gain_loss' =>
                data_get(
                    $taxLotResult,
                    'metrics.total_realized_gain_loss',
                    0
                ),

            'qualified_dividends' =>
                data_get(
                    $taxLotResult,
                    'metrics.qualified_dividends',
                    0
                ),

            'non_qualified_dividends' =>
                data_get(
                    $taxLotResult,
                    'metrics.non_qualified_dividends',
                    0
                ),

            'tax_exempt_income' =>
                data_get(
                    $taxLotResult,
                    'metrics.tax_exempt_income',
                    0
                ),

            'total_taxable_dividends' =>
                data_get(
                    $taxLotResult,
                    'metrics.total_taxable_dividends',
                    0
                ),

            'tax_withheld' =>
                data_get(
                    $taxLotResult,
                    'metrics.tax_withheld',
                    0
                ),

            'unknown_holding_period_count' =>
                data_get(
                    $taxLotResult,
                    'counts.unknown_holding_period_count',
                    0
                ),

            'wash_sale_count' =>
                data_get(
                    $washSaleResult,
                    'metrics.wash_sale_count',
                    0
                ),

            'likely_wash_sale_count' =>
                data_get(
                    $washSaleResult,
                    'metrics.likely_wash_sale_count',
                    0
                ),

            'estimated_disallowed_loss' =>
                data_get(
                    $washSaleResult,
                    'metrics.estimated_disallowed_loss',
                    0
                ),

            'harvest_opportunity_count' =>
                data_get(
                    $harvestingResult,
                    'metrics.opportunity_count',
                    0
                ),

            'harvesting_wash_sale_risk_count' =>
                data_get(
                    $harvestingResult,
                    'metrics.wash_sale_risk_count',
                    0
                ),

            'total_potential_loss' =>
                data_get(
                    $harvestingResult,
                    'metrics.total_potential_loss',
                    0
                ),

            'estimated_harvestable_loss' =>
                data_get(
                    $harvestingResult,
                    'metrics.estimated_harvestable_loss',
                    0
                ),
        ];
    }

    /**
     * Produce a 0–100 tax-efficiency score.
     */
    private function calculateScore(
        array $taxLotResult,
        array $washSaleResult,
        array $harvestingResult
    ): ?int {
        $hasTransactionAnalysis =
            ($taxLotResult['status'] ?? null)
            === 'complete';

        $hasHoldingAnalysis =
            ($harvestingResult['status'] ?? null)
            === 'complete';

        if (
            ! $hasTransactionAnalysis
            && ! $hasHoldingAnalysis
        ) {
            return null;
        }

        $score = 100;

        $shortTermGainLoss = (float) data_get(
            $taxLotResult,
            'metrics.realized_short_term_gain_loss',
            0
        );

        $longTermGainLoss = (float) data_get(
            $taxLotResult,
            'metrics.realized_long_term_gain_loss',
            0
        );

        $qualifiedDividends = (float) data_get(
            $taxLotResult,
            'metrics.qualified_dividends',
            0
        );

        $nonQualifiedDividends = (float) data_get(
            $taxLotResult,
            'metrics.non_qualified_dividends',
            0
        );

        $unknownHoldingPeriods = (int) data_get(
            $taxLotResult,
            'counts.unknown_holding_period_count',
            0
        );

        $washSaleCount = (int) data_get(
            $washSaleResult,
            'metrics.wash_sale_count',
            0
        );

        $estimatedDisallowedLoss = (float) data_get(
            $washSaleResult,
            'metrics.estimated_disallowed_loss',
            0
        );

        $harvestingWashSaleRiskCount =
            (int) data_get(
                $harvestingResult,
                'metrics.wash_sale_risk_count',
                0
            );

        if (
            $shortTermGainLoss > 0
            && $shortTermGainLoss
                > $longTermGainLoss
        ) {
            $score -= 15;
        }

        $totalTaxableDividends =
            $qualifiedDividends
            + $nonQualifiedDividends;

        if (
            $totalTaxableDividends > 0
            && (
                $nonQualifiedDividends
                / $totalTaxableDividends
            ) >= 0.50
        ) {
            $score -= 10;
        }

        if ($washSaleCount > 0) {
            $score -= min(
                30,
                10 + ($washSaleCount * 5)
            );
        }

        if ($estimatedDisallowedLoss >= 1000) {
            $score -= 10;
        }

        if ($unknownHoldingPeriods > 0) {
            $score -= min(
                15,
                $unknownHoldingPeriods * 3
            );
        }

        if ($harvestingWashSaleRiskCount > 0) {
            $score -= min(
                10,
                $harvestingWashSaleRiskCount * 3
            );
        }

        return max(
            0,
            min(100, $score)
        );
    }

    /**
     * @param Collection<int, InvestmentTransaction> $transactions
     * @param Collection<int, mixed> $holdings
     * @return array<int, array<string, mixed>>
     */
    private function buildWarnings(
        Collection $transactions,
        Collection $holdings,
        array $taxLotResult,
        array $washSaleResult,
        array $harvestingResult
    ): array {
        $warnings = [];

        if ($transactions->isEmpty()) {
            $warnings[] = [
                'code' =>
                    'transaction_history_missing',

                'message' =>
                    'No transaction history was available, so realized gains, dividends, and wash sales could not be fully analyzed.',
            ];
        }

        if ($holdings->isEmpty()) {
            $warnings[] = [
                'code' =>
                    'open_holdings_missing',

                'message' =>
                    'No open holdings were available for tax-loss harvesting analysis.',
            ];
        }

        $unknownHoldingPeriods = (int) data_get(
            $taxLotResult,
            'counts.unknown_holding_period_count',
            0
        );

        if ($unknownHoldingPeriods > 0) {
            $warnings[] = [
                'code' =>
                    'incomplete_holding_period_data',

                'message' =>
                    "{$unknownHoldingPeriods} realized transaction(s) are missing holding-period data.",
            ];
        }

        if (
            ($washSaleResult['status'] ?? null)
            === 'insufficient_data'
            && ! $transactions->isEmpty()
        ) {
            $warnings[] = [
                'code' =>
                    'wash_sale_analysis_limited',

                'message' =>
                    $washSaleResult['message']
                    ?? 'Wash-sale analysis could not be completed.',
            ];
        }

        if (
            ($harvestingResult['status'] ?? null)
            === 'insufficient_data'
            && ! $holdings->isEmpty()
        ) {
            $warnings[] = [
                'code' =>
                    'harvesting_analysis_limited',

                'message' =>
                    $harvestingResult['message']
                    ?? 'Tax-loss harvesting analysis could not be completed.',
            ];
        }

        return array_values($warnings);
    }

    /**
     * Preserve the current dashboard response while also exposing
     * the standardized AnalyticsResult structure.
     *
     * @return array<string, mixed>
     */
    private function legacyCompatibleResult(
        AnalyticsResult $result
    ): array {
        return array_merge(
            $result->toArray(),
            $result->data
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyMetrics(): array
    {
        return [
            'realized_short_term_gain_loss' => 0,
            'realized_long_term_gain_loss' => 0,
            'total_realized_gain_loss' => 0,
            'qualified_dividends' => 0,
            'non_qualified_dividends' => 0,
            'tax_exempt_income' => 0,
            'total_taxable_dividends' => 0,
            'tax_withheld' => 0,
            'unknown_holding_period_count' => 0,
            'wash_sale_count' => 0,
            'likely_wash_sale_count' => 0,
            'estimated_disallowed_loss' => 0,
            'harvest_opportunity_count' => 0,
            'harvesting_wash_sale_risk_count' => 0,
            'total_potential_loss' => 0,
            'estimated_harvestable_loss' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyTaxLotResult(): array
    {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                'No investment transactions were found.',

            'metrics' => [
                'realized_short_term_gain_loss' => 0,
                'realized_long_term_gain_loss' => 0,
                'total_realized_gain_loss' => 0,
                'qualified_dividends' => 0,
                'non_qualified_dividends' => 0,
                'tax_exempt_income' => 0,
                'total_taxable_dividends' => 0,
                'total_income' => 0,
                'tax_withheld' => 0,
            ],

            'counts' => [
                'transaction_count' => 0,
                'realized_transaction_count' => 0,
                'short_term_transaction_count' => 0,
                'long_term_transaction_count' => 0,
                'dividend_transaction_count' => 0,
                'unknown_holding_period_count' => 0,
            ],

            'flags' => [],

            'formula_version' =>
                'tax-lot-0.1.0',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyWashSaleResult(): array
    {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                'No qualifying loss-sale transactions were found.',

            'metrics' => [
                'wash_sale_count' => 0,
                'likely_wash_sale_count' => 0,
                'possible_wash_sale_count' => 0,
                'estimated_disallowed_loss' => 0,
            ],

            'wash_sales' => [],
            'flags' => [],

            'formula_version' =>
                'wash-sale-0.1.0',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyHarvestingResult(): array
    {
        return [
            'status' =>
                'insufficient_data',

            'message' =>
                'No open holdings were found.',

            'metrics' => [
                'opportunity_count' => 0,
                'wash_sale_risk_count' => 0,
                'total_potential_loss' => 0,
                'estimated_harvestable_loss' => 0,
                'minimum_loss_amount' => null,
                'minimum_loss_percent' => null,
            ],

            'opportunities' => [],
            'flags' => [],

            'formula_version' =>
                'tax-loss-harvesting-0.1.0',
        ];
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