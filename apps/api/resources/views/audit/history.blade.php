<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Change tracking
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Advisor Audit History
                </h2>
            </div>

            <a
                href="{{ route('advisor-audit.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                Current audit
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if ($currentRun === null)
                <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <h3 class="text-lg font-semibold text-slate-900">
                        No audit history yet
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Open Advisor Audit to create the first recorded run.
                    </p>
                </section>
            @else
                <section class="grid gap-6 xl:grid-cols-3">
                    <article class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl xl:col-span-2">
                        <p class="text-sm font-medium text-blue-300">
                            Latest Advisor Audit
                        </p>

                        <div class="mt-4 flex flex-wrap items-end gap-5">
                            <span class="text-7xl font-semibold">
                                {{ $currentRun->audit_grade }}
                            </span>

                            <div class="pb-2">
                                <p class="text-xl font-semibold">
                                    {{ $currentRun->audit_score ?? '—' }}
                                    @if ($currentRun->audit_score !== null)
                                        / 100
                                    @endif
                                </p>

                                <p class="mt-1 text-sm text-slate-400">
                                    {{ $currentRun->audit_label }}
                                </p>
                            </div>
                        </div>

                        @if ($comparison['has_previous'])
                            <p class="mt-5 text-sm text-slate-300">
                                Score change since the prior audit:

                                <span
                                    @class([
                                        'font-semibold',
                                        'text-emerald-400' =>
                                            $comparison['score_change'] > 0,
                                        'text-red-400' =>
                                            $comparison['score_change'] < 0,
                                        'text-slate-300' =>
                                            $comparison['score_change'] === 0,
                                    ])
                                >
                                    {{ $comparison['score_change'] > 0 ? '+' : '' }}
                                    {{ $comparison['score_change'] }}
                                </span>
                            </p>
                        @else
                            <p class="mt-5 text-sm text-slate-400">
                                This is the first recorded audit.
                            </p>
                        @endif
                    </article>

                    <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">
                            Latest run
                        </p>

                        <p class="mt-3 text-xl font-semibold text-slate-900">
                            {{ $currentRun->calculated_for_date->format(
                                'F j, Y'
                            ) }}
                        </p>

                        <div class="mt-6 space-y-4 border-t border-slate-200 pt-6">
                            <div class="flex justify-between gap-4">
                                <span class="text-sm text-slate-500">
                                    Review items
                                </span>

                                <span class="font-semibold text-slate-900">
                                    {{ $currentRun->issue_count }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-4">
                                <span class="text-sm text-slate-500">
                                    Annual cost
                                </span>

                                <span class="font-semibold text-slate-900">
                                    ${{ number_format(
                                        $currentRun->annual_cost,
                                        2
                                    ) }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-4">
                                <span class="text-sm text-slate-500">
                                    Potential savings
                                </span>

                                <span class="font-semibold text-emerald-700">
                                    ${{ number_format(
                                        $currentRun->potential_savings,
                                        2
                                    ) }}
                                </span>
                            </div>
                        </div>
                    </article>
                </section>

                @if ($comparison['has_previous'])
                    <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            [
                                'New findings',
                                $comparison['new_findings']->count(),
                                'text-violet-700',
                            ],
                            [
                                'Improved',
                                $comparison['improved_findings']->count(),
                                'text-emerald-700',
                            ],
                            [
                                'Worsened',
                                $comparison['worsened_findings']->count(),
                                'text-red-700',
                            ],
                            [
                                'Resolved',
                                $comparison['resolved_findings']->count(),
                                'text-blue-700',
                            ],
                        ] as [$label, $value, $class])
                            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <p class="text-sm font-medium text-slate-500">
                                    {{ $label }}
                                </p>

                                <p class="mt-3 text-3xl font-semibold {{ $class }}">
                                    {{ $value }}
                                </p>
                            </article>
                        @endforeach
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Category score changes
                        </h3>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($comparison['category_changes'] as $category)
                                <article class="rounded-2xl border border-slate-200 p-5">
                                    <p class="text-sm font-medium text-slate-500">
                                        {{ str($category['category'])
                                            ->replace('_', ' ')
                                            ->title() }}
                                    </p>

                                    <div class="mt-3 flex items-end justify-between gap-4">
                                        <div>
                                            <p class="text-2xl font-semibold text-slate-900">
                                                {{ $category['current_score'] ?? '—' }}
                                            </p>

                                            <p class="text-xs text-slate-400">
                                                Previous:
                                                {{ $category['previous_score'] ?? '—' }}
                                            </p>
                                        </div>

                                        @if ($category['change'] !== null)
                                            <p
                                                @class([
                                                    'font-semibold',
                                                    'text-emerald-700' =>
                                                        $category['change'] > 0,
                                                    'text-red-700' =>
                                                        $category['change'] < 0,
                                                    'text-slate-500' =>
                                                        $category['change'] === 0,
                                                ])
                                            >
                                                {{ $category['change'] > 0 ? '+' : '' }}
                                                {{ $category['change'] }}
                                            </p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Audit timeline
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $runs->count() }}
                            recorded audit{{ $runs->count() === 1 ? '' : 's' }}
                        </p>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach ($runs as $run)
                            <article class="flex flex-wrap items-center justify-between gap-6 px-6 py-5">
                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ $run->calculated_for_date->format(
                                            'F j, Y'
                                        ) }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $run->issue_count }}
                                        review items ·
                                        ${{ number_format(
                                            $run->annual_cost,
                                            2
                                        ) }}
                                        annual cost
                                    </p>
                                </div>

                                <div class="flex items-center gap-6">
                                    <div class="text-right">
                                        <p class="text-2xl font-semibold text-slate-900">
                                            {{ $run->audit_grade }}
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            {{ $run->audit_score ?? '—' }}
                                            / 100
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route(
                                            'advisor-audit.history.show',
                                            $run
                                        ) }}"
                                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        View
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
