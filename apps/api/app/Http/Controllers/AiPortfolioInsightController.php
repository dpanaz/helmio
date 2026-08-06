<?php

namespace App\Http\Controllers;

use App\Models\AiInsightRun;
use App\Services\AI\AiPortfolioInsightService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiPortfolioInsightController extends Controller
{
    public function index(
        Request $request,
    ): View {
        $insights = AiInsightRun::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->orderByDesc('generated_at')
            ->paginate(15);

        return view('ai-insights.index', [
            'insights' => $insights,
            'latestInsight' =>
                $insights->first(),
        ]);
    }

    public function generate(
        Request $request,
        AiPortfolioInsightService $insightService,
    ): RedirectResponse {
        $insight = $insightService->generate(
            $request->user(),
        );

        return redirect()
            ->route('ai-insights.show', $insight)
            ->with(
                'success',
                'Portfolio insight generated.',
            );
    }

    public function regenerate(
    Request $request,
    AiInsightRun $aiInsightRun,
    AiPortfolioInsightService $insightService,
): RedirectResponse {
    abort_unless(
        $aiInsightRun->user_id
            === $request->user()->id,
        403,
    );

    $newInsight = $insightService->generate(
        $request->user()
    );

    return redirect()
        ->route(
            'ai-insights.show',
            $newInsight
        )
        ->with(
            'success',
            'Portfolio insight regenerated using current data.'
        );
}

    public function show(
        Request $request,
        AiInsightRun $aiInsightRun,
    ): View {
        abort_unless(
            $aiInsightRun->user_id
                === $request->user()->id,
            403,
        );

        return view('ai-insights.show', [
            'insight' => $aiInsightRun,
        ]);
    }
}