<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    Advisor Audit
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Review advisor performance, costs, risk, trading behavior, cash management, diversification, and tax efficiency.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('advisor-audit.report') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    View Report
                </a>

                <a
                    href="{{ route('advisor-audit.report.pdf') }}"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Download PDF
                </a>

                <a
                    href="{{ route('advisor-audit.history') }}"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                >
                    Audit History
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form
                    id="advisor-audit-form"
                    class="grid gap-4 md:grid-cols-4"
                >
                    @csrf
                    <div>
                        <label
                            for="start_date"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Start date
                        </label>

                        <input
                            id="start_date"
                            name="start_date"
                            type="date"
                            value="{{ now()->subYear()->format('Y-m-d') }}"
                            class="mt-1 block w-full rounded-lg border-gray-300"
                            required
                        >
                    </div>

                    <div>
                        <label
                            for="end_date"
                            class="block text-sm font-medium text-gray-700"
                        >
                            End date
                        </label>

                        <input
                            id="end_date"
                            name="end_date"
                            type="date"
                            value="{{ now()->format('Y-m-d') }}"
                            class="mt-1 block w-full rounded-lg border-gray-300"
                            required
                        >
                    </div>

                    <div>
                        <label
                            for="benchmark_id"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Benchmark
                        </label>

                        <select
                            id="benchmark_id"
                            name="benchmark_id"
                            class="mt-1 block w-full rounded-lg border-gray-300"
                        >
                            @foreach ($benchmarks as $benchmark)
                                <option
                                    value="{{ $benchmark->id }}"
                                    @selected($benchmark->symbol === 'SPY')
                                >
                                    {{ $benchmark->name }}
                                    ({{ $benchmark->symbol }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            Run Advisor Audit
                        </button>
                    </div>
                </form>
            </div>

            <div
                id="loading-state"
                class="hidden rounded-xl bg-white p-10 text-center shadow-sm ring-1 ring-gray-200"
            >
                <p class="text-sm font-medium text-gray-700">
                    Running Advisor Audit…
                </p>

                <p class="mt-1 text-sm text-gray-500">
                    Reviewing all available analytics categories.
                </p>
            </div>

            <div
                id="error-state"
                class="hidden rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700"
            ></div>

            <div
                id="insufficient-state"
                class="hidden rounded-xl border border-amber-200 bg-amber-50 p-6"
            >
                <h3 class="font-semibold text-amber-900">
                    More account data is needed
                </h3>

                <p
                    id="insufficient-message"
                    class="mt-2 text-sm text-amber-800"
                ></p>
            </div>

            <div
                id="results"
                class="hidden space-y-6"
            >
                <div class="grid gap-6 lg:grid-cols-[1.1fr_1.9fr]">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Advisor Audit Score
                        </p>

                        <div class="mt-3 flex items-end gap-3">
                            <span
                                id="overall-score"
                                class="text-6xl font-bold tracking-tight text-gray-900"
                            >
                                —
                            </span>

                            <span class="pb-2 text-lg font-medium text-gray-400">
                                / 100
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <span
                                id="overall-label"
                                class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                            >
                                —
                            </span>

                            <span
                                id="advisor-rating"
                                class="text-sm font-medium text-gray-600"
                            >
                                —
                            </span>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">
                                    Data completeness
                                </span>

                                <span
                                    id="data-completeness"
                                    class="font-semibold text-gray-900"
                                >
                                    —
                                </span>
                            </div>

                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                                <div
                                    id="data-completeness-bar"
                                    class="h-full rounded-full bg-gray-900"
                                    style="width: 0%"
                                ></div>
                            </div>
                        </div>

                        <dl class="mt-6 space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">
                                    Analysis period
                                </dt>

                                <dd
                                    id="analysis-period"
                                    class="text-right font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">
                                    Benchmark
                                </dt>

                                <dd
                                    id="benchmark-name"
                                    class="text-right font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-gray-500">
                                    Accounts reviewed
                                </dt>

                                <dd
                                    id="account-count"
                                    class="text-right font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm font-medium text-gray-500">
                            Executive Summary
                        </p>

                        <h3
                            id="executive-headline"
                            class="mt-2 text-2xl font-semibold text-gray-900"
                        >
                            —
                        </h3>

                        <p
                            id="executive-summary"
                            class="mt-4 text-sm leading-6 text-gray-600"
                        >
                            —
                        </p>

                        <div class="mt-6 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-lg bg-red-50 p-4">
                                <p class="text-sm text-red-700">
                                    Critical concerns
                                </p>

                                <p
                                    id="critical-count"
                                    class="mt-1 text-2xl font-semibold text-red-900"
                                >
                                    —
                                </p>
                            </div>

                            <div class="rounded-lg bg-amber-50 p-4">
                                <p class="text-sm text-amber-700">
                                    Important findings
                                </p>

                                <p
                                    id="important-count"
                                    class="mt-1 text-2xl font-semibold text-amber-900"
                                >
                                    —
                                </p>
                            </div>

                            <div class="rounded-lg bg-emerald-50 p-4">
                                <p class="text-sm text-emerald-700">
                                    Opportunities
                                </p>

                                <p
                                    id="opportunity-count"
                                    class="mt-1 text-2xl font-semibold text-emerald-900"
                                >
                                    —
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Category Scores
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Each category contributes to the overall advisor assessment.
                        </p>
                    </div>

                    <div
                        id="category-grid"
                        class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                    ></div>
                </div>

                <div
                    id="critical-section"
                    class="hidden rounded-xl border border-red-200 bg-red-50 p-6"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-red-900">
                            Critical Findings
                        </h3>

                        <p class="mt-1 text-sm text-red-700">
                            These findings should be reviewed promptly.
                        </p>
                    </div>

                    <div
                        id="critical-findings"
                        class="mt-5 space-y-4"
                    ></div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Important Findings
                        </h3>

                        <div
                            id="important-findings"
                            class="mt-5 space-y-4"
                        ></div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Opportunities
                        </h3>

                        <div
                            id="opportunity-findings"
                            class="mt-5 space-y-4"
                        ></div>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Recommended Actions
                    </h3>

                    <div
                        id="recommended-actions"
                        class="mt-5 grid gap-4 lg:grid-cols-2"
                    ></div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Next Steps
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Review the full report, compare prior audits, or schedule recurring monthly reviews.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a
                                href="{{ route('advisor-audit.report') }}"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Full Report
                            </a>

                            <a
                                href="{{ route('advisor-audit.history') }}"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Compare History
                            </a>

                            <a
                                href="{{ route('advisor-audit.monthly-settings') }}"
                                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                            >
                                Monthly Audit Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form =
                document.getElementById('advisor-audit-form');

            const results =
                document.getElementById('results');

            const loadingState =
                document.getElementById('loading-state');

            const errorState =
                document.getElementById('error-state');

            const insufficientState =
                document.getElementById('insufficient-state');

            const formatPercent = (value) => {
                if (
                    value === null
                    || value === undefined
                ) {
                    return '—';
                }

                return new Intl.NumberFormat('en-US', {
                    style: 'percent',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                }).format(value);
            };

            const formatCurrency = (value) => {
                if (
                    value === null
                    || value === undefined
                ) {
                    return null;
                }

                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    maximumFractionDigits: 0,
                }).format(value);
            };

            const labelClasses = (score) => {
                if (score === null || score === undefined) {
                    return 'bg-gray-100 text-gray-700';
                }

                if (score >= 90) {
                    return 'bg-green-100 text-green-800';
                }

                if (score >= 80) {
                    return 'bg-emerald-100 text-emerald-800';
                }

                if (score >= 70) {
                    return 'bg-blue-100 text-blue-800';
                }

                if (score >= 60) {
                    return 'bg-amber-100 text-amber-800';
                }

                if (score >= 40) {
                    return 'bg-orange-100 text-orange-800';
                }

                return 'bg-red-100 text-red-800';
            };

            const advisorRatingLabel = (rating) => {
                const labels = {
                    strong: 'Strong oversight',
                    generally_sound: 'Generally sound',
                    mixed: 'Mixed results',
                    concerning: 'Concerning',
                    high_concern: 'High concern',
                };

                return labels[rating] ?? 'Not available';
            };

            const categoryLabel = (category) => {
                const labels = {
                    cost: 'Cost',
                    diversification: 'Diversification',
                    performance: 'Performance',
                    risk: 'Risk',
                    trading: 'Trading Discipline',
                    cash: 'Cash Drag',
                    tax: 'Tax Efficiency',
                };

                return labels[category] ?? category;
            };

            const categoryRoute = (category) => {
                const routes = {
                    cost: '{{ route('analytics.costs') }}',
                    diversification: '{{ route('analytics.diversification') }}',
                    performance: '{{ route('analytics.performance') }}',
                    risk: '{{ route('analytics.risk') }}',
                    trading: '{{ route('analytics.trading-discipline') }}',
                    cash: '{{ route('analytics.cash-drag') }}',
                    tax: '{{ route('analytics.tax-efficiency') }}',
                };

                return routes[category] ?? '#';
            };

            const severityClasses = (severity) => {
                const classes = {
                    critical: 'border-red-300 bg-white text-red-900',
                    high: 'border-red-200 bg-red-50 text-red-900',
                    moderate: 'border-amber-200 bg-amber-50 text-amber-900',
                    informational: 'border-blue-200 bg-blue-50 text-blue-900',
                };

                return classes[severity]
                    ?? 'border-gray-200 bg-gray-50 text-gray-900';
            };

            const renderCategories = (categories) => {
                const grid =
                    document.getElementById('category-grid');

                grid.innerHTML = '';

                Object.entries(categories ?? {})
                    .forEach(([key, category]) => {
                        const score =
                            category.score ?? null;

                        const card =
                            document.createElement('a');

                        card.href =
                            categoryRoute(key);

                        card.className =
                            'block rounded-xl border border-gray-200 bg-white p-5 transition hover:border-gray-300 hover:shadow-sm';

                        card.innerHTML = `
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        ${categoryLabel(key)}
                                    </p>

                                    <p class="mt-2 text-3xl font-bold text-gray-900">
                                        ${score ?? '—'}
                                    </p>
                                </div>

                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${labelClasses(score)}">
                                    ${category.label ?? 'Insufficient data'}
                                </span>
                            </div>

                            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-gray-900"
                                    style="width: ${score ?? 0}%"
                                ></div>
                            </div>

                            <p class="mt-4 text-xs text-gray-500">
                                ${category.available === false
                                    ? 'More data is required.'
                                    : 'Open category details'}
                            </p>
                        `;

                        grid.appendChild(card);
                    });
            };

            const renderFindings = (
                containerId,
                findings,
                emptyMessage
            ) => {
                const container =
                    document.getElementById(containerId);

                container.innerHTML = '';

                if (!findings || findings.length === 0) {
                    container.innerHTML = `
                        <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                            ${emptyMessage}
                        </div>
                    `;

                    return;
                }

                findings.forEach((finding) => {
                    const element =
                        document.createElement('div');

                    element.className =
                        `rounded-lg border p-4 ${severityClasses(finding.severity)}`;

                    const impact = formatCurrency(
                        finding.financial_impact
                    );

                    element.innerHTML = `
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide opacity-70">
                                        ${finding.category_label ?? categoryLabel(finding.category)}
                                    </span>

                                    <span class="rounded-full bg-white/70 px-2 py-0.5 text-xs font-semibold capitalize">
                                        ${finding.severity}
                                    </span>
                                </div>

                                <h4 class="mt-2 font-semibold">
                                    ${finding.title}
                                </h4>

                                <p class="mt-1 text-sm leading-6 opacity-90">
                                    ${finding.message}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-xs opacity-70">
                                    Priority
                                </p>

                                <p class="font-semibold">
                                    ${finding.priority}
                                </p>

                                ${impact
                                    ? `<p class="mt-2 text-sm font-semibold">${impact}</p>`
                                    : ''}
                            </div>
                        </div>
                    `;

                    container.appendChild(element);
                });
            };

            const renderRecommendations = (recommendations) => {
                const container =
                    document.getElementById('recommended-actions');

                container.innerHTML = '';

                if (
                    !recommendations
                    || recommendations.length === 0
                ) {
                    container.innerHTML = `
                        <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                            No specific actions were generated.
                        </div>
                    `;

                    return;
                }

                recommendations.forEach((recommendation, index) => {
                    const element =
                        document.createElement('div');

                    element.className =
                        'rounded-lg border border-gray-200 bg-gray-50 p-4';

                    element.innerHTML = `
                        <div class="flex gap-3">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-white">
                                ${index + 1}
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    ${recommendation.category_label ?? categoryLabel(recommendation.category)}
                                </p>

                                <p class="mt-1 text-sm leading-6 text-gray-800">
                                    ${recommendation.message}
                                </p>
                            </div>
                        </div>
                    `;

                    container.appendChild(element);
                });
            };

            const loadAdvisorAudit = async (
                persist = false
            ) => {
                loadingState.classList.remove('hidden');
                errorState.classList.add('hidden');
                insufficientState.classList.add('hidden');
                results.classList.add('hidden');

                const formData =
                    new FormData(form);

                const query =
                    new URLSearchParams(
                        formData
                    );

                const url = persist
                    ? `{{ route('advisor-audit.run') }}`
                    : `{{ route('advisor-audit.data') }}?${query.toString()}`;

                const options = persist
                    ? {
                        method: 'POST',

                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'input[name="_token"]'
                                ).value,
                        },

                        body: formData,
                    }
                    : {
                        headers: {
                            Accept: 'application/json',
                        },
                    };

                try {
                    const response = await fetch(
                        url,
                        options
                    );

                    const payload =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.message
                            ?? 'Unable to load the Advisor Audit.'
                        );
                    }

                    const data = payload.data;

                    if (
                        data.status === 'insufficient_data'
                        && data.overall_score === null
                    ) {
                        document.getElementById(
                            'insufficient-message'
                        ).textContent =
                            data.message
                            ?? 'More complete account data is required.';

                        insufficientState.classList.remove('hidden');
                    }

                    document.getElementById(
                        'overall-score'
                    ).textContent =
                        data.overall_score ?? '—';

                    const label =
                        document.getElementById(
                            'overall-label'
                        );

                    label.textContent =
                        data.overall_label
                        ?? 'Building your score';

                    label.className =
                        `inline-flex rounded-full px-3 py-1 text-sm font-semibold ${labelClasses(data.overall_score)}`;

                    document.getElementById(
                        'advisor-rating'
                    ).textContent =
                        advisorRatingLabel(
                            data.advisor_rating
                        );

                    document.getElementById(
                        'data-completeness'
                    ).textContent =
                        formatPercent(
                            data.data_completeness
                        );

                    document.getElementById(
                        'data-completeness-bar'
                    ).style.width =
                        `${Math.round((data.data_completeness ?? 0) * 100)}%`;

                    document.getElementById(
                        'analysis-period'
                    ).textContent =
                        data.period
                            ? `${data.period.start_date} to ${data.period.end_date}`
                            : '—';

                    document.getElementById(
                        'benchmark-name'
                    ).textContent =
                        data.benchmark?.name
                            ? `${data.benchmark.name} (${data.benchmark.symbol})`
                            : 'No benchmark';

                    document.getElementById(
                        'account-count'
                    ).textContent =
                        data.period?.account_count ?? 0;

                    const executive =
                        data.executive_summary ?? {};

                    document.getElementById(
                        'executive-headline'
                    ).textContent =
                        executive.headline
                        ?? 'Advisor audit complete';

                    document.getElementById(
                        'executive-summary'
                    ).textContent =
                        executive.summary
                        ?? 'No executive summary was generated.';

                    const summary =
                        data.findings?.summary ?? {};

                    document.getElementById(
                        'critical-count'
                    ).textContent =
                        summary.critical_count ?? 0;

                    document.getElementById(
                        'important-count'
                    ).textContent =
                        summary.important_count ?? 0;

                    document.getElementById(
                        'opportunity-count'
                    ).textContent =
                        summary.opportunity_count ?? 0;

                    renderCategories(
                        data.categories ?? {}
                    );

                    const critical =
                        data.findings?.critical ?? [];

                    const criticalSection =
                        document.getElementById(
                            'critical-section'
                        );

                    if (critical.length > 0) {
                        renderFindings(
                            'critical-findings',
                            critical,
                            'No critical findings detected.'
                        );

                        criticalSection.classList.remove('hidden');
                    } else {
                        criticalSection.classList.add('hidden');
                    }

                    renderFindings(
                        'important-findings',
                        data.findings?.important ?? [],
                        'No important concerns were detected.'
                    );

                    renderFindings(
                        'opportunity-findings',
                        data.findings?.opportunities ?? [],
                        'No major opportunities were identified.'
                    );

                    renderRecommendations(
                        data.findings?.recommendations ?? []
                    );

                    results.classList.remove('hidden');
                } catch (error) {
                    errorState.textContent =
                        error.message;

                    errorState.classList.remove('hidden');
                } finally {
                    loadingState.classList.add('hidden');
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();

                loadAdvisorAudit(true);
            });

            loadAdvisorAudit(false);
        });
    </script>
</x-app-layout>