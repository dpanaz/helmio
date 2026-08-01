<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Phase 2 analytics
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Performance analysis
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Performance score
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
                        Portfolio return
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if ($analytics['metrics']['portfolio_return'] !== null)
                            {{ number_format(
                                $analytics['metrics']['portfolio_return'] * 100,
                                2
                            ) }}%
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Benchmark return
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if ($analytics['metrics']['benchmark_return'] !== null)
                            {{ number_format(
                                $analytics['metrics']['benchmark_return'] * 100,
                                2
                            ) }}%
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Excess return
                    </p>

                    <p
                        @class([
                            'mt-3 text-3xl font-semibold',
                            'text-emerald-700' =>
                                ($analytics['metrics']['excess_return'] ?? 0) > 0,
                            'text-red-700' =>
                                ($analytics['metrics']['excess_return'] ?? 0) < 0,
                            'text-slate-900' =>
                                ($analytics['metrics']['excess_return'] ?? null) === null
                                || ($analytics['metrics']['excess_return'] ?? 0) === 0,
                        ])
                    >
                        @if ($analytics['metrics']['excess_return'] !== null)
                            {{ $analytics['metrics']['excess_return'] >= 0 ? '+' : '' }}
                            {{ number_format(
                                $analytics['metrics']['excess_return'] * 100,
                                2
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
                        'Beginning value',
                        '$'.number_format(
                            $analytics['metrics']['beginning_value'],
                            2
                        ),
                    ],
                    [
                        'Ending value',
                        '$'.number_format(
                            $analytics['metrics']['ending_value'],
                            2
                        ),
                    ],
                    [
                        'External cash flows',
                        '$'.number_format(
                            $analytics['metrics']['external_cash_flows'],
                            2
                        ),
                    ],
                    [
                        'Net investment growth',
                        '$'.number_format(
                            $analytics['metrics']['net_growth'],
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
                        Performance by account
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Time-weighted return estimates based on entered snapshots.
                    </p>
                </div>

                <div class="divide-y divide-slate-200">
                    @foreach ($analytics['accounts'] as $account)
                        <article class="p-6">
                            <div class="flex flex-wrap items-start justify-between gap-6">
                                <div>
                                    <h4 class="font-semibold text-slate-900">
                                        {{ $account['account_name'] }}
                                    </h4>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $account['benchmark_name'] ?? 'No benchmark selected' }}
                                    </p>

                                    @if ($account['data_warning'])
                                        <p class="mt-3 text-sm text-amber-700">
                                            {{ $account['data_warning'] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 gap-x-10 gap-y-4 text-right sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">
                                            Portfolio
                                        </p>

                                        <p class="mt-1 font-semibold text-slate-900">
                                            @if ($account['time_weighted_return'] !== null)
                                                {{ number_format(
                                                    $account['time_weighted_return'] * 100,
                                                    2
                                                ) }}%
                                            @else
                                                —
                                            @endif
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">
                                            Benchmark
                                        </p>

                                        <p class="mt-1 font-semibold text-slate-900">
                                            @if ($account['benchmark_return'] !== null)
                                                {{ number_format(
                                                    $account['benchmark_return'] * 100,
                                                    2
                                                ) }}%
                                            @else
                                                —
                                            @endif
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">
                                            Excess
                                        </p>

                                        <p class="mt-1 font-semibold text-slate-900">
                                            @if ($account['excess_return'] !== null)
                                                {{ $account['excess_return'] >= 0 ? '+' : '' }}
                                                {{ number_format(
                                                    $account['excess_return'] * 100,
                                                    2
                                                ) }}%
                                            @else
                                                —
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if ($account['time_weighted_return'] !== null)
                                <div class="mt-6 grid gap-4 rounded-2xl bg-slate-50 p-5 sm:grid-cols-4">
                                    <div>
                                        <p class="text-xs text-slate-500">
                                            Period
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-slate-900">
                                            {{ $account['period_start'] }}
                                            to
                                            {{ $account['period_end'] }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-500">
                                            Beginning
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-slate-900">
                                            ${{ number_format(
                                                $account['beginning_value'],
                                                2
                                            ) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-500">
                                            Ending
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-slate-900">
                                            ${{ number_format(
                                                $account['ending_value'],
                                                2
                                            ) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-500">
                                            Snapshots
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-slate-900">
                                            {{ $account['snapshot_count'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <p class="text-sm font-medium text-blue-300">
                    Methodology
                </p>

                <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-300">
                    Time-weighted return is calculated by dividing the account
                    history into periods between portfolio snapshots and
                    geometrically linking those period returns. This version
                    assumes external cash flows occur at the end of each period.
                    Accurate daily performance requires daily valuations and
                    cash-flow timing from a brokerage-data provider.
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Formula version:
                    {{ $analytics['formula_version'] }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
