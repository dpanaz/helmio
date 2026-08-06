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
            'dashboard' =>
                $dashboard,

            'accounts' =>
                $dashboard['accounts']
                ?? collect(),

            'portfolioValue' =>
                $dashboard['portfolioValue']
                ?? 0,

            'cashValue' =>
                $dashboard['cashValue']
                ?? 0,

            'accountCount' =>
                $dashboard['accountCount']
                ?? 0,

            'helmScore' =>
                $dashboard['helm']
                ?? null,

            'largestAccount' =>
                $dashboard['largestAccount']
                ?? null,

            'advisorAudit' =>
                $dashboard['advisorAudit']
                ?? null,

            'currentAuditRun' =>
                $dashboard['currentAuditRun']
                ?? null,

            'previousAuditRun' =>
                $dashboard['previousAuditRun']
                ?? null,

            'auditComparison' =>
                $dashboard['auditComparison']
                ?? null,

            'openFindings' =>
                $dashboard['openFindings']
                ?? collect(),

            'findingCounts' =>
                $dashboard['findingCounts']
                ?? [],

            'latestAiInsight' =>
                $dashboard['latestAiInsight']
                ?? null,
        ]);
    }
}