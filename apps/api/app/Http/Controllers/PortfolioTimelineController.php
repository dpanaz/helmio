<?php

namespace App\Http\Controllers;

use App\Models\TimelineEvent;
use App\Services\Timeline\TimelineComparisonService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioTimelineController extends Controller
{
    public function index(
        Request $request,
        TimelineComparisonService $timelineService,
    ): View {
        $timelineService->compare(
            $request->user(),
        );

        $query = TimelineEvent::query()
            ->where(
                'user_id',
                $request->user()->id,
            );

        if ($request->string('view')->toString() === 'attention') {
            $query->whereIn('severity', ['critical', 'high', 'medium']);
        }

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->string('category'),
            );
        }

        if ($request->filled('severity')) {
            $query->where(
                'severity',
                $request->string('severity'),
            );
        }

        $events = $query
            ->orderByDesc('event_date')
            ->orderByDesc('detected_at')
            ->paginate(25)
            ->withQueryString();

        return view('timeline.index', [
            'events' => $events,
            'attentionCount' => TimelineEvent::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('severity', ['critical', 'high', 'medium'])
                ->count(),

            'eventCount' =>
                TimelineEvent::query()
                    ->where(
                        'user_id',
                        $request->user()->id,
                    )
                    ->count(),

            'criticalCount' =>
                TimelineEvent::query()
                    ->where(
                        'user_id',
                        $request->user()->id,
                    )
                    ->where('severity', 'critical')
                    ->count(),

            'positiveCount' =>
                TimelineEvent::query()
                    ->where(
                        'user_id',
                        $request->user()->id,
                    )
                    ->where('severity', 'positive')
                    ->count(),

            'latestEvent' =>
                TimelineEvent::query()
                    ->where(
                        'user_id',
                        $request->user()->id,
                    )
                    ->orderByDesc('event_date')
                    ->orderByDesc('id')
                    ->first(),
        ]);
    }
}
