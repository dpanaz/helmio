<x-app-layout>
    <x-slot name="header">
        <div class="w-full text-left">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-950">
                        Advisor Action Center
                    </h2>

                    <p class="mt-1.5 max-w-3xl text-sm leading-6 text-slate-500">
                        Review the highest-priority portfolio findings, estimated financial impact, and recommended next steps.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('advisor-audit.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        Advisor Audit
                    </a>

                    <a
                        href="{{ route('advisor-audit.history') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    >
                        Audit History
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $summary = $actionCenter['summary'] ?? [];
        $critical = collect($actionCenter['critical'] ?? []);
        $important = collect($actionCenter['important'] ?? []);
        $opportunities = collect($actionCenter['opportunities'] ?? []);
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">
                        Active findings
                    </p>

                    <p class="mt-2 text-3xl font-bold text-slate-950">
                        {{ number_format(
                            (int) (
                                $summary['total_count']
                                ?? 0
                            )
                        ) }}
                    </p>
                </div>

                <div class="rounded-xl bg-red-50 p-5 ring-1 ring-red-100">
                    <p class="text-sm text-red-700">
                        Critical
                    </p>

                    <p class="mt-2 text-3xl font-bold text-red-950">
                        {{ number_format(
                            (int) (
                                $summary['critical_count']
                                ?? 0
                            )
                        ) }}
                    </p>
                </div>

                <div class="rounded-xl bg-amber-50 p-5 ring-1 ring-amber-100">
                    <p class="text-sm text-amber-700">
                        Important
                    </p>

                    <p class="mt-2 text-3xl font-bold text-amber-950">
                        {{ number_format(
                            (int) (
                                $summary['important_count']
                                ?? 0
                            )
                        ) }}
                    </p>
                </div>

                <div class="rounded-xl bg-emerald-50 p-5 ring-1 ring-emerald-100">
                    <p class="text-sm text-emerald-700">
                        Opportunities
                    </p>

                    <p class="mt-2 text-3xl font-bold text-emerald-950">
                        {{ number_format(
                            (int) (
                                $summary['opportunity_count']
                                ?? 0
                            )
                        ) }}
                    </p>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm text-slate-500">
                        Estimated impact
                    </p>

                    <p class="mt-2 text-3xl font-bold text-slate-950">
                        {{ money(
                            $summary[
                                'estimated_financial_impact'
                            ] ?? 0,
                            0
                        ) }}
                    </p>
                </div>
            </div>

            @if (
                $critical->isEmpty()
                && $important->isEmpty()
                && $opportunities->isEmpty()
            )
                <div class="rounded-xl bg-white p-10 text-center shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-950">
                        No active findings
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Run an Advisor Audit to generate prioritized findings and recommended actions.
                    </p>

                    <a
                        href="{{ route('advisor-audit.index') }}"
                        class="mt-5 inline-flex rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
                    >
                        Run Advisor Audit
                    </a>
                </div>
            @else
                @if ($critical->isNotEmpty())
                    <section class="rounded-xl border border-red-200 bg-red-50 p-6">
                        <div>
                            <h3 class="text-lg font-semibold text-red-950">
                                Critical Actions
                            </h3>

                            <p class="mt-1 text-sm text-red-700">
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
                    <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">
                                Important Findings
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Review these items with your advisor or portfolio manager.
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
                                <div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
                                    No important findings are currently active.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">
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
                                <div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
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