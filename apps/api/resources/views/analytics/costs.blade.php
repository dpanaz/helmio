<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Phase 2 analytics
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Cost and fee analysis
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (count($analytics['data_warnings']) > 0)
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <h3 class="font-semibold text-amber-900">
                        Data-quality notice
                    </h3>

                    <ul class="mt-3 space-y-2 text-sm text-amber-800">
                        @foreach ($analytics['data_warnings'] as $warning)
                            <li>• {{ $warning }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Portfolio value
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($analytics['portfolio_value'], 2) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Estimated annual cost
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($analytics['total_annual_cost'], 2) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        All-in cost rate
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if ($analytics['all_in_cost_rate'] !== null)
                            {{ number_format(
                                $analytics['all_in_cost_rate'] * 100,
                                2
                            ) }}%
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Fund expense cost
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($analytics['fund_expenses'], 2) }}
                    </p>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-4">
                @foreach ([
                    ['Advisory fees', $analytics['advisory_fees']],
                    ['Fund expenses', $analytics['fund_expenses']],
                    ['Transaction fees', $analytics['transaction_fees']],
                    ['Account fees', $analytics['account_fees']],
                ] as [$label, $amount])
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">
                            {{ $label }}
                        </p>

                        <p class="mt-3 text-2xl font-semibold text-slate-900">
                            ${{ number_format($amount, 2) }}
                        </p>
                    </article>
                @endforeach
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="font-semibold text-slate-900">
                        Costs by account
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Estimated annualized account costs as of
                        {{ $analytics['as_of'] }}.
                    </p>
                </div>

                @if ($analytics['accounts']->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-semibold text-slate-900">
                            No accounts available
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            Add an investment account to begin cost analysis.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Account
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Advisory
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Fund costs
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Trading
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Total
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Cost rate
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200">
                                @foreach ($analytics['accounts'] as $account)
                                    <tr>
                                        <td class="px-6 py-5">
                                            <p class="font-semibold text-slate-900">
                                                {{ $account['account_name'] }}
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $account['institution_name'] }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-slate-700">
                                            ${{ number_format(
                                                $account['advisory_fee'],
                                                2
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-slate-700">
                                            ${{ number_format(
                                                $account['fund_expense_cost'],
                                                2
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-slate-700">
                                            ${{ number_format(
                                                $account['transaction_fees'],
                                                2
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right font-semibold text-slate-900">
                                            ${{ number_format(
                                                $account['total_cost'],
                                                2
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right font-semibold text-slate-900">
                                            @if ($account['cost_rate'] !== null)
                                                {{ number_format(
                                                    $account['cost_rate'] * 100,
                                                    2
                                                ) }}%
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <p class="text-sm font-medium text-blue-300">
                    Methodology
                </p>

                <h3 class="mt-2 text-xl font-semibold">
                    What is included?
                </h3>

                <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300">
                    Estimated annual cost includes advisory fees, fixed account
                    fees, mutual fund and ETF expense ratios, and transaction
                    fees recorded during the trailing 12 months. It does not
                    yet include bid-ask spreads, cash-sweep spreads, embedded
                    annuity costs, surrender charges or taxes.
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Formula version: {{ $analytics['formula_version'] }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
