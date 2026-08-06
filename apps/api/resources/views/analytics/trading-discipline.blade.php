<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Trading Discipline
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Review turnover, trade frequency, fees, and potential churning concerns.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form id="trading-form" class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start date</label>
                        <input id="start_date" name="start_date" type="date" value="{{ now()->subYear()->format('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300" required>
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End date</label>
                        <input id="end_date" name="end_date" type="date" value="{{ now()->format('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300" required>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">Analyze Trading</button>
                    </div>
                </form>
            </div>

            <div id="loading-state" class="hidden rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-200">
                <p class="text-sm text-gray-600">Analyzing trading activity…</p>
            </div>

            <div id="error-state" class="hidden rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"></div>

            <div id="insufficient-state" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-6">
                <h3 class="font-semibold text-amber-900">No trading activity found</h3>
                <p id="insufficient-message" class="mt-2 text-sm text-amber-800"></p>
            </div>

            <div id="results" class="hidden space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Trading risk level</p>
                            <span id="risk-level" class="mt-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold">—</span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Transactions reviewed</p>
                            <p id="transaction-count" class="mt-1 text-2xl font-semibold text-gray-900">—</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><p class="text-sm text-gray-500">Turnover rate</p><p id="turnover-rate" class="mt-2 text-3xl font-bold text-gray-900">—</p></div>
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><p class="text-sm text-gray-500">Trade count</p><p id="trade-count" class="mt-2 text-3xl font-bold text-gray-900">—</p></div>
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><p class="text-sm text-gray-500">Trading fees</p><p id="fees" class="mt-2 text-3xl font-bold text-gray-900">—</p></div>
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><p class="text-sm text-gray-500">Fee rate</p><p id="fee-rate" class="mt-2 text-3xl font-bold text-gray-900">—</p></div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><p class="text-sm text-gray-500">Round trips</p><p id="round-trip-count" class="mt-2 text-3xl font-bold text-gray-900">—</p></div>
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><p class="text-sm text-gray-500">Short-term round trips</p><p id="short-term-round-trip-count" class="mt-2 text-3xl font-bold text-gray-900">—</p><p class="mt-1 text-xs text-gray-500">Positions held 30 days or less.</p></div>
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><p class="text-sm text-gray-500">Average holding period</p><p id="average-holding-period" class="mt-2 text-3xl font-bold text-gray-900">—</p></div>
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><p class="text-sm text-gray-500">Round-trip fees</p><p id="round-trip-fees" class="mt-2 text-3xl font-bold text-gray-900">—</p></div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Trading Summary</h3>
                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Purchases</dt><dd id="buy-amount" class="font-medium text-gray-900">—</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Sales</dt><dd id="sell-amount" class="font-medium text-gray-900">—</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Turnover amount</dt><dd id="turnover-amount" class="font-medium text-gray-900">—</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Average portfolio value</dt><dd id="average-portfolio-value" class="font-medium text-gray-900">—</dd></div>
                        </dl>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Findings</h3>
                        <div id="trading-flags" class="mt-5 space-y-3"></div>
                    </div>
                </div>

                <div id="round-trip-section" class="hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Matched Round-Trip Trades</h3>
                            <p class="mt-1 text-sm text-gray-500">Buys and sells matched using FIFO lot accounting.</p>
                        </div>
                        <div class="text-sm text-gray-500">Realized result: <span id="round-trip-realized-result" class="font-semibold text-gray-900">—</span></div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Security</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Buy date</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Sell date</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Quantity</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Holding period</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Fees</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Gain / loss</th>
                                </tr>
                            </thead>
                            <tbody id="round-trip-table-body" class="divide-y divide-gray-100 bg-white"></tbody>
                        </table>
                    </div>
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

            const formatPercent = (value) => value == null ? '—' : new Intl.NumberFormat('en-US', { style: 'percent', minimumFractionDigits: 1, maximumFractionDigits: 2 }).format(value);
            const formatCurrency = (value) => value == null ? '—' : new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
            const formatDays = (value) => {
                if (value == null) return '—';
                const rounded = Math.round(value);
                return `${rounded} ${rounded === 1 ? 'day' : 'days'}`;
            };
            const formatQuantity = (value) => value == null ? '—' : new Intl.NumberFormat('en-US', { maximumFractionDigits: 8 }).format(value);

            const riskLabel = (value) => ({ low: 'Low', moderate: 'Moderate', high: 'High', very_high: 'Very High' })[value] ?? 'Unknown';
            const riskClasses = (value) => ({ low: 'bg-green-100 text-green-800', moderate: 'bg-amber-100 text-amber-800', high: 'bg-orange-100 text-orange-800', very_high: 'bg-red-100 text-red-800' })[value] ?? 'bg-gray-100 text-gray-800';
            const flagClasses = (severity) => ({ informational: 'border-blue-200 bg-blue-50 text-blue-800', moderate: 'border-amber-200 bg-amber-50 text-amber-800', high: 'border-red-200 bg-red-50 text-red-800' })[severity] ?? 'border-gray-200 bg-gray-50 text-gray-800';

            const renderFlags = (flags) => {
                const container = document.getElementById('trading-flags');
                container.innerHTML = '';
                if (!flags || flags.length === 0) {
                    container.innerHTML = '<div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">No major trading findings detected.</div>';
                    return;
                }
                flags.forEach((flag) => {
                    const element = document.createElement('div');
                    element.className = `rounded-lg border p-4 ${flagClasses(flag.severity)}`;
                    const title = document.createElement('p');
                    title.className = 'font-semibold';
                    title.textContent = flag.title;
                    const message = document.createElement('p');
                    message.className = 'mt-1 text-sm';
                    message.textContent = flag.message;
                    element.append(title, message);
                    container.appendChild(element);
                });
            };

            const renderRoundTrips = (analysis) => {
                const section = document.getElementById('round-trip-section');
                const tableBody = document.getElementById('round-trip-table-body');
                const roundTrips = analysis?.round_trips ?? [];
                const metrics = analysis?.metrics ?? {};

                document.getElementById('round-trip-count').textContent = metrics.round_trip_count ?? 0;
                document.getElementById('short-term-round-trip-count').textContent = metrics.short_term_round_trip_count ?? 0;
                document.getElementById('average-holding-period').textContent = formatDays(metrics.average_holding_period_days);
                document.getElementById('round-trip-fees').textContent = formatCurrency(metrics.total_round_trip_fees);
                document.getElementById('round-trip-realized-result').textContent = formatCurrency(metrics.total_realized_gain_loss);

                tableBody.innerHTML = '';
                if (roundTrips.length === 0) {
                    section.classList.add('hidden');
                    return;
                }

                roundTrips.forEach((roundTrip) => {
                    const row = document.createElement('tr');
                    const resultClass = roundTrip.realized_gain_loss >= 0 ? 'text-green-700' : 'text-red-700';
                    row.innerHTML = `
                        <td class="whitespace-nowrap px-4 py-3 text-gray-900">Security #${roundTrip.security_id}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">${roundTrip.buy_date}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">${roundTrip.sell_date}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-900">${formatQuantity(roundTrip.quantity)}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-900">${formatDays(roundTrip.holding_period_days)}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-900">${formatCurrency(roundTrip.allocated_fees)}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-medium ${resultClass}">${formatCurrency(roundTrip.realized_gain_loss)}</td>`;
                    tableBody.appendChild(row);
                });

                section.classList.remove('hidden');
            };

            const loadTrading = async () => {
                loadingState.classList.remove('hidden');
                errorState.classList.add('hidden');
                insufficientState.classList.add('hidden');
                results.classList.add('hidden');

                const query = new URLSearchParams(new FormData(form));

                try {
                    const response = await fetch(`{{ route('analytics.trading-discipline.data') }}?${query.toString()}`, { headers: { Accept: 'application/json' } });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message ?? 'Unable to load trading analytics.');

                    const data = payload.data;
                    if (data.status === 'insufficient_data') {
                        document.getElementById('insufficient-message').textContent = data.message ?? 'No qualifying trading activity was found.';
                        insufficientState.classList.remove('hidden');
                        return;
                    }

                    const metrics = data.metrics ?? {};
                    const summary = data.summary ?? {};
                    const roundTripAnalysis = data.round_trip_analysis ?? {};

                    const badge = document.getElementById('risk-level');
                    badge.textContent = riskLabel(data.risk_level);
                    badge.className = `mt-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold ${riskClasses(data.risk_level)}`;

                    document.getElementById('transaction-count').textContent = summary.transaction_count ?? 0;
                    document.getElementById('turnover-rate').textContent = formatPercent(metrics.turnover_rate);
                    document.getElementById('trade-count').textContent = metrics.trade_count ?? 0;
                    document.getElementById('fees').textContent = formatCurrency(metrics.fees);
                    document.getElementById('fee-rate').textContent = formatPercent(metrics.fee_rate);
                    document.getElementById('buy-amount').textContent = formatCurrency(metrics.buy_amount);
                    document.getElementById('sell-amount').textContent = formatCurrency(metrics.sell_amount);
                    document.getElementById('turnover-amount').textContent = formatCurrency(metrics.turnover_amount);
                    document.getElementById('average-portfolio-value').textContent = formatCurrency(summary.average_portfolio_value);

                    renderFlags(data.flags ?? []);
                    renderRoundTrips(roundTripAnalysis);
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
                loadTrading();
            });

            loadTrading();
        });
    </script>
</x-app-layout>