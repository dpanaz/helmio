<x-app-layout>
    <x-slot name="header">
        <div>
            <p
                class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
            >
                Read-only monitoring
            </p>

            <h2
                class="mt-2 text-2xl font-semibold tracking-tight text-white"
            >
                Connect an investment account
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Add a manual account for testing or portfolio monitoring.
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >
                <div
                    class="border-b border-slate-800 bg-blue-500/[0.05] p-6 sm:p-8"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 6v6m0 4h.01"
                                />
                            </svg>
                        </div>

                        <div>
                            <p class="font-semibold text-white">
                                Manual account connection
                            </p>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-400"
                            >
                                Use this to enter an account manually.
                                Helmio uses read-only monitoring and never
                                requests trading authority.
                            </p>
                        </div>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('accounts.store') }}"
                    class="space-y-7 p-6 sm:p-8"
                >
                    @csrf

                    <div>
                        <label
                            for="institution_name"
                            class="block text-sm font-medium text-slate-400"
                        >
                            Brokerage or institution
                        </label>

                        <input
                            id="institution_name"
                            name="institution_name"
                            value="{{ old('institution_name') }}"
                            placeholder="Example: Fidelity"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('institution_name')
                            <p class="mt-2 text-sm text-red-300">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-slate-400"
                        >
                            Account name
                        </label>

                        <input
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Example: Retirement account"
                            required
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('name')
                            <p class="mt-2 text-sm text-red-300">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label
                                for="account_type"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Account type
                            </label>

                            <select
                                id="account_type"
                                name="account_type"
                                required
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">
                                    Select type
                                </option>

                                @foreach ([
                                    'individual' => 'Individual brokerage',
                                    'joint' => 'Joint brokerage',
                                    'traditional_ira' => 'Traditional IRA',
                                    'roth_ira' => 'Roth IRA',
                                    'sep_ira' => 'SEP IRA',
                                    '401k' => '401(k)',
                                    '403b' => '403(b)',
                                    'trust' => 'Trust',
                                    '529' => '529 plan',
                                    'other' => 'Other',
                                ] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('account_type') === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('account_type')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="account_number_mask"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Last four digits
                            </label>

                            <input
                                id="account_number_mask"
                                name="account_number_mask"
                                value="{{ old('account_number_mask') }}"
                                maxlength="4"
                                placeholder="1234"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('account_number_mask')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label
                                for="current_value"
                                class="block text-sm font-medium text-slate-400"
                            >
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
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('current_value')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="cash_value"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Cash balance
                            </label>

                            <input
                                id="cash_value"
                                name="cash_value"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old('cash_value', 0) }}"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            @error('cash_value')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div
                        class="border-t border-slate-800 pt-7"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Cost inputs
                        </p>

                        <h3
                            class="mt-2 text-lg font-semibold text-white"
                        >
                            Account expenses
                        </h3>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500"
                        >
                            Enter the advisory and annual account fees shown
                            on your agreement or statement.
                        </p>

                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            <div>
                                <label
                                    for="annual_advisory_fee_rate"
                                    class="block text-sm font-medium text-slate-400"
                                >
                                    Annual advisory fee
                                </label>

                                <div class="relative mt-2">
                                    <input
                                        id="annual_advisory_fee_rate"
                                        name="annual_advisory_fee_rate"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.001"
                                        value="{{ old('annual_advisory_fee_rate') }}"
                                        placeholder="1.25"
                                        class="block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 pr-10 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                                    >

                                    <span
                                        class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-slate-500"
                                    >
                                        %
                                    </span>
                                </div>

                                <p class="mt-2 text-xs text-slate-600">
                                    Enter 1.25 for a 1.25% annual advisory fee.
                                </p>

                                @error('annual_advisory_fee_rate')
                                    <p class="mt-2 text-sm text-red-300">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="annual_account_fee"
                                    class="block text-sm font-medium text-slate-400"
                                >
                                    Fixed annual account fee
                                </label>

                                <div class="relative mt-2">
                                    <span
                                        class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500"
                                    >
                                        $
                                    </span>

                                    <input
                                        id="annual_account_fee"
                                        name="annual_account_fee"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value="{{ old('annual_account_fee', 0) }}"
                                        class="block w-full rounded-xl border-slate-700 bg-slate-950 py-3 pl-8 pr-4 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>

                                @error('annual_account_fee')
                                    <p class="mt-2 text-sm text-red-300">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <label
                            class="mt-6 flex cursor-pointer items-start gap-4 rounded-2xl border border-slate-800 bg-slate-950 p-5"
                        >
                            <input
                                type="checkbox"
                                name="advisory_fee_applies_to_cash"
                                value="1"
                                @checked(old('advisory_fee_applies_to_cash', true))
                                class="mt-0.5 h-5 w-5 rounded border-slate-600 bg-slate-900 text-blue-600 focus:ring-blue-500"
                            >

                            <span>
                                <span
                                    class="block text-sm font-semibold text-slate-300"
                                >
                                    Advisory fee applies to cash
                                </span>

                                <span
                                    class="mt-1 block text-xs leading-5 text-slate-500"
                                >
                                    Uncheck this if cash is excluded from the
                                    advisor’s billing calculation.
                                </span>
                            </span>
                        </label>

                        @error('advisory_fee_applies_to_cash')
                            <p class="mt-2 text-sm text-red-300">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div
                        class="flex flex-wrap justify-end gap-3 border-t border-slate-800 pt-6"
                    >
                        <a
                            href="{{ route('accounts.index') }}"
                            class="rounded-xl border border-slate-700 bg-slate-950 px-5 py-3 font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500"
                        >
                            Add account
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>