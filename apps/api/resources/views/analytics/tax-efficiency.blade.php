<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">
                Tax Efficiency
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Review realized gains, dividend treatment, wash-sale risk, and tax-loss harvesting opportunities.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form
                    id="tax-form"
                    class="grid gap-4 md:grid-cols-3"
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

                    <div class="flex items-end">
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            Analyze Tax Efficiency
                        </button>
                    </div>
                </form>
            </div>

            <div
                id="loading-state"
                class="hidden rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-200"
            >
                <p class="text-sm text-gray-600">
                    Analyzing tax activity…
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
                    More tax data is needed
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
                                Tax analysis period
                            </p>

                            <p
                                id="analysis-period"
                                class="mt-1 text-lg font-semibold text-gray-900"
                            >
                                —
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-sm text-gray-500">
                                Transactions reviewed
                            </p>

                            <p
                                id="transaction-count"
                                class="mt-1 text-2xl font-semibold text-gray-900"
                            >
                                —
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Short-term gain / loss
                        </p>

                        <p
                            id="short-term-gain-loss"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Long-term gain / loss
                        </p>

                        <p
                            id="long-term-gain-loss"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Qualified dividends
                        </p>

                        <p
                            id="qualified-dividends"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Tax withheld
                        </p>

                        <p
                            id="tax-withheld"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Potential wash sales
                        </p>

                        <p
                            id="wash-sale-count"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Estimated disallowed loss
                        </p>

                        <p
                            id="disallowed-loss"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Harvest opportunities
                        </p>

                        <p
                            id="harvest-opportunity-count"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                        <p class="text-sm text-gray-500">
                            Estimated harvestable loss
                        </p>

                        <p
                            id="harvestable-loss"
                            class="mt-2 text-3xl font-bold text-gray-900"
                        >
                            —
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Income Summary
                        </h3>

                        <dl class="mt-5 space-y-4 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Total realized gain / loss
                                </dt>

                                <dd
                                    id="total-realized-gain-loss"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Non-qualified dividends
                                </dt>

                                <dd
                                    id="non-qualified-dividends"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Tax-exempt income
                                </dt>

                                <dd
                                    id="tax-exempt-income"
                                    class="font-medium text-gray-900"
                                >
                                    —
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Total taxable dividends
                                </dt>

                                <dd
                                    id="total-taxable-dividends"
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
                            id="tax-flags"
                            class="mt-5 space-y-3"
                        ></div>

                        <h3 class="mt-6 text-lg font-semibold text-gray-900">
                            Data Quality
                        </h3>

                        <div
                            id="tax-warnings"
                            class="mt-5 space-y-3"
                        ></div>
                    </div>
                </div>

                <div
                    id="wash-sale-section"
                    class="hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Potential Wash Sales
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Loss sales matched with same-security purchases inside the 30-day window.
                        </p>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">
                                        Security
                                    </th>

                                    <th class="px-4 py-3 text-left font-medium text-gray-500">
                                        Loss sale
                                    </th>

                                    <th class="px-4 py-3 text-left font-medium text-gray-500">
                                        Repurchase
                                    </th>

                                    <th class="px-4 py-3 text-right font-medium text-gray-500">
                                        Matched shares
                                    </th>

                                    <th class="px-4 py-3 text-right font-medium text-gray-500">
                                        Disallowed loss
                                    </th>

                                    <th class="px-4 py-3 text-left font-medium text-gray-500">
                                        Confidence
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                id="wash-sale-table-body"
                                class="divide-y divide-gray-100 bg-white"
                            ></tbody>
                        </table>
                    </div>
                </div>

                <div
                    id="harvesting-section"
                    class="hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Tax-Loss Harvesting Opportunities
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Open positions with meaningful unrealized losses.
                        </p>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">
                                        Security
                                    </th>

                                    <th class="px-4 py-3 text-right font-medium text-gray-500">
                                        Current value
                                    </th>

                                    <th class="px-4 py-3 text-right font-medium text-gray-500">
                                        Cost basis
                                    </th>

                                    <th class="px-4 py-3 text-right font-medium text-gray-500">
                                        Unrealized loss
                                    </th>

                                    <th class="px-4 py-3 text-right font-medium text-gray-500">
                                        Loss %
                                    </th>

                                    <th class="px-4 py-3 text-left font-medium text-gray-500">
                                        Wash-sale risk
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                id="harvesting-table-body"
                                class="divide-y divide-gray-100 bg-white"
                            ></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('tax-form');
            const results = document.getElementById('results');
            const loadingState = document.getElementById('loading-state');
            const errorState = document.getElementById('error-state');
            const insufficientState = document.getElementById('insufficient-state');

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

            const formatQuantity = (value) => {
                if (value === null || value === undefined) {
                    return '—';
                }

                return new Intl.NumberFormat('en-US', {
                    maximumFractionDigits: 8,
                }).format(value);
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
                warning = false
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

                messages.forEach((item) => {
                    const element =
                        document.createElement('div');

                    if (warning) {
                        element.className =
                            'rounded-lg bg-amber-50 p-3 text-sm text-amber-800';

                        element.textContent =
                            item.message;

                        container.appendChild(element);
                        return;
                    }

                    element.className =
                        `rounded-lg border p-4 ${flagClasses(item.severity)}`;

                    const title =
                        document.createElement('p');

                    title.className = 'font-semibold';
                    title.textContent = item.title;

                    const message =
                        document.createElement('p');

                    message.className = 'mt-1 text-sm';
                    message.textContent = item.message;

                    element.appendChild(title);
                    element.appendChild(message);

                    container.appendChild(element);
                });
            };

            const renderWashSales = (analysis) => {
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
                    section.classList.add('hidden');
                    return;
                }

                washSales.forEach((washSale) => {
                    const row =
                        document.createElement('tr');

                    row.innerHTML = `
                        <td class="whitespace-nowrap px-4 py-3 text-gray-900">
                            Security #${washSale.security_id}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                            ${washSale.loss_sale_date}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                            ${washSale.repurchase_date}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-900">
                            ${formatQuantity(washSale.matched_quantity)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-red-700">
                            ${formatCurrency(washSale.estimated_disallowed_loss)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 capitalize text-gray-700">
                            ${washSale.confidence}
                        </td>
                    `;

                    body.appendChild(row);
                });

                section.classList.remove('hidden');
            };

            const renderHarvesting = (analysis) => {
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
                    section.classList.add('hidden');
                    return;
                }

                opportunities.forEach((opportunity) => {
                    const row =
                        document.createElement('tr');

                    const riskLabel =
                        opportunity.wash_sale_risk
                            ? 'Review required'
                            : 'No recent purchase';

                    const riskClass =
                        opportunity.wash_sale_risk
                            ? 'text-red-700'
                            : 'text-green-700';

                    row.innerHTML = `
                        <td class="whitespace-nowrap px-4 py-3 text-gray-900">
                            Security #${opportunity.security_id}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-900">
                            ${formatCurrency(opportunity.current_value)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right text-gray-900">
                            ${formatCurrency(opportunity.cost_basis)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-red-700">
                            ${formatCurrency(opportunity.unrealized_gain_loss)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-right text-red-700">
                            ${formatPercent(-opportunity.loss_percent)}
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 font-medium ${riskClass}">
                            ${riskLabel}
                        </td>
                    `;

                    body.appendChild(row);
                });

                section.classList.remove('hidden');
            };

            const loadTaxAnalytics = async () => {
                loadingState.classList.remove('hidden');
                errorState.classList.add('hidden');
                insufficientState.classList.add('hidden');
                results.classList.add('hidden');

                const query = new URLSearchParams(
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

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            payload.message ??
                            'Unable to load tax analytics.'
                        );
                    }

                    const data = payload.data;

                    if (data.status === 'insufficient_data') {
                        document.getElementById(
                            'insufficient-message'
                        ).textContent =
                            data.message ??
                            'No tax activity was available.';

                        insufficientState.classList.remove('hidden');
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
                loadTaxAnalytics();
            });

            loadTaxAnalytics();
        });
    </script>
</x-app-layout>