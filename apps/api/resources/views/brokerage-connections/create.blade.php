<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Read-only synchronization
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Connect a brokerage
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('brokerage-connections.connect') }}"
                class="space-y-8 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                @csrf

                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                    <p class="font-semibold text-blue-950">
                        Development connection
                    </p>

                    <p class="mt-2 text-sm leading-6 text-blue-800">
                        This test provider imports a sample $250,000 brokerage
                        account with Apple and Vanguard holdings. It lets us
                        verify the entire synchronization flow before adding
                        live SnapTrade credentials.
                    </p>
                </div>

                <input
                    type="hidden"
                    name="provider"
                    value="fake"
                >

                <div>
                    <label
                        for="brokerage_name"
                        class="block text-sm font-medium text-slate-700"
                    >
                        Connection name
                    </label>

                    <input
                        id="brokerage_name"
                        name="brokerage_name"
                        value="{{ old(
                            'brokerage_name',
                            'Helmio Test Brokerage'
                        ) }}"
                        required
                        class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('brokerage_name')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">
                        Helmio will import
                    </p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            'Account balances',
                            'Current holdings',
                            'Transaction history',
                        ] as $capability)
                            <div class="rounded-xl bg-white px-4 py-3 text-sm font-medium text-slate-700">
                                {{ $capability }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-5">
                    <div class="flex gap-4">
                        <svg
                            class="mt-0.5 h-6 w-6 shrink-0 text-emerald-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>

                        <div>
                            <p class="font-semibold text-slate-900">
                                No trading authority
                            </p>

                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                This connection cannot buy, sell, transfer,
                                withdraw or modify your brokerage account.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
                    <a
                        href="{{ route('brokerage-connections.index') }}"
                        class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                    >
                        Continue securely
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>