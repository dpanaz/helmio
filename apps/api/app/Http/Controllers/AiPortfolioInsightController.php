<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAiPortfolioInsight;
use App\Models\AiInsightRun;
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
    ): RedirectResponse {
        GenerateAiPortfolioInsight::dispatch(
            userId: $request->user()->id,
            trigger: 'manual',
        );

        return redirect()
            ->route('ai-insights.index')
            ->with(
                'success',
                'Your AI portfolio insight is being generated.',
            );
    }

    public function regenerate(
        Request $request,
        AiInsightRun $aiInsightRun,
    ): RedirectResponse {
        abort_unless(
            $aiInsightRun->user_id
                === $request->user()->id,
            403,
        );

        GenerateAiPortfolioInsight::dispatch(
            userId: $request->user()->id,
            trigger: 'manual_regenerate',
        );

        return redirect()
            ->route(
                'ai-insights.show',
                $aiInsightRun,
            )
            ->with(
                'success',
                'Your updated AI portfolio insight is being generated.',
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