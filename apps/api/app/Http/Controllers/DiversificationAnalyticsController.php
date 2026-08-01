<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Analytics\DiversificationAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiversificationAnalyticsController extends Controller
{
    public function index(
        Request $request,
        DiversificationAnalyticsService $service,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with('holdings.security')
            ->orderBy('name')
            ->get();

        return view('analytics.diversification', [
            'analytics' => $service->calculate($accounts),
        ]);
    }
}
