<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Cost oversight
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Cost & Fee Analysis
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Understand the total cost of owning and managing your portfolio.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (count($analytics['data_warnings']) > 0)
                <x-analytics.message-card
                    tone="warning"
                    title="Data-quality notice"
                >
                    <div class="space-y-2">
                        @foreach ($analytics['data_warnings'] as $warning)
                            <p>{{ $warning }}</p>
                        @endforeach
                    </div>
                </x-analytics.message-card>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-analytics.metric-card label="Portfolio value">
                    ${{ number_format($analytics['portfolio_value'], 2) }}
                </x-analytics.metric-card>

                <x-analytics.metric-card label="Estimated annual cost">
                    ${{ number_format($analytics['total_annual_cost'], 2) }}
                </x-analytics.metric-card>

                <x-analytics.metric-card label="All-in cost rate">
                    @if ($analytics['all_in_cost_rate'] !== null)
                        {{ number_format(
                            $analytics['all_in_cost_rate'] * 100,
                            2
                        ) }}%
                    @else
                        —
                    @endif
                </x-analytics.metric-card>

                <x-analytics.metric-card label="Fund expense cost">
                    ${{ number_format($analytics['fund_expenses'], 2) }}
                </x-analytics.metric-card>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Advisory fees', $analytics['advisory_fees']],
                    ['Fund expenses', $analytics['fund_expenses']],
                    ['Transaction fees', $analytics['transaction_fees']],
                    ['Account fees', $analytics['account_fees']],
                ] as [$label, $amount])
                    <x-analytics.metric-card :label="$label">
                        ${{ number_format($amount, 2) }}
                    </x-analytics.metric-card>
                @endforeach
            </div>

            <x-analytics.panel
                title="Costs by Account"
                subtitle="Estimated annualized account costs as of {{ $analytics['as_of'] }}."
                :padding="false"
            >
                @if ($analytics['accounts']->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-semibold text-white">
                            No accounts available
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            Add an investment account to begin cost analysis.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-y border-slate-800 bg-slate-950">
                                <tr>
                                    @foreach ([
                                        'Account',
                                        'Advisory',
                                        'Fund costs',
                                        'Trading',
                                        'Total',
                                        'Cost rate',
                                    ] as $heading)
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600"
                                        >
                                            {{ $heading }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-800">
                                @foreach ($analytics['accounts'] as $account)
                                    <tr class="transition hover:bg-slate-800/40">
                                        <td class="px-6 py-5">
                                            <p class="font-semibold text-white">
                                                {{ $account['account_name'] }}
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $account['institution_name'] }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-5 text-slate-300">
                                            ${{ number_format($account['advisory_fee'], 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-slate-300">
                                            ${{ number_format($account['fund_expense_cost'], 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-slate-300">
                                            ${{ number_format($account['transaction_fees'], 2) }}
                                        </td>

                                        <td class="px-6 py-5 font-semibold text-white">
                                            ${{ number_format($account['total_cost'], 2) }}
                                        </td>

                                        <td class="px-6 py-5 font-semibold text-white">
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
            </x-analytics.panel>

            <x-analytics.methodology
                :formula-version="$analytics['formula_version']"
            >
                Estimated annual cost includes advisory fees, fixed account
                fees, mutual fund and ETF expense ratios, and transaction
                fees recorded during the trailing 12 months. It does not
                yet include bid-ask spreads, cash-sweep spreads, embedded
                annuity costs, surrender charges, or taxes.
            </x-analytics.methodology>
        </div>
    </div>
</x-app-layout>