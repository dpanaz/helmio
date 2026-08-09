<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Cash management
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Cash Drag Analytics
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Measure idle cash, excess allocation, and estimated missed
                benchmark growth.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <x-analytics.filter-panel title="Cash analysis controls">
                <form
                    id="cash-drag-form"
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

                    <div>
                        <label
                            for="target_cash_percent"
                            class="block text-sm font-medium text-slate-400"
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
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                        >

                        <p class="mt-2 text-xs text-slate-600">
                            Example: 0.05 means 5%.
                        </p>
                    </div>

                    <div class="md:col-span-4">
                        <button
                            type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Analyze Cash Drag
                        </button>
                    </div>
                </form>
            </x-analytics.filter-panel>

            <div
                id="loading-state"
                class="hidden rounded-2xl border border-slate-800 bg-slate-900 p-8 text-center text-sm text-slate-400"
            >
                Analyzing cash allocations…
            </div>

            <div
                id="error-state"
                class="hidden rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5 text-sm text-red-300"
            ></div>

            <div id="insufficient-state" class="hidden">
                <x-analytics.message-card
                    tone="warning"
                    title="More cash history is needed"
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
                            <p class="text-sm text-slate-500">
                                Cash drag score
                            </p>

                            <div class="mt-2 flex items-center gap-3">
                                <span
                                    id="cash-score"
                                    class="text-4xl font-semibold text-white"
                                >
                                    —
                                </span>

                                <span
                                    id="cash-rating"
                                    class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold"
                                >
                                    —
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-slate-600">
                                Benchmark
                            </p>

                            <p
                                id="benchmark-name"
                                class="mt-1 font-semibold text-slate-200"
                            >
                                —
                            </p>
                        </div>
                    </div>
                </x-analytics.panel>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['Current cash', 'current-cash'],
                        ['Current cash %', 'current-cash-percent'],
                        ['Average cash %', 'average-cash-percent'],
                        ['Estimated cash drag', 'opportunity-cost'],
                    ] as [$label, $id])
                        <x-analytics.metric-card :label="$label">
                            <span id="{{ $id }}">—</span>
                        </x-analytics.metric-card>
                    @endforeach
                </div>

                <x-analytics.panel
                    title="Cash Allocation History"
                    subtitle="Cash as a percentage of total portfolio value."
                >
                    <div class="h-80">
                        <canvas id="cash-chart"></canvas>
                    </div>
                </x-analytics.panel>

                <div class="grid gap-6 lg:grid-cols-2">
                    <x-analytics.panel title="Opportunity Cost">
                        <dl class="space-y-4 text-sm">
                            @foreach ([
                                ['Average cash', 'average-cash'],
                                ['Target cash amount', 'target-cash-amount'],
                                ['Excess cash', 'excess-cash'],
                                ['Benchmark return', 'benchmark-return'],
                            ] as [$label, $id])
                                <div
                                    class="flex justify-between border-b border-slate-800 pb-4 last:border-0 last:pb-0"
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

                    <x-analytics.panel title="Findings & Data Quality">
                        <div
                            id="cash-flags"
                            class="space-y-3"
                        ></div>

                        <h4
                            class="mt-7 border-t border-slate-800 pt-6 text-sm font-semibold text-white"
                        >
                            Data Quality
                        </h4>

                        <div
                            id="cash-warnings"
                            class="mt-4 space-y-3"
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
                document.getElementById('cash-drag-form');

            const results =
                document.getElementById('results');

            const loadingState =
                document.getElementById('loading-state');

            const errorState =
                document.getElementById('error-state');

            const insufficientState =
                document.getElementById('insufficient-state');

            let cashChart = null;

            const formatPercent = value =>
                value == null
                    ? '—'
                    : new Intl.NumberFormat('en-US', {
                        style: 'percent',
                        minimumFractionDigits: 1,
                        maximumFractionDigits: 2,
                    }).format(value);

            const formatCurrency = value =>
                value == null
                    ? '—'
                    : new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                        maximumFractionDigits: 0,
                    }).format(value);

            const ratingLabel = rating => ({
                excellent: 'Excellent',
                good: 'Good',
                moderate: 'Moderate',
                poor: 'Poor',
                critical: 'Critical',
            })[rating] ?? 'Unknown';

            const ratingClasses = rating => ({
                excellent: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
                good: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
                moderate: 'border-amber-500/20 bg-amber-500/10 text-amber-300',
                poor: 'border-orange-500/20 bg-orange-500/10 text-orange-300',
                critical: 'border-red-500/20 bg-red-500/10 text-red-300',
            })[rating] ?? 'border-slate-700 bg-slate-800 text-slate-300';

            const flagClasses = severity => ({
                informational: 'border-blue-500/20 bg-blue-500/[0.07] text-blue-300',
                moderate: 'border-amber-500/20 bg-amber-500/[0.07] text-amber-300',
                high: 'border-red-500/20 bg-red-500/[0.07] text-red-300',
            })[severity] ?? 'border-slate-700 bg-slate-800 text-slate-300';

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
                        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.07] p-4 text-sm text-emerald-300">
                            ${emptyMessage}
                        </div>
                    `;

                    return;
                }

                messages.forEach(message => {
                    const element =
                        document.createElement('div');

                    if (isWarning) {
                        element.className =
                            'rounded-xl border border-amber-500/20 bg-amber-500/[0.07] p-4 text-sm text-amber-300';

                        element.textContent =
                            message.message;

                        container.appendChild(
                            element
                        );

                        return;
                    }

                    element.className =
                        `rounded-xl border p-4 ${flagClasses(message.severity)}`;

                    element.innerHTML = `
                        <p class="font-semibold">
                            ${message.title}
                        </p>

                        <p class="mt-1 text-sm leading-6 text-slate-400">
                            ${message.message}
                        </p>
                    `;

                    container.appendChild(
                        element
                    );
                });
            };

            const renderCashChart = history => {
                const canvas =
                    document.getElementById(
                        'cash-chart'
                    );

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
                            point => point.date
                        ),

                        datasets: [
                            {
                                label: 'Cash allocation',

                                data: history.map(
                                    point =>
                                        point.cash_percent * 100
                                ),

                                borderColor: '#3b82f6',
                                backgroundColor: '#3b82f6',
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
                                beginAtZero: true,

                                grid: {
                                    color: 'rgba(51,65,85,.35)',
                                },

                                ticks: {
                                    color: '#64748b',

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

                const query =
                    new URLSearchParams(
                        new FormData(form)
                    );

                try {
                    const response = await fetch(
                        `{{ route('analytics.cash-drag.data') }}?${query.toString()}`,
                        {
                            headers: {
                                Accept:
                                    'application/json',
                            },
                        }
                    );

                    const payload =
                        await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.message ??
                            'Unable to load cash drag analytics.'
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
                            'No cash history was available.';

                        insufficientState.classList.remove(
                            'hidden'
                        );

                        return;
                    }

                    const allocation =
                        data.allocation?.metrics
                        ?? {};

                    const opportunity =
                        data.opportunity?.metrics
                        ?? {};

                    const score =
                        data.score ?? {};

                    document.getElementById(
                        'cash-score'
                    ).textContent =
                        score.score ?? '—';

                    const ratingBadge =
                        document.getElementById(
                            'cash-rating'
                        );

                    ratingBadge.textContent =
                        ratingLabel(
                            score.rating
                        );

                    ratingBadge.className =
                        `inline-flex rounded-full border px-3 py-1 text-sm font-semibold ${ratingClasses(score.rating)}`;

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
                    loadCashDrag();
                }
            );

            loadCashDrag();
        });
    </script>
</x-app-layout>