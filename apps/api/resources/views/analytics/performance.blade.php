<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Portfolio returns
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Performance Analytics
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Compare portfolio results with a selected market benchmark.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <x-analytics.filter-panel
                title="Performance analysis controls"
            >
                <form
                    id="performance-form"
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
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
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
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
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
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
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
                            class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Analyze Performance
                        </button>
                    </div>
                </form>
            </x-analytics.filter-panel>

            <div
                id="loading-state"
                class="hidden rounded-2xl border border-slate-800 bg-slate-900 p-8 text-center text-sm text-slate-400"
            >
                Calculating performance…
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
                    title="More performance history is needed"
                >
                    <p id="insufficient-message"></p>
                </x-analytics.message-card>
            </div>

            <div
                id="results"
                class="hidden space-y-6"
            >
                <x-analytics.panel
                    title="Portfolio vs Benchmark"
                    subtitle="Indexed growth beginning at 100."
                >
                    <div class="mb-5 flex justify-end">
                        <div
                            class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3"
                        >
                            <p class="text-xs text-slate-600">
                                Benchmark
                            </p>

                            <p
                                id="benchmark-name"
                                class="mt-1 text-sm font-semibold text-slate-200"
                            >
                                —
                            </p>
                        </div>
                    </div>

                    <div class="h-80">
                        <canvas id="performance-chart"></canvas>
                    </div>
                </x-analytics.panel>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <x-analytics.metric-card label="Portfolio return">
                        <span id="portfolio-return">—</span>
                    </x-analytics.metric-card>

                    <x-analytics.metric-card label="Benchmark return">
                        <span id="benchmark-return">—</span>
                    </x-analytics.metric-card>

                    <x-analytics.metric-card label="Relative performance">
                        <span id="alpha">—</span>
                    </x-analytics.metric-card>

                    <x-analytics.metric-card label="Opportunity cost">
                        <span id="opportunity-cost">—</span>
                    </x-analytics.metric-card>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <x-analytics.panel title="Portfolio Summary">
                        <dl class="space-y-4 text-sm">
                            @foreach ([
                                ['Beginning value', 'beginning-value'],
                                ['Ending value', 'ending-value'],
                                ['Net cash flow', 'net-cash-flow'],
                                ['Valuation points', 'valuation-count'],
                            ] as [$label, $id])
                                <div
                                    class="flex items-center justify-between border-b border-slate-800 pb-4 last:border-0 last:pb-0"
                                >
                                    <dt class="text-slate-500">
                                        {{ $label }}
                                    </dt>

                                    <dd
                                        id="{{ $id }}"
                                        class="font-semibold text-slate-200"
                                    >
                                        —
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-analytics.panel>

                    <x-analytics.panel title="Data Quality">
                        <div
                            id="warnings"
                            class="space-y-3"
                        ></div>
                    </x-analytics.panel>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form =
                document.getElementById('performance-form');

            const results =
                document.getElementById('results');

            const loadingState =
                document.getElementById('loading-state');

            const errorState =
                document.getElementById('error-state');

            const insufficientState =
                document.getElementById('insufficient-state');

            let performanceChart = null;

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

            const formatCurrency = (value) => {
                if (value === null || value === undefined) {
                    return '—';
                }

                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    maximumFractionDigits: 0,
                }).format(value);
            };

            const renderPerformanceChart = (
                chartData,
                benchmarkName
            ) => {
                const canvas =
                    document.getElementById(
                        'performance-chart'
                    );

                if (!canvas || !window.Chart) {
                    return;
                }

                if (performanceChart) {
                    performanceChart.destroy();
                }

                performanceChart = new Chart(canvas, {
                    type: 'line',

                    data: {
                        labels: chartData.map(
                            point => point.date
                        ),

                        datasets: [
                            {
                                label: 'Portfolio',

                                data: chartData.map(
                                    point =>
                                        point.portfolio_index
                                ),

                                borderColor: '#3b82f6',
                                backgroundColor: '#3b82f6',
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                tension: 0.2,
                                spanGaps: true,
                            },

                            {
                                label:
                                    benchmarkName ??
                                    'Benchmark',

                                data: chartData.map(
                                    point =>
                                        point.benchmark_index
                                ),

                                borderColor: '#94a3b8',
                                backgroundColor: '#94a3b8',
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                tension: 0.2,
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
                                },

                                title: {
                                    display: true,
                                    text: 'Indexed value',
                                    color: '#64748b',
                                },
                            },
                        },
                    },
                });
            };

            const loadPerformance = async () => {
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
                        `{{ route('analytics.performance.data') }}?${query.toString()}`,
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
                            'Unable to load performance analytics.'
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
                            data.message;

                        insufficientState.classList.remove(
                            'hidden'
                        );

                        return;
                    }

                    renderPerformanceChart(
                        data.chart ?? [],
                        data.benchmark?.name
                    );

                    document.getElementById(
                        'benchmark-name'
                    ).textContent =
                        data.benchmark?.name ??
                        'No benchmark';

                    document.getElementById(
                        'portfolio-return'
                    ).textContent =
                        formatPercent(
                            data.portfolio.return
                        );

                    document.getElementById(
                        'benchmark-return'
                    ).textContent =
                        formatPercent(
                            data.benchmark.return
                        );

                    document.getElementById(
                        'alpha'
                    ).textContent =
                        formatPercent(
                            data.comparison.alpha
                        );

                    document.getElementById(
                        'opportunity-cost'
                    ).textContent =
                        formatCurrency(
                            data.comparison
                                .opportunity_cost
                        );

                    document.getElementById(
                        'beginning-value'
                    ).textContent =
                        formatCurrency(
                            data.portfolio
                                .beginning_value
                        );

                    document.getElementById(
                        'ending-value'
                    ).textContent =
                        formatCurrency(
                            data.portfolio
                                .ending_value
                        );

                    document.getElementById(
                        'net-cash-flow'
                    ).textContent =
                        formatCurrency(
                            data.portfolio
                                .net_cash_flow
                        );

                    document.getElementById(
                        'valuation-count'
                    ).textContent =
                        data.portfolio
                            .valuation_count;

                    const warningsContainer =
                        document.getElementById(
                            'warnings'
                        );

                    warningsContainer.innerHTML = '';

                    const warnings =
                        data.data_quality?.warnings
                        ?? [];

                    if (warnings.length === 0) {
                        warningsContainer.innerHTML = `
                            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.07] p-4 text-sm text-emerald-300">
                                No data-quality warnings detected.
                            </div>
                        `;
                    } else {
                        warnings.forEach(
                            warning => {
                                const element =
                                    document.createElement(
                                        'div'
                                    );

                                element.className =
                                    'rounded-xl border border-amber-500/20 bg-amber-500/[0.07] p-4 text-sm leading-6 text-amber-300';

                                element.textContent =
                                    warning.message;

                                warningsContainer
                                    .appendChild(
                                        element
                                    );
                            }
                        );
                    }

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
                event => {
                    event.preventDefault();
                    loadPerformance();
                }
            );

            loadPerformance();
        });
    </script>
</x-app-layout>