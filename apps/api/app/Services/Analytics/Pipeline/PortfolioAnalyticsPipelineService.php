<?php

namespace App\Services\Analytics\Pipeline;

use App\Models\HelmScoreSnapshot;
use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use App\Models\PortfolioAnalysisRun;
use App\Models\User;
use App\Services\Analytics\HelmScoreNotificationService;
use App\Services\Analytics\HelmScoreService;
use App\Services\Analytics\Performance\PortfolioValuationGenerator;
use App\Services\MarketData\UserHistoricalPriceBackfillService;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;

class PortfolioAnalyticsPipelineService
{
    public function __construct(
        private readonly UserHistoricalPriceBackfillService $priceBackfill,
        private readonly PortfolioValuationGenerator $valuationGenerator,
        private readonly HelmScoreService $helmScoreService,
        private readonly HelmScoreNotificationService $notificationService,
    ) {
    }

    public function run(
        User $user,
        PortfolioAnalysisRun $run,
    ): array {
        $endDate =
            CarbonImmutable::today();

        $oneYearAgo =
            $endDate->subYear();

        $accountIds =
            InvestmentAccount::query()
                ->where(
                    'user_id',
                    $user->id,
                )
                ->pluck('id');

        $earliestTransaction =
            InvestmentTransaction::query()
                ->whereIn(
                    'investment_account_id',
                    $accountIds,
                )
                ->min(
                    'transaction_date',
                );

        /*
         * Helm Score currently analyzes one year, so there is no
         * reason to build history older than one year here.
         */
        $startDate =
            $earliestTransaction
                ? max(
                    $oneYearAgo,
                    CarbonImmutable::parse(
                        $earliestTransaction,
                    )->startOfDay(),
                )
                : $oneYearAgo;

        /*
         * STEP 1
         * Ensure historical market prices exist.
         */
        $run->markStep(
            PortfolioAnalysisRun::STATUS_BUILDING_HISTORY,
            'historical_prices',
            [
                'analysis_start_date' =>
                    $startDate->toDateString(),

                'analysis_end_date' =>
                    $endDate->toDateString(),
            ],
        );

        $priceResult =
            $this->priceBackfill->backfill(
                user:
                    $user,

                startDate:
                    $startDate,

                endDate:
                    $endDate,
            );

        /*
         * STEP 2
         * Build weekday portfolio valuation history.
         */
        $run->markStep(
            PortfolioAnalysisRun::STATUS_BUILDING_HISTORY,
            'portfolio_valuations',
        );

        $valuationCount = 0;
        $valuationErrors = [];

        $period =
            CarbonPeriod::create(
                $startDate,
                '1 day',
                $endDate,
            );

        foreach ($period as $date) {
            $valuationDate =
                CarbonImmutable::parse(
                    $date->toDateString(),
                );

            if ($valuationDate->isWeekend()) {
                continue;
            }

            try {
                $this->valuationGenerator
                    ->generateForUser(
                        user:
                            $user,

                        valuationDate:
                            $valuationDate,
                    );

                $valuationCount++;
            } catch (\Throwable $exception) {
                report(
                    $exception,
                );

                $valuationErrors[] = [
                    'date' =>
                        $valuationDate
                            ->toDateString(),

                    'message' =>
                        $exception
                            ->getMessage(),
                ];
            }
        }

        /*
         * STEP 3
         * Calculate the full Helm Score.
         */
        $run->markStep(
            PortfolioAnalysisRun::STATUS_ANALYZING,
            'helm_score',
        );

        $accounts =
            InvestmentAccount::query()
                ->where(
                    'user_id',
                    $user->id,
                )
                ->with([
                    'institution',
                    'holdings.security',
                    'transactions',
                ])
                ->get();

        $score =
            $this->helmScoreService
                ->calculate(
                    $accounts,
                );

        /*
         * Find the most recent earlier Helm Score before we write
         * today's snapshot. This lets the notification service
         * compare the new score with the previous analysis.
         */
        $previousSnapshot =
            HelmScoreSnapshot::query()
                ->where(
                    'user_id',
                    $user->id,
                )
                ->where(
                    'calculated_for_date',
                    '<',
                    $score[
                        'calculated_for_date'
                    ],
                )
                ->orderByDesc(
                    'calculated_for_date',
                )
                ->orderByDesc('id')
                ->first();

        /*
         * STEP 4
         * Persist today's Helm Score snapshot.
         */
        $snapshot =
            HelmScoreSnapshot::query()
                ->updateOrCreate(
                    [
                        'user_id' =>
                            $user->id,

                        'calculated_for_date' =>
                            $score[
                                'calculated_for_date'
                            ],

                        'formula_version' =>
                            $score[
                                'formula_version'
                            ],
                    ],
                    [
                        'overall_score' =>
                            $score[
                                'overall_score'
                            ],

                        'cost_score' =>
                            $score[
                                'categories'
                            ][
                                'cost'
                            ][
                                'score'
                            ],

                        'diversification_score' =>
                            $score[
                                'categories'
                            ][
                                'diversification'
                            ][
                                'score'
                            ],

                        'performance_score' =>
                            $score[
                                'categories'
                            ][
                                'performance'
                            ][
                                'score'
                            ],

                        'risk_score' =>
                            $score[
                                'categories'
                            ][
                                'risk'
                            ][
                                'score'
                            ],

                        'trading_score' =>
                            $score[
                                'categories'
                            ][
                                'trading'
                            ][
                                'score'
                            ],

                        'tax_score' =>
                            $score[
                                'categories'
                            ][
                                'tax'
                            ][
                                'score'
                            ],

                        'data_completeness' =>
                            $score[
                                'data_completeness'
                            ],

                        'score_details' =>
                            $score,
                    ],
                );

        /*
         * STEP 5
         * Generate Helm Score notifications.
         *
         * The notification service handles:
         * - initial "needs attention" summaries
         * - meaningful score declines
         * - newly detected high/critical findings
         * - duplicate prevention
         */
        $this->notificationService
            ->generate(
                user:
                    $user,

                score:
                    $score,

                previousSnapshot:
                    $previousSnapshot,
            );

        return [
            'price_backfill' =>
                $priceResult,

            'valuation_count' =>
                $valuationCount,

            'valuation_error_count' =>
                count(
                    $valuationErrors,
                ),

            'valuation_errors' =>
                $valuationErrors,

            'overall_score' =>
                $score[
                    'overall_score'
                ],

            'overall_label' =>
                $score[
                    'overall_label'
                ],

            'data_completeness' =>
                $score[
                    'data_completeness'
                ],

            'helm_score_snapshot_id' =>
                $snapshot->id,
        ];
    }
}