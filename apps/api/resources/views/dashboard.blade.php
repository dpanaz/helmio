<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">Independent investment oversight</p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Welcome back, {{ auth()->user()->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl lg:col-span-2">
                    <div class="flex flex-wrap items-start justify-between gap-6">
                        <div>
                            <p class="text-sm text-slate-400">Current Helm Score</p>
                            <div class="mt-3 flex items-end gap-4">
                                <span class="text-6xl font-semibold">—</span>
                                <span class="pb-2 text-slate-400">Awaiting account data</span>
                            </div>
                        </div>

                        <span class="rounded-full border border-blue-400/30 bg-blue-400/10 px-4 py-2 text-sm text-blue-200">
                            Setup required
                        </span>
                    </div>

                    <div class="mt-10 h-2 overflow-hidden rounded-full bg-white/10">
                        <div class="h-full w-0 rounded-full bg-blue-500"></div>
                    </div>

                    <p class="mt-5 max-w-2xl text-sm leading-6 text-slate-400">
                        Connect an investment account to calculate your cost,
                        performance, diversification, risk and trading scores.
                    </p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Next step</p>
                    <h3 class="mt-3 text-xl font-semibold text-slate-900">
                        Connect your first account
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Helmio will use read-only access. It will never place trades or move money.
                    </p>

                    <a
    href="{{ route('accounts.create') }}"
    class="mt-8 block w-full rounded-xl bg-blue-600 px-4 py-3 text-center font-semibold text-white hover:bg-blue-500"
>
    Connect account
</a>
                </div>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
    ['Portfolio value', '$'.number_format($portfolioValue, 2)],
    ['Cash balance', '$'.number_format($cashValue, 2)],
    ['Connected accounts', $accountCount],
    ['Open alerts', '0'],
] as [$label, $value])
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $value }}</p>
                    </div>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Account monitoring</p>
                            <h3 class="mt-1 text-xl font-semibold text-slate-900">Recent alerts</h3>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600">
                            No alerts
                        </span>
                    </div>

                    <div class="mt-10 rounded-2xl border border-dashed border-slate-300 p-8 text-center">
                        <p class="font-medium text-slate-900">No investment data yet</p>
                        <p class="mt-2 text-sm text-slate-500">
                            Alerts will appear after your first account analysis.
                        </p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">What Helmio monitors</p>

                    <div class="mt-6 space-y-5">
                        @foreach ([
                            'Advisory, brokerage and product expenses',
                            'Mutual fund and ETF expense ratios',
                            'Turnover and excessive-trading indicators',
                            'Performance relative to appropriate benchmarks',
                            'Concentration, overlap and cash drag',
                        ] as $item)
                            <div class="flex gap-3">
                                <div class="mt-1 h-5 w-5 flex-none rounded-full bg-blue-100"></div>
                                <p class="text-sm leading-6 text-slate-700">{{ $item }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
