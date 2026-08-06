<?php

namespace App\Http\Controllers;

use App\Services\Analytics\Tax\TaxAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxEfficiencyAnalyticsController extends Controller
{
    /**
     * Display the Tax Efficiency dashboard.
     */
    public function index(): View
    {
        return view('analytics.tax-efficiency');
    }

    /**
     * Return Tax Efficiency analytics as JSON.
     */
    public function data(
        Request $request,
        TaxAnalyticsService $taxAnalyticsService
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

        $result = $taxAnalyticsService->analyze(
            user: $request->user(),
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
        );

        return response()->json([
            'data' => $result,
        ]);
    }
}