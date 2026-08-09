<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Tax oversight
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Tax Efficiency
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Review realized gains, dividend treatment, wash-sale risk,
                and tax-loss harvesting opportunities.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <x-analytics.filter-panel title="Tax analysis controls">
                <form
                    id="tax-form"
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
                            Analyze Tax Efficiency
                        </button>
                    </div>
                </form>
            </x-analytics.filter-panel>

            <div
                id="loading-state"
                class="hidden rounded-2xl border border-slate-800 bg-slate-900 p-8 text-center text-sm text-slate-400"
            >
                Analyzing tax activity…
            </div>

            <div
                id="error-state"
                class="hidden rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5 text-sm text-red-300"
            ></div>

            <div id="insufficient-state" class="hidden">
                <x-analytics.message-card
                    tone="warning"
                    title="More tax data is needed"
                >
                    <p id="insufficient-message"></p>
                </x-analytics.message-card>
            </div>

            <div id="results" class="hidden space-y-6">

                <x-analytics.panel>
                    <div class="flex items-center justify-between gap-6">
                        <div>
                            <p class="text-sm text-slate-500">
                                Tax analysis period
                            </p>

                            <p
                                id="analysis-period"
                                class="mt-2 text-lg font-semibold text-white"
                            >
                                —
                            </p>
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
                        ['Short-term gain / loss', 'short-term-gain-loss'],
                        ['Long-term gain / loss', 'long-term-gain-loss'],
                        ['Qualified dividends', 'qualified-dividends'],
                        ['Tax withheld', 'tax-withheld'],
                        ['Potential wash sales', 'wash-sale-count'],
                        ['Estimated disallowed loss', 'disallowed-loss'],
                        ['Harvest opportunities', 'harvest-opportunity-count'],
                        ['Estimated harvestable loss', 'harvestable-loss'],
                    ] as [$label, $id])
                        <x-analytics.metric-card :label="$label">
                            <span id="{{ $id }}">—</span>
                        </x-analytics.metric-card>
                    @endforeach
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <x-analytics.panel title="Income Summary">
                        <dl class="space-y-4 text-sm">
                            @foreach ([
                                ['Total realized gain / loss', 'total-realized-gain-loss'],
                                ['Non-qualified dividends', 'non-qualified-dividends'],
                                ['Tax-exempt income', 'tax-exempt-income'],
                                ['Total taxable dividends', 'total-taxable-dividends'],
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
                            id="tax-flags"
                            class="space-y-3"
                        ></div>

                        <h4
                            class="mt-7 border-t border-slate-800 pt-6 text-sm font-semibold text-white"
                        >
                            Data Quality
                        </h4>

                        <div
                            id="tax-warnings"
                            class="mt-4 space-y-3"
                        ></div>
                    </x-analytics.panel>
                </div>

                <div
                    id="wash-sale-section"
                    class="hidden"
                >
                    <x-analytics.panel
                        title="Potential Wash Sales"
                        subtitle="Loss sales matched with same-security purchases inside the 30-day window."
                    >
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-y border-slate-800 bg-slate-950">
                                    <tr>
                                        @foreach ([
                                            'Security',
                                            'Loss sale',
                                            'Repurchase',
                                            'Matched shares',
                                            'Disallowed loss',
                                            'Confidence',
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
                                    id="wash-sale-table-body"
                                    class="divide-y divide-slate-800"
                                ></tbody>
                            </table>
                        </div>
                    </x-analytics.panel>
                </div>

                <div
                    id="harvesting-section"
                    class="hidden"
                >
                    <x-analytics.panel
                        title="Tax-Loss Harvesting Opportunities"
                        subtitle="Open positions with meaningful unrealized losses."
                    >
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-y border-slate-800 bg-slate-950">
                                    <tr>
                                        @foreach ([
                                            'Security',
                                            'Current value',
                                            'Cost basis',
                                            'Unrealized loss',
                                            'Loss %',
                                            'Wash-sale risk',
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
                                    id="harvesting-table-body"
                                    class="divide-y divide-slate-800"
                                ></tbody>
                            </table>
                        </div>
                    </x-analytics.panel>
                </div>
            </div>
        </div>
    </div>

    {{-- Keep your existing JavaScript logic below.
         Only replace the dynamic row classes with the dark equivalents. --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('tax-form');
            const results = document.getElementById('results');
            const loadingState = document.getElementById('loading-state');
            const errorState = document.getElementById('error-state');
            const insufficientState = document.getElementById('insufficient-state');

            const formatCurrency = value =>
                value == null
                    ? '—'
                    : new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                        maximumFractionDigits: 0,
                    }).format(value);

            const formatPercent = value =>
                value == null
                    ? '—'
                    : new Intl.NumberFormat('en-US', {
                        style: 'percent',
                        minimumFractionDigits: 1,
                        maximumFractionDigits: 2,
                    }).format(value);

            const formatQuantity = value =>
                value == null
                    ? '—'
                    : new Intl.NumberFormat('en-US', {
                        maximumFractionDigits: 8,
                    }).format(value);

            const flagClasses = severity => ({
                informational: 'border-blue-500/20 bg-blue-500/[0.07] text-blue-300',
                moderate: 'border-amber-500/20 bg-amber-500/[0.07] text-amber-300',
                high: 'border-red-500/20 bg-red-500/[0.07] text-red-300',
            })[severity] ?? 'border-slate-700 bg-slate-800 text-slate-300';

            const renderMessages = (
                containerId,
                messages,
                emptyMessage,
                warning = false
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

                messages.forEach(item => {
                    const element =
                        document.createElement('div');

                    if (warning) {
                        element.className =
                            'rounded-xl border border-amber-500/20 bg-amber-500/[0.07] p-4 text-sm text-amber-300';

                        element.textContent =
                            item.message;

                        container.appendChild(
                            element
                        );

                        return;
                    }

                    element.className =
                        `rounded-xl border p-4 ${flagClasses(item.severity)}`;

                    element.innerHTML = `
                        <p class="font-semibold">
                            ${item.title}
                        </p>

                        <p class="mt-1 text-sm leading-6 text-slate-400">
                            ${item.message}
                        </p>
                    `;

                    container.appendChild(
                        element
                    );
                });
            };

            const renderWashSales = analysis => {
                const section =
                    document.getElementById(
                        'wash-sale-section'
                    );

                const body =
                    document.getElementById(
                        'wash-sale-table-body'
                    );

                const washSales =
                    analysis?.wash_sales ?? [];

                body.innerHTML = '';

                if (washSales.length === 0) {
                    section.classList.add(
                        'hidden'
                    );

                    return;
                }

                washSales.forEach(washSale => {
                    const row =
                        document.createElement(
                            'tr'
                        );

                    row.innerHTML = `
                        <td class="whitespace-nowrap px-4 py-4 text-slate-200">
                            Security #${washSale.security_id}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-slate-500">
                            ${washSale.loss_sale_date}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-slate-500">
                            ${washSale.repurchase_date}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right text-slate-300">
                            ${formatQuantity(washSale.matched_quantity)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right font-semibold text-red-300">
                            ${formatCurrency(washSale.estimated_disallowed_loss)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 capitalize text-slate-300">
                            ${washSale.confidence}
                        </td>
                    `;

                    body.appendChild(row);
                });

                section.classList.remove(
                    'hidden'
                );
            };

            const renderHarvesting = analysis => {
                const section =
                    document.getElementById(
                        'harvesting-section'
                    );

                const body =
                    document.getElementById(
                        'harvesting-table-body'
                    );

                const opportunities =
                    analysis?.opportunities ?? [];

                body.innerHTML = '';

                if (opportunities.length === 0) {
                    section.classList.add(
                        'hidden'
                    );

                    return;
                }

                opportunities.forEach(
                    opportunity => {
                        const row =
                            document.createElement(
                                'tr'
                            );

                        const riskLabel =
                            opportunity.wash_sale_risk
                                ? 'Review required'
                                : 'No recent purchase';

                        const riskClass =
                            opportunity.wash_sale_risk
                                ? 'text-red-300'
                                : 'text-emerald-300';

                        row.innerHTML = `
                            <td class="whitespace-nowrap px-4 py-4 text-slate-200">
                                Security #${opportunity.security_id}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-slate-300">
                                ${formatCurrency(opportunity.current_value)}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-slate-300">
                                ${formatCurrency(opportunity.cost_basis)}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right font-semibold text-red-300">
                                ${formatCurrency(opportunity.unrealized_gain_loss)}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right text-red-300">
                                ${formatPercent(-opportunity.loss_percent)}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 font-medium ${riskClass}">
                                ${riskLabel}
                            </td>
                        `;

                        body.appendChild(row);
                    }
                );

                section.classList.remove(
                    'hidden'
                );
            };

            const loadTaxAnalytics = async () => {
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
                        `{{ route('analytics.tax-efficiency.data') }}?${query.toString()}`,
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
                            'Unable to load tax analytics.'
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
                            'No tax activity was available.';

                        insufficientState.classList.remove(
                            'hidden'
                        );

                        return;
                    }

                    const taxLot =
                        data.tax_lot_analysis ?? {};

                    const taxMetrics =
                        taxLot.metrics ?? {};

                    const washSale =
                        data.wash_sale_analysis ?? {};

                    const washMetrics =
                        washSale.metrics ?? {};

                    const harvesting =
                        data.tax_loss_harvesting ?? {};

                    const harvestingMetrics =
                        harvesting.metrics ?? {};

                    document.getElementById(
                        'analysis-period'
                    ).textContent =
                        `${data.period.start_date} to ${data.period.end_date}`;

                    document.getElementById(
                        'transaction-count'
                    ).textContent =
                        data.period.transaction_count ?? 0;

                    document.getElementById(
                        'short-term-gain-loss'
                    ).textContent =
                        formatCurrency(
                            taxMetrics.realized_short_term_gain_loss
                        );

                    document.getElementById(
                        'long-term-gain-loss'
                    ).textContent =
                        formatCurrency(
                            taxMetrics.realized_long_term_gain_loss
                        );

                    document.getElementById(
                        'qualified-dividends'
                    ).textContent =
                        formatCurrency(
                            taxMetrics.qualified_dividends
                        );

                    document.getElementById(
                        'tax-withheld'
                    ).textContent =
                        formatCurrency(
                            taxMetrics.tax_withheld
                        );

                    document.getElementById(
                        'wash-sale-count'
                    ).textContent =
                        washMetrics.wash_sale_count ?? 0;

                    document.getElementById(
                        'disallowed-loss'
                    ).textContent =
                        formatCurrency(
                            washMetrics.estimated_disallowed_loss
                        );

                    document.getElementById(
                        'harvest-opportunity-count'
                    ).textContent =
                        harvestingMetrics.opportunity_count ?? 0;

                    document.getElementById(
                        'harvestable-loss'
                    ).textContent =
                        formatCurrency(
                            harvestingMetrics.estimated_harvestable_loss
                        );

                    document.getElementById(
                        'total-realized-gain-loss'
                    ).textContent =
                        formatCurrency(
                            taxMetrics.total_realized_gain_loss
                        );

                    document.getElementById(
                        'non-qualified-dividends'
                    ).textContent =
                        formatCurrency(
                            taxMetrics.non_qualified_dividends
                        );

                    document.getElementById(
                        'tax-exempt-income'
                    ).textContent =
                        formatCurrency(
                            taxMetrics.tax_exempt_income
                        );

                    document.getElementById(
                        'total-taxable-dividends'
                    ).textContent =
                        formatCurrency(
                            taxMetrics.total_taxable_dividends
                        );

                    renderMessages(
                        'tax-flags',
                        data.flags ?? [],
                        'No major tax findings detected.'
                    );

                    renderMessages(
                        'tax-warnings',
                        data.warnings ?? [],
                        'No data-quality warnings detected.',
                        true
                    );

                    renderWashSales(washSale);
                    renderHarvesting(harvesting);

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
                    loadTaxAnalytics();
                }
            );

            loadTaxAnalytics();
        });
    </script>
</x-app-layout>