<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Portfolio explanation
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    AI Insight
                </h2>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('ai-insights.index') }}"
                    class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Insight history
                </a>

                <form
                    method="POST"
                    action="{{ route('ai-insights.generate') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
                    >
                        Generate new insight
                    </button>
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
                'bg-emerald-100 text-emerald-800',

            'failed' =>
                'bg-red-100 text-red-800',

            'blocked' =>
                'bg-amber-100 text-amber-800',

            default =>
                'bg-slate-100 text-slate-700',
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

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($insight->is_stale)
                <section class="rounded-3xl border border-amber-300 bg-amber-50 p-6 shadow-sm">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-amber-200 px-3 py-1 text-xs font-semibold text-amber-900">
                                    Needs Refresh
                                </span>

                                @if ($insight->stale_at)
                                    <span class="text-xs text-amber-700">
                                        Marked stale
                                        {{ $insight->stale_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-4 font-semibold text-amber-950">
                                This insight was generated before your latest portfolio update.
                            </p>

                            <p class="mt-2 max-w-3xl text-sm leading-6 text-amber-800">
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
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-slate-950 shadow-sm hover:bg-amber-400"
                            >
                                Regenerate with Current Data
                            </button>
                        </form>
                    </div>
                </section>
            @endif

            <section class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-xl">
                <div class="p-8 lg:p-10">
                    <div class="flex flex-wrap items-start justify-between gap-8">
                        <div class="max-w-4xl">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ str($insight->status)->title() }}
                                </span>

                                @if ($insight->is_stale)
                                    <span class="rounded-full bg-amber-400/20 px-3 py-1 text-xs font-semibold text-amber-200">
                                        Needs Refresh
                                    </span>
                                @else
                                    <span class="rounded-full bg-blue-500/20 px-3 py-1 text-xs font-semibold text-blue-200">
                                        Current
                                    </span>
                                @endif

                                @if ($confidence)
                                    <span class="rounded-full bg-violet-500/20 px-3 py-1 text-xs font-semibold text-violet-200">
                                        {{ str($confidence)->title() }}
                                        confidence
                                    </span>
                                @endif
                            </div>

                            <h3 class="mt-6 text-3xl font-semibold tracking-tight">
                                {{ $insight->headline
                                    ?: 'Portfolio insight' }}
                            </h3>

                            <p class="mt-5 text-base leading-8 text-slate-300">
                                {{ $insight->summary
                                    ?: 'No summary was generated.' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <p class="text-sm text-slate-400">
                                Generated
                            </p>

                            <p class="mt-2 font-semibold">
                                {{ $insight->generated_at->format(
                                    'M j, Y'
                                ) }}
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                {{ $insight->generated_at->format(
                                    'g:i A'
                                ) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Portfolio value
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        ${{ number_format($portfolioValue, 2) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Helm Score
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        {{ $helmScore ?? '—' }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Advisor Audit
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        {{ $auditGrade ?? '—' }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Data freshness
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        {{ str($freshnessStatus)
                            ->replace('_', ' ')
                            ->title() }}
                    </p>
                </article>
            </section>

            <section class="grid gap-8 xl:grid-cols-2">
                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
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
                            <p class="text-sm font-medium text-slate-500">
                                Review priorities
                            </p>

                            <h3 class="text-lg font-semibold text-slate-900">
                                What deserves attention
                            </h3>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($insight->priorities ?? [] as $priority)
                            <div class="rounded-2xl border border-slate-200 p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if (! empty($priority['severity']))
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                            {{ str($priority['severity'])->title() }}
                                        </span>
                                    @endif

                                    @if (! empty($priority['category']))
                                        <span class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                            {{ str($priority['category'])
                                                ->replace('_', ' ')
                                                ->title() }}
                                        </span>
                                    @endif
                                </div>

                                <h4 class="mt-3 font-semibold text-slate-900">
                                    {{ $priority['title']
                                        ?? 'Portfolio review item' }}
                                </h4>

                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $priority['reason']
                                        ?? 'Review the supporting analysis.' }}
                                </p>

                                @if (
                                    ! empty($priority['route_name'])
                                    && Route::has($priority['route_name'])
                                )
                                    <a
                                        href="{{ route(
                                            $priority['route_name']
                                        ) }}"
                                        class="mt-4 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                                    >
                                        Show supporting analysis →
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center">
                                <p class="font-medium text-slate-900">
                                    No priority issues identified
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    The current context did not produce a priority review item.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
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
                            <p class="text-sm font-medium text-slate-500">
                                Positive signals
                            </p>

                            <h3 class="text-lg font-semibold text-slate-900">
                                What is going well
                            </h3>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($insight->positive_changes ?? [] as $positive)
                            <div class="flex gap-3 rounded-2xl bg-emerald-50 p-4">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700"
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

                                <p class="text-sm leading-6 text-emerald-900">
                                    {{ $positive }}
                                </p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center">
                                <p class="font-medium text-slate-900">
                                    No positive changes recorded
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>

            @if (! empty($insight->limitations))
                <section class="rounded-3xl border border-amber-200 bg-amber-50 p-7">
                    <p class="text-sm font-semibold text-amber-950">
                        Data and analysis limitations
                    </p>

                    <div class="mt-4 space-y-3">
                        @foreach ($insight->limitations as $limitation)
                            <div class="flex gap-3">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>

                                <p class="text-sm leading-6 text-amber-900">
                                    {{ $limitation }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($insight->error_message)
                <section class="rounded-3xl border border-red-200 bg-red-50 p-7">
                    <p class="font-semibold text-red-900">
                        Insight-generation error
                    </p>

                    <p class="mt-3 text-sm leading-6 text-red-700">
                        {{ $insight->error_message }}
                    </p>
                </section>
            @endif

            <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">
                    Explainability record
                </h3>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Provider
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ str($insight->provider)->title() }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Model
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $insight->model ?: 'Not reported' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Context version
                        </p>

                        <p class="mt-2 break-all text-sm font-semibold text-slate-900">
                            {{ $insight->context_version }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Prompt version
                        </p>

                        <p class="mt-2 break-all text-sm font-semibold text-slate-900">
                            {{ $insight->prompt_version }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Portfolio value at generation
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            ${{ number_format(
                                (float) $portfolioValueAtGeneration,
                                2
                            ) }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Accounts at generation
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ number_format(
                                (int) $accountCountAtGeneration
                            ) }}
                        </p>
                    </div>
                </div>

                <p class="mt-6 text-sm leading-7 text-slate-500">
                    This explanation is generated from the stored context snapshot.
                    It does not modify account data or calculate the underlying
                    Helm Score and Advisor Audit metrics.
                </p>
            </section>
        </div>
    </div>
</x-app-layout>