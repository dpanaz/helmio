<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Phase 2 analytics
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Risk analysis
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Risk score
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
                        Annualized volatility
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if (
                            isset(
                                $analytics['metrics']['annualized_volatility']
                            )
                            && $analytics['metrics']['annualized_volatility'] !== null
                        )
                            {{ number_format(
                                $analytics['metrics']['annualized_volatility'] * 100,
                                1
                            ) }}%
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Maximum drawdown
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if (
                            isset(
                                $analytics['metrics']['maximum_drawdown']
                            )
                            && $analytics['metrics']['maximum_drawdown'] !== null
                        )
                            {{ number_format(
                                $analytics['metrics']['maximum_drawdown'] * 100,
                                1
                            ) }}%
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Equity exposure
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if (
                            isset(
                                $analytics['metrics']['equity_weight']
                            )
                            && $analytics['metrics']['equity_weight'] !== null
                        )
                            {{ number_format(
                                $analytics['metrics']['equity_weight'] * 100,
                                1
                            ) }}%
                        @else
                            —
                        @endif
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
                        'Portfolio value',
                        '$'.number_format(
                            $analytics['metrics']['portfolio_value'] ?? 0,
                            2
                        ),
                    ],
                    [
                        'Cash exposure',
                        isset($analytics['metrics']['cash_weight'])
                            && $analytics['metrics']['cash_weight'] !== null
                            ? number_format(
                                $analytics['metrics']['cash_weight'] * 100,
                                1
                            ).'%'
                            : '—',
                    ],
                    [
                        'Largest account',
                        $analytics['metrics']['largest_account_name'] ?? '—',
                    ],
                    [
                        'Return periods',
                        $analytics['metrics']['return_period_count'] ?? 0,
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

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="font-semibold text-slate-900">
                            Account exposure
                        </h3>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @forelse ($analytics['account_exposure'] as $account)
                            <div class="px-6 py-5">
                                <div class="flex items-center justify-between gap-5">
                                    <div>
                                        <p class="font-medium text-slate-900">
                                            {{ $account['account_name'] }}
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            ${{ number_format(
                                                $account['value'],
                                                2
                                            ) }}
                                        </p>
                                    </div>

                                    <p class="font-semibold text-slate-900">
                                        {{ number_format(
                                            $account['weight'] * 100,
                                            1
                                        ) }}%
                                    </p>
                                </div>

                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full bg-blue-600"
                                        style="width: {{ min(
                                            100,
                                            $account['weight'] * 100
                                        ) }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-500">
                                No account exposure data available.
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="font-semibold text-slate-900">
                            Return history
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Portfolio returns derived from entered snapshots.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @forelse ($analytics['return_series'] as $period)
                            <div class="flex items-center justify-between gap-5 px-6 py-5">
                                <div>
                                    <p class="font-medium text-slate-900">
                                        {{ $period['start_date'] }}
                                        to
                                        {{ $period['end_date'] }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Ending value:
                                        ${{ number_format(
                                            $period['ending_value'],
                                            2
                                        ) }}
                                    </p>
                                </div>

                                <p
                                    @class([
                                        'font-semibold',
                                        'text-emerald-700' =>
                                            $period['return'] > 0,
                                        'text-red-700' =>
                                            $period['return'] < 0,
                                        'text-slate-900' =>
                                            $period['return'] === 0,
                                    ])
                                >
                                    {{ $period['return'] >= 0 ? '+' : '' }}
                                    {{ number_format(
                                        $period['return'] * 100,
                                        2
                                    ) }}%
                                </p>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-500">
                                Add at least two portfolio snapshots to begin
                                calculating risk.
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <p class="text-sm font-medium text-blue-300">
                    Important methodology note
                </p>

                <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-300">
                    This initial risk score evaluates measured return
                    volatility, maximum drawdown, equity exposure, cash
                    exposure and account concentration. Volatility is
                    annualized under the assumption that entered return
                    periods are monthly. Helmio does not yet evaluate expected
                    return, beta, value at risk, downside deviation, liquidity,
                    credit quality or an investor-specific risk tolerance.
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Formula version:
                    {{ $analytics['formula_version'] }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
