<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">Read-only monitoring</p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">Connect an investment account</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                    <p class="font-semibold text-blue-900">Manual connection for the first build</p>
                    <p class="mt-2 text-sm leading-6 text-blue-800">
                        This lets us build and test the portfolio experience before connecting Plaid or SnapTrade.
                        Helmio will never request trading authority.
                    </p>
                </div>

                <form method="POST" action="{{ route('accounts.store') }}" class="mt-8 space-y-6">
                    @csrf

                    <div>
                        <label for="institution_name" class="block text-sm font-medium text-slate-700">
                            Brokerage or institution
                        </label>

                        <input
                            id="institution_name"
                            name="institution_name"
                            value="{{ old('institution_name') }}"
                            placeholder="Example: Fidelity"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('institution_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">
                            Account name
                        </label>

                        <input
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Example: Retirement account"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="account_type" class="block text-sm font-medium text-slate-700">
                                Account type
                            </label>

                            <select
                                id="account_type"
                                name="account_type"
                                required
                                class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select type</option>
                                <option value="individual">Individual brokerage</option>
                                <option value="joint">Joint brokerage</option>
                                <option value="traditional_ira">Traditional IRA</option>
                                <option value="roth_ira">Roth IRA</option>
                                <option value="sep_ira">SEP IRA</option>
                                <option value="401k">401(k)</option>
                                <option value="403b">403(b)</option>
                                <option value="trust">Trust</option>
                                <option value="529">529 plan</option>
                                <option value="other">Other</option>
                            </select>

                            @error('account_type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="account_number_mask" class="block text-sm font-medium text-slate-700">
                                Last four digits
                            </label>

                            <input
                                id="account_number_mask"
                                name="account_number_mask"
                                value="{{ old('account_number_mask') }}"
                                maxlength="4"
                                placeholder="1234"
                                class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="current_value" class="block text-sm font-medium text-slate-700">
                                Current account value
                            </label>

                            <input
                                id="current_value"
                                name="current_value"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('current_value', 0) }}"
                                required
                                class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label for="cash_value" class="block text-sm font-medium text-slate-700">
                                Cash balance
                            </label>

                            <input
                                id="cash_value"
                                name="cash_value"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('cash_value', 0) }}"
                                class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
                        <a
                            href="{{ route('accounts.index') }}"
                            class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                        >
                            Add account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
