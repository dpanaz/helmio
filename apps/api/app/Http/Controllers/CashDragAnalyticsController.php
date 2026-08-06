<?php

namespace App\Http\Controllers;

use App\Models\Benchmark;
use App\Services\Analytics\Cash\CashDragAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashDragAnalyticsController extends Controller
{
    public function index(): View
    {
        $benchmarks = Benchmark::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('analytics.cash-drag', [
            'benchmarks' => $benchmarks,
        ]);
    }

    public function data(
        Request $request,
        CashDragAnalyticsService $cashDragAnalyticsService
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

            'target_cash_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1',
            ],
        ]);

        $benchmark = isset($validated['benchmark_id'])
            ? Benchmark::query()
                ->where('is_active', true)
                ->findOrFail(
                    $validated['benchmark_id']
                )
            : Benchmark::query()
                ->where('is_active', true)
                ->where('symbol', 'SPY')
                ->first();

        $result = $cashDragAnalyticsService->analyze(
            user: $request->user(),

            startDate: Carbon::parse(
                $validated['start_date']
            ),

            endDate: Carbon::parse(
                $validated['end_date']
            ),

            benchmark: $benchmark,

            targetCashPercent: (float) (
                $validated['target_cash_percent']
                ?? 0.05
            ),
        );

        return response()->json([
            'data' => $result,
        ]);
    }
}