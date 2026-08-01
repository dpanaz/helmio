<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">{{ $account->name }}</p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">Add holding</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('accounts.holdings.store', $account) }}"
                class="space-y-6 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                @csrf

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Symbol</label>
                        <input
                            name="symbol"
                            value="{{ old('symbol') }}"
                            placeholder="AAPL"
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Security type</label>
                        <select
                            name="security_type"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                            <option value="">Select type</option>
                            <option value="stock">Stock</option>
                            <option value="etf">ETF</option>
                            <option value="mutual_fund">Mutual fund</option>
                            <option value="bond">Bond</option>
                            <option value="cash">Cash</option>
                            <option value="option">Option</option>
                            <option value="crypto">Crypto</option>
                            <option value="annuity">Annuity</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Security name</label>
                    <input
                        name="name"
                        value="{{ old('name') }}"
                        required
                        placeholder="Apple Inc."
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Asset class</label>
                        <input
                            name="asset_class"
                            value="{{ old('asset_class') }}"
                            placeholder="US Equity"
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Sector</label>
                        <input
                            name="sector"
                            value="{{ old('sector') }}"
                            placeholder="Technology"
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>
                </div>
                <div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label
            for="category"
            class="block text-sm font-medium text-slate-700"
        >
            Fund category
        </label>

        <input
            id="category"
            name="category"
            value="{{ old('category') }}"
            placeholder="Large Blend"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        <p class="mt-1 text-xs text-slate-500">
            Examples: Large Blend, Large Growth, Intermediate Bond.
        </p>
    </div>

    <div>
        <label
            for="comparison_group"
            class="block text-sm font-medium text-slate-700"
        >
            Comparison group
        </label>

        <input
            id="comparison_group"
            name="comparison_group"
            value="{{ old('comparison_group') }}"
            placeholder="us-large-blend"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        <p class="mt-1 text-xs text-slate-500">
            Funds are compared only within the same group.
        </p>
    </div>
</div>

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label
            for="benchmark_name"
            class="block text-sm font-medium text-slate-700"
        >
            Benchmark
        </label>

        <input
            id="benchmark_name"
            name="benchmark_name"
            value="{{ old('benchmark_name') }}"
            placeholder="S&P 500"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >
    </div>

    <label class="flex items-center gap-3 pt-8">
        <input
            type="checkbox"
            name="is_index_fund"
            value="1"
            @checked(old('is_index_fund'))
            class="rounded border-slate-300 text-blue-600"
        >

        <span class="text-sm font-medium text-slate-700">
            Index fund
        </span>
    </label>
</div>

<div class="grid gap-6 sm:grid-cols-3">
    <div>
        <label class="block text-sm font-medium text-slate-700">
            1-year return
        </label>

        <input
            type="number"
            step="0.001"
            name="trailing_1y_return"
            value="{{ old('trailing_1y_return') }}"
            placeholder="12.50"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        <p class="mt-1 text-xs text-slate-500">
            Enter 12.50 for 12.50%.
        </p>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">
            3-year annualized
        </label>

        <input
            type="number"
            step="0.001"
            name="trailing_3y_annualized_return"
            value="{{ old('trailing_3y_annualized_return') }}"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">
            5-year annualized
        </label>

        <input
            type="number"
            step="0.001"
            name="trailing_5y_annualized_return"
            value="{{ old('trailing_5y_annualized_return') }}"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >
    </div>
</div>

                <div class="grid gap-6 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Quantity</label>
                        <input
                            type="number"
                            step="0.00000001"
                            min="0"
                            name="quantity"
                            value="{{ old('quantity', 0) }}"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Price</label>
                        <input
                            type="number"
                            step="0.000001"
                            min="0"
                            name="price"
                            value="{{ old('price', 0) }}"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">
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
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                        <p class="mt-1 text-xs text-slate-500">
                            Enter 0.0085 for 0.85%.
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Cost basis</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="cost_basis"
                        value="{{ old('cost_basis') }}"
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                    <a
                        href="{{ route('accounts.holdings.index', $account) }}"
                        class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700"
                    >
                        Cancel
                    </a>

                    <button
                        class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                    >
                        Add holding
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
