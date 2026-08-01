<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                {{ $account->name }}
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Performance data
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Add portfolio snapshot
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Enter at least one beginning snapshot and one ending
                        snapshot. Monthly snapshots provide better analysis.
                    </p>

                    <form
                        method="POST"
                        action="{{ route(
                            'accounts.portfolio-snapshots.store',
                            $account
                        ) }}"
                        class="mt-6 space-y-5"
                    >
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-700">
                                Snapshot date
                            </label>

                            <input
                                type="date"
                                name="snapshot_date"
                                value="{{ old(
                                    'snapshot_date',
                                    now()->toDateString()
                                ) }}"
                                required
                                class="mt-2 block w-full rounded-xl border-slate-300"
                            >
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">
                                    Ending value
                                </label>

                                <input
                                    type="number"
                                    name="ending_value"
                                    min="0"
                                    step="0.01"
                                    value="{{ old(
                                        'ending_value',
                                        $account->current_value
                                    ) }}"
                                    required
                                    class="mt-2 block w-full rounded-xl border-slate-300"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">
                                    Cash value
                                </label>

                                <input
                                    type="number"
                                    name="cash_value"
                                    min="0"
                                    step="0.01"
                                    value="{{ old(
                                        'cash_value',
                                        $account->cash_value
                                    ) }}"
                                    class="mt-2 block w-full rounded-xl border-slate-300"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">
                                External cash flow
                            </label>

                            <input
                                type="number"
                                name="external_cash_flow"
                                step="0.01"
                                value="{{ old(
                                    'external_cash_flow',
                                    0
                                ) }}"
                                class="mt-2 block w-full rounded-xl border-slate-300"
                            >

                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                Deposits are positive. Withdrawals are negative.
                                Do not include dividends, trades, fees or market
                                gains and losses.
                            </p>
                        </div>

                        <button
                            class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                        >
                            Save snapshot
                        </button>
                    </form>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Account benchmark
                    </h3>

                    <form
                        method="POST"
                        action="{{ route(
                            'accounts.benchmark.update',
                            $account
                        ) }}"
                        class="mt-6 space-y-5"
                    >
                        @csrf
                        @method('PUT')

                        <select
                            name="benchmark_id"
                            class="block w-full rounded-xl border-slate-300"
                        >
                            <option value="">
                                No benchmark selected
                            </option>

                            @foreach ($benchmarks as $benchmark)
                                <option
                                    value="{{ $benchmark->id }}"
                                    @selected(
                                        $account->benchmark_id
                                        === $benchmark->id
                                    )
                                >
                                    {{ $benchmark->name }}

                                    @if ($benchmark->symbol)
                                        ({{ $benchmark->symbol }})
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        <button
                            class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white hover:bg-slate-700"
                        >
                            Save benchmark
                        </button>
                    </form>

                    <div class="mt-8 border-t border-slate-200 pt-7">
                        <h4 class="font-semibold text-slate-900">
                            Create benchmark
                        </h4>

                        <form
                            method="POST"
                            action="{{ route('benchmarks.store') }}"
                            class="mt-5 space-y-4"
                        >
                            @csrf

                            <input
                                name="name"
                                placeholder="S&P 500"
                                required
                                class="block w-full rounded-xl border-slate-300"
                            >

                            <div class="grid gap-4 sm:grid-cols-2">
                                <input
                                    name="symbol"
                                    placeholder="SPX"
                                    class="block w-full rounded-xl border-slate-300"
                                >

                                <select
                                    name="benchmark_type"
                                    required
                                    class="block w-full rounded-xl border-slate-300"
                                >
                                    <option value="index">Index</option>
                                    <option value="blended">Blended</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>

                            <button
                                class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                Create benchmark
                            </button>
                        </form>
                    </div>
                </article>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="font-semibold text-slate-900">
                        Portfolio snapshots
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $account->portfolioSnapshots->count() }}
                        snapshots recorded
                    </p>
                </div>

                @if ($account->portfolioSnapshots->isEmpty())
                    <div class="p-12 text-center text-sm text-slate-500">
                        No portfolio snapshots have been entered.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Date
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Value
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Cash
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        External flow
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200">
                                @foreach ($account->portfolioSnapshots as $snapshot)
                                    <tr>
                                        <td class="px-6 py-5 text-sm text-slate-700">
                                            {{ $snapshot->snapshot_date->format(
                                                'M j, Y'
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right font-semibold text-slate-900">
                                            ${{ number_format(
                                                $snapshot->ending_value,
                                                2
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            ${{ number_format(
                                                $snapshot->cash_value,
                                                2
                                            ) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            ${{ number_format(
                                                $snapshot->external_cash_flow,
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

            @if ($account->benchmark)
                <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Add {{ $account->benchmark->name }} return
                    </h3>

                    <form
                        method="POST"
                        action="{{ route(
                            'benchmarks.returns.store',
                            $account->benchmark
                        ) }}"
                        class="mt-6 grid gap-5 md:grid-cols-4"
                    >
                        @csrf

                        <input
                            type="date"
                            name="return_date"
                            required
                            class="rounded-xl border-slate-300"
                        >

                        <input
                            type="number"
                            name="period_return"
                            step="0.001"
                            placeholder="Return %"
                            required
                            class="rounded-xl border-slate-300"
                        >

                        <select
                            name="period_type"
                            required
                            class="rounded-xl border-slate-300"
                        >
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="annual">Annual</option>
                            <option value="daily">Daily</option>
                        </select>

                        <button
                            class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                        >
                            Save return
                        </button>
                    </form>
                </section>
            @endif

            <a
                href="{{ route('accounts.index') }}"
                class="inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
            >
                ← Back to accounts
            </a>
        </div>
    </div>
</x-app-layout>
