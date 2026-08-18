<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAiPortfolioInsight;
use App\Models\AiInsightRun;
use Illuminate\Http\JsonResponse;
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
        $baselineId = (int) (
            AiInsightRun::query()
                ->where(
                    'user_id',
                    $request->user()->id,
                )
                ->max('id')
            ?? 0
        );

        GenerateAiPortfolioInsight::dispatch(
            userId: $request->user()->id,
            trigger: 'manual',
        );

        return redirect()
            ->route(
                'ai-insights.index',
                [
                    'generating' => 1,
                    'baseline_id' => $baselineId,
                ],
            )
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

        $baselineId = (int) (
            AiInsightRun::query()
                ->where(
                    'user_id',
                    $request->user()->id,
                )
                ->max('id')
            ?? 0
        );

        GenerateAiPortfolioInsight::dispatch(
            userId: $request->user()->id,
            trigger: 'manual_regenerate',
        );

        return redirect()
            ->route(
                'ai-insights.index',
                [
                    'generating' => 1,
                    'baseline_id' => $baselineId,
                ],
            )
            ->with(
                'success',
                'Your updated AI portfolio insight is being generated.',
            );
    }

    public function status(
        Request $request,
    ): JsonResponse {
        $baselineId = max(
            0,
            (int) $request->query(
                'baseline_id',
                0,
            ),
        );

        $latestInsight = AiInsightRun::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->orderByDesc('id')
            ->first();

        $finished = $latestInsight !== null
            && $latestInsight->id > $baselineId;

        return response()->json([
            'finished' => $finished,

            'latest' => $latestInsight
                ? [
                    'id' =>
                        $latestInsight->id,

                    'status' =>
                        $latestInsight->status,

                    'headline' =>
                        $latestInsight->headline,

                    'generated_at' =>
                        $latestInsight
                            ->generated_at
                            ?->toIso8601String(),

                    'show_url' =>
                        route(
                            'ai-insights.show',
                            $latestInsight,
                        ),
                ]
                : null,
        ]);
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