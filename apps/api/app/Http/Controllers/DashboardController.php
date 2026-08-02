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

            // Existing dashboard Blade variables
            'accounts' => $dashboard['accounts'],
            'portfolioValue' => $dashboard['portfolioValue'],
            'cashValue' => $dashboard['cashValue'],
            'accountCount' => $dashboard['accountCount'],

            // New live Helm Score data
            'helmScore' => $dashboard['helm'],
            'largestAccount' => $dashboard['largestAccount'],
        ]);
    }
}