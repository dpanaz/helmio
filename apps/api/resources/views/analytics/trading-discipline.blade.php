<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Phase 2 analytics
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Trading discipline
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Trading score
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
                        Estimated turnover
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if ($analytics['metrics']['turnover_rate'] !== null)
                            {{ number_format(
                                $analytics['metrics']['turnover_rate'] * 100,
                                1
                            ) }}%
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Trailing 12-month trades
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $analytics['metrics']['trade_count'] }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Trading fees
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format(
                            $analytics['metrics']['trading_fees'],
                            2
                        ) }}
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
                        Questions to consider
                    </h3>

                    <div class="mt-5 space-y-3">
                        @foreach ($analytics['recommendations'] as $recommendation)
                            <p class="text-sm leading-6 text-blue-900">
                                {{ $recommendation }}
                            </p>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    [
                        'Purchases',
                        '$'.number_format(
                            $analytics['metrics']['purchase_value'],
                            2
                        ),
                    ],
                    [
                        'Sales',
                        '$'.number_format(
                            $analytics['metrics']['sales_value'],
                            2
                        ),
                    ],
                    [
                        'Trades per month',
                        number_format(
                            $analytics['metrics']['trades_per_month'],
                            1
                        ),
                    ],
                    [
                        'Short-holding indicators',
                        $analytics['metrics'][
                            'short_holding_indicator_count'
                        ],
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
                        Trading activity by security
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Review period:
                        {{ $analytics['period_start'] }}
                        through
                        {{ $analytics['period_end'] }}.
                    </p>
                </div>

                @if ($analytics['security_activity']->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-semibold text-slate-900">
                            No purchases or sales recorded
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Security
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Trades
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Purchases
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Sales
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Fees
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200">
                                @foreach ($analytics['security_activity'] as $activity)
                                    <tr>
                                        <td class="px-6 py-5">
                                            <p class="font-semibold text-slate-900">
                                                {{ $activity['symbol']
                                                    ?: $activity['name'] }}
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $activity['name'] }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            {{ $activity['trade_count'] }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            ${{ number_format(
                                                $activity['purchase_value'],
                                                2
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            ${{ number_format(
                                                $activity['sales_value'],
                                                2
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            ${{ number_format(
                                                $activity['fees'],
                                                2
                                            ) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            @if ($analytics['short_holding_indicators']->isNotEmpty())
                <section class="overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-sm">
                    <div class="border-b border-amber-200 bg-amber-50 px-6 py-5">
                        <h3 class="font-semibold text-amber-950">
                            Possible short-holding indicators
                        </h3>

                        <p class="mt-1 text-sm text-amber-800">
                            These are transaction-pattern indicators, not tax-lot
                            calculations or findings of improper conduct.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach ($analytics['short_holding_indicators'] as $indicator)
                            <article class="flex flex-wrap items-center justify-between gap-5 px-6 py-5">
                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ $indicator['symbol']
                                            ?: $indicator['name'] }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Purchased {{ $indicator['buy_date'] }}
                                        · Sold {{ $indicator['sell_date'] }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-semibold text-amber-800">
                                        {{ $indicator['days_between'] }}
                                        days
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Sale:
                                        ${{ number_format(
                                            $indicator['sale_amount'],
                                            2
                                        ) }}
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
                    Helmio’s trading analysis identifies patterns that may
                    deserve review. It does not determine whether trading was
                    suitable, authorized, excessive or legally improper.
                    Turnover is currently estimated using the lesser of trailing
                    12-month purchases or sales divided by current portfolio
                    value. Short-holding indicators are heuristic and do not
                    reconstruct tax lots.
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Formula version:
                    {{ $analytics['formula_version'] }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
