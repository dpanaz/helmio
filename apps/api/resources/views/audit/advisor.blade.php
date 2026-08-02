<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Independent portfolio review
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Advisor Audit
                </h2>
            </div>

            <a
                href="{{ route('analytics.helm-score') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                View Helm Score
            </a>
        </div>
    </x-slot>

    @php
        $severityStyles = [
            'critical' => [
                'badge' => 'bg-red-100 text-red-800',
                'dot' => 'bg-red-600',
                'border' => 'border-red-200',
                'label' => 'Critical',
            ],
            'high' => [
                'badge' => 'bg-orange-100 text-orange-800',
                'dot' => 'bg-orange-500',
                'border' => 'border-orange-200',
                'label' => 'High',
            ],
            'medium' => [
                'badge' => 'bg-amber-100 text-amber-800',
                'dot' => 'bg-amber-500',
                'border' => 'border-amber-200',
                'label' => 'Medium',
            ],
            'low' => [
                'badge' => 'bg-blue-100 text-blue-800',
                'dot' => 'bg-blue-500',
                'border' => 'border-blue-200',
                'label' => 'Low',
            ],
            'information' => [
                'badge' => 'bg-slate-100 text-slate-700',
                'dot' => 'bg-slate-400',
                'border' => 'border-slate-200',
                'label' => 'Information',
            ],
            'positive' => [
                'badge' => 'bg-emerald-100 text-emerald-800',
                'dot' => 'bg-emerald-500',
                'border' => 'border-emerald-200',
                'label' => 'Healthy',
            ],
        ];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-6 xl:grid-cols-3">
                <article class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl xl:col-span-2">
                    <div class="flex flex-wrap items-start justify-between gap-8">
                        <div>
                            <p class="text-sm font-medium text-blue-300">
                                Advisor Audit Grade
                            </p>

                            <div class="mt-4 flex items-end gap-5">
                                <span class="text-7xl font-semibold tracking-tight">
                                    {{ $audit['audit_grade'] }}
                                </span>

                                <div class="pb-2">
                                    <p class="text-xl font-semibold text-white">
                                        {{ $audit['audit_score'] ?? '—' }}
                                        @if ($audit['audit_score'] !== null)
                                            / 100
                                        @endif
                                    </p>

                                    <p class="mt-1 text-sm text-slate-400">
                                        {{ $audit['audit_label'] }}
                                    </p>
                                </div>
                            </div>

                            <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-400">
                                This grade summarizes the portfolio’s cost,
                                diversification, performance, risk, trading and
                                tax analytics. It is a review tool, not a legal
                                determination about an adviser or investment.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <p class="text-sm text-slate-400">
                                Review status
                            </p>

                            <p class="mt-2 text-xl font-semibold">
                                {{ $audit['review_recommended']
                                    ? 'Review recommended'
                                    : 'No priority review' }}
                            </p>

                            <p class="mt-3 text-sm text-slate-400">
                                {{ $audit['issue_count'] }}
                                item{{ $audit['issue_count'] === 1 ? '' : 's' }}
                                requiring review
                            </p>
                        </div>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Portfolio reviewed
                    </p>

                    <p class="mt-3 text-4xl font-semibold text-slate-900">
                        ${{ number_format(
                            $audit['portfolio_value'],
                            2
                        ) }}
                    </p>

                    <div class="mt-6 space-y-4 border-t border-slate-200 pt-6">
                        <div class="flex justify-between gap-4">
                            <span class="text-sm text-slate-500">
                                Estimated annual cost
                            </span>

                            <span class="font-semibold text-slate-900">
                                ${{ number_format(
                                    $audit['annual_cost'],
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
                                    $audit['potential_savings'],
                                    2
                                ) }}
                            </span>
                        </div>
                    </div>
                </article>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    [
                        'Priority findings',
                        $audit['critical_count'],
                        'text-red-700',
                    ],
                    [
                        'High findings',
                        $audit['high_count'],
                        'text-orange-700',
                    ],
                    [
                        'Medium findings',
                        $audit['medium_count'],
                        'text-amber-700',
                    ],
                    [
                        'Positive findings',
                        $audit['positive_count'],
                        'text-emerald-700',
                    ],
                    [
                        'Total review items',
                        $audit['issue_count'],
                        'text-slate-900',
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

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">
                            Audit findings
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Findings are ordered by review priority.
                        </p>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                        {{ $audit['findings']->count() }} findings
                    </span>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse ($audit['findings'] as $finding)
                        @php
                            $style = $severityStyles[
                                $finding['severity']
                            ] ?? $severityStyles['information'];
                        @endphp

                        <article class="p-6">
                            <div class="flex flex-wrap items-start justify-between gap-5">
                                <div class="flex max-w-4xl gap-4">
                                    <span class="mt-2 h-3 w-3 shrink-0 rounded-full {{ $style['dot'] }}"></span>

                                    <div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h4 class="font-semibold text-slate-900">
                                                {{ $finding['title'] }}
                                            </h4>

                                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $style['badge'] }}">
                                                {{ $style['label'] }}
                                            </span>
                                        </div>

                                        <p class="mt-3 text-sm leading-6 text-slate-600">
                                            {{ $finding['description'] }}
                                        </p>

                                        @if ($finding['recommendation'])
                                            <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                    Suggested review
                                                </p>

                                                <p class="mt-2 text-sm leading-6 text-slate-700">
                                                    {{ $finding['recommendation'] }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right">
                                    @if ($finding['score'] !== null)
                                        <p class="text-2xl font-semibold text-slate-900">
                                            {{ $finding['score'] }}
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            Category score
                                        </p>
                                    @endif

                                    @if ($finding['route'])
                                        <a
                                            href="{{ route($finding['route']) }}"
                                            class="mt-4 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                                        >
                                            Open analysis →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center">
                            <p class="font-semibold text-slate-900">
                                No findings available
                            </p>

                            <p class="mt-2 text-sm text-slate-500">
                                Add portfolio data to begin the audit.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <p class="text-sm font-medium text-blue-300">
                    Important limitation
                </p>

                <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-300">
                    Advisor Audit summarizes portfolio data and algorithmic
                    indicators. It does not determine whether advice was
                    suitable, authorized, negligent, conflicted or legally
                    improper. Complete review may require account statements,
                    advisory agreements, tax records, investment objectives and
                    professional analysis.
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Formula version:
                    {{ $audit['formula_version'] }}
                    · Calculated for
                    {{ $audit['calculated_for_date'] }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
