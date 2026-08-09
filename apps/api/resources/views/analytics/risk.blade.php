<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Portfolio risk
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Risk Analytics
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Measure volatility, drawdowns, risk-adjusted returns,
                and benchmark sensitivity.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <x-analytics.filter-panel
                title="Risk analysis controls"
                subtitle="Choose the analysis period, benchmark, and annual risk-free rate."
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
                            Risk-free rate
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
                            Analyze Risk
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

                <x-analytics.panel>
                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Overall risk level
                            </p>

                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span
                                    id="risk-level-badge"
                                    class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold"
                                >
                                    —
                                </span>

                                <span
                                    id="benchmark-name"
                                    class="text-sm text-slate-400"
                                >
                                    —
                                </span>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-right"
                        >
                            <p class="text-xs text-slate-600">
                                Return periods
                            </p>

                            <p
                                id="return-period-count"
                                class="mt-1 text-xl font-semibold text-white"
                            >
                                —
                            </p>
                        </div>
                    </div>
                </x-analytics.panel>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <x-analytics.metric-card
                        label="Annualized volatility"
                        description="Variation in portfolio returns."
                    >
                        <span id="annualized-volatility">—</span>
                    </x-analytics.metric-card>

                    <x-analytics.metric-card
                        label="Maximum drawdown"
                        description="Largest peak-to-trough decline."
                    >
                        <span id="maximum-drawdown">—</span>
                    </x-analytics.metric-card>

                    <x-analytics.metric-card
                        label="Downside deviation"
                        description="Harmful volatility below the target return."
                    >
                        <span id="downside-deviation">—</span>
                    </x-analytics.metric-card>

                    <x-analytics.metric-card
                        label="Sharpe ratio"
                        description="Return earned per unit of total risk."
                    >
                        <span id="sharpe-ratio">—</span>
                    </x-analytics.metric-card>

                    <x-analytics.metric-card
                        label="Sortino ratio"
                        description="Return earned per unit of downside risk."
                    >
                        <span id="sortino-ratio">—</span>
                    </x-analytics.metric-card>

                    <x-analytics.metric-card
                        label="Beta"
                        description="Sensitivity relative to the benchmark."
                    >
                        <span id="beta">—</span>
                    </x-analytics.metric-card>
                </div>

                <x-analytics.panel
                    title="Daily Return Comparison"
                    subtitle="Portfolio and benchmark returns by period."
                >
                    <div class="h-80">
                        <canvas id="risk-chart"></canvas>
                    </div>
                </x-analytics.panel>

                <div class="grid gap-6 lg:grid-cols-2">
                    <x-analytics.panel title="Risk Findings">
                        <div
                            id="risk-flags"
                            class="space-y-3"
                        ></div>
                    </x-analytics.panel>

                    <x-analytics.panel title="Data Quality">
                        <div
                            id="risk-warnings"
                            class="space-y-3"
                        ></div>
                    </x-analytics.panel>
                </div>

                <x-analytics.panel
                    title="Observation Summary"
                    subtitle="Distribution of analyzed portfolio return periods."
                >
                    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                            ['Positive days', 'positive-days'],
                            ['Negative days', 'negative-days'],
                            ['Flat days', 'flat-days'],
                            ['Benchmark matches', 'aligned-return-count'],
                        ] as [$label, $id])
                            <div
                                class="rounded-xl border border-slate-800 bg-slate-950 p-4"
                            >
                                <dt class="text-sm text-slate-500">
                                    {{ $label }}
                                </dt>

                                <dd
                                    id="{{ $id }}"
                                    class="mt-2 text-2xl font-semibold text-white"
                                >
                                    —
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </x-analytics.panel>
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