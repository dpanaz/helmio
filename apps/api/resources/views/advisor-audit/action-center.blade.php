<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Prioritized oversight
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Advisor Action Center
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Review the highest-priority portfolio findings,
                    estimated financial impact, and recommended next steps.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('advisor-audit.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                >
                    Advisor Audit
                </a>

                <a
                    href="{{ route('advisor-audit.history') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                >
                    Audit History
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $summary =
            $actionCenter['summary']
            ?? [];

        $critical =
            collect(
                $actionCenter['critical']
                ?? []
            );

        $important =
            collect(
                $actionCenter['important']
                ?? []
            );

        $opportunities =
            collect(
                $actionCenter['opportunities']
                ?? []
            );
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            {{-- Summary --}}
            <section
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
            >
                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Active findings
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold text-white"
                    >
                        {{ number_format(
                            (int) (
                                $summary['total_count']
                                ?? 0
                            )
                        ) }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-red-500/20 bg-red-500/[0.06] p-5"
                >
                    <p class="text-sm text-red-300">
                        Critical
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold text-white"
                    >
                        {{ number_format(
                            (int) (
                                $summary['critical_count']
                                ?? 0
                            )
                        ) }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] p-5"
                >
                    <p class="text-sm text-amber-300">
                        Important
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold text-white"
                    >
                        {{ number_format(
                            (int) (
                                $summary['important_count']
                                ?? 0
                            )
                        ) }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.06] p-5"
                >
                    <p class="text-sm text-emerald-300">
                        Opportunities
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold text-white"
                    >
                        {{ number_format(
                            (int) (
                                $summary['opportunity_count']
                                ?? 0
                            )
                        ) }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Estimated impact
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        {{ money(
                            $summary[
                                'estimated_financial_impact'
                            ] ?? 0,
                            0
                        ) }}
                    </p>
                </article>
            </section>

            @if (
                $critical->isEmpty()
                && $important->isEmpty()
                && $opportunities->isEmpty()
            )
                <section
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-10 text-center shadow-xl"
                >
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300"
                    >
                        <svg
                            class="h-6 w-6"
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

                    <h3
                        class="mt-4 text-lg font-semibold text-white"
                    >
                        No active findings
                    </h3>

                    <p
                        class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500"
                    >
                        Run an Advisor Audit to generate prioritized
                        findings and recommended actions.
                    </p>

                    <a
                        href="{{ route('advisor-audit.index') }}"
                        class="mt-5 inline-flex rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500"
                    >
                        Run Advisor Audit
                    </a>
                </section>
            @else
                @if ($critical->isNotEmpty())
                    <section
                        class="rounded-3xl border border-red-500/20 bg-red-500/[0.04] p-6"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-red-400"
                            >
                                Immediate review
                            </p>

                            <h3
                                class="mt-2 text-lg font-semibold text-white"
                            >
                                Critical Actions
                            </h3>

                            <p class="mt-1 text-sm text-slate-400">
                                These items should be reviewed promptly.
                            </p>
                        </div>

                        <div class="mt-5 space-y-4">
                            @foreach ($critical as $finding)
                                @include(
                                    'advisor-audit.partials.action-finding',
                                    [
                                        'finding' => $finding,
                                        'tone' => 'critical',
                                    ]
                                )
                            @endforeach
                        </div>
                    </section>
                @endif

                <div class="grid gap-6 xl:grid-cols-2">
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-400"
                            >
                                Review recommended
                            </p>

                            <h3
                                class="mt-2 text-lg font-semibold text-white"
                            >
                                Important Findings
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Review these items with your advisor or
                                portfolio manager.
                            </p>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($important as $finding)
                                @include(
                                    'advisor-audit.partials.action-finding',
                                    [
                                        'finding' => $finding,
                                        'tone' => 'important',
                                    ]
                                )
                            @empty
                                <div
                                    class="rounded-xl border border-slate-800 bg-slate-950 p-4 text-sm text-slate-500"
                                >
                                    No important findings are currently active.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-400"
                            >
                                Potential improvements
                            </p>

                            <h3
                                class="mt-2 text-lg font-semibold text-white"
                            >
                                Opportunities
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Potential improvements, savings, or portfolio advantages.
                            </p>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($opportunities as $finding)
                                @include(
                                    'advisor-audit.partials.action-finding',
                                    [
                                        'finding' => $finding,
                                        'tone' => 'opportunity',
                                    ]
                                )
                            @empty
                                <div
                                    class="rounded-xl border border-slate-800 bg-slate-950 p-4 text-sm text-slate-500"
                                >
                                    No active opportunities were identified.
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>