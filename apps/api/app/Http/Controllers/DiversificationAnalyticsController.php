<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Services\Analytics\DiversificationAnalyticsService;
use App\Services\Analytics\LookThroughDiversificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiversificationAnalyticsController extends Controller
{
    public function index(
        Request $request,
        DiversificationAnalyticsService $service,
        LookThroughDiversificationService $lookThroughService,
    ): View {
        $accounts = InvestmentAccount::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->with(
                'holdings.security',
            )
            ->orderBy(
                'name',
            )
            ->get();

        $analytics =
            $service->calculate(
                $accounts,
            );

        $lookThrough =
            $lookThroughService->calculate(
                $accounts,
            );

        return view(
            'analytics.diversification',
            [
                'analytics' =>
                    $analytics,

                'lookThrough' =>
                    $lookThrough,
            ],
        );
    }
}