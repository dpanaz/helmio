<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    Risk Analytics
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Measure volatility, drawdowns, risk-adjusted returns, and benchmark sensitivity.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form
                    id="risk-form"
                    class="grid gap-4 md:grid-cols-4"
                >
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

                    <div>
                        <label
                            for="annual_risk_free_rate"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Risk-free rate
                        </label>

                        <div class="mt-1 flex rounded-lg shadow-sm">
                            <input
                                id="annual_risk_free_rate"
                                name="annual_risk_free_rate"
                                type="number"
                                step="0.001"
                                value="0"
                                class="block w-full rounded-l-lg border-gray-300"
                            >

                            <span class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                                Decimal
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-gray-500">
                            Example: 0.04 means 4%.
                        </p>
                    </div>

                    <div class="md:col-span-4">
                        <button
                            type="submit"
                            class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            Analyze Risk
                        </button>
                    </div>
                </form>
            </div>

            <div
                id="loading-state"
                class="hidden rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-200"
            >
                <p class="text-sm text-gray-600">
                    Calculating portfolio risk…
                </p>
            </div>

            <div
                id="error-state"
                class="hidden rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
            ></div>

            <div
                id="insufficient-state"
                class="hidden rounded-xl border border-amber-200 bg-amber-50 p-6"
            >
                <h3 class="font-semibold text-amber-900">
                    More return history is needed
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
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-500">
                                Overall risk level
                            </p>

                            <div class="mt-2 flex items-center gap-3">
                                <span
                                    id="risk-level-badge"
                                    class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                >
                                    —
                                </span>

                                <span
                                    id="benchmark-name"
                                    class="text-sm text-gray-500"
                                >
                                    —
                                </span>
                            </div>
                        </div>

                        <div class="text-sm text-gray-500">
                            <span id="return-period-count">—</span>
                            return periods analyzed
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Annualized volatility
                        </p>

                        <p
                            id="annualized-volatility"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            Variation in portfolio returns.
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Maximum drawdown
                        </p>

                        <p
                            id="maximum-drawdown"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            Largest peak-to-trough decline.
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Downside deviation
                        </p>

                        <p
                            id="downside-deviation"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            Harmful volatility below the target return.
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Sharpe ratio
                        </p>

                        <p
                            id="sharpe-ratio"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            Return earned per unit of total risk.
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Sortino ratio
                        </p>

                        <p
                            id="sortino-ratio"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            Return earned per unit of downside risk.
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Beta
                        </p>

                        <p
                            id="beta"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            Sensitivity relative to the benchmark.
                        </p>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Daily Return Comparison
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Portfolio and benchmark returns by period.
                        </p>
                    </div>

                    <div class="mt-6 h-80">
                        <canvas id="risk-chart"></canvas>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Risk Findings
                        </h3>

                        <div
                            id="risk-flags"
                            class="mt-5 space-y-3"
                        ></div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Data Quality
                        </h3>

                        <div
                            id="risk-warnings"
                            class="mt-5 space-y-3"
                        ></div>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Observation Summary
                    </h3>

                    <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <dt class="text-gray-500">
                                Positive days
                            </dt>

                            <dd
                                id="positive-days"
                                class="mt-1 text-xl font-semibold text-gray-900"
                            >
                                —
                            </dd>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-4">
                            <dt class="text-gray-500">
                                Negative days
                            </dt>

                            <dd
                                id="negative-days"
                                class="mt-1 text-xl font-semibold text-gray-900"
                            >
                                —
                            </dd>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-4">
                            <dt class="text-gray-500">
                                Flat days
                            </dt>

                            <dd
                                id="flat-days"
                                class="mt-1 text-xl font-semibold text-gray-900"
                            >
                                —
                            </dd>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-4">
                            <dt class="text-gray-500">
                                Benchmark matches
                            </dt>

                            <dd
                                id="aligned-return-count"
                                class="mt-1 text-xl font-semibold text-gray-900"
                            >
                                —
                            </dd>
                        </div>
                    </dl>
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

            const riskLevelLabel = (level) => {
                const labels = {
                    very_low: 'Very Low',
                    low: 'Low',
                    moderate: 'Moderate',
                    high: 'High',
                    very_high: 'Very High',
                };

                return labels[level] ?? 'Unknown';
            };

            const riskLevelClasses = (level) => {
                const classes = {
                    very_low: 'bg-green-100 text-green-800',
                    low: 'bg-emerald-100 text-emerald-800',
                    moderate: 'bg-amber-100 text-amber-800',
                    high: 'bg-orange-100 text-orange-800',
                    very_high: 'bg-red-100 text-red-800',
                };

                return classes[level] ?? 'bg-gray-100 text-gray-800';
            };

            const flagClasses = (severity) => {
                const classes = {
                    informational: 'border-blue-200 bg-blue-50 text-blue-800',
                    moderate: 'border-amber-200 bg-amber-50 text-amber-800',
                    high: 'border-red-200 bg-red-50 text-red-800',
                };

                return classes[severity] ??
                    'border-gray-200 bg-gray-50 text-gray-800';
            };

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
                                position: 'top',
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
                                ticks: {
                                    maxTicksLimit: 8,
                                },
                            },

                            y: {
                                title: {
                                    display: true,
                                    text: 'Return',
                                },

                                ticks: {
                                    callback(value) {
                                        return `${value}%`;
                                    },
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
                        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">
                            No major risk findings detected.
                        </div>
                    `;

                    return;
                }

                flags.forEach((flag) => {
                    const element =
                        document.createElement('div');

                    element.className =
                        `rounded-lg border p-4 ${flagClasses(flag.severity)}`;

                    const title =
                        document.createElement('p');

                    title.className = 'font-semibold';
                    title.textContent = flag.title;

                    const message =
                        document.createElement('p');

                    message.className = 'mt-1 text-sm';
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
                        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">
                            No data-quality warnings detected.
                        </div>
                    `;

                    return;
                }

                warnings.forEach((warning) => {
                    const element =
                        document.createElement('div');

                    element.className =
                        'rounded-lg bg-amber-50 p-3 text-sm text-amber-800';

                    element.textContent = warning.message;

                    container.appendChild(element);
                });
            };

            const loadRisk = async () => {
                loadingState.classList.remove('hidden');
                errorState.classList.add('hidden');
                insufficientState.classList.add('hidden');
                results.classList.add('hidden');

                const formData = new FormData(form);
                const query = new URLSearchParams(formData);

                try {
                    const response = await fetch(
                        `{{ route('analytics.risk.data') }}?${query.toString()}`,
                        {
                            headers: {
                                Accept: 'application/json',
                            },
                        }
                    );

                    const payload = await response.json();

                    if (!response.ok) {
                        const message =
                            payload.message ??
                            'Unable to load risk analytics.';

                        throw new Error(message);
                    }

                    const data = payload.data;

                    if (data.status === 'insufficient_data') {
                        document.getElementById(
                            'insufficient-message'
                        ).textContent =
                            data.message ??
                            'More portfolio history is required.';

                        insufficientState.classList.remove('hidden');
                        return;
                    }

                    const metrics = data.metrics ?? {};
                    const observations = data.observations ?? {};
                    const period = data.period ?? {};

                    document.getElementById(
                        'annualized-volatility'
                    ).textContent = formatPercent(
                        metrics.annualized_volatility
                    );

                    document.getElementById(
                        'maximum-drawdown'
                    ).textContent = formatPercent(
                        metrics.maximum_drawdown
                    );

                    document.getElementById(
                        'downside-deviation'
                    ).textContent = formatPercent(
                        metrics.downside_deviation
                    );

                    document.getElementById(
                        'sharpe-ratio'
                    ).textContent = formatNumber(
                        metrics.sharpe_ratio
                    );

                    document.getElementById(
                        'sortino-ratio'
                    ).textContent = formatNumber(
                        metrics.sortino_ratio
                    );

                    document.getElementById(
                        'beta'
                    ).textContent = formatNumber(
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
                        `inline-flex rounded-full px-3 py-1 text-sm font-semibold ${riskLevelClasses(data.risk_level)}`;

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

                    results.classList.remove('hidden');
                } catch (error) {
                    errorState.textContent = error.message;
                    errorState.classList.remove('hidden');
                } finally {
                    loadingState.classList.add('hidden');
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                loadRisk();
            });

            loadRisk();
        });
    </script>
</x-app-layout>