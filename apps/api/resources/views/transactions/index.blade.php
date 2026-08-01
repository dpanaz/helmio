<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">{{ $account->name }}</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Transactions</h2>
            </div>

            <a
                href="{{ route('accounts.transactions.create', $account) }}"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
            >
                Add transaction
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

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Deposits</p>
                    <p class="mt-3 text-2xl font-semibold text-emerald-700">
                        ${{ number_format($summary['deposits'], 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Withdrawals</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        ${{ number_format($summary['withdrawals'], 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Recorded fees</p>
                    <p class="mt-3 text-2xl font-semibold text-red-700">
                        ${{ number_format($summary['fees'], 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Trades</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        {{ number_format($summary['tradeCount']) }}
                    </p>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                @if ($transactions->isEmpty())
                    <div class="p-12 text-center">
                        <h3 class="text-xl font-semibold text-slate-900">
                            No transactions recorded
                        </h3>

                        <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-slate-600">
                            Add deposits, withdrawals, purchases, sales, dividends and fees.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Date
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Type
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Security
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Gross
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Fees
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Net
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200">
                                @foreach ($transactions as $transaction)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600">
                                            {{ $transaction->transaction_date->format('M j, Y') }}
                                        </td>

                                        <td class="px-6 py-5">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">
                                                {{ str($transaction->transaction_type)->replace('_', ' ')->title() }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-5 text-sm text-slate-700">
                                            {{ $transaction->security?->symbol
                                                ?? $transaction->security?->name
                                                ?? '—' }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-slate-700">
                                            ${{ number_format($transaction->gross_amount, 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-red-700">
                                            ${{ number_format($transaction->fees, 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right font-semibold text-slate-900">
                                            ${{ number_format($transaction->net_amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-200 px-6 py-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </section>

            <div class="flex gap-6">
                <a
                    href="{{ route('accounts.holdings.index', $account) }}"
                    class="text-sm font-semibold text-blue-600 hover:text-blue-500"
                >
                    View holdings
                </a>

                <a
                    href="{{ route('accounts.index') }}"
                    class="text-sm font-semibold text-slate-600 hover:text-slate-900"
                >
                    Back to accounts
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
