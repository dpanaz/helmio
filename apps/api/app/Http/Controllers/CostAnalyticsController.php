<?php

namespace App\Http\Controllers;

use App\Models\Benchmark;
use App\Models\InvestmentAccount;
use App\Services\Analytics\Benchmark\BenchmarkRecommendationService;
use App\Services\Analytics\CostAnalyticsService;
use App\Services\Analytics\Value\CostAdjustedPerformanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CostAnalyticsController extends Controller
{
    public function index(
        Request $request,
        CostAnalyticsService $costAnalytics,
        CostAdjustedPerformanceService $costAdjustedPerformance,
        BenchmarkRecommendationService $benchmarkRecommendation,
    ): View {
        $user = $request->user();

        /*
         * -------------------------------------------------------------
         * Analysis period
         * -------------------------------------------------------------
         */
        $endDate = Carbon::parse(
            $request->input(
                'end_date',
                now()->toDateString(),
            )
        )->endOfDay();

        $startDate = Carbon::parse(
            $request->input(
                'start_date',
                $endDate
                    ->copy()
                    ->subYear()
                    ->toDateString(),
            )
        )->startOfDay();

        /*
         * -------------------------------------------------------------
         * Accounts
         * -------------------------------------------------------------
         */
        $accounts = InvestmentAccount::query()
            ->where(
                'user_id',
                $user->id,
            )
            ->with([
                'institution',

                'holdings.security',

                'transactions' => fn ($query) =>
                    $query->whereBetween(
                        'transaction_date',
                        [
                            $startDate->toDateString(),
                            $endDate->toDateString(),
                        ],
                    ),
            ])
            ->orderBy('name')
            ->get();

        $analytics =
            $costAnalytics->calculate(
                $accounts
            );

        /*
         * -------------------------------------------------------------
         * Helmio benchmark recommendation
         * -------------------------------------------------------------
         *
         * The recommendation is based on the investor's documented
         * profile, not on the portfolio's current measured risk.
         */
        $benchmarkRecommendationResult =
            $benchmarkRecommendation
                ->recommend($user);

        /*
         * -------------------------------------------------------------
         * Benchmark selection
         * -------------------------------------------------------------
         *
         * Priority:
         *
         * 1. Explicit benchmark selected by the user.
         * 2. Helmio recommended benchmark.
         * 3. Active default benchmark.
         * 4. First active benchmark.
         */
        $requestedBenchmarkId =
            $request->integer(
                'benchmark_id'
            );

        $benchmark =
            null;

        $usingRecommendedBenchmark =
            false;

        if ($requestedBenchmarkId > 0) {
            $benchmark =
                Benchmark::query()
                    ->whereKey(
                        $requestedBenchmarkId
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();
        }

        /*
         * No explicit user choice:
         * use Helmio's recommendation.
         */
        if ($benchmark === null) {
            $recommendedBenchmark =
                $benchmarkRecommendationResult[
                    'benchmark'
                ] ?? null;

            if (
                $recommendedBenchmark
                instanceof Benchmark
                && $recommendedBenchmark->is_active
            ) {
                $benchmark =
                    $recommendedBenchmark;

                $usingRecommendedBenchmark =
                    true;
            }
        }

        /*
         * Fallback to configured default benchmark.
         */
        if ($benchmark === null) {
            $benchmark =
                Benchmark::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->where(
                        'is_default',
                        true
                    )
                    ->orderBy('id')
                    ->first();
        }

        /*
         * Final fallback.
         */
        if ($benchmark === null) {
            $benchmark =
                Benchmark::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderBy('id')
                    ->first();
        }

        /*
         * -------------------------------------------------------------
         * Cost-adjusted performance analysis
         * -------------------------------------------------------------
         */
        $costPerformance =
            $costAdjustedPerformance
                ->analyze(
                    user:
                        $user,

                    startDate:
                        $startDate,

                    endDate:
                        $endDate,

                    benchmark:
                        $benchmark,
                );

        /*
         * -------------------------------------------------------------
         * Available benchmark choices
         * -------------------------------------------------------------
         */
        $benchmarks =
            Benchmark::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderByRaw(
                    "
                    CASE
                        WHEN symbol = 'HELMIO-60-40' THEN 0
                        WHEN symbol = 'VTI' THEN 1
                        WHEN symbol = 'SPY' THEN 2
                        WHEN symbol = 'QQQ' THEN 3
                        ELSE 4
                    END
                    "
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'symbol',
                    'benchmark_type',
                    'expense_ratio',
                    'metadata',
                ]);

        return view(
            'analytics.costs',
            [
                /*
                 * Existing cost analysis.
                 */
                'analytics' =>
                    $analytics,

                /*
                 * Cost vs performance analysis.
                 */
                'costPerformance' =>
                    $costPerformance,

                /*
                 * Benchmark actually used.
                 */
                'benchmark' =>
                    $benchmark,

                /*
                 * Available comparison benchmarks.
                 */
                'benchmarks' =>
                    $benchmarks,

                /*
                 * Helmio recommendation metadata.
                 */
                'benchmarkRecommendation' =>
                    $benchmarkRecommendationResult,

                /*
                 * True when the current benchmark was automatically
                 * selected by Helmio rather than manually selected.
                 */
                'usingRecommendedBenchmark' =>
                    $usingRecommendedBenchmark,

                /*
                 * Period.
                 */
                'startDate' =>
                    $startDate
                        ->toDateString(),

                'endDate' =>
                    $endDate
                        ->toDateString(),
            ],
        );
    }
}