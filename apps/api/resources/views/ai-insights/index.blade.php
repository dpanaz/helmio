<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Explainable portfolio intelligence
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    AI Portfolio Insights
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Plain-English explanations generated from Helmio’s deterministic analytics.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('ai-insights.generate') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.456-2.456L14.25 6l1.035-.259a3.375 3.375 0 0 0 2.456-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423L16.5 15.75l.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"
                        />
                    </svg>

                    Generate new insight
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <section class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-xl">
                <div class="grid gap-8 p-8 lg:grid-cols-[1.35fr_1fr] lg:p-10">
                    <div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-500/20 text-violet-300">
                            <svg
                                class="h-7 w-7"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                                />
                            </svg>
                        </div>

                        <p class="mt-6 text-sm font-semibold uppercase tracking-[0.18em] text-violet-300">
                            AI explanation layer
                        </p>

                        <h3 class="mt-3 max-w-2xl text-3xl font-semibold tracking-tight">
                            Understand what your portfolio data is telling you.
                        </h3>

                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                            Helmio’s AI does not calculate your scores. It explains the
                            deterministic analytics already produced by the Helm Score,
                            Advisor Audit, brokerage sync and findings engines.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <p class="text-sm font-medium text-slate-400">
                            Latest insight status
                        </p>

                        @if ($latestInsight)
                            <p class="mt-3 text-2xl font-semibold">
                                {{ $latestInsight->is_stale
                                    ? 'Needs Refresh'
                                    : str($latestInsight->status)->title() }}
                            </p>

                            <p class="mt-2 text-sm text-slate-300">
                                Generated
                                {{ $latestInsight->generated_at->diffForHumans() }}
                            </p>

                            @if ($latestInsight->is_stale)
                                <div class="mt-5 rounded-2xl border border-amber-300/30 bg-amber-400/10 p-4">
                                    <p class="text-sm font-semibold text-amber-200">
                                        Portfolio data changed
                                    </p>

                                    <p class="mt-2 text-sm leading-6 text-amber-100/90">
                                        {{ $latestInsight->stale_reason
                                            ?: 'Your portfolio changed after this insight was generated.' }}
                                    </p>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'ai-insights.regenerate',
                                            $latestInsight
                                        ) }}"
                                        class="mt-4"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                                        >
                                            Regenerate Insight
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <div class="mt-6 border-t border-white/10 pt-5">
                                <a
                                    href="{{ route(
                                        'ai-insights.show',
                                        $latestInsight
                                    ) }}"
                                    class="inline-flex items-center gap-2 text-sm font-semibold text-violet-300 hover:text-violet-200"
                                >
                                    Open latest insight

                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m9 18 6-6-6-6"
                                        />
                                    </svg>
                                </a>
                            </div>
                        @else
                            <p class="mt-3 text-2xl font-semibold">
                                No insight yet
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-300">
                                Generate the first portfolio explanation using your
                                current Helmio analytics.
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            @if ($insights->isEmpty())
                <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                        <svg
                            class="h-8 w-8"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-xl font-semibold text-slate-900">
                        No AI insights generated
                    </h3>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">
                        Create your first explanation to see portfolio priorities,
                        positive changes, limitations and supporting analytics.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('ai-insights.generate') }}"
                        class="mt-7"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
                        >
                            Generate first insight
                        </button>
                    </form>
                </section>
            @else
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Insight history
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Saved explanations generated from your portfolio context.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach ($insights as $insight)
                            @php
                                $statusClasses = match ($insight->status) {
                                    'completed' =>
                                        'bg-emerald-100 text-emerald-800',

                                    'failed' =>
                                        'bg-red-100 text-red-800',

                                    'blocked' =>
                                        'bg-amber-100 text-amber-800',

                                    default =>
                                        'bg-slate-100 text-slate-700',
                                };

                                $confidence = data_get(
                                    $insight->response_payload,
                                    'confidence',
                                );
                            @endphp

                            <article class="p-6">
                                <div class="flex flex-wrap items-start justify-between gap-6">
                                    <div class="max-w-4xl">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                                {{ str($insight->status)->title() }}
                                            </span>

                                            @if ($insight->is_stale)
                                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                                    Needs Refresh
                                                </span>
                                            @else
                                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                                    Current
                                                </span>
                                            @endif

                                            @if ($confidence)
                                                <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800">
                                                    {{ str($confidence)->title() }}
                                                    confidence
                                                </span>
                                            @endif

                                            <span class="text-xs text-slate-400">
                                                {{ $insight->generated_at->format(
                                                    'M j, Y g:i A'
                                                ) }}
                                            </span>
                                        </div>

                                        <h4 class="mt-4 text-lg font-semibold text-slate-900">
                                            {{ $insight->headline
                                                ?: 'Portfolio insight' }}
                                        </h4>

                                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">
                                            {{ $insight->summary
                                                ?: 'No summary was generated.' }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-3">
                                        @if ($insight->is_stale)
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'ai-insights.regenerate',
                                                    $insight
                                                ) }}"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center rounded-xl bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                                                >
                                                    Regenerate
                                                </button>
                                            </form>
                                        @endif

                                        <a
                                            href="{{ route(
                                                'ai-insights.show',
                                                $insight
                                            ) }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                        >
                                            View insight

                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m9 18 6-6-6-6"
                                                />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <div>
                    {{ $insights->links() }}
                </div>
            @endif

            <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <p class="text-sm font-semibold text-slate-900">
                    How AI insights work
                </p>

                <p class="mt-3 text-sm leading-7 text-slate-500">
                    Helmio passes a controlled portfolio context to the insight
                    provider. The context contains scores, findings, holdings,
                    data freshness and limitations. The AI explanation cannot place
                    trades, change account data or override Helmio’s calculations.
                </p>
            </section>
        </div>
    </div>
</x-app-layout>