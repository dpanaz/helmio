<?php

namespace App\Http\Controllers;

use App\Models\Benchmark;
use App\Services\Analytics\Performance\PerformanceAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceAnalyticsController extends Controller
{
    public function index(): View
    {
        $benchmarks = Benchmark::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('analytics.performance', [
            'benchmarks' => $benchmarks,
        ]);
    }

    public function data(
        Request $request,
        PerformanceAnalyticsService $performanceAnalyticsService,
    ): JsonResponse {
        $validated = $request->validate([
            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],

            'benchmark_id' => [
                'nullable',
                'integer',
                'exists:benchmarks,id',
            ],
        ]);

        $benchmark = isset($validated['benchmark_id'])
            ? Benchmark::query()
                ->where('is_active', true)
                ->findOrFail(
                    $validated['benchmark_id'],
                )
            : Benchmark::query()
                ->where('is_active', true)
                ->where('is_default', true)
                ->first();

        /*
         * Fall back to SPY if no benchmark has been
         * explicitly marked as the default.
         */
        if ($benchmark === null) {
            $benchmark = Benchmark::query()
                ->where('is_active', true)
                ->where('symbol', 'SPY')
                ->first();
        }

        /*
         * Final fallback: use the first active benchmark.
         */
        if ($benchmark === null) {
            $benchmark = Benchmark::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->first();
        }

        $result =
            $performanceAnalyticsService->analyze(
                user:
                    $request->user(),

                startDate:
                    Carbon::parse(
                        $validated['start_date'],
                    ),

                endDate:
                    Carbon::parse(
                        $validated['end_date'],
                    ),

                benchmark:
                    $benchmark,
            );

        return response()->json([
            'data' => $result,
        ]);
    }
}