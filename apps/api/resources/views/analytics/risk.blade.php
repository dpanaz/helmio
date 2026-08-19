<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Portfolio risk
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Understand your portfolio risk
            </h2>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                See how much your portfolio moves, how severe declines have been,
                and whether the returns you are earning are reasonable for the risk taken.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <x-analytics.filter-panel
                title="Analysis settings"
                subtitle="Adjust these only if you want to change the period or benchmark used in the risk analysis."
            >
                <form
                    id="risk-form"
                    class="grid gap-5 md:grid-cols-4"
                >
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

                    <div>
                        <label
                            for="annual_risk_free_rate"
                            class="block text-sm font-medium text-slate-400"
                        >
                            Risk-free rate (advanced)
                        </label>

                        <div class="mt-2 flex">
                            <input
                                id="annual_risk_free_rate"
                                name="annual_risk_free_rate"
                                type="number"
                                step="0.001"
                                value="0"
                                class="block w-full rounded-l-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <span
                                class="inline-flex items-center rounded-r-xl border border-l-0 border-slate-700 bg-slate-800 px-4 text-sm text-slate-400"
                            >
                                Decimal
                            </span>
                        </div>

                        <p class="mt-2 text-xs text-slate-600">
                            Example: 0.04 means 4%.
                        </p>
                    </div>

                    <div class="md:col-span-4">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Update Analysis
                        </button>
                    </div>
                </form>
            </x-analytics.filter-panel>

            <div
                id="loading-state"
                class="hidden rounded-2xl border border-slate-800 bg-slate-900 p-8 text-center"
            >
                <div
                    class="mx-auto h-7 w-7 animate-spin rounded-full border-2 border-slate-700 border-t-blue-400"
                ></div>

                <p class="mt-4 text-sm text-slate-400">
                    Calculating portfolio risk…
                </p>
            </div>

            <div
                id="error-state"
                class="hidden rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5 text-sm text-red-300"
            ></div>

            <div
                id="insufficient-state"
                class="hidden"
            >
                <x-analytics.message-card
                    tone="warning"
                    title="More return history is needed"
                >
                    <p id="insufficient-message"></p>
                </x-analytics.message-card>
            </div>

            <div id="results" class="hidden space-y-6">

                {{-- ===================================================== --}}
                {{-- RISK SUMMARY --}}
                {{-- ===================================================== --}}

                <section
                    class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
                >
                    <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-blue-400">
                                    Risk summary
                                </p>

                                <h3 class="mt-1 text-lg font-semibold text-white">
                                    What Helmio sees
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    A plain-English view of the portfolio's current risk profile.
                                </p>
                            </div>

                            <span
                                id="risk-level-badge"
                                class="inline-flex w-fit rounded-full border px-3 py-1 text-sm font-semibold"
                            >
                                —
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Compared with
                            </p>

                            <p
                                id="benchmark-name"
                                class="mt-2 text-sm font-semibold text-slate-200"
                            >
                                —
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                The benchmark gives Helmio a reference point for evaluating market sensitivity.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Return periods analyzed
                            </p>

                            <p
                                id="return-period-count"
                                class="mt-2 text-2xl font-semibold text-white"
                            >
                                —
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                More return history generally makes the risk analysis more reliable.
                            </p>
                        </div>

                        <div class="rounded-xl border border-blue-500/20 bg-blue-500/[0.05] p-4 sm:col-span-2 lg:col-span-1">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-blue-400">
                                How to read this page
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-300">
                                Lower risk is not always better. The goal is to understand whether the risk taken is appropriate for your objectives and the return being earned.
                            </p>
                        </div>
                    </div>
                </section>


                {{-- ===================================================== --}}
                {{-- CORE RISK METRICS --}}
                {{-- ===================================================== --}}

                <section>
                    <div class="mb-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                            Core risk measures
                        </p>

                        <h3 class="mt-1 text-lg font-semibold text-white">
                            The six numbers that matter
                        </h3>

                        <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500">
                            Each measure answers a different question about how the portfolio behaves.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">

                        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        Annualized volatility
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        How much does the portfolio move?
                                    </p>
                                </div>

                                <span
                                    id="annualized-volatility"
                                    class="text-xl font-semibold tabular-nums text-blue-300"
                                >
                                    —
                                </span>
                            </div>

                            <p class="mt-4 text-xs leading-5 text-slate-400">
                                Measures how widely returns fluctuate over time. Higher volatility means larger swings in value.
                            </p>

                            <div class="mt-4 rounded-xl bg-slate-950/60 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                    In plain English
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Higher number = bumpier ride.
                                </p>
                            </div>
                        </article>

                        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        Maximum drawdown
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        What was the worst decline?
                                    </p>
                                </div>

                                <span
                                    id="maximum-drawdown"
                                    class="text-xl font-semibold tabular-nums text-blue-300"
                                >
                                    —
                                </span>
                            </div>

                            <p class="mt-4 text-xs leading-5 text-slate-400">
                                The largest peak-to-trough drop during the selected period.
                            </p>

                            <div class="mt-4 rounded-xl bg-slate-950/60 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                    In plain English
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Shows the worst loss an investor would have experienced before recovery.
                                </p>
                            </div>
                        </article>

                        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        Downside deviation
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        How volatile are the bad periods?
                                    </p>
                                </div>

                                <span
                                    id="downside-deviation"
                                    class="text-xl font-semibold tabular-nums text-blue-300"
                                >
                                    —
                                </span>
                            </div>

                            <p class="mt-4 text-xs leading-5 text-slate-400">
                                Focuses only on returns that fall below the target return instead of counting upside movement as risk.
                            </p>

                            <div class="mt-4 rounded-xl bg-slate-950/60 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                    In plain English
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Lower means fewer or smaller harmful swings.
                                </p>
                            </div>
                        </article>

                        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        Sharpe ratio
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Are returns worth the total risk?
                                    </p>
                                </div>

                                <span
                                    id="sharpe-ratio"
                                    class="text-xl font-semibold tabular-nums text-blue-300"
                                >
                                    —
                                </span>
                            </div>

                            <p class="mt-4 text-xs leading-5 text-slate-400">
                                Compares excess return with total volatility. A higher value generally indicates better risk-adjusted performance.
                            </p>

                            <div class="mt-4 rounded-xl bg-slate-950/60 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                    In plain English
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Higher is generally better.
                                </p>
                            </div>
                        </article>

                        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        Sortino ratio
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Are returns worth the downside risk?
                                    </p>
                                </div>

                                <span
                                    id="sortino-ratio"
                                    class="text-xl font-semibold tabular-nums text-blue-300"
                                >
                                    —
                                </span>
                            </div>

                            <p class="mt-4 text-xs leading-5 text-slate-400">
                                Similar to Sharpe, but penalizes harmful downside volatility rather than all volatility.
                            </p>

                            <div class="mt-4 rounded-xl bg-slate-950/60 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                    In plain English
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Higher means more return for each unit of downside risk.
                                </p>
                            </div>
                        </article>

                        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-white">
                                        Beta
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        How sensitive is the portfolio to the market?
                                    </p>
                                </div>

                                <span
                                    id="beta"
                                    class="text-xl font-semibold tabular-nums text-blue-300"
                                >
                                    —
                                </span>
                            </div>

                            <p class="mt-4 text-xs leading-5 text-slate-400">
                                Measures how strongly the portfolio tends to move relative to the selected benchmark.
                            </p>

                            <div class="mt-4 rounded-xl bg-slate-950/60 px-3 py-2.5">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                    In plain English
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Around 1 means market-like sensitivity; above 1 means larger benchmark-related swings.
                                </p>
                            </div>
                        </article>
                    </div>
                </section>


                {{-- ===================================================== --}}
                {{-- FINDINGS --}}
                {{-- ===================================================== --}}

                <section
                    class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
                >
                    <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                            What deserves attention
                        </p>

                        <h3 class="mt-1 text-lg font-semibold text-white">
                            Risk findings
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Helmio highlights specific patterns that may warrant a closer look.
                        </p>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div
                            id="risk-flags"
                            class="space-y-3"
                        ></div>
                    </div>
                </section>


                {{-- ===================================================== --}}
                {{-- RETURN COMPARISON --}}
                {{-- ===================================================== --}}

                <section
                    class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
                >
                    <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                            Return behavior
                        </p>

                        <h3 class="mt-1 text-lg font-semibold text-white">
                            Portfolio vs. benchmark
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Daily portfolio returns compared with the selected benchmark. This helps show whether your portfolio tends to move more or less than the market.
                        </p>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="h-80">
                            <canvas id="risk-chart"></canvas>
                        </div>
                    </div>
                </section>


                {{-- ===================================================== --}}
                {{-- SUPPORTING DATA --}}
                {{-- ===================================================== --}}

                <details
                    class="group overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
                >
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6"
                    >
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                Supporting information
                            </p>

                            <h3 class="mt-1 text-base font-semibold text-white">
                                Data quality and observation details
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                Expand this section if you want to inspect the underlying return-history coverage.
                            </p>
                        </div>

                        <svg
                            class="h-5 w-5 shrink-0 text-slate-500 transition group-open:rotate-180"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m6 9 6 6 6-6"
                            />
                        </svg>
                    </summary>

                    <div class="border-t border-slate-800 p-5 sm:p-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div>
                                <h4 class="text-sm font-semibold text-white">
                                    Data quality
                                </h4>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Warnings here explain missing or limited data that may affect the analysis.
                                </p>

                                <div
                                    id="risk-warnings"
                                    class="mt-4 space-y-3"
                                ></div>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-white">
                                    Return observations
                                </h4>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    A quick count of the return periods used in the calculation.
                                </p>

                                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                                    @foreach ([
                                        ['Positive days', 'positive-days'],
                                        ['Negative days', 'negative-days'],
                                        ['Flat days', 'flat-days'],
                                        ['Benchmark matches', 'aligned-return-count'],
                                    ] as [$label, $id])
                                        <div
                                            class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                                        >
                                            <dt class="text-xs text-slate-500">
                                                {{ $label }}
                                            </dt>

                                            <dd
                                                id="{{ $id }}"
                                                class="mt-2 text-xl font-semibold text-white"
                                            >
                                                —
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </div>
                    </div>
                </details>

            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('risk-form');
            const results = document.getElementById('results');
            const loadingState = document.getElementById('loading-state');
            const errorState = document.getElementById('error-state');
            const insufficientState = document.getElementById('insufficient-state');

            let riskChart = null;

            const formatPercent = (value) => {
                if (value === null || value === undefined) {
                    return '—';
                }

                return new Intl.NumberFormat('en-US', {
                    style: 'percent',
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 2,
                }).format(value);
            };

            const formatNumber = (value, digits = 2) => {
                if (value === null || value === undefined) {
                    return '—';
                }

                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: digits,
                    maximumFractionDigits: digits,
                }).format(value);
            };

            const riskLevelLabel = (level) => ({
                very_low: 'Very Low',
                low: 'Low',
                moderate: 'Moderate',
                high: 'High',
                very_high: 'Very High',
            })[level] ?? 'Unknown';

            const riskLevelClasses = (level) => ({
                very_low: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
                low: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
                moderate: 'border-amber-500/20 bg-amber-500/10 text-amber-300',
                high: 'border-orange-500/20 bg-orange-500/10 text-orange-300',
                very_high: 'border-red-500/20 bg-red-500/10 text-red-300',
            })[level] ?? 'border-slate-700 bg-slate-800 text-slate-300';

            const flagClasses = (severity) => ({
                informational: 'border-blue-500/20 bg-blue-500/[0.07] text-blue-300',
                moderate: 'border-amber-500/20 bg-amber-500/[0.07] text-amber-300',
                high: 'border-red-500/20 bg-red-500/[0.07] text-red-300',
            })[severity] ?? 'border-slate-700 bg-slate-800 text-slate-300';

            const renderRiskChart = (series, benchmarkName) => {
                const canvas = document.getElementById('risk-chart');

                if (!canvas || !window.Chart) {
                    return;
                }

                if (riskChart) {
                    riskChart.destroy();
                }

                riskChart = new Chart(canvas, {
                    type: 'line',

                    data: {
                        labels: series.map(
                            (point) => point.date
                        ),

                        datasets: [
                            {
                                label: 'Portfolio',
                                data: series.map(
                                    (point) =>
                                        point.portfolio_return === null
                                            ? null
                                            : point.portfolio_return * 100
                                ),
                                borderColor: '#3b82f6',
                                backgroundColor: '#3b82f6',
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                tension: 0.15,
                                spanGaps: true,
                            },
                            {
                                label: benchmarkName ?? 'Benchmark',
                                data: series.map(
                                    (point) =>
                                        point.benchmark_return === null
                                            ? null
                                            : point.benchmark_return * 100
                                ),
                                borderColor: '#94a3b8',
                                backgroundColor: '#94a3b8',
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                tension: 0.15,
                                spanGaps: true,
                            },
                        ],
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },

                        plugins: {
                            legend: {
                                labels: {
                                    color: '#94a3b8',
                                },
                            },

                            tooltip: {
                                callbacks: {
                                    label(context) {
                                        const value = context.parsed.y;

                                        if (
                                            value === null ||
                                            value === undefined
                                        ) {
                                            return `${context.dataset.label}: —`;
                                        }

                                        return `${context.dataset.label}: ${value.toFixed(2)}%`;
                                    },
                                },
                            },
                        },

                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(51,65,85,.35)',
                                },

                                ticks: {
                                    color: '#64748b',
                                    maxTicksLimit: 8,
                                },
                            },

                            y: {
                                grid: {
                                    color: 'rgba(51,65,85,.35)',
                                },

                                ticks: {
                                    color: '#64748b',

                                    callback(value) {
                                        return `${value}%`;
                                    },
                                },

                                title: {
                                    display: true,
                                    text: 'Return',
                                    color: '#64748b',
                                },
                            },
                        },
                    },
                });
            };

            const renderFlags = (flags) => {
                const container =
                    document.getElementById('risk-flags');

                container.innerHTML = '';

                if (!flags || flags.length === 0) {
                    container.innerHTML = `
                        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.07] p-4 text-sm text-emerald-300">
                            No major risk findings detected.
                        </div>
                    `;

                    return;
                }

                flags.forEach((flag) => {
                    const element =
                        document.createElement('div');

                    element.className =
                        `rounded-xl border p-4 ${flagClasses(flag.severity)}`;

                    const title =
                        document.createElement('p');

                    title.className = 'font-semibold';
                    title.textContent = flag.title;

                    const message =
                        document.createElement('p');

                    message.className =
                        'mt-1 text-sm leading-6 text-slate-400';

                    message.textContent = flag.message;

                    element.appendChild(title);
                    element.appendChild(message);

                    container.appendChild(element);
                });
            };

            const renderWarnings = (warnings) => {
                const container =
                    document.getElementById('risk-warnings');

                container.innerHTML = '';

                if (!warnings || warnings.length === 0) {
                    container.innerHTML = `
                        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.07] p-4 text-sm text-emerald-300">
                            No data-quality warnings detected.
                        </div>
                    `;

                    return;
                }

                warnings.forEach((warning) => {
                    const element =
                        document.createElement('div');

                    element.className =
                        'rounded-xl border border-amber-500/20 bg-amber-500/[0.07] p-4 text-sm leading-6 text-amber-300';

                    element.textContent =
                        warning.message;

                    container.appendChild(element);
                });
            };

            const loadRisk = async () => {
                loadingState.classList.remove('hidden');
                errorState.classList.add('hidden');
                insufficientState.classList.add('hidden');
                results.classList.add('hidden');

                const query =
                    new URLSearchParams(
                        new FormData(form)
                    );

                try {
                    const response = await fetch(
                        `{{ route('analytics.risk.data') }}?${query.toString()}`,
                        {
                            headers: {
                                Accept: 'application/json',
                            },
                        }
                    );

                    const payload =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.message ??
                            'Unable to load risk analytics.'
                        );
                    }

                    const data = payload.data;

                    if (
                        data.status ===
                        'insufficient_data'
                    ) {
                        document.getElementById(
                            'insufficient-message'
                        ).textContent =
                            data.message ??
                            'More portfolio history is required.';

                        insufficientState.classList.remove(
                            'hidden'
                        );

                        return;
                    }

                    const metrics =
                        data.metrics ?? {};

                    const observations =
                        data.observations ?? {};

                    const period =
                        data.period ?? {};

                    document.getElementById(
                        'annualized-volatility'
                    ).textContent =
                        formatPercent(
                            metrics.annualized_volatility
                        );

                    document.getElementById(
                        'maximum-drawdown'
                    ).textContent =
                        formatPercent(
                            metrics.maximum_drawdown
                        );

                    document.getElementById(
                        'downside-deviation'
                    ).textContent =
                        formatPercent(
                            metrics.downside_deviation
                        );

                    document.getElementById(
                        'sharpe-ratio'
                    ).textContent =
                        formatNumber(
                            metrics.sharpe_ratio
                        );

                    document.getElementById(
                        'sortino-ratio'
                    ).textContent =
                        formatNumber(
                            metrics.sortino_ratio
                        );

                    document.getElementById(
                        'beta'
                    ).textContent =
                        formatNumber(
                            metrics.beta
                        );

                    const riskBadge =
                        document.getElementById(
                            'risk-level-badge'
                        );

                    riskBadge.textContent =
                        riskLevelLabel(
                            data.risk_level
                        );

                    riskBadge.className =
                        `inline-flex rounded-full border px-3 py-1 text-sm font-semibold ${riskLevelClasses(data.risk_level)}`;

                    document.getElementById(
                        'benchmark-name'
                    ).textContent =
                        data.benchmark?.name
                            ? `Compared with ${data.benchmark.name}`
                            : 'No benchmark selected';

                    document.getElementById(
                        'return-period-count'
                    ).textContent =
                        period.return_period_count ?? 0;

                    document.getElementById(
                        'aligned-return-count'
                    ).textContent =
                        period.aligned_return_count ?? 0;

                    document.getElementById(
                        'positive-days'
                    ).textContent =
                        observations.positive_days ?? 0;

                    document.getElementById(
                        'negative-days'
                    ).textContent =
                        observations.negative_days ?? 0;

                    document.getElementById(
                        'flat-days'
                    ).textContent =
                        observations.flat_days ?? 0;

                    renderRiskChart(
                        data.series ?? [],
                        data.benchmark?.name
                    );

                    renderFlags(
                        data.flags ?? []
                    );

                    renderWarnings(
                        data.warnings ?? []
                    );

                    results.classList.remove(
                        'hidden'
                    );
                } catch (error) {
                    errorState.textContent =
                        error.message;

                    errorState.classList.remove(
                        'hidden'
                    );
                } finally {
                    loadingState.classList.add(
                        'hidden'
                    );
                }
            };

            form.addEventListener(
                'submit',
                (event) => {
                    event.preventDefault();
                    loadRisk();
                }
            );

            loadRisk();
        });
    </script>
</x-app-layout>