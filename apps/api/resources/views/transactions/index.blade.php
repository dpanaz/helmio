<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                    {{ $account->name }}
                </p>

                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                    Transactions
                </h2>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                    Review deposits, withdrawals, trades, income, fees,
                    and other account activity used throughout Helmio analytics.
                </p>
            </div>

            <a
                href="{{ route('accounts.transactions.create', $account) }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 4.5v15m7.5-7.5h-15"
                    />
                </svg>

                Add Transaction
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-300">
                        Deposits
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-white">
                        ${{ number_format($summary['deposits'], 2) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                        Withdrawals
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-white">
                        ${{ number_format($summary['withdrawals'], 2) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-red-500/20 bg-red-500/[0.05] p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-red-300">
                        Recorded fees
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-white">
                        ${{ number_format($summary['fees'], 2) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-300">
                        Trades
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-white">
                        {{ number_format($summary['tradeCount']) }}
                    </p>
                </article>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">
                        Account activity
                    </p>

                    <h3 class="mt-1 text-lg font-semibold text-white">
                        Transaction history
                    </h3>
                </div>

                @if ($transactions->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <h3 class="text-xl font-semibold text-white">
                            No transactions recorded
                        </h3>

                        <p class="mx-auto mt-3 max-w-lg text-sm leading-7 text-slate-500">
                            Add deposits, withdrawals, purchases, sales,
                            dividends, interest, fees, and transfers.
                        </p>

                        <a
                            href="{{ route('accounts.transactions.create', $account) }}"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Add First Transaction
                        </a>
                    </div>
                @else

                    {{-- Desktop table --}}
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full">
                            <thead class="bg-slate-950/80">
                                <tr class="border-b border-slate-800">
                                    @foreach ([
                                        'Date',
                                        'Type',
                                        'Security',
                                        'Gross',
                                        'Fees',
                                        'Net',
                                    ] as $heading)
                                        <th
                                            @class([
                                                'px-6 py-4 text-xs font-semibold uppercase tracking-wide text-slate-600',
                                                'text-left' => in_array($heading, ['Date', 'Type', 'Security']),
                                                'text-right' => ! in_array($heading, ['Date', 'Type', 'Security']),
                                            ])
                                        >
                                            {{ $heading }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-800">
                                @foreach ($transactions as $transaction)
                                    @php
                                        $type =
                                            $transaction->transaction_type;

                                        $typeClasses =
                                            match ($type) {
                                                'buy' =>
                                                    'border-blue-500/20 bg-blue-500/10 text-blue-300',

                                                'sell' =>
                                                    'border-violet-500/20 bg-violet-500/10 text-violet-300',

                                                'deposit',
                                                'transfer_in',
                                                'dividend',
                                                'interest' =>
                                                    'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

                                                'withdrawal',
                                                'transfer_out',
                                                'fee',
                                                'tax' =>
                                                    'border-red-500/20 bg-red-500/10 text-red-300',

                                                default =>
                                                    'border-slate-700 bg-slate-800 text-slate-400',
                                            };
                                    @endphp

                                    <tr class="transition hover:bg-slate-800/25">
                                        <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-400">
                                            {{ $transaction->transaction_date->format('M j, Y') }}
                                        </td>

                                        <td class="px-6 py-5">
                                            <span
                                                class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $typeClasses }}"
                                            >
                                                {{ str($type)
                                                    ->replace('_', ' ')
                                                    ->title() }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-5 text-sm text-slate-300">
                                            {{ $transaction->security?->symbol
                                                ?? $transaction->security?->name
                                                ?? '—' }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-slate-400">
                                            ${{ number_format($transaction->gross_amount, 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-red-300">
                                            ${{ number_format($transaction->fees, 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right font-semibold text-white">
                                            ${{ number_format($transaction->net_amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="divide-y divide-slate-800 md:hidden">
                        @foreach ($transactions as $transaction)
                            <article class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-white">
                                            {{ str($transaction->transaction_type)
                                                ->replace('_', ' ')
                                                ->title() }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $transaction->transaction_date->format('M j, Y') }}
                                        </p>
                                    </div>

                                    <p class="font-semibold text-white">
                                        ${{ number_format($transaction->net_amount, 2) }}
                                    </p>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                                            Security
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-300">
                                            {{ $transaction->security?->symbol
                                                ?? $transaction->security?->name
                                                ?? '—' }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                                            Gross
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-slate-300">
                                            ${{ number_format($transaction->gross_amount, 2) }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                                            Fees
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-red-300">
                                            ${{ number_format($transaction->fees, 2) }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-3">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                                            Net
                                        </p>

                                        <p class="mt-1 text-sm font-semibold text-white">
                                            ${{ number_format($transaction->net_amount, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="border-t border-slate-800 px-6 py-4 text-slate-400">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </section>

            <div class="flex flex-wrap gap-5">
                <a
                    href="{{ route('accounts.holdings.index', $account) }}"
                    class="text-sm font-semibold text-blue-400 transition hover:text-blue-300"
                >
                    View Holdings
                </a>

                <a
                    href="{{ route('accounts.index') }}"
                    class="text-sm font-semibold text-slate-500 transition hover:text-white"
                >
                    Back to Accounts
                </a>
            </div>
        </div>
    </div>
</x-app-layout>