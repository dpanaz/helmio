<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Tax oversight
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Understand your portfolio taxes
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                See where investment activity may be creating tax consequences,
                which issues deserve attention, and where potential tax-saving opportunities may exist.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <x-analytics.filter-panel
                title="Analysis period"
                subtitle="Choose the dates you want Helmio to review for taxable investment activity."
            >
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
                            Update Analysis
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

                {{-- TAX SUMMARY --}}
                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                    <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-blue-400">
                            Tax summary
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-white">
                            What Helmio reviewed
                        </h3>
                        <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                            Helmio looks for investment activity that may affect taxes, including realized gains, dividend treatment, potential wash sales, and losses that may offer harvesting opportunities.
                        </p>
                    </div>

                    <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Analysis period
                            </p>
                            <p id="analysis-period" class="mt-2 text-sm font-semibold text-slate-200">—</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Tax results can change substantially depending on the dates selected.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Transactions reviewed
                            </p>
                            <p id="transaction-count" class="mt-2 text-2xl font-semibold tabular-nums text-white">—</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                The number of transactions included in this tax review.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- PRIMARY TAX METRICS --}}
                <section>
                    <div class="mb-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                            Core tax measures
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-white">
                            Start with these four
                        </h3>
                        <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500">
                            These are the clearest signals of taxable gains, possible tax-rule issues, and potential tax-saving opportunities.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['Short-term gain / loss','short-term-gain-loss','What was realized on shorter-held investments?','Short-term gains are generally taxed differently from long-term gains, so frequent realization can matter.'],
                            ['Long-term gain / loss','long-term-gain-loss','What was realized on longer-held investments?','Shows gains or losses from investments that qualified as long-term under the available transaction data.'],
                            ['Potential wash sales','wash-sale-count','Were losses followed by nearby repurchases?','A wash sale may limit when a loss can be deducted. Helmio flags potential matches for review.'],
                            ['Harvest opportunities','harvest-opportunity-count','Are there unrealized losses worth reviewing?','Identifies positions with losses that may offer a tax-loss harvesting opportunity, subject to your broader tax situation.'],
                        ] as [$label,$id,$question,$definition])
                            <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                                <p class="text-sm font-semibold text-white">{{ $label }}</p>
                                <p class="mt-1 min-h-[2rem] text-xs leading-4 text-slate-500">{{ $question }}</p>
                                <p id="{{ $id }}" class="mt-4 text-2xl font-semibold tabular-nums text-blue-300">—</p>

                                <div class="mt-4 rounded-xl bg-slate-950/60 px-3 py-2.5">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                        In plain English
                                    </p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $definition }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- FINDINGS --}}
                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                    <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                            What deserves attention
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-white">
                            Tax findings
                        </h3>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            These are the tax-related patterns Helmio believes are most useful to review.
                        </p>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div id="tax-flags" class="space-y-3"></div>
                    </div>
                </section>

                {{-- WASH SALES --}}
                <div id="wash-sale-section" class="hidden">
                    <section class="overflow-hidden rounded-2xl border border-red-500/20 bg-slate-900 shadow-lg">
                        <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-red-300">
                                Review recommended
                            </p>
                            <h3 class="mt-1 text-lg font-semibold text-white">
                                Potential wash sales
                            </h3>
                            <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500">
                                Helmio found loss sales with same-security purchases inside the 30-day window. These are potential matches, not a final tax determination.
                            </p>
                        </div>

                        <div class="overflow-x-auto p-5 sm:p-6">
                            <table class="min-w-full text-sm">
                                <thead class="border-y border-slate-800 bg-slate-950">
                                    <tr>
                                        @foreach ([
                                            'Security','Loss sale','Repurchase','Matched shares','Disallowed loss','Confidence'
                                        ] as $heading)
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                {{ $heading }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody id="wash-sale-table-body" class="divide-y divide-slate-800"></tbody>
                            </table>
                        </div>
                    </section>
                </div>

                {{-- HARVESTING --}}
                <div id="harvesting-section" class="hidden">
                    <section class="overflow-hidden rounded-2xl border border-blue-500/20 bg-slate-900 shadow-lg">
                        <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-blue-400">
                                Potential opportunity
                            </p>
                            <h3 class="mt-1 text-lg font-semibold text-white">
                                Tax-loss harvesting opportunities
                            </h3>
                            <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500">
                                These open positions have meaningful unrealized losses. Harvesting may help in some situations, but investment fit, replacement exposure, and wash-sale rules still matter.
                            </p>
                        </div>

                        <div class="overflow-x-auto p-5 sm:p-6">
                            <table class="min-w-full text-sm">
                                <thead class="border-y border-slate-800 bg-slate-950">
                                    <tr>
                                        @foreach ([
                                            'Security','Current value','Cost basis','Unrealized loss','Loss %','Wash-sale risk'
                                        ] as $heading)
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                {{ $heading }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody id="harvesting-table-body" class="divide-y divide-slate-800"></tbody>
                            </table>
                        </div>
                    </section>
                </div>

                {{-- SUPPORTING TAX DETAILS --}}
                <details class="group overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                Supporting information
                            </p>
                            <h3 class="mt-1 text-base font-semibold text-white">
                                Additional tax measures and data quality
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Expand for dividends, withholding, estimated losses, and data-quality details.
                            </p>
                        </div>

                        <svg class="h-5 w-5 shrink-0 text-slate-500 transition group-open:rotate-180"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>

                    <div class="border-t border-slate-800 p-5 sm:p-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div>
                                <h4 class="text-sm font-semibold text-white">Additional tax measures</h4>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    @foreach ([
                                        ['Qualified dividends','qualified-dividends'],
                                        ['Tax withheld','tax-withheld'],
                                        ['Estimated disallowed loss','disallowed-loss'],
                                        ['Estimated harvestable loss','harvestable-loss'],
                                    ] as [$label,$id])
                                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                                            <p class="text-xs text-slate-500">{{ $label }}</p>
                                            <p id="{{ $id }}" class="mt-2 text-lg font-semibold text-white">—</p>
                                        </div>
                                    @endforeach
                                </div>

                                <h4 class="mt-6 text-sm font-semibold text-white">Income summary</h4>
                                <dl class="mt-4 space-y-3">
                                    @foreach ([
                                        ['Total realized gain / loss','total-realized-gain-loss'],
                                        ['Non-qualified dividends','non-qualified-dividends'],
                                        ['Tax-exempt income','tax-exempt-income'],
                                        ['Total taxable dividends','total-taxable-dividends'],
                                    ] as [$label,$id])
                                        <div class="flex justify-between gap-4 rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-3">
                                            <dt class="text-xs text-slate-500">{{ $label }}</dt>
                                            <dd id="{{ $id }}" class="text-sm font-semibold text-slate-200">—</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-white">Data quality</h4>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Warnings here identify missing or limited data that may affect the tax analysis.
                                </p>
                                <div id="tax-warnings" class="mt-4 space-y-3"></div>
                            </div>
                        </div>
                    </div>
                </details>
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
                            ${washSale.security_symbol ?? washSale.symbol ?? washSale.ticker ?? `Security #${washSale.security_id}`}
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
                                ${opportunity.security_symbol ?? opportunity.symbol ?? opportunity.ticker ?? `Security #${opportunity.security_id}`}
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