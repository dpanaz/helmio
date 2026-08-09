<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Change intelligence
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Portfolio Timeline
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    A chronological record of material portfolio,
                    audit, risk, cost, trading, and allocation changes.
                </p>
            </div>

            <a
                href="{{ route('advisor-audit.history') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
            >
                Audit history
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            {{-- ===================================================== --}}
            {{-- SUMMARY CARDS --}}
            {{-- ===================================================== --}}

            <section
                class="grid gap-4 sm:grid-cols-3"
            >
                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p class="text-sm text-slate-500">
                                Timeline events
                            </p>

                            <p
                                class="mt-3 text-3xl font-semibold text-white"
                            >
                                {{ number_format($eventCount) }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.9"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 12h4l2.5-5 4 10 2.5-5H21"
                                />
                            </svg>
                        </div>
                    </div>

                    <p
                        class="mt-4 text-xs leading-5 text-slate-600"
                    >
                        Material changes identified across recorded
                        portfolio and audit events.
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-6"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p class="text-sm text-emerald-300">
                                Positive changes
                            </p>

                            <p
                                class="mt-3 text-3xl font-semibold text-white"
                            >
                                {{ number_format($positiveCount) }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-300"
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
                                    d="m5 12 4 4L19 6"
                                />
                            </svg>
                        </div>
                    </div>

                    <p
                        class="mt-4 text-xs leading-5 text-slate-500"
                    >
                        Score improvements and favorable portfolio
                        developments.
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-red-500/20 bg-red-500/[0.05] p-6"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p class="text-sm text-red-300">
                                Critical changes
                            </p>

                            <p
                                class="mt-3 text-3xl font-semibold text-white"
                            >
                                {{ number_format($criticalCount) }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/10 text-red-300"
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
                                    d="M12 9v4m0 4h.01M10.3 4.5 2.6 18a1 1 0 0 0 .87 1.5h17.06a1 1 0 0 0 .87-1.5L13.7 4.5a1 1 0 0 0-1.74 0Z"
                                />
                            </svg>
                        </div>
                    </div>

                    <p
                        class="mt-4 text-xs leading-5 text-slate-500"
                    >
                        Material changes that may warrant prompt
                        review.
                    </p>
                </article>
            </section>

            {{-- ===================================================== --}}
            {{-- FILTERS --}}
            {{-- ===================================================== --}}

            <section
                class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
            >
                <div
                    class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-semibold text-white"
                        >
                            Filter timeline
                        </p>

                        <p
                            class="mt-1 text-xs text-slate-500"
                        >
                            Narrow events by category and severity.
                        </p>
                    </div>

                    @if (
                        request('category')
                        || request('severity')
                    )
                        <a
                            href="{{ route('portfolio-timeline.index') }}"
                            class="text-xs font-semibold text-blue-400 transition hover:text-blue-300"
                        >
                            Clear filters
                        </a>
                    @endif
                </div>

                <form
                    method="GET"
                    action="{{ route('portfolio-timeline.index') }}"
                    class="grid gap-4 sm:grid-cols-[1fr_1fr_auto]"
                >
                    <div>
                        <label
                            for="category"
                            class="block text-sm font-medium text-slate-400"
                        >
                            Category
                        </label>

                        <select
                            id="category"
                            name="category"
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 shadow-none focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">
                                All categories
                            </option>

                            @foreach ([
                                'overall',
                                'audit',
                                'portfolio',
                                'holdings',
                                'cost',
                                'diversification',
                                'performance',
                                'risk',
                                'trading',
                                'tax',
                            ] as $category)
                                <option
                                    value="{{ $category }}"
                                    @selected(
                                        request('category') === $category
                                    )
                                >
                                    {{ str($category)
                                        ->replace('_', ' ')
                                        ->title() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            for="severity"
                            class="block text-sm font-medium text-slate-400"
                        >
                            Severity
                        </label>

                        <select
                            id="severity"
                            name="severity"
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 shadow-none focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">
                                All severities
                            </option>

                            @foreach ([
                                'critical',
                                'high',
                                'medium',
                                'low',
                                'information',
                                'positive',
                            ] as $severity)
                                <option
                                    value="{{ $severity }}"
                                    @selected(
                                        request('severity') === $severity
                                    )
                                >
                                    {{ str($severity)
                                        ->replace('_', ' ')
                                        ->title() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
                        >
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
                                    d="M3 4.5h18M6.75 9h10.5m-7.5 4.5h4.5m-2.25 4.5h.008"
                                />
                            </svg>

                            Filter
                        </button>
                    </div>
                </form>
            </section>

            {{-- ===================================================== --}}
            {{-- TIMELINE --}}
            {{-- ===================================================== --}}

            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >
                <div
                    class="border-b border-slate-800 px-6 py-5"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3
                                class="text-lg font-semibold text-white"
                            >
                                Change history
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Material changes detected between
                                recorded portfolio assessments.
                            </p>
                        </div>

                        @if ($events->count() > 0)
                            <span
                                class="text-xs font-medium text-slate-600"
                            >
                                Showing {{ $events->count() }}
                                {{ Str::plural(
                                    'event',
                                    $events->count()
                                ) }}
                            </span>
                        @endif
                    </div>
                </div>

                @forelse ($events as $event)
                    @php
                        $severityClasses = match (
                            $event->severity
                        ) {
                            'critical' =>
                                'border-red-500/20 bg-red-500/10 text-red-300',

                            'high' =>
                                'border-orange-500/20 bg-orange-500/10 text-orange-300',

                            'medium' =>
                                'border-amber-500/20 bg-amber-500/10 text-amber-300',

                            'low' =>
                                'border-blue-500/20 bg-blue-500/10 text-blue-300',

                            'positive' =>
                                'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

                            default =>
                                'border-slate-700 bg-slate-800 text-slate-400',
                        };

                        $dotClasses = match (
                            $event->severity
                        ) {
                            'critical' =>
                                'bg-red-500 ring-red-500/20',

                            'high' =>
                                'bg-orange-500 ring-orange-500/20',

                            'medium' =>
                                'bg-amber-500 ring-amber-500/20',

                            'low' =>
                                'bg-blue-500 ring-blue-500/20',

                            'positive' =>
                                'bg-emerald-500 ring-emerald-500/20',

                            default =>
                                'bg-slate-500 ring-slate-500/20',
                        };

                        $eventCardClasses = match (
                            $event->severity
                        ) {
                            'critical' =>
                                'border-red-500/15 bg-red-500/[0.025]',

                            'high' =>
                                'border-orange-500/10 bg-orange-500/[0.02]',

                            'medium' =>
                                'border-amber-500/10 bg-amber-500/[0.02]',

                            'positive' =>
                                'border-emerald-500/10 bg-emerald-500/[0.02]',

                            default =>
                                'border-slate-800 bg-slate-950/40',
                        };
                    @endphp

                    <article
                        class="relative border-b border-slate-800 px-6 py-6 last:border-b-0 sm:px-8"
                    >
                        <div
                            class="relative pl-8 sm:pl-10"
                        >
                            {{-- Vertical rail --}}
                            <span
                                class="absolute left-[5px] top-5 bottom-[-1.5rem] w-px bg-slate-800 last:hidden"
                                aria-hidden="true"
                            ></span>

                            {{-- Timeline marker --}}
                            <span
                                class="absolute left-0 top-2 h-3 w-3 rounded-full ring-4 {{ $dotClasses }}"
                                aria-hidden="true"
                            ></span>

                            <div
                                class="rounded-2xl border p-5 {{ $eventCardClasses }}"
                            >
                                <div
                                    class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
                                >
                                    <div class="max-w-4xl">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="rounded-full border px-3 py-1 text-xs font-semibold {{ $severityClasses }}"
                                            >
                                                {{ str($event->severity)
                                                    ->replace('_', ' ')
                                                    ->title() }}
                                            </span>

                                            <span
                                                class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                                            >
                                                {{ str($event->category)
                                                    ->replace('_', ' ')
                                                    ->title() }}
                                            </span>

                                            <span
                                                class="text-xs text-slate-600"
                                            >
                                                {{ $event->event_date->format(
                                                    'M j, Y'
                                                ) }}
                                            </span>
                                        </div>

                                        <h3
                                            class="mt-4 text-lg font-semibold text-white"
                                        >
                                            {{ $event->headline }}
                                        </h3>

                                        @if ($event->summary)
                                            <p
                                                class="mt-2 text-sm leading-7 text-slate-400"
                                            >
                                                {{ $event->summary }}
                                            </p>
                                        @endif

                                        @if (! empty($event->metrics))
                                            <div
                                                class="mt-5 flex flex-wrap gap-2"
                                            >
                                                @foreach (
                                                    $event->metrics
                                                    as $key => $value
                                                )
                                                    @if ($value !== null)
                                                        <div
                                                            class="rounded-xl border border-slate-800 bg-slate-950 px-3 py-2"
                                                        >
                                                            <p
                                                                class="text-[10px] font-semibold uppercase tracking-wide text-slate-600"
                                                            >
                                                                {{ str($key)
                                                                    ->replace('_', ' ')
                                                                    ->title() }}
                                                            </p>

                                                            <p
                                                                class="mt-1 text-xs font-semibold text-slate-300"
                                                            >
                                                                @if (is_float($value))
                                                                    {{ number_format(
                                                                        $value,
                                                                        2
                                                                    ) }}
                                                                @elseif (is_bool($value))
                                                                    {{ $value
                                                                        ? 'Yes'
                                                                        : 'No' }}
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (
                                            ! empty($event->before)
                                            || ! empty($event->after)
                                        )
                                            <div
                                                class="mt-5 grid gap-3 sm:grid-cols-2"
                                            >
                                                @if (! empty($event->before))
                                                    <div
                                                        class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                                                    >
                                                        <p
                                                            class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                                                        >
                                                            Before
                                                        </p>

                                                        <div
                                                            class="mt-2 space-y-1 text-sm text-slate-400"
                                                        >
                                                            @foreach (
                                                                $event->before
                                                                as $key => $value
                                                            )
                                                                <div
                                                                    class="flex justify-between gap-4"
                                                                >
                                                                    <span>
                                                                        {{ str($key)
                                                                            ->replace('_', ' ')
                                                                            ->title() }}
                                                                    </span>

                                                                    <span
                                                                        class="font-medium text-slate-300"
                                                                    >
                                                                        {{ is_float($value)
                                                                            ? number_format($value, 2)
                                                                            : $value }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (! empty($event->after))
                                                    <div
                                                        class="rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-4"
                                                    >
                                                        <p
                                                            class="text-xs font-semibold uppercase tracking-wide text-blue-400"
                                                        >
                                                            After
                                                        </p>

                                                        <div
                                                            class="mt-2 space-y-1 text-sm text-slate-400"
                                                        >
                                                            @foreach (
                                                                $event->after
                                                                as $key => $value
                                                            )
                                                                <div
                                                                    class="flex justify-between gap-4"
                                                                >
                                                                    <span>
                                                                        {{ str($key)
                                                                            ->replace('_', ' ')
                                                                            ->title() }}
                                                                    </span>

                                                                    <span
                                                                        class="font-medium text-white"
                                                                    >
                                                                        {{ is_float($value)
                                                                            ? number_format($value, 2)
                                                                            : $value }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if (
                                        $event->route_name
                                        && Route::has(
                                            $event->route_name
                                        )
                                    )
                                        <div class="shrink-0">
                                            <a
                                                href="{{ $event->route_name === 'advisor-audit.history.show'
                                                    && $event->source_id
                                                        ? route(
                                                            $event->route_name,
                                                            $event->source_id
                                                        )
                                                        : route(
                                                            $event->route_name
                                                        ) }}"
                                                class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-blue-400 transition hover:border-blue-500/50 hover:text-blue-300"
                                            >
                                                Supporting analysis

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
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-14 text-center">
                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-800 bg-slate-950 text-slate-500"
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
                                    d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                />
                            </svg>
                        </div>

                        <p
                            class="mt-4 font-semibold text-white"
                        >
                            No timeline events yet
                        </p>

                        <p
                            class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500"
                        >
                            At least two recorded audits are required
                            before Helmio can identify and display
                            meaningful changes over time.
                        </p>

                        <a
                            href="{{ route('advisor-audit.index') }}"
                            class="mt-5 inline-flex rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Open Advisor Audit
                        </a>
                    </div>
                @endforelse
            </section>

            {{-- Pagination --}}
            @if ($events->hasPages())
                <div class="text-slate-400">
                    {{ $events->withQueryString()->links() }}
                </div>
            @endif

            {{-- Explanation --}}
            <section
                class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
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
                                d="M9 12h6m-6 4.5h6M7.5 3.75h7.629a2.25 2.25 0 0 1 1.591.659l2.871 2.871a2.25 2.25 0 0 1 .659 1.591V18a2.25 2.25 0 0 1-2.25 2.25H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z"
                            />
                        </svg>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Timeline methodology
                        </p>

                        <h3
                            class="mt-2 font-semibold text-white"
                        >
                            Helmio records material change, not every fluctuation.
                        </h3>

                        <p
                            class="mt-3 max-w-4xl text-sm leading-7 text-slate-400"
                        >
                            Portfolio Timeline compares recorded portfolio
                            and Advisor Audit states and surfaces changes
                            significant enough to warrant context. Small or
                            immaterial score movements may be omitted to keep
                            the timeline focused on meaningful events.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>