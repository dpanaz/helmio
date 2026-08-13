<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
                >
                    Portfolio explanation
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    AI Insight
                </h2>

                <p
                    class="mt-2 text-sm text-slate-400"
                >
                    A plain-English explanation grounded in your stored
                    Helmio portfolio data.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('ai-insights.index') }}"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                >
                    Insight history
                </a>

                <form
                    method="POST"
                    action="{{ route('ai-insights.generate') }}"
                    data-ai-insight-form
                >
                    @csrf

                    <button
                        type="submit"
                        data-ai-insight-button
                        class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-500 disabled:hover:bg-violet-600"
                    >
                        <span data-ai-insight-idle>
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
                        class="mt-2 hidden text-xs text-violet-300"
                    >
                        Analyzing your portfolio. This may take a few moments.
                    </p>
                </form>
            </div>
        </div>
    </x-slot>

    @php
        $confidence = data_get(
            $insight->response_payload,
            'confidence',
        );

        $statusClasses = match ($insight->status) {
            'completed' =>
                'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

            'failed' =>
                'border-red-500/20 bg-red-500/10 text-red-300',

            'blocked' =>
                'border-amber-500/20 bg-amber-500/10 text-amber-300',

            default =>
                'border-slate-700 bg-slate-800 text-slate-400',
        };

        $freshnessStatus = data_get(
            $insight->context_snapshot,
            'data_freshness.status',
            'unknown',
        );

        $portfolioValue = (float) data_get(
            $insight->context_snapshot,
            'portfolio.total_value',
            0,
        );

        $helmScore = data_get(
            $insight->context_snapshot,
            'helm_score.overall_score',
        );

        $auditGrade = data_get(
            $insight->context_snapshot,
            'advisor_audit.grade',
        );

        $portfolioValueAtGeneration =
            $insight->portfolio_value_at_generation
            ?? $portfolioValue;

        $accountCountAtGeneration =
            $insight->account_count_at_generation
            ?? data_get(
                $insight->context_snapshot,
                'portfolio.account_count',
                0,
            );
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            @if (session('success'))
                <div
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            {{-- Stale warning --}}
            @if ($insight->is_stale)
                <section
                    class="rounded-3xl border border-amber-500/20 bg-amber-500/[0.07] p-6"
                >
                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <div
                                class="flex flex-wrap items-center gap-3"
                            >
                                <span
                                    class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300"
                                >
                                    Needs Refresh
                                </span>

                                @if ($insight->stale_at)
                                    <span
                                        class="text-xs text-amber-400"
                                    >
                                        Marked stale
                                        {{ $insight->stale_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>

                            <p
                                class="mt-4 font-semibold text-white"
                            >
                                This insight was generated before your
                                latest portfolio update.
                            </p>

                            <p
                                class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                            >
                                {{ $insight->stale_reason
                                    ?: 'Your portfolio changed after this insight was generated.' }}
                            </p>
                        </div>

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
                                class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-300 disabled:hover:bg-amber-400"
                            >
                                <span data-ai-insight-idle>
                                    Regenerate with Current Data
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
                                Refreshing this insight with your latest portfolio data.
                            </p>
                        </form>
                    </div>
                </section>
            @endif

            {{-- Main insight --}}
            <section
                class="overflow-hidden rounded-3xl border border-violet-500/20 bg-slate-900 shadow-xl"
            >
                <div class="p-7 lg:p-9">
                    <div
                        class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between"
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
                            </div>

                            <p
                                class="mt-7 text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
                            >
                                Portfolio explanation
                            </p>

                            <h3
                                class="mt-3 text-3xl font-semibold tracking-tight text-white"
                            >
                                {{ $insight->headline
                                    ?: 'Portfolio insight' }}
                            </h3>

                            <p
                                class="mt-5 text-base leading-8 text-slate-300"
                            >
                                {{ $insight->summary
                                    ?: 'No summary was generated.' }}
                            </p>
                        </div>

                        <div
                            class="shrink-0 rounded-2xl border border-slate-800 bg-slate-950 p-5 lg:w-52"
                        >
                            <p class="text-sm text-slate-500">
                                Generated
                            </p>

                            <p
                                class="mt-2 font-semibold text-white"
                            >
                                {{ $insight->generated_at->format(
                                    'M j, Y'
                                ) }}
                            </p>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{ $insight->generated_at->format(
                                    'g:i A'
                                ) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Context metrics --}}
            <section
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >
                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Portfolio value
                    </p>

                    <p
                        class="mt-3 text-2xl font-semibold text-white"
                    >
                        ${{ number_format(
                            $portfolioValue,
                            2
                        ) }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-6"
                >
                    <p class="text-sm text-blue-300">
                        Helm Score
                    </p>

                    <p
                        class="mt-3 text-2xl font-semibold text-white"
                    >
                        {{ $helmScore ?? '—' }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Advisor Audit
                    </p>

                    <p
                        class="mt-3 text-2xl font-semibold text-white"
                    >
                        {{ $auditGrade ?? '—' }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Data freshness
                    </p>

                    <p
                        class="mt-3 text-2xl font-semibold text-white"
                    >
                        {{ str($freshnessStatus)
                            ->replace('_', ' ')
                            ->title() }}
                    </p>
                </article>
            </section>

            {{-- Priorities and positives --}}
            <section
                class="grid gap-6 xl:grid-cols-2"
            >
                {{-- Priorities --}}
                <article
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-300"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m9-3.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 7.5h.008v.008H12V16.5Z"
                                />
                            </svg>
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-amber-300"
                            >
                                Review priorities
                            </p>

                            <h3
                                class="text-lg font-semibold text-white"
                            >
                                What deserves attention
                            </h3>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($insight->priorities ?? [] as $priority)
                            <div
                                class="rounded-2xl border border-slate-800 bg-slate-950 p-5"
                            >
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    @if (! empty($priority['severity']))
                                        <span
                                            class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300"
                                        >
                                            {{ str($priority['severity'])->title() }}
                                        </span>
                                    @endif

                                    @if (! empty($priority['category']))
                                        <span
                                            class="text-xs font-medium uppercase tracking-wide text-slate-600"
                                        >
                                            {{ str($priority['category'])
                                                ->replace('_', ' ')
                                                ->title() }}
                                        </span>
                                    @endif
                                </div>

                                <h4
                                    class="mt-3 font-semibold text-white"
                                >
                                    {{ $priority['title']
                                        ?? 'Portfolio review item' }}
                                </h4>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-400"
                                >
                                    {{ $priority['reason']
                                        ?? 'Review the supporting analysis.' }}
                                </p>

                                @if (
                                    ! empty($priority['route_name'])
                                    && Route::has(
                                        $priority['route_name']
                                    )
                                )
                                    <a
                                        href="{{ route(
                                            $priority['route_name']
                                        ) }}"
                                        class="mt-4 inline-flex text-sm font-semibold text-blue-400 transition hover:text-blue-300"
                                    >
                                        Show supporting analysis →
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div
                                class="rounded-2xl border border-dashed border-slate-700 bg-slate-950 p-6 text-center"
                            >
                                <p
                                    class="font-medium text-white"
                                >
                                    No priority issues identified
                                </p>

                                <p
                                    class="mt-2 text-sm text-slate-500"
                                >
                                    The current context did not produce
                                    a priority review item.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>

                {{-- Positives --}}
                <article
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m4.5 12.75 6 6 9-13.5"
                                />
                            </svg>
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-emerald-300"
                            >
                                Positive signals
                            </p>

                            <h3
                                class="text-lg font-semibold text-white"
                            >
                                What is going well
                            </h3>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse (
                            $insight->positive_changes ?? []
                            as $positive
                        )
                            <div
                                class="flex gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.06] p-4"
                            >
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-emerald-300"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m4.5 12.75 6 6 9-13.5"
                                    />
                                </svg>

                                <p
                                    class="text-sm leading-6 text-slate-300"
                                >
                                    {{ $positive }}
                                </p>
                            </div>
                        @empty
                            <div
                                class="rounded-2xl border border-dashed border-slate-700 bg-slate-950 p-6 text-center"
                            >
                                <p
                                    class="font-medium text-white"
                                >
                                    No positive changes recorded
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>

            {{-- Limitations --}}
            @if (! empty($insight->limitations))
                <section
                    class="rounded-3xl border border-amber-500/20 bg-amber-500/[0.06] p-7"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-400"
                    >
                        Data limitations
                    </p>

                    <h3
                        class="mt-2 font-semibold text-white"
                    >
                        What Helmio could not fully assess
                    </h3>

                    <div class="mt-5 space-y-3">
                        @foreach ($insight->limitations as $limitation)
                            <div class="flex gap-3">
                                <span
                                    class="mt-2 h-2 w-2 shrink-0 rounded-full bg-amber-400"
                                ></span>

                                <p
                                    class="text-sm leading-6 text-slate-400"
                                >
                                    {{ $limitation }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Error --}}
            @if ($insight->error_message)
                <section
                    class="rounded-3xl border border-red-500/20 bg-red-500/[0.06] p-7"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.16em] text-red-400"
                    >
                        Provider error
                    </p>

                    <h3
                        class="mt-2 font-semibold text-white"
                    >
                        Insight-generation error
                    </h3>

                    <p
                        class="mt-3 text-sm leading-6 text-slate-400"
                    >
                        {{ $insight->error_message }}
                    </p>
                </section>
            @endif

            {{-- Explainability --}}
            <section
                class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl"
            >
                <div>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
                    >
                        Explainability
                    </p>

                    <h3
                        class="mt-2 text-lg font-semibold text-white"
                    >
                        Generation record
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Details about the model, context, and portfolio
                        snapshot used to create this explanation.
                    </p>
                </div>

                <div
                    class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
                >
                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-4"
                    >
                        <p
                            class="text-xs uppercase tracking-wide text-slate-600"
                        >
                            Provider
                        </p>

                        <p
                            class="mt-2 text-sm font-semibold text-white"
                        >
                            {{ str($insight->provider)->title() }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-4"
                    >
                        <p
                            class="text-xs uppercase tracking-wide text-slate-600"
                        >
                            Model
                        </p>

                        <p
                            class="mt-2 text-sm font-semibold text-white"
                        >
                            {{ $insight->model
                                ?: 'Not reported' }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-4"
                    >
                        <p
                            class="text-xs uppercase tracking-wide text-slate-600"
                        >
                            Context version
                        </p>

                        <p
                            class="mt-2 break-all text-sm font-semibold text-white"
                        >
                            {{ $insight->context_version }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-4"
                    >
                        <p
                            class="text-xs uppercase tracking-wide text-slate-600"
                        >
                            Prompt version
                        </p>

                        <p
                            class="mt-2 break-all text-sm font-semibold text-white"
                        >
                            {{ $insight->prompt_version }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-4"
                    >
                        <p
                            class="text-xs uppercase tracking-wide text-slate-600"
                        >
                            Value at generation
                        </p>

                        <p
                            class="mt-2 text-sm font-semibold text-white"
                        >
                            ${{ number_format(
                                (float) $portfolioValueAtGeneration,
                                2
                            ) }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-950 p-4"
                    >
                        <p
                            class="text-xs uppercase tracking-wide text-slate-600"
                        >
                            Accounts
                        </p>

                        <p
                            class="mt-2 text-sm font-semibold text-white"
                        >
                            {{ number_format(
                                (int) $accountCountAtGeneration
                            ) }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-6 rounded-2xl border border-violet-500/20 bg-violet-500/[0.05] p-5"
                >
                    <p
                        class="text-sm leading-7 text-slate-400"
                    >
                        This explanation is generated from the stored
                        context snapshot. It does not modify account data
                        or calculate the underlying Helm Score and Advisor
                        Audit metrics.
                    </p>
                </div>
            </section>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-ai-insight-form]').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('[data-ai-insight-button]');
                const idle = form.querySelector('[data-ai-insight-idle]');
                const loading = form.querySelector('[data-ai-insight-loading]');
                const status = form.querySelector('[data-ai-insight-status]');

                if (!button || button.disabled) {
                    return;
                }

                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                button.classList.add('cursor-not-allowed', 'opacity-70');

                idle?.classList.add('hidden');
                loading?.classList.remove('hidden');
                loading?.classList.add('inline-flex');
                status?.classList.remove('hidden');
            });
        });
    });
</script>

</x-app-layout>