<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Independent advisor oversight
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Advisor Audit
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Review advisor performance, costs, risk, trading behavior,
                    cash management, diversification, and tax efficiency.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('advisor-audit.report') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                >
                    View Report
                </a>

                <a
                    href="{{ route('advisor-audit.report.pdf') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                >
                    Download PDF
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

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            {{-- Analysis controls --}}
            <section
                class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-black/10"
            >
                <div class="mb-5">
                    <p class="text-sm font-semibold text-white">
                        Audit controls
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Choose the review period and comparison benchmark.
                    </p>
                </div>

                <form
                    id="advisor-audit-form"
                    class="grid gap-5 md:grid-cols-4"
                >
                    @csrf

                    <div>
                        <label
                            for="start_date"
                            class="block text-sm font-medium text-slate-400"
                        >
                            Start date
                        </label>

                        <input
                            id="start_date"
                            name="start_date"
                            type="date"
                            value="{{ now()->subYear()->format('Y-m-d') }}"
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 shadow-none focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                    </div>

                    <div>
                        <label
                            for="end_date"
                            class="block text-sm font-medium text-slate-400"
                        >
                            End date
                        </label>

                        <input
                            id="end_date"
                            name="end_date"
                            type="date"
                            value="{{ now()->format('Y-m-d') }}"
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 shadow-none focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                    </div>

                    <div>
                        <label
                            for="benchmark_id"
                            class="block text-sm font-medium text-slate-400"
                        >
                            Benchmark
                        </label>

                        <select
                            id="benchmark_id"
                            name="benchmark_id"
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 shadow-none focus:border-blue-500 focus:ring-blue-500"
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
                            class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Run Advisor Audit
                        </button>
                    </div>
                </form>
            </section>

            {{-- Loading --}}
            <div
                id="loading-state"
                class="hidden rounded-3xl border border-slate-800 bg-slate-900 p-10 text-center"
            >
                <div
                    class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-slate-700 border-t-blue-400"
                ></div>

                <p class="mt-4 text-sm font-medium text-slate-300">
                    Running Advisor Audit…
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    Reviewing all available analytics categories.
                </p>
            </div>

            {{-- Error --}}
            <div
                id="error-state"
                class="hidden rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5 text-sm text-red-300"
            ></div>

            {{-- Insufficient data --}}
            <div
                id="insufficient-state"
                class="hidden rounded-2xl border border-amber-500/20 bg-amber-500/[0.07] p-5"
            >
                <div class="flex gap-4">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-300"
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

                    <div>
                        <h3 class="font-semibold text-amber-300">
                            More account data is needed
                        </h3>

                        <p
                            id="insufficient-message"
                            class="mt-1 text-sm leading-6 text-slate-400"
                        ></p>
                    </div>
                </div>
            </div>

            <div
                id="results"
                class="hidden space-y-6"
            >
                {{-- Score + summary --}}
                <div
                    class="grid gap-6 lg:grid-cols-[1.05fr_1.95fr]"
                >
                    {{-- Score --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p class="text-sm font-semibold text-white">
                                    Advisor Audit Score
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Overall oversight assessment
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-6 flex justify-center"
                        >
                            <div
                                id="advisor-score-dial"
                                data-score="0"
                                class="relative flex h-56 w-56 items-center justify-center"
                            >
                                <svg
                                    class="absolute inset-0 h-full w-full -rotate-90"
                                    viewBox="0 0 220 220"
                                >
                                    <circle
                                        cx="110"
                                        cy="110"
                                        r="88"
                                        fill="none"
                                        stroke="#1e293b"
                                        stroke-width="15"
                                    />

                                    <circle
                                        id="advisor-score-ring"
                                        cx="110"
                                        cy="110"
                                        r="88"
                                        fill="none"
                                        stroke="#3b82f6"
                                        stroke-width="15"
                                        stroke-linecap="round"
                                        pathLength="100"
                                        stroke-dasharray="100"
                                        stroke-dashoffset="100"
                                    />
                                </svg>

                                <div
                                    class="relative flex h-40 w-40 flex-col items-center justify-center rounded-full border border-slate-800 bg-slate-950"
                                >
                                    <div class="flex items-baseline">
                                        <span
                                            id="overall-score"
                                            class="text-5xl font-semibold tracking-tight text-white"
                                        >
                                            —
                                        </span>

                                        <span
                                            class="ml-1 text-sm text-slate-600"
                                        >
                                            /100
                                        </span>
                                    </div>

                                    <span
                                        id="overall-label"
                                        class="mt-3 inline-flex rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-300"
                                    >
                                        —
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p
                            id="advisor-rating"
                            class="mt-5 text-center text-sm font-medium text-slate-400"
                        >
                            —
                        </p>

                        <div
                            id="audit-confidence-wrap"
                            class="mt-3 flex justify-center"
                        >
                            <span
                                id="audit-confidence"
                                class="hidden inline-flex rounded-full border px-3 py-1 text-xs font-semibold"
                            ></span>
                        </div>

                        <div class="mt-6">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-slate-500">
                                    Data completeness
                                </span>

                                <span
                                    id="data-completeness"
                                    class="font-semibold text-white"
                                >
                                    —
                                </span>
                            </div>

                            <div
                                class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800"
                            >
                                <div
                                    id="data-completeness-bar"
                                    class="h-full rounded-full bg-blue-500"
                                    style="width: 0%"
                                ></div>
                            </div>
                        </div>

                        <dl
                            class="mt-6 space-y-4 border-t border-slate-800 pt-5 text-sm"
                        >
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">
                                    Analysis period
                                </dt>

                                <dd
                                    id="analysis-period"
                                    class="text-right font-medium text-slate-300"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">
                                    Benchmark
                                </dt>

                                <dd
                                    id="benchmark-name"
                                    class="text-right font-medium text-slate-300"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500">
                                    Accounts reviewed
                                </dt>

                                <dd
                                    id="account-count"
                                    class="text-right font-medium text-slate-300"
                                >
                                    —
                                </dd>
                            </div>
                        </dl>
                    </section>

                    {{-- Executive summary --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Executive Summary
                        </p>

                        <h3
                            id="executive-headline"
                            class="mt-3 text-2xl font-semibold tracking-tight text-white"
                        >
                            —
                        </h3>

                        <p
                            id="executive-summary"
                            class="mt-4 max-w-3xl text-sm leading-7 text-slate-400"
                        >
                            —
                        </p>

                        <div
                            class="mt-7 grid gap-4 sm:grid-cols-3"
                        >
                            <div
                                class="rounded-2xl border border-red-500/20 bg-red-500/[0.06] p-5"
                            >
                                <p class="text-sm text-red-300">
                                    Critical concerns
                                </p>

                                <p
                                    id="critical-count"
                                    class="mt-2 text-3xl font-semibold text-white"
                                >
                                    —
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] p-5"
                            >
                                <p class="text-sm text-amber-300">
                                    Important findings
                                </p>

                                <p
                                    id="important-count"
                                    class="mt-2 text-3xl font-semibold text-white"
                                >
                                    —
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.06] p-5"
                            >
                                <p class="text-sm text-emerald-300">
                                    Opportunities
                                </p>

                                <p
                                    id="opportunity-count"
                                    class="mt-2 text-3xl font-semibold text-white"
                                >
                                    —
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Category scores --}}
                <section
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-white">
                            Category Scores
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Each category contributes to the overall advisor assessment.
                        </p>
                    </div>

                    <div
                        id="category-grid"
                        class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                    ></div>
                </section>

                {{-- Critical --}}
                <section
                    id="critical-section"
                    class="hidden rounded-3xl border border-red-500/20 bg-red-500/[0.05] p-6"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-red-300">
                            Critical Findings
                        </h3>

                        <p class="mt-1 text-sm text-slate-400">
                            These findings should be reviewed promptly.
                        </p>
                    </div>

                    <div
                        id="critical-findings"
                        class="mt-5 space-y-4"
                    ></div>
                </section>

                {{-- Important + opportunities --}}
                <div class="grid gap-6 lg:grid-cols-2">
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <h3 class="text-lg font-semibold text-white">
                            Important Findings
                        </h3>

                        <div
                            id="important-findings"
                            class="mt-5 space-y-4"
                        ></div>
                    </section>

                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <h3 class="text-lg font-semibold text-white">
                            Opportunities
                        </h3>

                        <div
                            id="opportunity-findings"
                            class="mt-5 space-y-4"
                        ></div>
                    </section>
                </div>

                {{-- Recommended actions --}}
                <section
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <h3 class="text-lg font-semibold text-white">
                        Recommended Actions
                    </h3>

                    <div
                        id="recommended-actions"
                        class="mt-5 grid gap-4 lg:grid-cols-2"
                    ></div>
                </section>

                {{-- Next steps --}}
                <section
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h3 class="text-lg font-semibold text-white">
                                Next Steps
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Review the full report, compare prior audits,
                                or schedule recurring monthly reviews.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a
                                href="{{ route('advisor-audit.report') }}"
                                class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:border-slate-600 hover:text-white"
                            >
                                Full Report
                            </a>

                            <a
                                href="{{ route('advisor-audit.history') }}"
                                class="rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:border-slate-600 hover:text-white"
                            >
                                Compare History
                            </a>

                            <a
                                href="{{ route('advisor-audit.monthly-settings') }}"
                                class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                Monthly Audit Settings
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form =
                document.getElementById(
                    'advisor-audit-form'
                );

            const results =
                document.getElementById(
                    'results'
                );

            const loadingState =
                document.getElementById(
                    'loading-state'
                );

            const errorState =
                document.getElementById(
                    'error-state'
                );

            const insufficientState =
                document.getElementById(
                    'insufficient-state'
                );

            let scoreAnimationFrame = null;

            const formatPercent = (value) => {
                if (
                    value === null
                    || value === undefined
                ) {
                    return '—';
                }

                return new Intl.NumberFormat(
                    'en-US',
                    {
                        style: 'percent',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0,
                    }
                ).format(value);
            };

            const formatCurrency = (value) => {
                if (
                    value === null
                    || value === undefined
                ) {
                    return null;
                }

                return new Intl.NumberFormat(
                    'en-US',
                    {
                        style: 'currency',
                        currency: 'USD',
                        maximumFractionDigits: 0,
                    }
                ).format(value);
            };

            const labelClasses = (score) => {
                if (
                    score === null
                    || score === undefined
                ) {
                    return 'border-slate-700 bg-slate-800 text-slate-400';
                }

                if (score >= 90) {
                    return 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300';
                }

                if (score >= 80) {
                    return 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300';
                }

                if (score >= 70) {
                    return 'border-blue-500/20 bg-blue-500/10 text-blue-300';
                }

                if (score >= 60) {
                    return 'border-amber-500/20 bg-amber-500/10 text-amber-300';
                }

                if (score >= 40) {
                    return 'border-orange-500/20 bg-orange-500/10 text-orange-300';
                }

                return 'border-red-500/20 bg-red-500/10 text-red-300';
            };

            const scoreColor = (score) => {
                if (
                    score === null
                    || score === undefined
                ) {
                    return '#64748b';
                }

                if (score >= 80) {
                    return '#10b981';
                }

                if (score >= 70) {
                    return '#3b82f6';
                }

                if (score >= 60) {
                    return '#f59e0b';
                }

                if (score >= 40) {
                    return '#f97316';
                }

                return '#ef4444';
            };

            const animateScore = (score) => {
                const numberElement =
                    document.getElementById(
                        'overall-score'
                    );

                const ring =
                    document.getElementById(
                        'advisor-score-ring'
                    );

                if (
                    scoreAnimationFrame
                    !== null
                ) {
                    cancelAnimationFrame(
                        scoreAnimationFrame
                    );
                }

                if (
                    score === null
                    || score === undefined
                ) {
                    numberElement.textContent =
                        '—';

                    ring.style.strokeDashoffset =
                        '100';

                    ring.style.stroke =
                        '#64748b';

                    return;
                }

                const target =
                    Math.max(
                        0,
                        Math.min(
                            100,
                            Number(score)
                        )
                    );

                ring.style.stroke =
                    scoreColor(target);

                ring.style.strokeDashoffset =
                    '100';

                numberElement.textContent =
                    '0';

                const duration = 1400;

                const start =
                    performance.now();

                const animate = (now) => {
                    const progress =
                        Math.min(
                            (now - start)
                            / duration,
                            1
                        );

                    const eased =
                        1 -
                        Math.pow(
                            1 - progress,
                            3
                        );

                    const current =
                        target * eased;

                    numberElement.textContent =
                        Math.round(current);

                    ring.style.strokeDashoffset =
                        String(
                            100 - current
                        );

                    if (progress < 1) {
                        scoreAnimationFrame =
                            requestAnimationFrame(
                                animate
                            );
                    } else {
                        numberElement.textContent =
                            Math.round(target);

                        ring.style.strokeDashoffset =
                            String(
                                100 - target
                            );
                    }
                };

                scoreAnimationFrame =
                    requestAnimationFrame(
                        animate
                    );
            };

            const advisorRatingLabel = (rating) => {
                const labels = {
                    strong:
                        'Strong oversight',

                    generally_sound:
                        'Generally sound',

                    mixed:
                        'Mixed results',

                    concerning:
                        'Concerning',

                    high_concern:
                        'High concern',
                };

                return labels[rating]
                    ?? 'Not available';
            };

            const categoryLabel = (category) => {
                const labels = {
                    cost:
                        'Cost',

                    diversification:
                        'Diversification',

                    performance:
                        'Performance',

                    risk:
                        'Risk',

                    suitability:
                        'Suitability',

                    trading:
                        'Trading Discipline',

                    cash:
                        'Cash Drag',

                    tax:
                        'Tax Efficiency',
                };

                return labels[category]
                    ?? category;
            };

            const categoryRoute = (category) => {
                const routes = {
                    cost:
                        '{{ route('analytics.costs') }}',

                    diversification:
                        '{{ route('analytics.diversification') }}',

                    performance:
                        '{{ route('analytics.performance') }}',

                    risk:
                        '{{ route('analytics.risk') }}',

                    suitability:
                        '#',

                    trading:
                        '{{ route('analytics.trading-discipline') }}',

                    cash:
                        '{{ route('analytics.cash-drag') }}',

                    tax:
                        '{{ route('analytics.tax-efficiency') }}',
                };

                return routes[category]
                    ?? '#';
            };

            const severityClasses = (severity) => {
                const classes = {
                    critical:
                        'border-red-500/30 bg-red-500/[0.07] text-red-300',

                    high:
                        'border-red-500/20 bg-red-500/[0.06] text-red-300',

                    moderate:
                        'border-amber-500/20 bg-amber-500/[0.06] text-amber-300',

                    informational:
                        'border-blue-500/20 bg-blue-500/[0.06] text-blue-300',
                };

                return classes[severity]
                    ?? 'border-slate-700 bg-slate-800 text-slate-300';
            };

            const escapeHtml = (value) => {
                const element =
                    document.createElement('div');

                element.textContent =
                    value ?? '';

                return element.innerHTML;
            };

            const categoryStatusLabel = (category) => {
                if (
                    category?.available !== false
                    && category?.score !== null
                    && category?.score !== undefined
                ) {
                    return category.label
                        ?? 'Available';
                }

                const message =
                    String(
                        category?.message
                        ?? ''
                    ).toLowerCase();

                if (
                    message.includes('building')
                    || message.includes('waiting')
                    || message.includes('history')
                ) {
                    return 'Building history';
                }

                return category?.label
                    ?? 'Insufficient data';
            };

            const categoryMessage = (category) => {
                if (
                    category?.available === false
                    || category?.score === null
                    || category?.score === undefined
                ) {
                    return category?.message
                        ?? 'More data is required.';
                }

                return 'Open category details →';
            };

            const confidenceClasses = (status) => {
                if (status === 'provisional') {
                    return 'border-amber-500/20 bg-amber-500/10 text-amber-300';
                }

                if (status === 'complete') {
                    return 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300';
                }

                return 'border-slate-700 bg-slate-800 text-slate-400';
            };

            const confidenceLabel = (data) => {
                if (data?.status === 'provisional') {
                    return 'Provisional score';
                }

                if (data?.status === 'complete') {
                    return 'Established score';
                }

                return 'Building advisor audit';
            };

            const renderCategories = (
                categories
            ) => {
                const grid =
                    document.getElementById(
                        'category-grid'
                    );

                grid.innerHTML = '';

                Object.entries(
                    categories ?? {}
                ).forEach(
                    ([key, category]) => {
                        const score =
                            category.score
                            ?? null;

                        const isAvailable =
                            category.available !== false
                            && score !== null
                            && score !== undefined;

                        const card =
                            document.createElement(
                                isAvailable ? 'a' : 'div'
                            );

                        if (isAvailable) {
                            card.href =
                                categoryRoute(key);
                        }

                        card.className =
                            isAvailable
                                ? 'block rounded-2xl border border-slate-800 bg-slate-950 p-5 transition hover:-translate-y-0.5 hover:border-blue-500/40'
                                : 'block rounded-2xl border border-slate-800 bg-slate-950 p-5';

                        const statusLabel =
                            categoryStatusLabel(
                                category
                            );

                        const detailMessage =
                            categoryMessage(
                                category
                            );

                        card.innerHTML = `
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-slate-500">
                                        ${categoryLabel(key)}
                                    </p>

                                    <p class="mt-2 text-3xl font-semibold text-white">
                                        ${score ?? '—'}
                                    </p>
                                </div>

                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${labelClasses(score)}">
                                    ${escapeHtml(statusLabel)}
                                </span>
                            </div>

                            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-blue-500"
                                    style="width: ${score ?? 0}%"
                                ></div>
                            </div>

                            <p class="mt-4 text-xs text-slate-600">
                                ${escapeHtml(detailMessage)}
                            </p>
                        `;

                        grid.appendChild(card);
                    }
                );
            };

            const renderFindings = (
                containerId,
                findings,
                emptyMessage
            ) => {
                const container =
                    document.getElementById(
                        containerId
                    );

                container.innerHTML = '';

                if (
                    !findings
                    || findings.length === 0
                ) {
                    container.innerHTML = `
                        <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 text-sm text-slate-500">
                            ${emptyMessage}
                        </div>
                    `;

                    return;
                }

                findings.forEach(
                    (finding) => {
                        const element =
                            document.createElement(
                                'div'
                            );

                        element.className =
                            `rounded-2xl border p-5 ${severityClasses(finding.severity)}`;

                        const impact =
                            formatCurrency(
                                finding.financial_impact
                            );

                        element.innerHTML = `
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-semibold uppercase tracking-wide opacity-80">
                                            ${
                                                finding.category_label
                                                ?? categoryLabel(
                                                    finding.category
                                                )
                                            }
                                        </span>

                                        <span class="rounded-full border border-white/10 bg-black/10 px-2 py-0.5 text-xs font-semibold capitalize">
                                            ${finding.severity}
                                        </span>
                                    </div>

                                    <h4 class="mt-3 font-semibold text-white">
                                        ${finding.title}
                                    </h4>

                                    <p class="mt-2 text-sm leading-6 text-slate-400">
                                        ${finding.message}
                                    </p>
                                </div>

                                <div class="shrink-0 text-right">
                                    <p class="text-xs text-slate-500">
                                        Priority
                                    </p>

                                    <p class="mt-1 font-semibold text-white">
                                        ${finding.priority}
                                    </p>

                                    ${
                                        impact
                                        ? `
                                            <p class="mt-2 text-sm font-semibold text-white">
                                                ${impact}
                                            </p>
                                        `
                                        : ''
                                    }
                                </div>
                            </div>
                        `;

                        container.appendChild(
                            element
                        );
                    }
                );
            };

            const renderRecommendations = (
                recommendations
            ) => {
                const container =
                    document.getElementById(
                        'recommended-actions'
                    );

                container.innerHTML = '';

                if (
                    !recommendations
                    || recommendations.length === 0
                ) {
                    container.innerHTML = `
                        <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 text-sm text-slate-500">
                            No specific actions were generated.
                        </div>
                    `;

                    return;
                }

                recommendations.forEach(
                    (
                        recommendation,
                        index
                    ) => {
                        const element =
                            document.createElement(
                                'div'
                            );

                        element.className =
                            'rounded-2xl border border-slate-800 bg-slate-950 p-5';

                        element.innerHTML = `
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-semibold text-white">
                                    ${index + 1}
                                </div>

                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-400">
                                        ${
                                            recommendation.category_label
                                            ?? categoryLabel(
                                                recommendation.category
                                            )
                                        }
                                    </p>

                                    <p class="mt-2 text-sm leading-6 text-slate-300">
                                        ${recommendation.message}
                                    </p>
                                </div>
                            </div>
                        `;

                        container.appendChild(
                            element
                        );
                    }
                );
            };

            const loadAdvisorAudit =
                async (
                    persist = false
                ) => {
                    loadingState
                        .classList
                        .remove(
                            'hidden'
                        );

                    errorState
                        .classList
                        .add(
                            'hidden'
                        );

                    insufficientState
                        .classList
                        .add(
                            'hidden'
                        );

                    results
                        .classList
                        .add(
                            'hidden'
                        );

                    const formData =
                        new FormData(
                            form
                        );

                    const query =
                        new URLSearchParams(
                            formData
                        );

                    const url =
                        persist
                            ? `{{ route('advisor-audit.run') }}`
                            : `{{ route('advisor-audit.data') }}?${query.toString()}`;

                    const options =
                        persist
                            ? {
                                method:
                                    'POST',

                                headers: {
                                    Accept:
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        document.querySelector(
                                            'input[name="_token"]'
                                        ).value,
                                },

                                body:
                                    formData,
                            }
                            : {
                                headers: {
                                    Accept:
                                        'application/json',
                                },
                            };

                    try {
                        const response =
                            await fetch(
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

                        const data =
                            payload.data;

                        if (
                            data.status
                                === 'insufficient_data'
                            && data.overall_score
                                === null
                        ) {
                            document
                                .getElementById(
                                    'insufficient-message'
                                )
                                .textContent =
                                    data.message
                                    ?? 'More complete account data is required.';

                            insufficientState
                                .classList
                                .remove(
                                    'hidden'
                                );
                        }

                        animateScore(
                            data.overall_score
                        );

                        const label =
                            document.getElementById(
                                'overall-label'
                            );

                        label.textContent =
                            data.overall_label
                            ?? 'Building your score';

                        label.className =
                            `mt-3 inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${labelClasses(data.overall_score)}`;

                        document
                            .getElementById(
                                'advisor-rating'
                            )
                            .textContent =
                                advisorRatingLabel(
                                    data.advisor_rating
                                );

                        const confidenceBadge =
                            document.getElementById(
                                'audit-confidence'
                            );

                        confidenceBadge.textContent =
                            confidenceLabel(data);

                        confidenceBadge.className =
                            `inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${confidenceClasses(data.status)}`;

                        confidenceBadge
                            .classList
                            .remove('hidden');

                        document
                            .getElementById(
                                'data-completeness'
                            )
                            .textContent =
                                formatPercent(
                                    data.data_completeness
                                );

                        document
                            .getElementById(
                                'data-completeness-bar'
                            )
                            .style.width =
                                `${
                                    Math.round(
                                        (
                                            data.data_completeness
                                            ?? 0
                                        )
                                        * 100
                                    )
                                }%`;

                        document
                            .getElementById(
                                'analysis-period'
                            )
                            .textContent =
                                data.period
                                    ? `${data.period.start_date} to ${data.period.end_date}`
                                    : '—';

                        document
                            .getElementById(
                                'benchmark-name'
                            )
                            .textContent =
                                data.benchmark?.name
                                    ? `${data.benchmark.name} (${data.benchmark.symbol})`
                                    : 'No benchmark';

                        document
                            .getElementById(
                                'account-count'
                            )
                            .textContent =
                                data.period
                                    ?.account_count
                                ?? 0;

                        const executive =
                            data.executive_summary
                            ?? {};

                        document
                            .getElementById(
                                'executive-headline'
                            )
                            .textContent =
                                executive.headline
                                ?? (
                                    data.status === 'provisional'
                                        ? 'Advisor audit is still building'
                                        : 'Advisor audit complete'
                                );

                        document
                            .getElementById(
                                'executive-summary'
                            )
                            .textContent =
                                executive.summary
                                ?? (
                                    data.status === 'provisional'
                                        ? `${Math.round((data.data_completeness ?? 0) * 100)}% of audit categories currently have sufficient data. The score is provisional and will update as more history becomes available.`
                                        : 'No executive summary was generated.'
                                );

                        const summary =
                            data.findings
                                ?.summary
                            ?? {};

                        document
                            .getElementById(
                                'critical-count'
                            )
                            .textContent =
                                summary.critical_count
                                ?? 0;

                        document
                            .getElementById(
                                'important-count'
                            )
                            .textContent =
                                summary.important_count
                                ?? 0;

                        document
                            .getElementById(
                                'opportunity-count'
                            )
                            .textContent =
                                summary.opportunity_count
                                ?? 0;

                        renderCategories(
                            data.categories
                            ?? {}
                        );

                        const critical =
                            data.findings
                                ?.critical
                            ?? [];

                        const criticalSection =
                            document.getElementById(
                                'critical-section'
                            );

                        if (
                            critical.length > 0
                        ) {
                            renderFindings(
                                'critical-findings',
                                critical,
                                'No critical findings detected.'
                            );

                            criticalSection
                                .classList
                                .remove(
                                    'hidden'
                                );
                        } else {
                            criticalSection
                                .classList
                                .add(
                                    'hidden'
                                );
                        }

                        renderFindings(
                            'important-findings',
                            data.findings
                                ?.important
                            ?? [],
                            'No important concerns were detected.'
                        );

                        renderFindings(
                            'opportunity-findings',
                            data.findings
                                ?.opportunities
                            ?? [],
                            'No major opportunities were identified.'
                        );

                        renderRecommendations(
                            data.findings
                                ?.recommendations
                            ?? []
                        );

                        results
                            .classList
                            .remove(
                                'hidden'
                            );
                    } catch (error) {
                        errorState
                            .textContent =
                                error.message;

                        errorState
                            .classList
                            .remove(
                                'hidden'
                            );
                    } finally {
                        loadingState
                            .classList
                            .add(
                                'hidden'
                            );
                    }
                };

            form.addEventListener(
                'submit',
                (event) => {
                    event.preventDefault();

                    loadAdvisorAudit(
                        true
                    );
                }
            );

            loadAdvisorAudit(
                false
            );
        });
    </script>
</x-app-layout>