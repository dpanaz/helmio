<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    Performance Analytics
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Compare your portfolio against a market benchmark.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form
                    id="performance-form"
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

                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            Analyze Performance
                        </button>
                    </div>
                </form>
            </div>

            <div
                id="loading-state"
                class="hidden rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-200"
            >
                <p class="text-sm text-gray-600">
                    Calculating performance…
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
                    More performance history is needed
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
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Portfolio vs Benchmark
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Indexed growth beginning at 100.
                            </p>
                        </div>

                        <div class="text-sm">
                            <p class="text-gray-500">
                                Benchmark
                            </p>

                            <p
                                id="benchmark-name"
                                class="mt-1 font-semibold text-gray-900"
                            >
                                —
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 h-80">
                        <canvas id="performance-chart"></canvas>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Portfolio return
                        </p>

                        <p
                            id="portfolio-return"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Benchmark return
                        </p>

                        <p
                            id="benchmark-return"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Relative performance
                        </p>

                        <p
                            id="alpha"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Opportunity cost
                        </p>

                        <p
                            id="opportunity-cost"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Portfolio summary
                        </h3>

                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Beginning value
                                </dt>

                                <dd
                                    id="beginning-value"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Ending value
                                </dt>

                                <dd
                                    id="ending-value"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Net cash flow
                                </dt>

                                <dd
                                    id="net-cash-flow"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Valuation points
                                </dt>

                                <dd
                                    id="valuation-count"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Data quality
                        </h3>

                        <div
                            id="warnings"
                            class="mt-5 space-y-3"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('performance-form');
            const results = document.getElementById('results');
            const loadingState = document.getElementById('loading-state');
            const errorState = document.getElementById('error-state');
            const insufficientState = document.getElementById('insufficient-state');

            let performanceChart = null;

            const renderPerformanceChart = (
                chartData,
                benchmarkName
            ) => {
                const canvas = document.getElementById(
                    'performance-chart'
                );

                if (!canvas || !window.Chart) {
                    return;
                }

                const labels = chartData.map(
                    (point) => point.date
                );

                const portfolioValues = chartData.map(
                    (point) => point.portfolio_index
                );

                const benchmarkValues = chartData.map(
                    (point) => point.benchmark_index
                );

                if (performanceChart) {
                    performanceChart.destroy();
                }

                performanceChart = new Chart(canvas, {
                    type: 'line',

                    data: {
                        labels,

                        datasets: [
                            {
                                label: 'Portfolio',
                                data: portfolioValues,
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
                                data: benchmarkValues,
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
                                position: 'top',
                            },

                            tooltip: {
                                callbacks: {
                                    label(context) {
                                        const value =
                                            context.parsed.y;

                                        if (
                                            value === null ||
                                            value === undefined
                                        ) {
                                            return `${context.dataset.label}: —`;
                                        }

                                        return `${context.dataset.label}: ${value.toFixed(2)}`;
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
                                    text: 'Indexed value',
                                },
                            },
                        },
                    },
                });
            };

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

            const loadPerformance = async () => {
                loadingState.classList.remove('hidden');
                errorState.classList.add('hidden');
                insufficientState.classList.add('hidden');
                results.classList.add('hidden');

                const formData = new FormData(form);
                const query = new URLSearchParams(formData);

                try {
                    const response = await fetch(
                        `{{ route('analytics.performance.data') }}?${query.toString()}`,
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
                            'Unable to load performance analytics.';

                        throw new Error(message);
                    }

                    const data = payload.data;

                    if (data.status === 'insufficient_data') {
                        document.getElementById(
                            'insufficient-message'
                        ).textContent = data.message;

                        insufficientState.classList.remove('hidden');
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
                    ).textContent = formatPercent(
                        data.portfolio.return
                    );

                    document.getElementById(
                        'benchmark-return'
                    ).textContent = formatPercent(
                        data.benchmark.return
                    );

                    document.getElementById(
                        'alpha'
                    ).textContent = formatPercent(
                        data.comparison.alpha
                    );

                    document.getElementById(
                        'opportunity-cost'
                    ).textContent = formatCurrency(
                        data.comparison.opportunity_cost
                    );

                    document.getElementById(
                        'beginning-value'
                    ).textContent = formatCurrency(
                        data.portfolio.beginning_value
                    );

                    document.getElementById(
                        'ending-value'
                    ).textContent = formatCurrency(
                        data.portfolio.ending_value
                    );

                    document.getElementById(
                        'net-cash-flow'
                    ).textContent = formatCurrency(
                        data.portfolio.net_cash_flow
                    );

                    document.getElementById(
                        'valuation-count'
                    ).textContent =
                        data.portfolio.valuation_count;

                    const warningsContainer =
                        document.getElementById('warnings');

                    warningsContainer.innerHTML = '';

                    const warnings =
                        data.data_quality?.warnings ?? [];

                    if (warnings.length === 0) {
                        warningsContainer.innerHTML = `
                            <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">
                                No data-quality warnings detected.
                            </div>
                        `;
                    } else {
                        warnings.forEach((warning) => {
                            const element =
                                document.createElement('div');

                            element.className =
                                'rounded-lg bg-amber-50 p-3 text-sm text-amber-800';

                            element.textContent = warning.message;

                            warningsContainer.appendChild(element);
                        });
                    }

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
                loadPerformance();
            });

            loadPerformance();
        });
    </script>
</x-app-layout>