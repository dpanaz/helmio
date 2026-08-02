<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Investor report
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Advisor Audit Report
                </h2>
            </div>

            <div class="flex flex-wrap gap-3 print:hidden">
                <button
                    type="button"
                    onclick="window.print()"
                    class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Print report
                </button>

                <a
                    href="{{ route('advisor-audit.report.pdf') }}"
                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
                >
                    Download PDF
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $categoryLabels = [
            'cost' => 'Cost',
            'diversification' => 'Diversification',
            'performance' => 'Performance',
            'risk' => 'Risk',
            'trading' => 'Trading',
            'tax' => 'Tax',
        ];
    @endphp

    <div class="py-10 print:py-0">
        <div class="mx-auto max-w-5xl space-y-8 px-4 sm:px-6 lg:px-8 print:max-w-none print:px-0">
            <section class="rounded-3xl bg-slate-950 p-8 text-white print:rounded-none">
                <div class="flex flex-wrap items-start justify-between gap-8">
                    <div>
                        <p class="text-sm font-medium text-blue-300">
                            Advisor Audit Grade
                        </p>

                        <div class="mt-4 flex items-end gap-5">
                            <span class="text-7xl font-semibold">
                                {{ $audit['audit_grade'] }}
                            </span>

                            <div class="pb-2">
                                <p class="text-xl font-semibold">
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
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <p class="text-sm text-slate-400">
                            Report generated
                        </p>

                        <p class="mt-2 font-semibold">
                            {{ $generatedAt->format('F j, Y') }}
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            {{ $generatedAt->format('g:i A') }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    [
                        'Portfolio value',
                        '$'.number_format(
                            $audit['portfolio_value'],
                            2
                        ),
                    ],
                    [
                        'Estimated annual cost',
                        '$'.number_format(
                            $audit['annual_cost'],
                            2
                        ),
                    ],
                    [
                        'Potential savings',
                        '$'.number_format(
                            $audit['potential_savings'],
                            2
                        ),
                    ],
                    [
                        'Review items',
                        $audit['issue_count'],
                    ],
                ] as [$label, $value])
                    <article class="rounded-2xl border border-slate-200 bg-white p-6">
                        <p class="text-sm text-slate-500">
                            {{ $label }}
                        </p>

                        <p class="mt-3 text-2xl font-semibold text-slate-900">
                            {{ $value }}
                        </p>
                    </article>
                @endforeach
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-7">
                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Prepared for
                    </p>

                    <h3 class="mt-2 text-xl font-semibold text-slate-900">
                        {{ $user->name }}
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $user->email }}
                    </p>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    @foreach ($accounts as $account)
                        <article class="rounded-2xl bg-slate-50 p-5">
                            <p class="font-semibold text-slate-900">
                                {{ $account->name }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $account->institution?->name
                                    ?? 'Manual account' }}
                            </p>

                            <p class="mt-3 text-lg font-semibold text-slate-900">
                                ${{ number_format(
                                    $account->current_value,
                                    2
                                ) }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-7">
                <h3 class="text-lg font-semibold text-slate-900">
                    Category scores
                </h3>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($audit['category_scores'] as $key => $category)
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm text-slate-500">
                                        {{ $categoryLabels[$key]
                                            ?? str($key)->title() }}
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-900">
                                        {{ $category['label'] }}
                                    </p>
                                </div>

                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-lg font-semibold text-slate-900">
                                    {{ $category['score'] ?? '—' }}
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-7 py-6">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Audit findings
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Findings are listed in order of review priority.
                    </p>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse ($findings as $finding)
                        <article class="p-7">
                            <div class="flex flex-wrap items-start justify-between gap-5">
                                <div class="max-w-3xl">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ str($finding->severity)->title() }}
                                        </span>

                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                            {{ str($finding->status)->title() }}
                                        </span>
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

                                    @if ($finding->review_notes)
                                        <div class="mt-4">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                Review notes
                                            </p>

                                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                                {{ $finding->review_notes }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right">
                                    @if ($finding->score !== null)
                                        <p class="text-2xl font-semibold text-slate-900">
                                            {{ $finding->score }}
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            Category score
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-500">
                            No findings are available.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-7">
                <h3 class="text-lg font-semibold text-slate-900">
                    Methodology and limitations
                </h3>

                <p class="mt-4 text-sm leading-7 text-slate-600">
                    Helmio calculates category scores using deterministic,
                    versioned formulas applied to the portfolio data entered or
                    imported into the application. The report identifies data
                    patterns that may deserve review.
                </p>

                <p class="mt-4 text-sm leading-7 text-slate-600">
                    This report does not determine whether advice was suitable,
                    authorized, negligent, conflicted or legally improper. It
                    does not provide investment, legal, accounting or tax
                    advice. A complete review may require statements, advisory
                    contracts, tax records, investor objectives and qualified
                    professional analysis.
                </p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Advisor Audit version
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $audit['formula_version'] }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Helm Score version
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            {{ $audit['helm_score']['formula_version'] }}
                        </p>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap justify-between gap-4 print:hidden">
                <a
                    href="{{ route('advisor-audit.index') }}"
                    class="text-sm font-semibold text-blue-600 hover:text-blue-500"
                >
                    ← Back to Advisor Audit
                </a>

                <a
                    href="{{ route('advisor-audit.report.pdf') }}"
                    class="text-sm font-semibold text-blue-600 hover:text-blue-500"
                >
                    Download PDF →
                </a>
            </div>
        </div>
    </div>
</x-app-layout>