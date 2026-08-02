<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        DashboardService $dashboardService,
    ): View {
        $dashboard = $dashboardService->build(
            $request->user()->id,
        );

        return view('dashboard', [
            'dashboard' => $dashboard,

            'accounts' =>
                $dashboard['accounts'],

            'portfolioValue' =>
                $dashboard['portfolioValue'],

            'cashValue' =>
                $dashboard['cashValue'],

            'accountCount' =>
                $dashboard['accountCount'],

            'helmScore' =>
                $dashboard['helm'],

            'largestAccount' =>
                $dashboard['largestAccount'],

            'advisorAudit' =>
                $dashboard['advisorAudit'],

            'currentAuditRun' =>
                $dashboard['currentAuditRun'],

            'previousAuditRun' =>
                $dashboard['previousAuditRun'],

            'auditComparison' =>
                $dashboard['auditComparison'],

            'openFindings' =>
                $dashboard['openFindings'],

            'findingCounts' =>
                $dashboard['findingCounts'],
        ]);
    }
}