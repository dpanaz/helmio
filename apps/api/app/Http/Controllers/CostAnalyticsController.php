<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Analytics\CostAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CostAnalyticsController extends Controller
{
    public function index(
        Request $request,
        CostAnalyticsService $costAnalytics,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'institution',
                'holdings.security',
                'transactions' => fn ($query) =>
                    $query->whereDate(
                        'transaction_date',
                        '>=',
                        now()->subYear()->toDateString(),
                    ),
            ])
            ->orderBy('name')
            ->get();

        return view('analytics.costs', [
            'analytics' => $costAnalytics->calculate($accounts),
        ]);
    }
}
