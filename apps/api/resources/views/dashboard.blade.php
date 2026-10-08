<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Your portfolio overview</h2></x-slot>
    <div class="bg-[#080d18] min-h-screen py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6">
            @if (($unreadSupportCount ?? 0) > 0)
                <a href="{{ route('support.index') }}" class="block rounded-xl border border-blue-500/30 p-4 text-blue-200">You have a new message from Helmio Support.</a>
            @endif
            <div class="flex flex-wrap items-end justify-between gap-4 pb-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-400">Helmio dashboard</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">Your portfolio at a glance</h1>
                    <p class="mt-2 text-sm text-slate-400">A clear view of your investments, priorities, and progress.</p>
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

            {{-- Classic dashboard: at-a-glance portfolio cards. All values are persisted review data. --}}
            <section aria-label="Portfolio snapshot" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
                    <p class="mt-2 text-xs text-slate-400">Priority findings currently shown</p>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                    <p class="text-sm text-slate-400">Since last review</p>
                    @if (data_get($dashboardComparison, 'has_previous', false))
                        <p class="mt-3 text-2xl font-semibold text-white">{{ collect(data_get($dashboardComparison, 'resolved_findings', []))->count() }} resolved</p>
                        <p class="mt-2 text-xs text-slate-400">{{ collect(data_get($dashboardComparison, 'new_findings', []))->count() }} new findings</p>
                    @else
                        <p class="mt-3 text-2xl font-semibold text-white">First review</p>
                        <p class="mt-2 text-xs text-slate-400">Changes will appear after your next review</p>
                    @endif
                </div>
            </section>

            <section aria-labelledby="dashboard-breakdown" class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-lg sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="dashboard-breakdown" class="text-xl font-semibold text-white">Your Helm Score breakdown</h2>
                        <p class="mt-1 text-sm text-slate-400">See how each part of your portfolio contributes to the review.</p>
                    </div>
                    <span class="rounded-full border border-blue-500/30 bg-blue-500/10 px-3 py-1 text-sm text-blue-200">{{ is_numeric($dashboardScore) ? $dashboardScore.'/100 overall' : 'Score pending' }}</span>
                </div>
                @if ($dashboardCategories->isNotEmpty())
                    <div class="mt-6 grid gap-x-10 gap-y-5 md:grid-cols-2">
                        @foreach ($dashboardCategories as $key => $category)
                            @php $categoryScore = data_get($category, 'score'); @endphp
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                    <span class="font-medium text-slate-200">{{ str($key)->replace('_', ' ')->title() }}</span>
                                    <span class="tabular-nums text-slate-400">{{ is_numeric($categoryScore) ? round($categoryScore).'/100' : 'Not assessed' }}</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-slate-800" role="meter" aria-label="{{ str($key)->replace('_', ' ')->title() }} score" aria-valuemin="0" aria-valuemax="100" @if (is_numeric($categoryScore)) aria-valuenow="{{ min(100, max(0, round($categoryScore))) }}" @else aria-valuetext="Not assessed" @endif>
                                    @if (is_numeric($categoryScore))
                                        <div class="h-full rounded-full bg-blue-500" style="width: {{ min(100, max(0, (float) $categoryScore)) }}%"></div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-6 text-sm text-slate-400">Category scores will appear when enough portfolio data has been assessed.</p>
                @endif
            </section>

            @include('review.summary', ['review' => $dashboard, 'isOnboarding' => false])
        </div>
    </div>
</x-app-layout>
