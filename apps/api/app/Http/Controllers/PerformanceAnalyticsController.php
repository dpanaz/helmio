<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Analytics\PerformanceAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceAnalyticsController extends Controller
{
    public function index(
        Request $request,
        PerformanceAnalyticsService $service,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'portfolioSnapshots',
                'benchmark.returns',
                'institution',
            ])
            ->orderBy('name')
            ->get();

        return view('analytics.performance', [
            'analytics' => $service->calculate($accounts),
        ]);
    }
}
