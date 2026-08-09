<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Trading oversight
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Trading Discipline
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Review turnover, trade frequency, fees, holding periods,
                and potential churning concerns.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <x-analytics.filter-panel title="Trading analysis controls">
                <form
                    id="trading-form"
                    class="grid gap-5 md:grid-cols-3"
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

                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Analyze Trading
                        </button>
                    </div>
                </form>
            </x-analytics.filter-panel>

            <div
                id="loading-state"
                class="hidden rounded-2xl border border-slate-800 bg-slate-900 p-8 text-center text-sm text-slate-400"
            >
                Analyzing trading activity…
            </div>

            <div
                id="error-state"
                class="hidden rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5 text-sm text-red-300"
            ></div>

            <div id="insufficient-state" class="hidden">
                <x-analytics.message-card
                    tone="warning"
                    title="No trading activity found"
                >
                    <p id="insufficient-message"></p>
                </x-analytics.message-card>
            </div>

            <div id="results" class="hidden space-y-6">

                <x-analytics.panel>
                    <div class="flex items-center justify-between gap-6">
                        <div>
                            <p class="text-sm text-slate-500">
                                Trading risk level
                            </p>

                            <span
                                id="risk-level"
                                class="mt-3 inline-flex rounded-full border px-3 py-1 text-sm font-semibold"
                            >
                                —
                            </span>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-slate-600">
                                Transactions reviewed
                            </p>

                            <p
                                id="transaction-count"
                                class="mt-1 text-3xl font-semibold text-white"
                            >
                                —
                            </p>
                        </div>
                    </div>
                </x-analytics.panel>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['Turnover rate', 'turnover-rate'],
                        ['Trade count', 'trade-count'],
                        ['Trading fees', 'fees'],
                        ['Fee rate', 'fee-rate'],
                        ['Round trips', 'round-trip-count'],
                        ['Short-term round trips', 'short-term-round-trip-count'],
                        ['Average holding period', 'average-holding-period'],
                        ['Round-trip fees', 'round-trip-fees'],
                    ] as [$label, $id])
                        <x-analytics.metric-card :label="$label">
                            <span id="{{ $id }}">—</span>
                        </x-analytics.metric-card>
                    @endforeach
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <x-analytics.panel title="Trading Summary">
                        <dl class="space-y-4 text-sm">
                            @foreach ([
                                ['Purchases', 'buy-amount'],
                                ['Sales', 'sell-amount'],
                                ['Turnover amount', 'turnover-amount'],
                                ['Average portfolio value', 'average-portfolio-value'],
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

                    <x-analytics.panel title="Findings">
                        <div
                            id="trading-flags"
                            class="space-y-3"
                        ></div>
                    </x-analytics.panel>
                </div>

                <div id="round-trip-section" class="hidden">
                    <x-analytics.panel
                        title="Matched Round-Trip Trades"
                        subtitle="Buys and sells matched using FIFO lot accounting."
                    >
                        <div class="mb-5 text-right text-sm text-slate-500">
                            Realized result:
                            <span
                                id="round-trip-realized-result"
                                class="font-semibold text-white"
                            >
                                —
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-y border-slate-800 bg-slate-950">
                                    <tr>
                                        @foreach ([
                                            'Security',
                                            'Buy date',
                                            'Sell date',
                                            'Quantity',
                                            'Holding period',
                                            'Fees',
                                            'Gain / loss',
                                        ] as $heading)
                                            <th
                                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600"
                                            >
                                                {{ $heading }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody
                                    id="round-trip-table-body"
                                    class="divide-y divide-slate-800"
                                ></tbody>
                            </table>
                        </div>
                    </x-analytics.panel>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('trading-form');
            const results = document.getElementById('results');
            const loadingState = document.getElementById('loading-state');
            const errorState = document.getElementById('error-state');
            const insufficientState = document.getElementById('insufficient-state');

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

            const formatDays = value => {
                if (value == null) {
                    return '—';
                }

                const rounded =
                    Math.round(value);

                return `${rounded} ${rounded === 1 ? 'day' : 'days'}`;
            };

            const formatQuantity = value =>
                value == null
                    ? '—'
                    : new Intl.NumberFormat(
                        'en-US',
                        {
                            maximumFractionDigits: 8,
                        }
                    ).format(value);

            const riskLabel = value => ({
                low: 'Low',
                moderate: 'Moderate',
                high: 'High',
                very_high: 'Very High',
            })[value] ?? 'Unknown';

            const riskClasses = value => ({
                low: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
                moderate: 'border-amber-500/20 bg-amber-500/10 text-amber-300',
                high: 'border-orange-500/20 bg-orange-500/10 text-orange-300',
                very_high: 'border-red-500/20 bg-red-500/10 text-red-300',
            })[value] ?? 'border-slate-700 bg-slate-800 text-slate-300';

            const flagClasses = severity => ({
                informational: 'border-blue-500/20 bg-blue-500/[0.07] text-blue-300',
                moderate: 'border-amber-500/20 bg-amber-500/[0.07] text-amber-300',
                high: 'border-red-500/20 bg-red-500/[0.07] text-red-300',
            })[severity] ?? 'border-slate-700 bg-slate-800 text-slate-300';

            const renderFlags = flags => {
                const container =
                    document.getElementById(
                        'trading-flags'
                    );

                container.innerHTML = '';

                if (!flags || flags.length === 0) {
                    container.innerHTML = `
                        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.07] p-4 text-sm text-emerald-300">
                            No major trading findings detected.
                        </div>
                    `;

                    return;
                }

                flags.forEach(flag => {
                    const element =
                        document.createElement(
                            'div'
                        );

                    element.className =
                        `rounded-xl border p-4 ${flagClasses(flag.severity)}`;

                    element.innerHTML = `
                        <p class="font-semibold">
                            ${flag.title}
                        </p>

                        <p class="mt-1 text-sm leading-6 text-slate-400">
                            ${flag.message}
                        </p>
                    `;

                    container.appendChild(
                        element
                    );
                });
            };

            const renderRoundTrips = analysis => {
                const section =
                    document.getElementById(
                        'round-trip-section'
                    );

                const tableBody =
                    document.getElementById(
                        'round-trip-table-body'
                    );

                const roundTrips =
                    analysis?.round_trips ?? [];

                const metrics =
                    analysis?.metrics ?? {};

                document.getElementById(
                    'round-trip-count'
                ).textContent =
                    metrics.round_trip_count ?? 0;

                document.getElementById(
                    'short-term-round-trip-count'
                ).textContent =
                    metrics.short_term_round_trip_count
                    ?? 0;

                document.getElementById(
                    'average-holding-period'
                ).textContent =
                    formatDays(
                        metrics.average_holding_period_days
                    );

                document.getElementById(
                    'round-trip-fees'
                ).textContent =
                    formatCurrency(
                        metrics.total_round_trip_fees
                    );

                document.getElementById(
                    'round-trip-realized-result'
                ).textContent =
                    formatCurrency(
                        metrics.total_realized_gain_loss
                    );

                tableBody.innerHTML = '';

                if (roundTrips.length === 0) {
                    section.classList.add(
                        'hidden'
                    );

                    return;
                }

                roundTrips.forEach(roundTrip => {
                    const row =
                        document.createElement(
                            'tr'
                        );

                    const resultClass =
                        roundTrip.realized_gain_loss >= 0
                            ? 'text-emerald-300'
                            : 'text-red-300';

                    row.innerHTML = `
                        <td class="whitespace-nowrap px-4 py-4 text-slate-200">
                            Security #${roundTrip.security_id}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-slate-500">
                            ${roundTrip.buy_date}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-slate-500">
                            ${roundTrip.sell_date}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right text-slate-300">
                            ${formatQuantity(roundTrip.quantity)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right text-slate-300">
                            ${formatDays(roundTrip.holding_period_days)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right text-slate-300">
                            ${formatCurrency(roundTrip.allocated_fees)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right font-semibold ${resultClass}">
                            ${formatCurrency(roundTrip.realized_gain_loss)}
                        </td>
                    `;

                    tableBody.appendChild(row);
                });

                section.classList.remove(
                    'hidden'
                );
            };

            const loadTrading = async () => {
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
                        `{{ route('analytics.trading-discipline.data') }}?${query.toString()}`,
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
                            'Unable to load trading analytics.'
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
                            'No qualifying trading activity was found.';

                        insufficientState.classList.remove(
                            'hidden'
                        );

                        return;
                    }

                    const metrics =
                        data.metrics ?? {};

                    const summary =
                        data.summary ?? {};

                    const roundTripAnalysis =
                        data.round_trip_analysis
                        ?? {};

                    const badge =
                        document.getElementById(
                            'risk-level'
                        );

                    badge.textContent =
                        riskLabel(
                            data.risk_level
                        );

                    badge.className =
                        `mt-3 inline-flex rounded-full border px-3 py-1 text-sm font-semibold ${riskClasses(data.risk_level)}`;

                    document.getElementById(
                        'transaction-count'
                    ).textContent =
                        summary.transaction_count ?? 0;

                    document.getElementById(
                        'turnover-rate'
                    ).textContent =
                        formatPercent(
                            metrics.turnover_rate
                        );

                    document.getElementById(
                        'trade-count'
                    ).textContent =
                        metrics.trade_count ?? 0;

                    document.getElementById(
                        'fees'
                    ).textContent =
                        formatCurrency(
                            metrics.fees
                        );

                    document.getElementById(
                        'fee-rate'
                    ).textContent =
                        formatPercent(
                            metrics.fee_rate
                        );

                    document.getElementById(
                        'buy-amount'
                    ).textContent =
                        formatCurrency(
                            metrics.buy_amount
                        );

                    document.getElementById(
                        'sell-amount'
                    ).textContent =
                        formatCurrency(
                            metrics.sell_amount
                        );

                    document.getElementById(
                        'turnover-amount'
                    ).textContent =
                        formatCurrency(
                            metrics.turnover_amount
                        );

                    document.getElementById(
                        'average-portfolio-value'
                    ).textContent =
                        formatCurrency(
                            summary.average_portfolio_value
                        );

                    renderFlags(
                        data.flags ?? []
                    );

                    renderRoundTrips(
                        roundTripAnalysis
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
                    loadTrading();
                }
            );

            loadTrading();
        });
    </script>
</x-app-layout>