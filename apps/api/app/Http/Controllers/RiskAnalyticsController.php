<?php

namespace App\Http\Controllers;

use App\Models\Benchmark;
use App\Services\Analytics\Risk\RiskAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskAnalyticsController extends Controller
{
    public function index(): View
    {
        $benchmarks = Benchmark::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('analytics.risk', [
            'benchmarks' => $benchmarks,
        ]);
    }

    public function data(
        Request $request,
        RiskAnalyticsService $riskAnalyticsService
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

            'annual_risk_free_rate' => [
                'nullable',
                'numeric',
                'gt:-1',
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

        $result = $riskAnalyticsService->analyze(
            user: $request->user(),

            startDate: Carbon::parse(
                $validated['start_date']
            ),

            endDate: Carbon::parse(
                $validated['end_date']
            ),

            benchmark: $benchmark,

            annualRiskFreeRate: (float) (
                $validated[
                    'annual_risk_free_rate'
                ] ?? 0
            ),
        );

        return response()->json([
            'data' => $result,
        ]);
    }
}