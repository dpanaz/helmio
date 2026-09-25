<x-app-layout>
    @php
        $activeFindings = $run->findings->filter(fn ($finding) => $finding->status !== 'resolved');
        $priorityFindings = $activeFindings->filter(fn ($finding) => in_array($finding->severity, ['critical', 'high', 'medium', 'moderate'], true));
        $otherFindings = $run->findings->reject(fn ($finding) => $priorityFindings->contains('id', $finding->id));
        $newCount = $comparison['new_findings']->count();
        $resolvedCount = $comparison['resolved_findings']->count();
        $scoreChange = $comparison['score_change'];
    @endphp
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-300">
                    Advisor Audit · saved report
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-white">
                    {{ $run->calculated_for_date->format('F j, Y') }}
                </h2>
            </div>

            <a
                href="{{ route('advisor-audit.history') }}"
                class="rounded-xl border border-slate-600 px-5 py-3 text-sm font-semibold text-slate-200 hover:bg-slate-800"
            >
                Back to history
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-6 xl:grid-cols-3">
                <article class="rounded-3xl border border-slate-700 bg-slate-900 p-8 text-white xl:col-span-2">
                    <p class="text-sm font-medium text-blue-300">
                        Advisor Audit Grade
                    </p>

                    <div class="mt-4 flex items-end gap-5">
                        <span class="text-7xl font-semibold">
                            {{ $run->audit_grade }}
                        </span>

                        <div class="pb-2">
                            <p class="text-xl font-semibold">
                                {{ $run->audit_score ?? '—' }}
                                @if ($run->audit_score !== null)
                                    / 100
                                @endif
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                {{ $run->audit_label }}
                            </p>
                        </div>
                    </div>
                    @if ($comparison['has_previous'] && $scoreChange !== null)
                        <p class="mt-5 text-sm {{ $scoreChange > 0 ? 'text-emerald-300' : ($scoreChange < 0 ? 'text-rose-300' : 'text-slate-300') }}">
                            {{ $scoreChange > 0 ? '+' : '' }}{{ $scoreChange }} points since {{ $previousRun->calculated_for_date->format('M j, Y') }}
                        </p>
                    @endif
                </article>

                <article class="rounded-3xl border border-slate-700 bg-slate-900 p-8">
                    <p class="text-sm text-slate-300">
                        Portfolio value at this audit
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-white">
                        ${{ number_format(
                            $run->portfolio_value,
                            2
                        ) }}
                    </p>

                    <p class="mt-5 text-sm text-slate-300">
                        {{ $run->findings->count() }} recorded findings · {{ $priorityFindings->count() }} need attention
                    </p>
                </article>
            </section>

            @if ($comparison['has_previous'])
                <div>
                    <h3 class="text-lg font-semibold text-white">Since the previous audit</h3>
                    <p class="mt-1 text-sm text-slate-400">Changes compared with {{ $previousRun->calculated_for_date->format('M j, Y') }}. These counts describe findings, not portfolio transactions.</p>
                </div>
                <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        [
                            'New',
                            $comparison['new_findings']->count(),
                        ],
                        [
                            'Improved',
                            $comparison['improved_findings']->count(),
                        ],
                        [
                            'Worsened',
                            $comparison['worsened_findings']->count(),
                        ],
                        [
                            'Resolved',
                            $comparison['resolved_findings']->count(),
                        ],
                    ] as [$label, $value])
                        <article class="rounded-2xl border border-slate-700 bg-slate-900 p-6">
                            <p class="text-sm text-slate-300">
                                {{ $label }}
                            </p>

                            <p class="mt-3 text-3xl font-semibold text-white">
                                {{ $value }}
                            </p>
                        </article>
                    @endforeach
                </section>
            @endif

            <section class="rounded-3xl border border-slate-700 bg-slate-900 p-6 sm:p-8">
                <h3 class="text-lg font-semibold text-white">What this report shows</h3>
                <p class="mt-2 text-sm leading-6 text-slate-300">This is a snapshot from {{ $run->calculated_for_date->format('M j, Y') }}. Findings below reflect what the audit recorded then; check the current Advisor Audit for today's status. A finding's number is its individual score, not a dollar amount.</p>
                @if ($comparison['has_previous'])
                    <p class="mt-3 text-sm text-slate-300">{{ $newCount }} new {{ Str::plural('finding', $newCount) }} and {{ $resolvedCount }} resolved since the previous report.</p>
                @endif
            </section>

            @foreach ([['Needs attention', $priorityFindings, 'Open findings with moderate or higher severity'], ['Other recorded findings', $otherFindings, 'Low-priority, informational, positive, and resolved findings']] as [$heading, $findings, $description])
            @if ($findings->isNotEmpty())
            <section class="overflow-hidden rounded-3xl border border-slate-700 bg-slate-900">
                <div class="border-b border-slate-700 px-6 py-5">
                    <h3 class="text-lg font-semibold text-white">{{ $heading }} <span class="ml-2 text-sm font-normal text-slate-400">{{ $findings->count() }}</span></h3>
                    <p class="mt-1 text-sm text-slate-400">{{ $description }}</p>
                </div>

                <div class="divide-y divide-slate-700">
                    @foreach ($findings->sortBy(fn ($finding) => array_search($finding->severity, ['critical', 'high', 'medium', 'moderate', 'low', 'information', 'informational', 'positive']) ?: 99) as $finding)
                        <article class="p-6">
                            <div class="flex flex-wrap items-start justify-between gap-5">
                                <div class="max-w-4xl">
                                    <div class="flex gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ in_array($finding->severity, ['critical', 'high'], true) ? 'bg-rose-950 text-rose-200' : 'bg-slate-700 text-slate-200' }}">
                                            {{ str($finding->severity)->title() }}
                                        </span>

                                        <span class="rounded-full bg-blue-950 px-3 py-1 text-xs font-semibold text-blue-200">
                                            {{ str($finding->status)->title() }}
                                        </span>
                                    </div>

                                    <h4 class="mt-4 font-semibold text-white">
                                        {{ $finding->title }}
                                    </h4>

                                    <p class="mt-2 text-sm leading-6 text-slate-300">
                                        {{ $finding->description }}
                                    </p>

                                    @if ($finding->recommendation)
                                        <div class="mt-4 rounded-2xl bg-slate-800 p-4">
                                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-blue-300">Suggested next step</p>
                                            <p class="text-sm leading-6 text-slate-200">
                                                {{ $finding->recommendation }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                @if ($finding->score !== null)
                                    <p class="text-sm font-semibold text-slate-300">
                                        Finding score: {{ $finding->score }}
                                    </p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
            @endif
            @endforeach
            @if ($run->findings->isEmpty())
                <p class="rounded-3xl border border-slate-700 bg-slate-900 p-8 text-slate-300">No findings were stored for this audit.</p>
            @endif
        </div>
    </div>
</x-app-layout>
