<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">{{ $account->name }}</p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">Add transaction</h2>
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

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Transaction type
                        </label>

                        <select
                            name="transaction_type"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                            <option value="">Select type</option>
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                            <option value="dividend">Dividend</option>
                            <option value="interest">Interest</option>
                            <option value="fee">Fee</option>
                            <option value="transfer_in">Transfer in</option>
                            <option value="transfer_out">Transfer out</option>
                            <option value="distribution">Distribution</option>
                            <option value="tax">Tax withholding</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Transaction date
                        </label>

                        <input
                            type="date"
                            name="transaction_date"
                            value="{{ old('transaction_date', now()->toDateString()) }}"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">
                        Security
                    </label>

                    <select
                        name="security_id"
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >
                        <option value="">No security</option>

                        @foreach ($securities as $security)
                            <option value="{{ $security->id }}">
                                {{ $security->symbol ?: $security->name }}
                                — {{ $security->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Quantity
                        </label>

                        <input
                            type="number"
                            step="0.00000001"
                            name="quantity"
                            value="{{ old('quantity') }}"
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Price
                        </label>

                        <input
                            type="number"
                            step="0.000001"
                            min="0"
                            name="price"
                            value="{{ old('price') }}"
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Gross amount
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="gross_amount"
                            value="{{ old('gross_amount', 0) }}"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >

                        <p class="mt-1 text-xs text-slate-500">
                            Enter a positive number. Helmio assigns the correct direction.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Fees
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="fees"
                            value="{{ old('fees', 0) }}"
                            class="mt-2 block w-full rounded-xl border-slate-300"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">
                        Description
                    </label>

                    <input
                        name="description"
                        value="{{ old('description') }}"
                        placeholder="Optional note"
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                    <a
                        href="{{ route('accounts.transactions.index', $account) }}"
                        class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700"
                    >
                        Cancel
                    </a>

                    <button
                        class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                    >
                        Add transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
