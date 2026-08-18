@php
    $generationInProgress = request()->boolean('generating');
    $generationBaselineId = max(0, (int) request()->query('baseline_id', 0));
    $currentLatestInsightId = (int) ($latestInsight?->id ?? 0);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
                >
                    Explainable portfolio intelligence
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    AI Portfolio Insights
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Plain-English explanations generated from Helmio’s
                    deterministic portfolio analytics.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('ai-insights.generate') }}"
                data-ai-insight-form
            >
                @csrf

                <button
                    type="submit"
                    data-ai-insight-button
                    @disabled($generationInProgress)
                    class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:bg-violet-600"
                >
                    <span
                        data-ai-insight-idle
                        class="inline-flex items-center gap-2"
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
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                            />
                        </svg>

                        Generate new insight
                    </span>

                    <span
                        data-ai-insight-loading
                        class="hidden items-center gap-2"
                    >
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                        </svg>
                        Generating insight…
                    </span>
                </button>

                <p
                    data-ai-insight-status
                    class="mt-2 hidden text-right text-xs text-violet-300"
                >
                    Analyzing your portfolio. This may take a few moments.
                </p>
            </form>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            @if ($generationInProgress)
                <div
                    data-ai-generation-banner
                    class="rounded-2xl border border-violet-500/25 bg-violet-500/[0.08] px-5 py-4"
                >
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-500/15 text-violet-300">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-violet-200">
                                Generating your AI portfolio insight…
                            </p>

                            <p
                                data-ai-generation-message
                                class="mt-1 text-sm leading-6 text-slate-400"
                            >
                                Helmio is analyzing your current portfolio context. This page will update automatically when the insight is ready.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif (session('success'))
                <div
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Hero --}}
            <section
                class="overflow-hidden rounded-3xl border border-violet-500/20 bg-slate-900 shadow-xl"
            >
                <div
                    class="grid gap-8 p-7 lg:grid-cols-[1.35fr_1fr] lg:p-9"
                >
                    <div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl border border-violet-500/20 bg-violet-500/10 text-violet-300"
                        >
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

                        <p
                            class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-violet-400"
                        >
                            AI explanation layer
                        </p>

                        <h3
                            class="mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-white"
                        >
                            Understand what your portfolio data is telling you.
                        </h3>

                        <p
                            class="mt-4 max-w-2xl text-sm leading-7 text-slate-400"
                        >
                            Helmio’s AI does not calculate your scores. It
                            explains the deterministic analytics produced by
                            the Helm Score, Advisor Audit, brokerage sync,
                            and findings engines.
                        </p>

                        <div
                            class="mt-7 flex flex-wrap gap-3"
                        >
                            <span
                                class="rounded-full border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs font-medium text-slate-400"
                            >
                                Portfolio context
                            </span>

                            <span
                                class="rounded-full border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs font-medium text-slate-400"
                            >
                                Findings
                            </span>

                            <span
                                class="rounded-full border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs font-medium text-slate-400"
                            >
                                Data limitations
                            </span>

                            <span
                                class="rounded-full border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs font-medium text-slate-400"
                            >
                                Explainable output
                            </span>
                        </div>
                    </div>

                    {{-- Latest status --}}
                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-6"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-medium text-slate-500"
                                >
                                    Latest insight status
                                </p>

                                @if ($generationInProgress)
                                    <p
                                        class="mt-2 flex items-center gap-3 text-2xl font-semibold text-white"
                                    >
                                        <span class="relative flex h-3 w-3">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-75"></span>
                                            <span class="relative inline-flex h-3 w-3 rounded-full bg-violet-400"></span>
                                        </span>
                                        Generating…
                                    </p>
                                @elseif ($latestInsight)
                                    <p
                                        class="mt-2 text-2xl font-semibold text-white"
                                    >
                                        {{ $latestInsight->is_stale
                                            ? 'Needs Refresh'
                                            : str($latestInsight->status)->title() }}
                                    </p>
                                @else
                                    <p
                                        class="mt-2 text-2xl font-semibold text-white"
                                    >
                                        No insight yet
                                    </p>
                                @endif
                            </div>

                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-300"
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
                                        d="M12 6v6l4 2"
                                    />
                                </svg>
                            </div>
                        </div>

                        @if ($latestInsight)
                            @if ($generationInProgress)
                                <p
                                    class="mt-3 text-sm leading-6 text-violet-300"
                                >
                                    A new insight is in progress. The insight below is your previous result until generation finishes.
                                </p>
                            @endif

                            <p
                                class="mt-3 text-sm text-slate-500"
                            >
                                Generated
                                {{ $latestInsight->generated_at->diffForHumans() }}
                            </p>

                            @if ($latestInsight->is_stale)
                                <div
                                    class="mt-5 rounded-2xl border border-amber-500/20 bg-amber-500/[0.07] p-4"
                                >
                                    <p
                                        class="text-sm font-semibold text-amber-300"
                                    >
                                        Portfolio data changed
                                    </p>

                                    <p
                                        class="mt-2 text-sm leading-6 text-slate-400"
                                    >
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
                                        data-ai-insight-form
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            data-ai-insight-button
                                            class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-300 disabled:hover:bg-amber-400"
                                        >
                                            <span data-ai-insight-idle>
                                                Regenerate Insight
                                            </span>

                                            <span
                                                data-ai-insight-loading
                                                class="hidden items-center gap-2"
                                            >
                                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                                                </svg>
                                                Regenerating…
                                            </span>
                                        </button>

                                        <p
                                            data-ai-insight-status
                                            class="mt-2 hidden text-xs text-amber-300"
                                        >
                                            Refreshing this insight with current portfolio data.
                                        </p>
                                    </form>
                                </div>
                            @endif

                            <div
                                class="mt-6 border-t border-slate-800 pt-5"
                            >
                                <a
                                    href="{{ route(
                                        'ai-insights.show',
                                        $latestInsight
                                    ) }}"
                                    class="inline-flex items-center gap-2 text-sm font-semibold text-violet-400 transition hover:text-violet-300"
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
                            <p
                                class="mt-3 text-sm leading-6 text-slate-500"
                            >
                                Generate the first portfolio explanation
                                using your current Helmio analytics.
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            @if ($insights->isEmpty())
                {{-- Empty state --}}
                <section
                    class="rounded-3xl border border-dashed border-slate-700 bg-slate-900 p-12 text-center shadow-xl"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-violet-500/20 bg-violet-500/10 text-violet-300"
                    >
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

                    <h3
                        class="mt-5 text-xl font-semibold text-white"
                    >
                        No AI insights generated
                    </h3>

                    <p
                        class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500"
                    >
                        Create your first explanation to see portfolio
                        priorities, positive changes, limitations, and
                        supporting analytics.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('ai-insights.generate') }}"
                        class="mt-7"
                        data-ai-insight-form
                    >
                        @csrf

                        <button
                            type="submit"
                            data-ai-insight-button
                            @disabled($generationInProgress)
                            class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-500 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:bg-violet-600"
                        >
                            <span data-ai-insight-idle>
                                Generate first insight
                            </span>

                            <span
                                data-ai-insight-loading
                                class="hidden items-center gap-2"
                            >
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                                </svg>
                                Generating insight…
                            </span>
                        </button>

                        <p
                            data-ai-insight-status
                            class="mt-3 hidden text-xs text-violet-300"
                        >
                            Analyzing your portfolio. This may take a few moments.
                        </p>
                    </form>
                </section>
            @else
                {{-- History --}}
                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="border-b border-slate-800 px-6 py-5"
                    >
                        <h3
                            class="text-lg font-semibold text-white"
                        >
                            Insight history
                        </h3>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Saved explanations generated from your
                            portfolio context.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-800">
                        @foreach ($insights as $insight)
                            @php
                                $statusClasses = match (
                                    $insight->status
                                ) {
                                    'completed' =>
                                        'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

                                    'failed' =>
                                        'border-red-500/20 bg-red-500/10 text-red-300',

                                    'blocked' =>
                                        'border-amber-500/20 bg-amber-500/10 text-amber-300',

                                    default =>
                                        'border-slate-700 bg-slate-800 text-slate-400',
                                };

                                $confidence = data_get(
                                    $insight->response_payload,
                                    'confidence',
                                );
                            @endphp

                            <article
                                class="p-6 transition hover:bg-slate-800/30"
                            >
                                <div
                                    class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
                                >
                                    <div class="max-w-4xl">
                                        <div
                                            class="flex flex-wrap items-center gap-3"
                                        >
                                            <span
                                                class="rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}"
                                            >
                                                {{ str($insight->status)->title() }}
                                            </span>

                                            @if ($insight->is_stale)
                                                <span
                                                    class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300"
                                                >
                                                    Needs Refresh
                                                </span>
                                            @else
                                                <span
                                                    class="rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
                                                >
                                                    Current
                                                </span>
                                            @endif

                                            @if ($confidence)
                                                <span
                                                    class="rounded-full border border-violet-500/20 bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-300"
                                                >
                                                    {{ str($confidence)->title() }}
                                                    confidence
                                                </span>
                                            @endif

                                            <span
                                                class="text-xs text-slate-600"
                                            >
                                                {{ $insight->generated_at->format(
                                                    'M j, Y g:i A'
                                                ) }}
                                            </span>
                                        </div>

                                        <h4
                                            class="mt-4 text-lg font-semibold text-white"
                                        >
                                            {{ $insight->headline
                                                ?: 'Portfolio insight' }}
                                        </h4>

                                        <p
                                            class="mt-2 line-clamp-3 text-sm leading-6 text-slate-400"
                                        >
                                            {{ $insight->summary
                                                ?: 'No summary was generated.' }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex shrink-0 flex-wrap gap-3"
                                    >
                                        @if ($insight->is_stale)
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'ai-insights.regenerate',
                                                    $insight
                                                ) }}"
                                                data-ai-insight-form
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    data-ai-insight-button
                                                    @disabled($generationInProgress)
                                                    class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-amber-300 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:bg-amber-400"
                                                >
                                                    <span data-ai-insight-idle>
                                                        Regenerate
                                                    </span>

                                                    <span
                                                        data-ai-insight-loading
                                                        class="hidden items-center gap-2"
                                                    >
                                                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                                                        </svg>
                                                        Regenerating…
                                                    </span>
                                                </button>
                                            </form>
                                        @endif

                                        <a
                                            href="{{ route(
                                                'ai-insights.show',
                                                $insight
                                            ) }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-violet-500/50 hover:text-white"
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

                <div class="text-slate-400">
                    {{ $insights->links() }}
                </div>
            @endif

            {{-- Methodology --}}
            <section
                class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-violet-500/20 bg-violet-500/10 text-violet-300"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6v6m0 4h.01"
                            />
                        </svg>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
                        >
                            How AI insights work
                        </p>

                        <p
                            class="mt-3 text-sm leading-7 text-slate-400"
                        >
                            Helmio passes a controlled portfolio context to
                            the insight provider. The context contains scores,
                            findings, holdings, data freshness, and limitations.
                            The AI explanation cannot place trades, change
                            account data, or override Helmio’s calculations.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const generationInProgress = @json($generationInProgress);
        const generationBaselineId = @json($generationBaselineId);
        const currentLatestInsightId = @json($currentLatestInsightId);
        const statusUrl = @json(route('ai-insights.status'));
        const indexUrl = @json(route('ai-insights.index'));

        const setFormLoading = (form) => {
            const button = form.querySelector('[data-ai-insight-button]');
            const idle = form.querySelector('[data-ai-insight-idle]');
            const loading = form.querySelector('[data-ai-insight-loading]');
            const status = form.querySelector('[data-ai-insight-status]');

            if (!button) {
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.classList.add('cursor-not-allowed', 'opacity-70');

            idle?.classList.add('hidden');
            loading?.classList.remove('hidden');
            loading?.classList.add('inline-flex');
            status?.classList.remove('hidden');
        };

        document.querySelectorAll('[data-ai-insight-form]').forEach((form) => {
            form.addEventListener('submit', () => {
                setFormLoading(form);
            });

            if (generationInProgress) {
                const button = form.querySelector('[data-ai-insight-button]');

                if (button) {
                    button.disabled = true;
                    button.classList.add('cursor-not-allowed', 'opacity-70');
                }
            }
        });

        if (!generationInProgress) {
            return;
        }

        /*
         * Recovery path:
         * If this page was rendered after a newer insight already exists,
         * the generation has finished even if the URL still contains
         * ?generating=1&baseline_id=...
         */
        if (
            currentLatestInsightId > generationBaselineId
        ) {
            window.location.replace(indexUrl);
            return;
        }

        const bannerMessage = document.querySelector(
            '[data-ai-generation-message]'
        );

        const startedAt = Date.now();
        const pollEveryMs = 3000;
        const maxMonitorMs = 6 * 60 * 1000;

        const setMessage = (message) => {
            if (bannerMessage) {
                bannerMessage.textContent = message;
            }
        };

        const poll = async () => {
            try {
                const url = new URL(statusUrl, window.location.origin);
                url.searchParams.set(
                    'baseline_id',
                    String(generationBaselineId)
                );

                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (!response.ok) {
                    throw new Error(
                        `Status request failed with ${response.status}`
                    );
                }

                const data = await response.json();

                const hasNewInsight =
                    Boolean(data.latest)
                    && Number(data.latest.id) > generationBaselineId;

                if (
                    (data.finished || hasNewInsight)
                    && data.latest
                ) {
                    if (data.latest.status === 'completed') {
                        setMessage(
                            'Your new insight is ready. Updating the page…'
                        );
                    } else if (data.latest.status === 'failed') {
                        setMessage(
                            'Insight generation finished with an error. Updating the page…'
                        );
                    } else if (data.latest.status === 'blocked') {
                        setMessage(
                            'Insight generation was blocked. Updating the page…'
                        );
                    } else {
                        setMessage(
                            'Insight generation finished. Updating the page…'
                        );
                    }

                    window.setTimeout(() => {
                        window.location.replace(indexUrl);
                    }, 500);

                    return;
                }

                const elapsedSeconds = Math.floor(
                    (Date.now() - startedAt) / 1000
                );

                if (elapsedSeconds >= 20) {
                    setMessage(
                        `Still working… ${elapsedSeconds}s elapsed. You can leave this page and return later; generation will continue in the background.`
                    );
                }

                if (Date.now() - startedAt >= maxMonitorMs) {
                    setMessage(
                        'Generation is taking longer than expected, but it may still be running in the background. Refresh this page in a few minutes to check the result.'
                    );

                    return;
                }

                window.setTimeout(poll, pollEveryMs);
            } catch (error) {
                console.error(
                    'Unable to check AI insight generation status.',
                    error
                );

                if (Date.now() - startedAt >= maxMonitorMs) {
                    setMessage(
                        'Helmio could not confirm the current generation status. The job may still be running in the background.'
                    );

                    return;
                }

                setMessage(
                    'Your insight is still being generated. Helmio will keep checking automatically.'
                );

                window.setTimeout(poll, pollEveryMs);
            }
        };

        window.setTimeout(poll, 1200);
    });
</script>

</x-app-layout>