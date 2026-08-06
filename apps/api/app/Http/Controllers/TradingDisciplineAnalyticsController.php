<?php

namespace App\Http\Controllers;

use App\Services\Analytics\Trading\TradingAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TradingDisciplineAnalyticsController extends Controller
{
    public function index(): View
    {
        return view('analytics.trading-discipline');
    }

    public function data(
        Request $request,
        TradingAnalyticsService $tradingAnalyticsService
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
        ]);

        $result = $tradingAnalyticsService->analyze(
            user: $request->user(),

            startDate: Carbon::parse(
                $validated['start_date']
            ),

            endDate: Carbon::parse(
                $validated['end_date']
            ),
        );

        return response()->json([
            'data' => $result,
        ]);
    }
}