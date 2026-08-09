<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                {{ $account->name }}
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Add Holding
            </h2>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                Add a security and the portfolio information Helmio uses
                for cost, diversification, performance, and risk analysis.
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('accounts.holdings.store', $account) }}"
                class="space-y-6"
            >
                @csrf

                @if ($errors->any())
                    <div class="rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5">
                        <p class="font-semibold text-red-300">
                            Please correct the highlighted fields.
                        </p>

                        <ul class="mt-3 space-y-1 text-sm text-slate-400">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                            Security details
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Identity & Classification
                        </h3>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 sm:grid-cols-2">
                        <div>
                            <label for="symbol" class="block text-sm font-medium text-slate-400">
                                Symbol
                            </label>

                            <input
                                id="symbol"
                                name="symbol"
                                value="{{ old('symbol') }}"
                                placeholder="AAPL"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="security_type" class="block text-sm font-medium text-slate-400">
                                Security type
                            </label>

                            <select
                                id="security_type"
                                name="security_type"
                                required
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select type</option>

                                @foreach ([
                                    'stock' => 'Stock',
                                    'etf' => 'ETF',
                                    'mutual_fund' => 'Mutual fund',
                                    'bond' => 'Bond',
                                    'cash' => 'Cash',
                                    'option' => 'Option',
                                    'crypto' => 'Crypto',
                                    'annuity' => 'Annuity',
                                    'other' => 'Other',
                                ] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('security_type') === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="name" class="block text-sm font-medium text-slate-400">
                                Security name
                            </label>

                            <input
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                placeholder="Apple Inc."
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="asset_class" class="block text-sm font-medium text-slate-400">
                                Asset class
                            </label>

                            <input
                                id="asset_class"
                                name="asset_class"
                                value="{{ old('asset_class') }}"
                                placeholder="US Equity"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="sector" class="block text-sm font-medium text-slate-400">
                                Sector
                            </label>

                            <input
                                id="sector"
                                name="sector"
                                value="{{ old('sector') }}"
                                placeholder="Technology"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400">
                            Fund analytics
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Category & Benchmark
                        </h3>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 sm:grid-cols-2">
                        <div>
                            <label for="category" class="block text-sm font-medium text-slate-400">
                                Fund category
                            </label>

                            <input
                                id="category"
                                name="category"
                                value="{{ old('category') }}"
                                placeholder="Large Blend"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-2 text-xs text-slate-600">
                                Examples: Large Blend, Large Growth, Intermediate Bond.
                            </p>
                        </div>

                        <div>
                            <label for="comparison_group" class="block text-sm font-medium text-slate-400">
                                Comparison group
                            </label>

                            <input
                                id="comparison_group"
                                name="comparison_group"
                                value="{{ old('comparison_group') }}"
                                placeholder="us-large-blend"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-2 text-xs text-slate-600">
                                Funds are compared only within the same group.
                            </p>
                        </div>

                        <div>
                            <label for="benchmark_name" class="block text-sm font-medium text-slate-400">
                                Benchmark
                            </label>

                            <input
                                id="benchmark_name"
                                name="benchmark_name"
                                value="{{ old('benchmark_name') }}"
                                placeholder="S&P 500"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div class="flex items-end">
                            <label class="flex w-full cursor-pointer items-center gap-3 rounded-xl border border-slate-800 bg-slate-950 px-4 py-3">
                                <input
                                    type="checkbox"
                                    name="is_index_fund"
                                    value="1"
                                    @checked(old('is_index_fund'))
                                    class="rounded border-slate-600 bg-slate-900 text-blue-600 focus:ring-blue-500"
                                >

                                <span class="text-sm font-medium text-slate-300">
                                    Index fund
                                </span>
                            </label>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                            Historical returns
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Performance Data
                        </h3>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-400">
                                1-year return
                            </label>

                            <div class="relative mt-2">
                                <input
                                    type="number"
                                    step="0.001"
                                    name="trailing_1y_return"
                                    value="{{ old('trailing_1y_return') }}"
                                    placeholder="12.50"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 pr-9 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                                >

                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-slate-500">
                                    %
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400">
                                3-year annualized
                            </label>

                            <div class="relative mt-2">
                                <input
                                    type="number"
                                    step="0.001"
                                    name="trailing_3y_annualized_return"
                                    value="{{ old('trailing_3y_annualized_return') }}"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 pr-9 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >

                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-slate-500">
                                    %
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400">
                                5-year annualized
                            </label>

                            <div class="relative mt-2">
                                <input
                                    type="number"
                                    step="0.001"
                                    name="trailing_5y_annualized_return"
                                    value="{{ old('trailing_5y_annualized_return') }}"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 pr-9 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >

                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-slate-500">
                                    %
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                            Position details
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Holding Value & Cost
                        </h3>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-400">
                                Quantity
                            </label>

                            <input
                                type="number"
                                step="0.00000001"
                                min="0"
                                name="quantity"
                                value="{{ old('quantity', 0) }}"
                                required
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400">
                                Price
                            </label>

                            <div class="relative mt-2">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500">
                                    $
                                </span>

                                <input
                                    type="number"
                                    step="0.000001"
                                    min="0"
                                    name="price"
                                    value="{{ old('price', 0) }}"
                                    required
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 py-3 pl-8 pr-4 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-400">
                                Expense ratio
                            </label>

                            <input
                                type="number"
                                step="0.000001"
                                min="0"
                                max="1"
                                name="expense_ratio"
                                value="{{ old('expense_ratio') }}"
                                placeholder="0.0085"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-2 text-xs text-slate-600">
                                Enter 0.0085 for 0.85%.
                            </p>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-slate-400">
                                Cost basis
                            </label>

                            <div class="relative mt-2">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500">
                                    $
                                </span>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="cost_basis"
                                    value="{{ old('cost_basis') }}"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 py-3 pl-8 pr-4 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('accounts.holdings.index', $account) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-5 py-3 font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500"
                    >
                        Add Holding
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>