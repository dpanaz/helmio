<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    {{ $account->name }}
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Holdings
                </h2>
            </div>

            <a
                href="{{ route('accounts.holdings.create', $account) }}"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
            >
                Add holding
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <section class="grid gap-5 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Account value</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($totalValue, 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Holdings</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $holdings->count() }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Expense monitoring</p>
                    <p class="mt-3 text-lg font-semibold text-slate-900">
                        {{ $holdings->whereNotNull('security.expense_ratio')->count() }} funds tracked
                    </p>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                @if ($holdings->isEmpty())
                    <div class="p-12 text-center">
                        <h3 class="text-xl font-semibold text-slate-900">
                            No holdings yet
                        </h3>

                        <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-slate-600">
                            Add a stock, ETF, mutual fund, bond, cash position or other investment.
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
                                        Quantity
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Price
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Value
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Expense ratio
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200">
                                @foreach ($holdings as $holding)
                                    <tr>
                                        <td class="px-6 py-5">
                                            <div class="font-semibold text-slate-900">
                                                {{ $holding->security->symbol ?: $holding->security->name }}
                                            </div>

                                            <div class="mt-1 text-sm text-slate-500">
                                                {{ $holding->security->name }}
                                                · {{ str($holding->security->security_type)->replace('_', ' ')->title() }}
                                            </div>
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            {{ number_format($holding->quantity, 4) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            ${{ number_format($holding->price, 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right font-semibold text-slate-900">
                                            ${{ number_format($holding->market_value, 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-slate-700">
                                            @if ($holding->security->expense_ratio !== null)
                                                {{ number_format($holding->security->expense_ratio * 100, 2) }}%
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

            <a
                href="{{ route('accounts.index') }}"
                class="inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
            >
                ← Back to accounts
            </a>
        </div>
    </div>
</x-app-layout>
