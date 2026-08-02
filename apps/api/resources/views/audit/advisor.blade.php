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
            <a
                href="{{ route('advisor-audit.report') }}"
                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
                View report
            </a>

            <a
                href="{{ route('advisor-audit.report.pdf') }}"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
            >
                Download PDF
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
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
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
                Persistent audit findings
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Track when issues appear, change status or become resolved.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                {{ $openFindingCount }} open
            </span>

            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                {{ $reviewedFindingCount }} reviewed
            </span>

            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                {{ $resolvedFindingCount }} resolved
            </span>
        </div>
    </div>

    <div class="divide-y divide-slate-200">
        @forelse ($persistentFindings as $finding)
            @php
                $severityClasses = match ($finding->severity) {
                    'critical' =>
                        'bg-red-100 text-red-800',

                    'high' =>
                        'bg-orange-100 text-orange-800',

                    'medium' =>
                        'bg-amber-100 text-amber-800',

                    'low' =>
                        'bg-blue-100 text-blue-800',

                    'positive' =>
                        'bg-emerald-100 text-emerald-800',

                    default =>
                        'bg-slate-100 text-slate-700',
                };

                $statusClasses = match ($finding->status) {
                    'open' =>
                        'bg-red-50 text-red-700',

                    'reviewed' =>
                        'bg-blue-50 text-blue-700',

                    'dismissed' =>
                        'bg-slate-100 text-slate-600',

                    'resolved' =>
                        'bg-emerald-50 text-emerald-700',

                    default =>
                        'bg-slate-100 text-slate-600',
                };
            @endphp

            <article class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $severityClasses }}">
                                {{ str($finding->severity)->title() }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                {{ str($finding->status)->title() }}
                            </span>

                            @if (
                                $finding->first_detected_at
                                && $finding->first_detected_at->isToday()
                            )
                                <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800">
                                    New
                                </span>
                            @endif
                        </div>

                        <h4 class="mt-4 font-semibold text-slate-900">
                            {{ $finding->title }}
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $finding->description }}
                        </p>

                        @if ($finding->recommendation)
                            <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Suggested review
                                </p>

                                <p class="mt-2 text-sm leading-6 text-slate-700">
                                    {{ $finding->recommendation }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-slate-400">
                            <span>
                                First detected:
                                {{ $finding->first_detected_at?->format(
                                    'M j, Y'
                                ) }}
                            </span>

                            <span>
                                Last detected:
                                {{ $finding->last_detected_at?->format(
                                    'M j, Y g:i A'
                                ) }}
                            </span>
                        </div>
                    </div>

                    <div class="w-full max-w-sm">
                        @if (
                            $finding->status !== 'resolved'
                        )
                            <form
                                method="POST"
                                action="{{ route(
                                    'audit-findings.update',
                                    $finding
                                ) }}"
                                class="space-y-3 rounded-2xl border border-slate-200 p-4"
                            >
                                @csrf
                                @method('PATCH')

                                <label class="block text-sm font-medium text-slate-700">
                                    Finding status
                                </label>

                                <select
                                    name="status"
                                    class="block w-full rounded-xl border-slate-300"
                                >
                                    <option
                                        value="open"
                                        @selected(
                                            $finding->status === 'open'
                                        )
                                    >
                                        Open
                                    </option>

                                    <option
                                        value="reviewed"
                                        @selected(
                                            $finding->status === 'reviewed'
                                        )
                                    >
                                        Reviewed
                                    </option>

                                    <option
                                        value="dismissed"
                                        @selected(
                                            $finding->status === 'dismissed'
                                        )
                                    >
                                        Dismissed
                                    </option>
                                </select>

                                <textarea
                                    name="review_notes"
                                    rows="3"
                                    placeholder="Optional review notes"
                                    class="block w-full rounded-xl border-slate-300 text-sm"
                                >{{ old(
                                    'review_notes',
                                    $finding->review_notes
                                ) }}</textarea>

                                <button
                                    class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-700"
                                >
                                    Save status
                                </button>
                            </form>
                        @else
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="font-semibold text-emerald-900">
                                    Issue resolved
                                </p>

                                <p class="mt-2 text-sm text-emerald-700">
                                    No longer detected as of
                                    {{ $finding->resolved_at?->format(
                                        'M j, Y'
                                    ) }}.
                                </p>
                            </div>
                        @endif

                        @if ($finding->route_name)
                            <a
                                href="{{ route(
                                    $finding->route_name
                                ) }}"
                                class="mt-4 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                            >
                                Open supporting analysis →
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="p-12 text-center">
                <p class="font-semibold text-slate-900">
                    No persistent findings yet
                </p>

                <p class="mt-2 text-sm text-slate-500">
                    Findings will appear after the Advisor Audit runs.
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
