<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                {{ $account->name }}
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Add transaction
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('accounts.transactions.store', $account) }}"
                class="space-y-6 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                @csrf

                @if ($errors->any())
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                        <p class="font-semibold text-red-900">
                            Please correct the highlighted fields.
                        </p>

                        <ul class="mt-3 space-y-1 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label
                            for="transaction_type"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Transaction type
                        </label>

                        <select
                            id="transaction_type"
                            name="transaction_type"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Select type</option>

                            @foreach ([
                                'buy' => 'Buy',
                                'sell' => 'Sell',
                                'deposit' => 'Deposit',
                                'withdrawal' => 'Withdrawal',
                                'dividend' => 'Dividend',
                                'interest' => 'Interest',
                                'fee' => 'Fee',
                                'transfer_in' => 'Transfer in',
                                'transfer_out' => 'Transfer out',
                                'distribution' => 'Distribution',
                                'tax' => 'Tax withholding',
                                'other' => 'Other',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('transaction_type') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('transaction_type')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="transaction_date"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Transaction date
                        </label>

                        <input
                            id="transaction_date"
                            type="date"
                            name="transaction_date"
                            value="{{ old(
                                'transaction_date',
                                now()->toDateString()
                            ) }}"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('transaction_date')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label
                            for="settlement_date"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Settlement date
                        </label>

                        <input
                            id="settlement_date"
                            type="date"
                            name="settlement_date"
                            value="{{ old('settlement_date') }}"
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('settlement_date')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="security_id"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Security
                        </label>

                        <select
                            id="security_id"
                            name="security_id"
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">No security</option>

                            @foreach ($securities as $security)
                                <option
                                    value="{{ $security->id }}"
                                    @selected(
                                        (string) old('security_id')
                                        === (string) $security->id
                                    )
                                >
                                    {{ $security->symbol ?: $security->name }}
                                    — {{ $security->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('security_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label
                            for="quantity"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Quantity
                        </label>

                        <input
                            id="quantity"
                            type="number"
                            step="0.00000001"
                            name="quantity"
                            value="{{ old('quantity') }}"
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('quantity')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="price"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Price
                        </label>

                        <input
                            id="price"
                            type="number"
                            step="0.000001"
                            min="0"
                            name="price"
                            value="{{ old('price') }}"
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('price')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label
                            for="gross_amount"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Gross amount
                        </label>

                        <input
                            id="gross_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            name="gross_amount"
                            value="{{ old('gross_amount', 0) }}"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        <p class="mt-1 text-xs text-slate-500">
                            Enter a positive number. Helmio assigns the
                            transaction direction.
                        </p>

                        @error('gross_amount')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="fees"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Fees
                        </label>

                        <input
                            id="fees"
                            type="number"
                            step="0.01"
                            min="0"
                            name="fees"
                            value="{{ old('fees', 0) }}"
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('fees')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label
                        for="description"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Description
                    </label>

                    <input
                        id="description"
                        name="description"
                        value="{{ old('description') }}"
                        placeholder="Optional transaction note"
                        class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('description')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="border-t border-slate-200 pt-6">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Tax information
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        These fields are optional and primarily apply to sales,
                        dividends, distributions and tax-withholding
                        transactions.
                    </p>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label
                                for="realized_gain_loss"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Realized gain or loss
                            </label>

                            <input
                                id="realized_gain_loss"
                                name="realized_gain_loss"
                                type="number"
                                step="0.01"
                                value="{{ old('realized_gain_loss') }}"
                                placeholder="-1250.00"
                                class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-1 text-xs text-slate-500">
                                Enter losses as negative values.
                            </p>

                            @error('realized_gain_loss')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="holding_period_days"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Holding period in days
                            </label>

                            <input
                                id="holding_period_days"
                                name="holding_period_days"
                                type="number"
                                min="0"
                                step="1"
                                value="{{ old('holding_period_days') }}"
                                class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-1 text-xs text-slate-500">
                                More than 365 days is treated as long-term.
                            </p>

                            @error('holding_period_days')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label
                                for="tax_withheld"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Tax withheld
                            </label>

                            <input
                                id="tax_withheld"
                                name="tax_withheld"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('tax_withheld', 0) }}"
                                class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('tax_withheld')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-4 pt-7">
                            <label class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="is_qualified_dividend"
                                    value="1"
                                    @checked(old('is_qualified_dividend'))
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                >

                                <span class="text-sm font-medium text-slate-700">
                                    Qualified dividend
                                </span>
                            </label>

                            <label class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="is_tax_exempt"
                                    value="1"
                                    @checked(old('is_tax_exempt'))
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                >

                                <span class="text-sm font-medium text-slate-700">
                                    Tax-exempt income
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                    <a
                        href="{{ route('accounts.transactions.index', $account) }}"
                        class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                    >
                        Add transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>