<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Analytics\RiskAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskAnalyticsController extends Controller
{
    public function index(
        Request $request,
        RiskAnalyticsService $service,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->with([
                'holdings.security',
                'portfolioSnapshots',
                'institution',
            ])
            ->orderBy('name')
            ->get();

        return view(
            'analytics.risk',
            [
                'analytics' =>
                    $service->calculate(
                        $accounts,
                    ),
            ],
        );
    }
}
