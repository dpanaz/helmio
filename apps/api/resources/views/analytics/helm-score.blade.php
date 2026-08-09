<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Independent portfolio assessment
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Helm Score
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                A consolidated view of portfolio costs, diversification,
                performance, risk, trading, and tax efficiency.
            </p>
        </div>
    </x-slot>

    @php
        $categoryRoutes = [
            'cost' => [
                'route' => 'analytics.costs',
                'label' => 'View cost analysis',
            ],
            'diversification' => [
                'route' => 'analytics.diversification',
                'label' => 'View diversification analysis',
            ],
            'trading' => [
                'route' => 'analytics.trading-discipline',
                'label' => 'View trading analysis',
            ],
            'performance' => [
                'route' => 'analytics.performance',
                'label' => 'View performance analysis',
            ],
            'risk' => [
                'route' => 'analytics.risk',
                'label' => 'View risk analysis',
            ],
            'tax' => [
                'route' => 'analytics.tax-efficiency',
                'label' => 'View tax analysis',
            ],
        ];

        $overallScore =
            $helmScore['overall_score'];

        $overallColor = match (true) {
            $overallScore === null => '#64748b',
            $overallScore >= 80 => '#10b981',
            $overallScore >= 60 => '#3b82f6',
            $overallScore >= 40 => '#f59e0b',
            default => '#ef4444',
        };
    @endphp

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >
                <div class="grid gap-8 p-7 lg:grid-cols-[1fr_20rem] lg:p-9">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Overall Helm Score
                        </p>

                        @if ($overallScore !== null)
                            <div class="mt-5 flex items-end gap-4">
                                <span
                                    class="text-7xl font-semibold tracking-tight text-white"
                                >
                                    {{ $overallScore }}
                                </span>

                                <span class="pb-2 text-lg font-medium"
                                    style="color: {{ $overallColor }}"
                                >
                                    {{ $helmScore['overall_label'] }}
                                </span>
                            </div>

                            <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-400">
                                Your overall Helm Score combines completed
                                analytics categories. Scores remain provisional
                                until all categories have sufficient supporting data.
                            </p>
                        @else
                            <p class="mt-5 text-3xl font-semibold text-white">
                                {{ $helmScore['overall_label'] }}
                            </p>

                            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-400">
                                Helmio will publish an overall score after at least
                                four analytics categories have sufficient data.
                            </p>
                        @endif
                    </div>

                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-5"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">
                                Score completeness
                            </p>

                            <span class="text-xl font-semibold text-white">
                                {{ number_format(
                                    $helmScore['data_completeness'] * 100,
                                    0
                                ) }}%
                            </span>
                        </div>

                        <div
                            class="mt-4 h-2 overflow-hidden rounded-full bg-slate-800"
                        >
                            <div
                                class="h-full rounded-full bg-blue-500"
                                style="width: {{ min(
                                    100,
                                    $helmScore['data_completeness'] * 100
                                ) }}%"
                            ></div>
                        </div>

                        <p class="mt-4 text-xs leading-5 text-slate-600">
                            Completed categories divided by the six planned
                            Helm Score categories.
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($helmScore['categories'] as $key => $category)
                    @php
                        $score = $category['score'];

                        $toneClass = match (true) {
                            $score === null =>
                                'border-slate-800',

                            $score >= 80 =>
                                'border-emerald-500/25',

                            $score >= 60 =>
                                'border-blue-500/25',

                            $score >= 40 =>
                                'border-amber-500/25',

                            default =>
                                'border-red-500/25',
                        };

                        $badgeClass = match (true) {
                            $score === null =>
                                'bg-slate-800 text-slate-500',

                            $score >= 80 =>
                                'bg-emerald-500/10 text-emerald-300',

                            $score >= 60 =>
                                'bg-blue-500/10 text-blue-300',

                            $score >= 40 =>
                                'bg-amber-500/10 text-amber-300',

                            default =>
                                'bg-red-500/10 text-red-300',
                        };
                    @endphp

                    <article
                        class="flex flex-col rounded-3xl border {{ $toneClass }} bg-slate-900 p-6 shadow-xl"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-600">
                                    {{ str($key)->replace('_', ' ')->title() }}
                                </p>

                                <p class="mt-2 text-lg font-semibold text-white">
                                    {{ $category['label'] }}
                                </p>
                            </div>

                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-semibold {{ $badgeClass }}"
                            >
                                {{ $score ?? '—' }}
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            @forelse ($category['reasons'] as $reason)
                                <div class="flex gap-3">
                                    <span
                                        class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-600"
                                    ></span>

                                    <p class="text-sm leading-6 text-slate-400">
                                        {{ $reason }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">
                                    No findings are available for this category.
                                </p>
                            @endforelse
                        </div>

                        @if (count($category['recommendations']) > 0)
                            <div
                                class="mt-6 rounded-2xl border border-blue-500/20 bg-blue-500/[0.06] p-4"
                            >
                                <p class="text-xs font-semibold uppercase tracking-wider text-blue-400">
                                    Recommended next step
                                </p>

                                <p class="mt-2 text-sm leading-6 text-slate-300">
                                    {{ $category['recommendations'][0] }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-auto pt-6">
                            @if (
                                isset($categoryRoutes[$key])
                                && $score !== null
                            )
                                <a
                                    href="{{ route(
                                        $categoryRoutes[$key]['route']
                                    ) }}"
                                    class="inline-flex text-sm font-semibold text-blue-400 hover:text-blue-300"
                                >
                                    {{ $categoryRoutes[$key]['label'] }}
                                    →
                                </a>
                            @else
                                <span class="text-sm text-slate-600">
                                    Awaiting sufficient data
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-analytics.panel title="How the Helm Score Works">
                    <p class="text-sm leading-7 text-slate-400">
                        Each category is calculated from deterministic
                        portfolio data and versioned formulas. Helmio does not
                        use artificial intelligence to calculate scores. AI may
                        explain results, but every score remains reproducible
                        from stored account data.
                    </p>

                    <p class="mt-4 text-sm leading-7 text-slate-400">
                        A low score does not conclude that an adviser,
                        investment, or transaction is improper. It identifies
                        patterns that may deserve closer review.
                    </p>
                </x-analytics.panel>

                <x-analytics.panel title="Current Score Coverage">
                    <div class="space-y-4">
                        @foreach ($helmScore['categories'] as $key => $category)
                            <div
                                class="flex items-center justify-between border-b border-slate-800 pb-4 last:border-0 last:pb-0"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        @class([
                                            'h-2.5 w-2.5 rounded-full',
                                            'bg-emerald-400' =>
                                                $category['score'] !== null,
                                            'bg-slate-700' =>
                                                $category['score'] === null,
                                        ])
                                    ></span>

                                    <span class="text-sm font-medium text-slate-300">
                                        {{ str($key)->replace('_', ' ')->title() }}
                                    </span>
                                </div>

                                <span class="text-sm font-semibold text-white">
                                    {{ $category['score'] ?? 'Pending' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </x-analytics.panel>
            </div>

            <x-analytics.methodology
                title="Calculation transparency"
                :formula-version="$helmScore['formula_version']"
            >
                Helmio stores the formula version, supporting metrics,
                and category findings for each score snapshot. Historical
                reports can therefore be reproduced using the methodology
                active when they were generated.

                <p class="mt-3 text-xs text-slate-600">
                    Calculated for
                    {{ $helmScore['calculated_for_date'] }}.
                </p>
            </x-analytics.methodology>
        </div>
    </div>
</x-app-layout>