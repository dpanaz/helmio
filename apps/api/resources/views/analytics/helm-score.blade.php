<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Independent portfolio assessment
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Helm Score
            </h2>
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
        ];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-3xl bg-slate-950 p-8 text-white shadow-xl">
                <div class="flex flex-wrap items-start justify-between gap-8">
                    <div>
                        <p class="text-sm font-medium text-blue-300">
                            Overall Helm Score
                        </p>

                        @if ($helmScore['overall_score'] !== null)
                            <div class="mt-4 flex flex-wrap items-end gap-4">
                                <span class="text-7xl font-semibold tracking-tight">
                                    {{ $helmScore['overall_score'] }}
                                </span>

                                <span class="pb-2 text-xl text-slate-300">
                                    {{ $helmScore['overall_label'] }}
                                </span>
                            </div>

                            <p class="mt-4 max-w-xl text-sm leading-6 text-slate-400">
                                Your overall score is based on each completed
                                analytics category. Scores remain provisional
                                until all categories have sufficient data.
                            </p>
                        @else
                            <p class="mt-4 text-3xl font-semibold">
                                {{ $helmScore['overall_label'] }}
                            </p>

                            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-400">
                                Helmio will publish an overall score after at
                                least four analytics categories have sufficient
                                supporting data.
                            </p>
                        @endif
                    </div>

                    <div class="min-w-60 rounded-2xl border border-white/10 bg-white/5 p-5">
                        <p class="text-sm text-slate-400">
                            Score completeness
                        </p>

                        <p class="mt-2 text-3xl font-semibold">
                            {{ number_format(
                                $helmScore['data_completeness'] * 100,
                                0
                            ) }}%
                        </p>

                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                            <div
                                class="h-full rounded-full bg-blue-500"
                                style="width: {{ min(
                                    100,
                                    $helmScore['data_completeness'] * 100
                                ) }}%"
                            ></div>
                        </div>

                        <p class="mt-3 text-xs leading-5 text-slate-500">
                            Completed categories divided by the six planned
                            Helm Score categories.
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($helmScore['categories'] as $key => $category)
                    <article class="flex flex-col rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    {{ str($key)->replace('_', ' ')->title() }}
                                </p>

                                <p class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ $category['label'] }}
                                </p>
                            </div>

                            <div
                                @class([
                                    'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-semibold',
                                    'bg-emerald-100 text-emerald-800' =>
                                        $category['score'] !== null
                                        && $category['score'] >= 80,
                                    'bg-blue-100 text-blue-800' =>
                                        $category['score'] !== null
                                        && $category['score'] >= 60
                                        && $category['score'] < 80,
                                    'bg-amber-100 text-amber-800' =>
                                        $category['score'] !== null
                                        && $category['score'] >= 40
                                        && $category['score'] < 60,
                                    'bg-red-100 text-red-800' =>
                                        $category['score'] !== null
                                        && $category['score'] < 40,
                                    'bg-slate-100 text-slate-500' =>
                                        $category['score'] === null,
                                ])
                            >
                                {{ $category['score'] ?? '—' }}
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            @forelse ($category['reasons'] as $reason)
                                <p class="text-sm leading-6 text-slate-600">
                                    {{ $reason }}
                                </p>
                            @empty
                                <p class="text-sm leading-6 text-slate-500">
                                    No findings are available for this category.
                                </p>
                            @endforelse
                        </div>

                        @if (count($category['recommendations']) > 0)
                            <div class="mt-6 rounded-2xl bg-blue-50 p-4">
                                <p class="text-sm font-semibold text-blue-900">
                                    Recommended next step
                                </p>

                                <p class="mt-2 text-sm leading-6 text-blue-800">
                                    {{ $category['recommendations'][0] }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-auto pt-6">
                            @if (
                                isset($categoryRoutes[$key])
                                && $category['score'] !== null
                            )
                                <a
                                    href="{{ route(
                                        $categoryRoutes[$key]['route']
                                    ) }}"
                                    class="inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                                >
                                    {{ $categoryRoutes[$key]['label'] }} →
                                </a>
                            @elseif ($category['score'] === null)
                                <span class="text-sm font-medium text-slate-400">
                                    Awaiting sufficient data
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    [
                        'Cost',
                        $helmScore['categories']['cost']['score'] ?? null,
                        'analytics.costs',
                    ],
                    [
                        'Diversification',
                        $helmScore['categories']['diversification']['score'] ?? null,
                        'analytics.diversification',
                    ],
                    [
                        'Trading',
                        $helmScore['categories']['trading']['score'] ?? null,
                        'analytics.trading-discipline',
                    ],
                    [
                        'Performance',
                        $helmScore['categories']['performance']['score'] ?? null,
                        'analytics.performance',
                    ],
                ] as [$label, $score, $routeName])
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">
                            {{ $label }} score
                        </p>

                        <p class="mt-3 text-3xl font-semibold text-slate-900">
                            {{ $score ?? '—' }}
                        </p>

                        <a
                            href="{{ route($routeName) }}"
                            class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                        >
                            Open analysis →
                        </a>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        How the Helm Score works
                    </h3>

                    <p class="mt-4 text-sm leading-7 text-slate-600">
                        Each category is calculated from deterministic
                        portfolio data and versioned formulas. Helmio does not
                        use artificial intelligence to calculate scores. AI may
                        later explain results, but every score must remain
                        reproducible from stored account data.
                    </p>

                    <p class="mt-4 text-sm leading-7 text-slate-600">
                        A low score is not a conclusion that an adviser,
                        investment or transaction is improper. It identifies
                        data and patterns that may deserve closer review.
                    </p>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Current score coverage
                    </h3>

                    <div class="mt-6 space-y-4">
                        @foreach ($helmScore['categories'] as $key => $category)
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        @class([
                                            'h-3 w-3 rounded-full',
                                            'bg-emerald-500' =>
                                                $category['score'] !== null,
                                            'bg-slate-300' =>
                                                $category['score'] === null,
                                        ])
                                    ></span>

                                    <span class="text-sm font-medium text-slate-700">
                                        {{ str($key)->replace('_', ' ')->title() }}
                                    </span>
                                </div>

                                <span class="text-sm font-semibold text-slate-900">
                                    {{ $category['score'] ?? 'Pending' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <p class="text-sm font-medium text-blue-300">
                            Calculation transparency
                        </p>

                        <h3 class="mt-2 text-xl font-semibold">
                            Every score must be explainable
                        </h3>

                        <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300">
                            Helmio stores the formula version, supporting
                            metrics and category findings for each score
                            snapshot. Historical reports can therefore be
                            reproduced using the methodology that was active
                            when they were generated.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">
                            Formula version
                        </p>

                        <p class="mt-2 text-sm font-semibold text-white">
                            {{ $helmScore['formula_version'] }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Calculated for
                            {{ $helmScore['calculated_for_date'] }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>