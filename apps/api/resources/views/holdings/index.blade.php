<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    {{ $account->name }}
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Holdings
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Review the securities held in this account, including
                    market value, position size, pricing, and fund expenses.
                </p>
            </div>

            <a
                href="{{ route('accounts.holdings.create', $account) }}"
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

                Add Holding
            </a>
        </div>
    </x-slot>

    @php
        $expenseTrackedCount =
            $holdings
                ->whereNotNull('security.expense_ratio')
                ->count();

        $largestHolding =
            $holdings
                ->sortByDesc('market_value')
                ->first();

        $largestHoldingWeight =
            $largestHolding
            && (float) $totalValue > 0
                ? (
                    (float) $largestHolding->market_value
                    / (float) $totalValue
                ) * 100
                : null;
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"
        >

            @if (session('success'))
                <div
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif


            {{-- ================================================= --}}
            {{-- SUMMARY --}}
            {{-- ================================================= --}}

            <section
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >

                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                    >
                        Account value
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        ${{ number_format($totalValue, 2) }}
                    </p>
                </article>


                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                    >
                        Holdings
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        {{ number_format($holdings->count()) }}
                    </p>
                </article>


                <article
                    class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-6 shadow-xl"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-300"
                    >
                        Expense monitoring
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        {{ number_format($expenseTrackedCount) }}
                    </p>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        funds with expense data
                    </p>
                </article>


                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                    >
                        Largest position
                    </p>

                    @if ($largestHolding)
                        <p
                            class="mt-3 truncate text-lg font-semibold text-white"
                        >
                            {{ $largestHolding->security->symbol
                                ?: $largestHolding->security->name }}
                        </p>

                        @if ($largestHoldingWeight !== null)
                            <p
                                class="mt-2 text-sm font-medium text-blue-300"
                            >
                                {{ number_format(
                                    $largestHoldingWeight,
                                    1
                                ) }}%
                                of account
                            </p>
                        @endif
                    @else
                        <p
                            class="mt-3 text-2xl font-semibold text-white"
                        >
                            —
                        </p>
                    @endif
                </article>

            </section>


            {{-- ================================================= --}}
            {{-- HOLDINGS --}}
            {{-- ================================================= --}}

            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >

                <div
                    class="border-b border-slate-800 px-6 py-5 sm:px-8"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400"
                            >
                                Portfolio positions
                            </p>

                            <h3
                                class="mt-1 text-lg font-semibold text-white"
                            >
                                Current Holdings
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Securities currently included in this account.
                            </p>
                        </div>

                        @if ($holdings->isNotEmpty())
                            <span
                                class="inline-flex w-fit rounded-full border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs font-semibold text-slate-400"
                            >
                                {{ number_format(
                                    $holdings->count()
                                ) }}

                                {{ Str::plural(
                                    'position',
                                    $holdings->count()
                                ) }}
                            </span>
                        @endif
                    </div>
                </div>


                @if ($holdings->isEmpty())

                    <div
                        class="px-6 py-16 text-center sm:px-8"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-800 bg-slate-950 text-slate-500"
                        >
                            <svg
                                class="h-7 w-7"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.75 18.75h16.5M5.25 15V9m4.5 6V5.25m4.5 9.75v-3.75m4.5 3.75V7.5"
                                />
                            </svg>
                        </div>

                        <h3
                            class="mt-5 text-xl font-semibold text-white"
                        >
                            No holdings yet
                        </h3>

                        <p
                            class="mx-auto mt-3 max-w-lg text-sm leading-7 text-slate-500"
                        >
                            Add a stock, ETF, mutual fund, bond,
                            cash position, or other investment to
                            begin monitoring this account.
                        </p>

                        <a
                            href="{{ route(
                                'accounts.holdings.create',
                                $account
                            ) }}"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Add First Holding
                        </a>
                    </div>

                @else

                    {{-- Desktop table --}}
                    <div class="hidden overflow-x-auto md:block">

                        <table class="min-w-full">

                            <thead class="bg-slate-950/80">
                                <tr class="border-b border-slate-800">

                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600"
                                    >
                                        Security
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-600"
                                    >
                                        Quantity
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-600"
                                    >
                                        Price
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-600"
                                    >
                                        Market Value
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-600"
                                    >
                                        Weight
                                    </th>

                                    <th
                                        class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-600"
                                    >
                                        Expense Ratio
                                    </th>

                                </tr>
                            </thead>


                            <tbody class="divide-y divide-slate-800">

                                @foreach ($holdings as $holding)

                                    @php
                                        $holdingWeight =
                                            (float) $totalValue > 0
                                                ? (
                                                    (float) $holding->market_value
                                                    / (float) $totalValue
                                                ) * 100
                                                : null;

                                        $securityType =
                                            str(
                                                $holding
                                                    ->security
                                                    ->security_type
                                            )
                                                ->replace('_', ' ')
                                                ->title();

                                        $expenseRatio =
                                            $holding
                                                ->security
                                                ->expense_ratio !== null
                                                ? (
                                                    $holding
                                                        ->security
                                                        ->expense_ratio
                                                    * 100
                                                )
                                                : null;

                                        $expenseClasses =
                                            match (true) {
                                                $expenseRatio === null =>
                                                    null,

                                                $expenseRatio >= 1 =>
                                                    'border-red-500/20 bg-red-500/10 text-red-300',

                                                $expenseRatio >= 0.50 =>
                                                    'border-amber-500/20 bg-amber-500/10 text-amber-300',

                                                default =>
                                                    'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
                                            };
                                    @endphp

                                    <tr
                                        class="transition hover:bg-slate-800/25"
                                    >

                                        <td class="px-6 py-5">
                                            <div
                                                class="flex items-center gap-4"
                                            >
                                                <div
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-800 bg-slate-950 text-xs font-bold text-blue-300"
                                                >
                                                    {{ strtoupper(
                                                        substr(
                                                            $holding
                                                                ->security
                                                                ->symbol
                                                                ?: $holding
                                                                    ->security
                                                                    ->name,
                                                            0,
                                                            2
                                                        )
                                                    ) }}
                                                </div>

                                                <div class="min-w-0">

                                                    <p
                                                        class="font-semibold text-white"
                                                    >
                                                        {{ $holding
                                                            ->security
                                                            ->symbol
                                                            ?: $holding
                                                                ->security
                                                                ->name }}
                                                    </p>

                                                    <p
                                                        class="mt-1 max-w-sm truncate text-sm text-slate-500"
                                                    >
                                                        {{ $holding
                                                            ->security
                                                            ->name }}

                                                        <span
                                                            class="mx-1 text-slate-700"
                                                        >
                                                            •
                                                        </span>

                                                        {{ $securityType }}
                                                    </p>

                                                </div>
                                            </div>
                                        </td>


                                        <td
                                            class="px-6 py-5 text-right text-sm text-slate-400"
                                        >
                                            {{ number_format(
                                                $holding->quantity,
                                                4
                                            ) }}
                                        </td>


                                        <td
                                            class="px-6 py-5 text-right text-sm text-slate-400"
                                        >
                                            ${{ number_format(
                                                $holding->price,
                                                2
                                            ) }}
                                        </td>


                                        <td
                                            class="px-6 py-5 text-right"
                                        >
                                            <p
                                                class="font-semibold text-white"
                                            >
                                                ${{ number_format(
                                                    $holding->market_value,
                                                    2
                                                ) }}
                                            </p>
                                        </td>


                                        <td
                                            class="px-6 py-5 text-right"
                                        >
                                            @if ($holdingWeight !== null)

                                                <div
                                                    class="inline-flex min-w-20 flex-col items-end"
                                                >
                                                    <span
                                                        class="text-sm font-semibold text-slate-300"
                                                    >
                                                        {{ number_format(
                                                            $holdingWeight,
                                                            1
                                                        ) }}%
                                                    </span>

                                                    <div
                                                        class="mt-2 h-1.5 w-16 overflow-hidden rounded-full bg-slate-800"
                                                    >
                                                        <div
                                                            class="h-full rounded-full bg-blue-500"
                                                            style="width: {{ min(
                                                                100,
                                                                $holdingWeight
                                                            ) }}%"
                                                        ></div>
                                                    </div>
                                                </div>

                                            @else

                                                <span class="text-slate-600">
                                                    —
                                                </span>

                                            @endif
                                        </td>


                                        <td
                                            class="px-6 py-5 text-right"
                                        >
                                            @if ($expenseRatio !== null)

                                                <span
                                                    class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $expenseClasses }}"
                                                >
                                                    {{ number_format(
                                                        $expenseRatio,
                                                        2
                                                    ) }}%
                                                </span>

                                            @else

                                                <span class="text-slate-600">
                                                    —
                                                </span>

                                            @endif
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>
                        </table>

                    </div>


                    {{-- Mobile cards --}}
                    <div
                        class="divide-y divide-slate-800 md:hidden"
                    >

                        @foreach ($holdings as $holding)

                            @php
                                $holdingWeight =
                                    (float) $totalValue > 0
                                        ? (
                                            (float) $holding->market_value
                                            / (float) $totalValue
                                        ) * 100
                                        : null;

                                $expenseRatio =
                                    $holding
                                        ->security
                                        ->expense_ratio !== null
                                        ? (
                                            $holding
                                                ->security
                                                ->expense_ratio
                                            * 100
                                        )
                                        : null;
                            @endphp

                            <article class="p-5">

                                <div
                                    class="flex items-start justify-between gap-4"
                                >

                                    <div
                                        class="flex min-w-0 items-center gap-3"
                                    >
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-800 bg-slate-950 text-xs font-bold text-blue-300"
                                        >
                                            {{ strtoupper(
                                                substr(
                                                    $holding
                                                        ->security
                                                        ->symbol
                                                        ?: $holding
                                                            ->security
                                                            ->name,
                                                    0,
                                                    2
                                                )
                                            ) }}
                                        </div>

                                        <div class="min-w-0">

                                            <p
                                                class="truncate font-semibold text-white"
                                            >
                                                {{ $holding
                                                    ->security
                                                    ->symbol
                                                    ?: $holding
                                                        ->security
                                                        ->name }}
                                            </p>

                                            <p
                                                class="mt-1 truncate text-xs text-slate-500"
                                            >
                                                {{ $holding
                                                    ->security
                                                    ->name }}
                                            </p>

                                        </div>
                                    </div>


                                    <p
                                        class="shrink-0 text-right font-semibold text-white"
                                    >
                                        ${{ number_format(
                                            $holding->market_value,
                                            2
                                        ) }}
                                    </p>

                                </div>


                                <div
                                    class="mt-5 grid grid-cols-2 gap-3"
                                >

                                    <div
                                        class="rounded-xl border border-slate-800 bg-slate-950 p-3"
                                    >
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-slate-600"
                                        >
                                            Quantity
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-300"
                                        >
                                            {{ number_format(
                                                $holding->quantity,
                                                4
                                            ) }}
                                        </p>
                                    </div>


                                    <div
                                        class="rounded-xl border border-slate-800 bg-slate-950 p-3"
                                    >
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-slate-600"
                                        >
                                            Price
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-300"
                                        >
                                            ${{ number_format(
                                                $holding->price,
                                                2
                                            ) }}
                                        </p>
                                    </div>


                                    <div
                                        class="rounded-xl border border-slate-800 bg-slate-950 p-3"
                                    >
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-slate-600"
                                        >
                                            Weight
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-300"
                                        >
                                            {{ $holdingWeight !== null
                                                ? number_format(
                                                    $holdingWeight,
                                                    1
                                                ).'%'
                                                : '—' }}
                                        </p>
                                    </div>


                                    <div
                                        class="rounded-xl border border-slate-800 bg-slate-950 p-3"
                                    >
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wide text-slate-600"
                                        >
                                            Expense Ratio
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold text-slate-300"
                                        >
                                            {{ $expenseRatio !== null
                                                ? number_format(
                                                    $expenseRatio,
                                                    2
                                                ).'%'
                                                : '—' }}
                                        </p>
                                    </div>

                                </div>

                            </article>

                        @endforeach

                    </div>

                @endif

            </section>


            {{-- ================================================= --}}
            {{-- ACTIONS --}}
            {{-- ================================================= --}}

            <div
                class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between"
            >

                <a
                    href="{{ route('accounts.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 transition hover:text-white"
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
                            d="m15 18-6-6 6-6"
                        />
                    </svg>

                    Back to Accounts
                </a>


                <div class="flex flex-wrap gap-2">

                    <a
                        href="{{ route(
                            'accounts.transactions.index',
                            $account
                        ) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-blue-500/50 hover:text-white"
                    >
                        View Transactions
                    </a>

                    <a
                        href="{{ route(
                            'accounts.holdings.create',
                            $account
                        ) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        Add Holding
                    </a>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>