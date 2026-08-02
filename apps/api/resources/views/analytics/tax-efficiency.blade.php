<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Phase 2 analytics
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Tax efficiency
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Tax score
                    </p>

                    <p class="mt-3 text-4xl font-semibold text-slate-900">
                        {{ $analytics['score'] ?? '—' }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ $analytics['label'] }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Short-term gains
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format(
                            $analytics['metrics']['short_term_gains'] ?? 0,
                            2
                        ) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Long-term gains
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format(
                            $analytics['metrics']['long_term_gains'] ?? 0,
                            2
                        ) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Wash-sale indicators
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-amber-700">
                        {{ $analytics['metrics']['wash_sale_indicator_count'] ?? 0 }}
                    </p>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Findings
                    </h3>

                    <div class="mt-5 space-y-3">
                        @foreach ($analytics['reasons'] as $reason)
                            <p class="text-sm leading-6 text-slate-600">
                                {{ $reason }}
                            </p>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-3xl border border-blue-200 bg-blue-50 p-7">
                    <h3 class="text-lg font-semibold text-blue-950">
                        Recommended review
                    </h3>

                    <div class="mt-5 space-y-3">
                        @forelse ($analytics['recommendations'] as $recommendation)
                            <p class="text-sm leading-6 text-blue-900">
                                {{ $recommendation }}
                            </p>
                        @empty
                            <p class="text-sm leading-6 text-blue-900">
                                No additional tax review is currently indicated.
                            </p>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    [
                        'Realized losses',
                        '$'.number_format(
                            $analytics['metrics']['realized_losses'] ?? 0,
                            2
                        ),
                    ],
                    [
                        'Ordinary dividends',
                        '$'.number_format(
                            $analytics['metrics']['ordinary_dividends'] ?? 0,
                            2
                        ),
                    ],
                    [
                        'Taxable interest',
                        '$'.number_format(
                            $analytics['metrics']['taxable_interest'] ?? 0,
                            2
                        ),
                    ],
                    [
                        'Tax withheld',
                        '$'.number_format(
                            $analytics['metrics']['tax_withheld'] ?? 0,
                            2
                        ),
                    ],
                ] as [$label, $value])
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">
                            {{ $label }}
                        </p>

                        <p class="mt-3 text-2xl font-semibold text-slate-900">
                            {{ $value }}
                        </p>
                    </article>
                @endforeach
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="font-semibold text-slate-900">
                        Taxable accounts
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Review period:
                        {{ $analytics['period_start'] }}
                        through
                        {{ $analytics['period_end'] }}.
                    </p>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse ($analytics['account_summary'] as $account)
                        <article class="flex flex-wrap items-center justify-between gap-6 px-6 py-5">
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ $account['account_name'] }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ str($account['account_type'])
                                        ->replace('_', ' ')
                                        ->title() }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-right sm:grid-cols-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Realized gain/loss
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-900">
                                        ${{ number_format(
                                            $account['realized_gain_loss'],
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Taxable income
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-900">
                                        ${{ number_format(
                                            $account['taxable_income'],
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-400">
                                        Tax withheld
                                    </p>

                                    <p class="mt-1 font-semibold text-slate-900">
                                        ${{ number_format(
                                            $account['tax_withheld'],
                                            2
                                        ) }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-10 text-center text-sm text-slate-500">
                            No taxable accounts were identified.
                        </div>
                    @endforelse
                </div>
            </section>

            @if ($analytics['wash_sale_indicators']->isNotEmpty())
                <section class="overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-sm">
                    <div class="border-b border-amber-200 bg-amber-50 px-6 py-5">
                        <h3 class="font-semibold text-amber-950">
                            Possible wash-sale indicators
                        </h3>

                        <p class="mt-1 text-sm text-amber-800">
                            These are heuristic transaction matches, not tax determinations.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach ($analytics['wash_sale_indicators'] as $indicator)
                            <article class="flex flex-wrap items-center justify-between gap-6 px-6 py-5">
                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ $indicator['symbol']
                                            ?: $indicator['name'] }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Loss sale:
                                        {{ $indicator['sale_date'] }}
                                        · Purchase:
                                        {{ $indicator['purchase_date'] }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-semibold text-amber-800">
                                        ${{ number_format(
                                            $indicator['realized_loss'],
                                            2
                                        ) }}
                                        loss
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $indicator['days_between'] }}
                                        days apart
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <p class="text-sm font-medium text-blue-300">
                    Important methodology note
                </p>

                <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-300">
                    Helmio’s tax analysis is informational and does not calculate
                    tax liability or provide tax advice. Wash-sale indicators are
                    based on purchases of the same recorded security within 30
                    days before or after a loss sale. Complete analysis requires
                    adjusted tax basis, substantially-identical security matching,
                    household-wide accounts and tax-lot data.
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Formula version:
                    {{ $analytics['formula_version'] }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
