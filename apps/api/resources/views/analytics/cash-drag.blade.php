<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Cash Drag Analytics
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Measure idle cash, excess allocation, and estimated missed benchmark growth.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form
                    id="cash-drag-form"
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
                            for="target_cash_percent"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Target cash allocation
                        </label>

                        <input
                            id="target_cash_percent"
                            name="target_cash_percent"
                            type="number"
                            min="0"
                            max="1"
                            step="0.01"
                            value="0.05"
                            class="mt-1 block w-full rounded-lg border-gray-300"
                        >

                        <p class="mt-1 text-xs text-gray-500">
                            Example: 0.05 means 5%.
                        </p>
                    </div>

                    <div class="md:col-span-4">
                        <button
                            type="submit"
                            class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            Analyze Cash Drag
                        </button>
                    </div>
                </form>
            </div>

            <div
                id="loading-state"
                class="hidden rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-200"
            >
                <p class="text-sm text-gray-600">
                    Analyzing cash allocations…
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
                    More cash history is needed
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
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">
                                Cash drag score
                            </p>

                            <div class="mt-2 flex items-center gap-3">
                                <span
                                    id="cash-score"
                                    class="text-3xl font-bold text-gray-900"
                                >
                                    —
                                </span>

                                <span
                                    id="cash-rating"
                                    class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                >
                                    —
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-sm text-gray-500">
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
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Current cash
                        </p>

                        <p
                            id="current-cash"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Current cash %
                        </p>

                        <p
                            id="current-cash-percent"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Average cash %
                        </p>

                        <p
                            id="average-cash-percent"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Estimated cash drag
                        </p>

                        <p
                            id="opportunity-cost"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Cash Allocation History
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Cash as a percentage of total portfolio value.
                        </p>
                    </div>

                    <div class="mt-6 h-80">
                        <canvas id="cash-chart"></canvas>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Opportunity Cost
                        </h3>

                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Average cash
                                </dt>

                                <dd
                                    id="average-cash"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Target cash amount
                                </dt>

                                <dd
                                    id="target-cash-amount"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Excess cash
                                </dt>

                                <dd
                                    id="excess-cash"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Benchmark return
                                </dt>

                                <dd
                                    id="benchmark-return"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Findings
                        </h3>

                        <div
                            id="cash-flags"
                            class="mt-5 space-y-3"
                        ></div>

                        <h3 class="mt-6 text-lg font-semibold text-gray-900">
                            Data Quality
                        </h3>

                        <div
                            id="cash-warnings"
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
            const form = document.getElementById('cash-drag-form');
            const results = document.getElementById('results');
            const loadingState = document.getElementById('loading-state');
            const errorState = document.getElementById('error-state');
            const insufficientState = document.getElementById('insufficient-state');

            let cashChart = null;

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

            const ratingLabel = (rating) => {
                const labels = {
                    excellent: 'Excellent',
                    good: 'Good',
                    moderate: 'Moderate',
                    poor: 'Poor',
                    critical: 'Critical',
                };

                return labels[rating] ?? 'Unknown';
            };

            const ratingClasses = (rating) => {
                const classes = {
                    excellent: 'bg-green-100 text-green-800',
                    good: 'bg-emerald-100 text-emerald-800',
                    moderate: 'bg-amber-100 text-amber-800',
                    poor: 'bg-orange-100 text-orange-800',
                    critical: 'bg-red-100 text-red-800',
                };

                return classes[rating] ??
                    'bg-gray-100 text-gray-800';
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

            const renderMessages = (
                containerId,
                messages,
                emptyMessage,
                isWarning = false
            ) => {
                const container =
                    document.getElementById(containerId);

                container.innerHTML = '';

                if (!messages || messages.length === 0) {
                    container.innerHTML = `
                        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">
                            ${emptyMessage}
                        </div>
                    `;

                    return;
                }

                messages.forEach((message) => {
                    const element =
                        document.createElement('div');

                    if (isWarning) {
                        element.className =
                            'rounded-lg bg-amber-50 p-3 text-sm text-amber-800';

                        element.textContent =
                            message.message;

                        container.appendChild(element);
                        return;
                    }

                    element.className =
                        `rounded-lg border p-4 ${flagClasses(message.severity)}`;

                    const title =
                        document.createElement('p');

                    title.className = 'font-semibold';
                    title.textContent = message.title;

                    const body =
                        document.createElement('p');

                    body.className = 'mt-1 text-sm';
                    body.textContent = message.message;

                    element.appendChild(title);
                    element.appendChild(body);

                    container.appendChild(element);
                });
            };

            const renderCashChart = (history) => {
                const canvas =
                    document.getElementById('cash-chart');

                if (!canvas || !window.Chart) {
                    return;
                }

                if (cashChart) {
                    cashChart.destroy();
                }

                cashChart = new Chart(canvas, {
                    type: 'line',

                    data: {
                        labels: history.map(
                            (point) => point.date
                        ),

                        datasets: [
                            {
                                label: 'Cash allocation',
                                data: history.map(
                                    (point) =>
                                        point.cash_percent * 100
                                ),
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                tension: 0.2,
                            },
                        ],
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },

                        scales: {
                            x: {
                                ticks: {
                                    maxTicksLimit: 8,
                                },
                            },

                            y: {
                                beginAtZero: true,

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

            const loadCashDrag = async () => {
                loadingState.classList.remove('hidden');
                errorState.classList.add('hidden');
                insufficientState.classList.add('hidden');
                results.classList.add('hidden');

                const query = new URLSearchParams(
                    new FormData(form)
                );

                try {
                    const response = await fetch(
                        `{{ route('analytics.cash-drag.data') }}?${query.toString()}`,
                        {
                            headers: {
                                Accept: 'application/json',
                            },
                        }
                    );

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.message ??
                            'Unable to load cash drag analytics.'
                        );
                    }

                    const data = payload.data;

                    if (data.status === 'insufficient_data') {
                        document.getElementById(
                            'insufficient-message'
                        ).textContent =
                            data.message ??
                            'No cash history was available.';

                        insufficientState.classList.remove('hidden');
                        return;
                    }

                    const allocation =
                        data.allocation?.metrics ?? {};

                    const opportunity =
                        data.opportunity?.metrics ?? {};

                    const score = data.score ?? {};

                    document.getElementById(
                        'cash-score'
                    ).textContent =
                        score.score ?? '—';

                    const ratingBadge =
                        document.getElementById(
                            'cash-rating'
                        );

                    ratingBadge.textContent =
                        ratingLabel(score.rating);

                    ratingBadge.className =
                        `inline-flex rounded-full px-3 py-1 text-sm font-semibold ${ratingClasses(score.rating)}`;

                    document.getElementById(
                        'benchmark-name'
                    ).textContent =
                        data.benchmark?.name ??
                        'No benchmark';

                    document.getElementById(
                        'current-cash'
                    ).textContent =
                        formatCurrency(
                            allocation.current_cash
                        );

                    document.getElementById(
                        'current-cash-percent'
                    ).textContent =
                        formatPercent(
                            allocation.current_cash_percent
                        );

                    document.getElementById(
                        'average-cash-percent'
                    ).textContent =
                        formatPercent(
                            allocation.average_cash_percent
                        );

                    document.getElementById(
                        'opportunity-cost'
                    ).textContent =
                        formatCurrency(
                            opportunity.estimated_opportunity_cost
                        );

                    document.getElementById(
                        'average-cash'
                    ).textContent =
                        formatCurrency(
                            opportunity.average_cash
                        );

                    document.getElementById(
                        'target-cash-amount'
                    ).textContent =
                        formatCurrency(
                            opportunity.target_cash_amount
                        );

                    document.getElementById(
                        'excess-cash'
                    ).textContent =
                        formatCurrency(
                            opportunity.excess_cash
                        );

                    document.getElementById(
                        'benchmark-return'
                    ).textContent =
                        formatPercent(
                            opportunity.benchmark_return
                        );

                    renderCashChart(
                        data.allocation?.history ?? []
                    );

                    renderMessages(
                        'cash-flags',
                        data.flags ?? [],
                        'No major cash-drag findings detected.'
                    );

                    renderMessages(
                        'cash-warnings',
                        data.warnings ?? [],
                        'No data-quality warnings detected.',
                        true
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
                loadCashDrag();
            });

            loadCashDrag();
        });
    </script>
</x-app-layout>