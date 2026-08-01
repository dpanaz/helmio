
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

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl">
                <div class="flex flex-wrap items-start justify-between gap-8">
                    <div>
                        <p class="text-sm font-medium text-blue-300">
                            Overall Helm Score
                        </p>

                        @if ($helmScore['overall_score'] !== null)
                            <div class="mt-4 flex items-end gap-4">
                                <span class="text-7xl font-semibold">
                                    {{ $helmScore['overall_score'] }}
                                </span>

                                <span class="pb-2 text-xl text-slate-300">
                                    {{ $helmScore['overall_label'] }}
                                </span>
                            </div>
                        @else
                            <p class="mt-4 text-3xl font-semibold">
                                {{ $helmScore['overall_label'] }}
                            </p>

                            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-400">
                                Helmio will publish an overall score after at least
                                four analytics categories have sufficient supporting
                                data.
                            </p>
                        @endif
                    </div>

                    <div class="min-w-56 rounded-2xl bg-white/5 p-5">
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
                                style="width: {{ min(100, $helmScore['data_completeness'] * 100) }}%"
                            ></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($helmScore['categories'] as $key => $category)
                    <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
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
                                    'flex h-14 w-14 items-center justify-center rounded-2xl text-xl font-semibold',
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
                            @foreach ($category['reasons'] as $reason)
                                <p class="text-sm leading-6 text-slate-600">
                                    {{ $reason }}
                                </p>
                            @endforeach
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

                        @if ($key === 'cost' && $category['score'] !== null)
                            <a
                                href="{{ route('analytics.costs') }}"
                                class="mt-6 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                            >
                                View cost analysis →
                            </a>
                        @endif

                        @if (
                            $key === 'diversification'
                            && $category['score'] !== null
                        )
                            <a
                                href="{{ route('analytics.diversification') }}"
                                class="mt-6 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                            >
                                View diversification analysis →
                            </a>
                        @endif

                        @if (
                            $key === 'trading'
                            && $category['score'] !== null
                        )
                            <a
                                href="{{ route('analytics.trading-discipline') }}"
                                class="mt-6 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                            >
                                View trading analysis →
                            </a>
                        @endif
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Cost score
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $helmScore['categories']['cost']['score'] ?? '—' }}
                    </p>

                    <a
                        href="{{ route('analytics.costs') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Review fees and costs →
                    </a>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Diversification score
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $helmScore['categories']['diversification']['score'] ?? '—' }}
                    </p>

                    <a
                        href="{{ route('analytics.diversification') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Review concentration →
                    </a>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Trading score
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $helmScore['categories']['trading']['score'] ?? '—' }}
                    </p>

                    <a
                        href="{{ route('analytics.trading-discipline') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Review trading activity →
                    </a>
                </article>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">
                            How the Helm Score works
                        </h3>

                        <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-600">
                            Each category is calculated from deterministic
                            portfolio data and versioned formulas. Helmio does
                            not use artificial intelligence to calculate
                            scores. AI may later explain the results, but every
                            score must remain reproducible from stored account
                            data.
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-5 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Formula version
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-700">
                            {{ $helmScore['formula_version'] }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
```
