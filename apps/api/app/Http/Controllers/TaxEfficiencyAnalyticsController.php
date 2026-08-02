<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Analytics\TaxEfficiencyAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxEfficiencyAnalyticsController extends Controller
{
    public function index(
        Request $request,
        TaxEfficiencyAnalyticsService $service,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'transactions.security',
                'institution',
            ])
            ->orderBy('name')
            ->get();

        return view('analytics.tax-efficiency', [
            'analytics' => $service->calculate($accounts),
        ]);
    }
}
