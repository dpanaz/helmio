<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Your portfolio overview</h2></x-slot>
    {{-- Server-rendered graphs appear on initial HTML load; animation needs no JavaScript or scroll event. --}}
    <style>
        @keyframes helmio-graph-reveal {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }
        @keyframes helmio-dial-reveal {
            from { opacity: .65; transform: scale(.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .helmio-category-graph {
            transform-origin: left center;
            animation: helmio-graph-reveal 650ms ease-out both;
        }
        .helmio-score-dial {
            animation: helmio-dial-reveal 650ms ease-out both;
        }
        @media (prefers-reduced-motion: reduce) {
            .helmio-category-graph, .helmio-score-dial { animation: none; }
        }
    </style>
    <div class="bg-[#080d18] min-h-screen py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6">
            @if (($unreadSupportCount ?? 0) > 0)
                <a href="{{ route('support.index') }}" class="block rounded-xl border border-blue-500/30 p-4 text-blue-200">You have a new message from Helmio Support.</a>
            @endif
            <div class="flex flex-wrap items-end justify-between gap-4 pb-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-400">Helmio dashboard</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">Your investment overview</h1>
                    <p class="mt-2 text-sm text-slate-400">Your portfolio health, priorities, and next steps in one place.</p>
                </div>
                <a href="{{ route('accounts.index') }}" class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-medium text-blue-200 hover:border-blue-500">View accounts →</a>
            </div>

            @php
                $dashboardHelm = $dashboard['helm'] ?? [];
                $dashboardScore = data_get($dashboardHelm, 'overall_score');
                $dashboardCategories = collect(data_get($dashboardHelm, 'categories', []))
                    ->filter(fn ($category) => is_array($category))
                    ->take(6);
                $dashboardFindings = collect($dashboard['openFindings'] ?? []);
                $dashboardComparison = $dashboard['auditComparison'] ?? [];
            @endphp

            @php
                $dashboardAnalysis = $dashboard['analysisRun'] ?? null;
                $dashboardUpdating = $dashboardAnalysis && ! in_array($dashboardAnalysis->status, ['ready', 'failed'], true);
            @endphp
            @if ($dashboardUpdating)
                <p role="status" class="rounded-xl border border-blue-500/30 bg-blue-950/30 px-4 py-3 text-sm text-blue-200">Your review is updating. Results below are from the last completed review.</p>
            @elseif (data_get($dashboardAnalysis, 'status') === 'failed')
                <p role="alert" class="rounded-xl border border-amber-500/30 bg-amber-950/30 px-4 py-3 text-sm text-amber-200">The latest review could not finish. Any results below are from an earlier review.</p>
            @endif

            {{-- Restore the classic top: Helm dial on the left, category health lines on the right. --}}
            <section aria-label="Helm Score and category health" class="grid overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl lg:grid-cols-[minmax(270px,1fr)_minmax(0,2fr)]">
                <div class="flex flex-col items-center justify-center border-b border-slate-800 bg-gradient-to-b from-[#14294c] to-slate-900 px-6 py-9 text-center lg:border-b-0 lg:border-r">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-300">Your Helm Score</p>
                    @php
                        $dialColor = match (true) { ! is_numeric($dashboardScore) => '#64748b', (float) $dashboardScore >= 80 => '#22c55e', (float) $dashboardScore >= 70 => '#3b82f6', (float) $dashboardScore >= 60 => '#f59e0b', (float) $dashboardScore >= 40 => '#f97316', default => '#ef4444' };
                        $dialPercent = is_numeric($dashboardScore) ? min(100, max(0, (float) $dashboardScore)) : 0;
                    @endphp
                    <div class="helmio-score-dial mt-6 flex h-52 w-52 items-center justify-center rounded-full p-3" style="background: conic-gradient({{ $dialColor }} {{ $dialPercent }}%, #334155 0)">
                        <div class="flex h-full w-full flex-col items-center justify-center rounded-full bg-[#0f1b30]">
                            <span class="text-6xl font-bold tabular-nums text-white">{{ is_numeric($dashboardScore) ? round($dashboardScore) : '—' }}</span>
                            <span class="mt-1 text-sm text-slate-400">{{ is_numeric($dashboardScore) ? 'out of 100' : 'Not assessed' }}</span>
                        </div>
                    </div>
                    <p class="mt-5 text-lg font-semibold text-white">{{ is_numeric($dashboardScore) ? data_get($dashboardHelm, 'overall_label', 'Based on available data') : 'Building your score' }}</p>
                    <p class="mt-2 text-xs text-slate-400">Based on available portfolio data</p>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-semibold text-white">Portfolio health</h2>
                            <p class="mt-1 text-sm text-slate-400">See which areas need attention at a glance.</p>
                        </div>
                        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-300" aria-label="Score color legend">
                            <span><span class="text-emerald-400">●</span> Strong 80+</span>
                            <span><span class="text-blue-400">●</span> Good 70–79</span>
                            <span><span class="text-amber-400">●</span> Watch 60–69</span>
                            <span><span class="text-orange-400">●</span> Concern 40–59</span>
                            <span><span class="text-red-400">●</span> Critical 0–39</span>
                            <span><span class="text-slate-400">●</span> Unknown</span>
                        </div>
                    </div>
                    @if ($dashboardCategories->isNotEmpty())
                        <div class="mt-7 space-y-5">
                            @foreach ($dashboardCategories as $key => $category)
                                @php
                                    $categoryScore = data_get($category, 'score');
                                    $categoryHasScore = is_numeric($categoryScore);
                                    $categoryPercent = $categoryHasScore ? min(100, max(0, (float) $categoryScore)) : 0;
                                    $categoryColor = match (true) { ! $categoryHasScore => '#64748b', $categoryPercent >= 80 => '#22c55e', $categoryPercent >= 70 => '#3b82f6', $categoryPercent >= 60 => '#f59e0b', $categoryPercent >= 40 => '#f97316', default => '#ef4444' };
                                    $categoryStatus = match (true) { ! $categoryHasScore => 'Not assessed', $categoryPercent >= 80 => 'Strong', $categoryPercent >= 70 => 'Good', $categoryPercent >= 60 => 'Watch', $categoryPercent >= 40 => 'Concern', default => 'Critical' };
                                @endphp
                                <div>
                                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-sm">
                                        <span class="font-semibold text-slate-100">{{ $key === 'risk' ? 'Risk Management' : str($key)->replace('_', ' ')->title() }}</span>
                                        <span class="font-medium tabular-nums" style="color: {{ $categoryColor }}">{{ $categoryStatus }} · {{ $categoryHasScore ? round($categoryScore).'/100' : '—' }}</span>
                                    </div>
                                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-800" role="meter" aria-label="{{ str($key)->replace('_', ' ')->title() }} score" aria-valuemin="0" aria-valuemax="100" @if ($categoryHasScore) aria-valuenow="{{ round($categoryPercent) }}" @else aria-valuetext="Not assessed" @endif>
                                        @if ($categoryHasScore)
                                            <div class="helmio-category-graph h-full rounded-full" style="width: {{ $categoryPercent }}%; background-color: {{ $categoryColor }}"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-8 text-sm text-slate-400">Category results will appear after your first completed analysis.</p>
                    @endif
                    <p class="mt-6 text-xs text-slate-400">Scores are indicators, not a guarantee of investment performance. Unassessed categories are not scored as zero.</p>
                </div>
            </section>

            {{-- Keep the homepage focused on the three numbers customers need first. --}}
            <section aria-label="Portfolio snapshot" class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                    <p class="text-sm text-slate-400">Portfolio value</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-white">{{ money($dashboard['portfolioValue'] ?? 0) }}</p>
                    <p class="mt-2 text-xs text-slate-400">Across connected accounts</p>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                    <p class="text-sm text-slate-400">Connected accounts</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['accountCount'] ?? 0 }}</p>
                    <a class="mt-2 inline-block text-xs text-blue-300 hover:underline" href="{{ route('accounts.index') }}">Manage accounts →</a>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                    <p class="text-sm text-slate-400">Items to review</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboardFindings->count() }}</p>
                    <p class="mt-2 text-xs text-slate-400">Open findings shown in this review</p>
                </div>
            </section>

            @include('review.summary', ['review' => $dashboard, 'isOnboarding' => false, 'hideScoreDial' => true, 'compactDashboard' => true])
        </div>
    </div>
</x-app-layout>
