<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                {{ $account->name }}
            </p>

            <h2 class="text-2xl font-semibold tracking-tight text-white">
                Add Transaction
            </h2>

            <p class="max-w-3xl text-sm leading-6 text-slate-400">
                Record portfolio activity for this account, including trades,
                deposits, withdrawals, income, fees, and tax information.
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('accounts.transactions.store', $account) }}"
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
                            Transaction details
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Activity
                        </h3>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 sm:grid-cols-2">
                        <div>
                            <label for="transaction_type" class="block text-sm font-medium text-slate-400">
                                Transaction type
                            </label>

                            <select
                                id="transaction_type"
                                name="transaction_type"
                                required
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
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
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="transaction_date" class="block text-sm font-medium text-slate-400">
                                Transaction date
                            </label>

                            <input
                                id="transaction_date"
                                type="date"
                                name="transaction_date"
                                value="{{ old('transaction_date', now()->toDateString()) }}"
                                required
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('transaction_date')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="settlement_date" class="block text-sm font-medium text-slate-400">
                                Settlement date
                            </label>

                            <input
                                id="settlement_date"
                                type="date"
                                name="settlement_date"
                                value="{{ old('settlement_date') }}"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('settlement_date')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="security_id" class="block text-sm font-medium text-slate-400">
                                Security
                            </label>

                            <select
                                id="security_id"
                                name="security_id"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
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
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                            Economics
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Quantity, Price & Amounts
                        </h3>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 sm:grid-cols-2">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-slate-400">
                                Quantity
                            </label>

                            <input
                                id="quantity"
                                type="number"
                                step="0.00000001"
                                name="quantity"
                                value="{{ old('quantity') }}"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('quantity')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-slate-400">
                                Price
                            </label>

                            <input
                                id="price"
                                type="number"
                                step="0.000001"
                                min="0"
                                name="price"
                                value="{{ old('price') }}"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('price')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="gross_amount" class="block text-sm font-medium text-slate-400">
                                Gross amount
                            </label>

                            <div class="relative mt-2">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500">
                                    $
                                </span>

                                <input
                                    id="gross_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="gross_amount"
                                    value="{{ old('gross_amount', 0) }}"
                                    required
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 py-3 pl-8 pr-4 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>

                            <p class="mt-2 text-xs leading-5 text-slate-600">
                                Enter a positive number. Helmio assigns the transaction direction.
                            </p>

                            @error('gross_amount')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="fees" class="block text-sm font-medium text-slate-400">
                                Fees
                            </label>

                            <div class="relative mt-2">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500">
                                    $
                                </span>

                                <input
                                    id="fees"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="fees"
                                    value="{{ old('fees', 0) }}"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 py-3 pl-8 pr-4 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>

                            @error('fees')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-slate-400">
                                Description
                            </label>

                            <input
                                id="description"
                                name="description"
                                value="{{ old('description') }}"
                                placeholder="Optional transaction note"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('description')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-400">
                            Tax context
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Tax Information
                        </h3>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Optional fields used primarily for sales, dividends,
                            distributions, and tax-withholding transactions.
                        </p>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 sm:grid-cols-2">
                        <div>
                            <label for="realized_gain_loss" class="block text-sm font-medium text-slate-400">
                                Realized gain or loss
                            </label>

                            <input
                                id="realized_gain_loss"
                                name="realized_gain_loss"
                                type="number"
                                step="0.01"
                                value="{{ old('realized_gain_loss') }}"
                                placeholder="-1250.00"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-2 text-xs text-slate-600">
                                Enter losses as negative values.
                            </p>

                            @error('realized_gain_loss')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="holding_period_days" class="block text-sm font-medium text-slate-400">
                                Holding period in days
                            </label>

                            <input
                                id="holding_period_days"
                                name="holding_period_days"
                                type="number"
                                min="0"
                                step="1"
                                value="{{ old('holding_period_days') }}"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-2 text-xs text-slate-600">
                                More than 365 days is treated as long-term.
                            </p>

                            @error('holding_period_days')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="tax_withheld" class="block text-sm font-medium text-slate-400">
                                Tax withheld
                            </label>

                            <div class="relative mt-2">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500">
                                    $
                                </span>

                                <input
                                    id="tax_withheld"
                                    name="tax_withheld"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value="{{ old('tax_withheld', 0) }}"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 py-3 pl-8 pr-4 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>

                            @error('tax_withheld')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="space-y-3 sm:pt-7">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-800 bg-slate-950 px-4 py-3">
                                <input
                                    type="checkbox"
                                    name="is_qualified_dividend"
                                    value="1"
                                    @checked(old('is_qualified_dividend'))
                                    class="rounded border-slate-600 bg-slate-900 text-blue-600 focus:ring-blue-500"
                                >

                                <span class="text-sm font-medium text-slate-300">
                                    Qualified dividend
                                </span>
                            </label>

                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-800 bg-slate-950 px-4 py-3">
                                <input
                                    type="checkbox"
                                    name="is_tax_exempt"
                                    value="1"
                                    @checked(old('is_tax_exempt'))
                                    class="rounded border-slate-600 bg-slate-900 text-blue-600 focus:ring-blue-500"
                                >

                                <span class="text-sm font-medium text-slate-300">
                                    Tax-exempt income
                                </span>
                            </label>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('accounts.transactions.index', $account) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-5 py-3 font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500"
                    >
                        Add Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>