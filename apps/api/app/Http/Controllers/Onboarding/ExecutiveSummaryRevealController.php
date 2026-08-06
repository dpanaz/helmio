<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Services\AI\AiPortfolioInsightService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutiveSummaryRevealController extends Controller
{
    public function index(
        Request $request,
        AiPortfolioInsightService $insightService,
    ): View {
        $insight = $insightService->latestOrGenerate(
            $request->user(),
        );

        return view('onboarding.executive-summary', [
            'insight' => $insight,
        ]);
    }
}