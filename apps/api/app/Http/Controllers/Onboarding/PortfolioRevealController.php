<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Services\Onboarding\PortfolioSummaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioRevealController extends Controller
{
    public function index(
        Request $request,
        PortfolioSummaryService $summaryService,
    ): View {
        return view('onboarding.reveal', [
            'summary' => $summaryService->build(
                $request->user(),
            ),
        ]);
    }
}