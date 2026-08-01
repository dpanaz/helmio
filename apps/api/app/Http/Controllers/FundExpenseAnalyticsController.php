<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Analytics\FundExpenseAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundExpenseAnalyticsController extends Controller
{
    public function index(
        Request $request,
        FundExpenseAnalyticsService $analyticsService,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with('holdings.security')
            ->orderBy('name')
            ->get();

        return view('analytics.fund-expenses', [
            'analytics' => $analyticsService->calculate($accounts),
        ]);
    }
}
