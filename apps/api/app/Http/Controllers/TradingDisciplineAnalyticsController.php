<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Analytics\TradingDisciplineAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TradingDisciplineAnalyticsController extends Controller
{
    public function index(
        Request $request,
        TradingDisciplineAnalyticsService $service,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'transactions.security',
                'institution',
            ])
            ->orderBy('name')
            ->get();

        return view('analytics.trading-discipline', [
            'analytics' => $service->calculate($accounts),
        ]);
    }
}
