<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Read-only synchronization
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Connect a brokerage
                </h2>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Securely connect an investment account so Helmio can synchronize balances, holdings, and transaction history.
                </p>
            </div>

            <a
                href="{{ route('brokerage-connections.index') }}"
                class="inline-flex w-fit items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
            >
                View Connections
            </a>
        </div>
    </x-slot>

    @php
        $availableProviders = collect(
            $providers ?? ['fake']
        );

        $snapTradeAvailable =
            $availableProviders->contains(
                'snaptrade'
            );

        $fakeAvailable =
            $availableProviders->contains(
                'fake'
            );

        $defaultProvider =
            old(
                'provider',
                $snapTradeAvailable
                    ? 'snaptrade'
                    : 'fake'
            );
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
                    <p class="font-semibold text-red-900">
                        Please correct the following:
                    </p>

                    <ul class="mt-3 space-y-1 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('brokerage-connections.connect') }}"
                class="space-y-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
                x-data="{
                    provider: @js($defaultProvider)
                }"
            >
                @csrf

                <section>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">
                            Choose a connection method
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Use SnapTrade for a real brokerage connection or the fake provider to test Helmio locally.
                        </p>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @if ($snapTradeAvailable)
                            <label
                                class="cursor-pointer rounded-2xl border p-5 transition"
                                :class="provider === 'snaptrade'
                                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100'
                                    : 'border-slate-200 bg-white hover:border-slate-300'"
                            >
                                <div class="flex items-start gap-4">
                                    <input
                                        type="radio"
                                        name="provider"
                                        value="snaptrade"
                                        x-model="provider"
                                        class="mt-1 h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500"
                                    >

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold text-slate-900">
                                                Connect a real brokerage
                                            </p>

                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                Recommended
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            Open SnapTrade's secure connection portal to select your brokerage and complete authentication.
                                        </p>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @foreach ([
                                                'Read-only access',
                                                'Brokerage login portal',
                                                'Automatic synchronization',
                                            ] as $benefit)
                                                <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                                    {{ $benefit }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endif

                        @if ($fakeAvailable)
                            <label
                                class="cursor-pointer rounded-2xl border p-5 transition"
                                :class="provider === 'fake'
                                    ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-100'
                                    : 'border-slate-200 bg-white hover:border-slate-300'"
                            >
                                <div class="flex items-start gap-4">
                                    <input
                                        type="radio"
                                        name="provider"
                                        value="fake"
                                        x-model="provider"
                                        class="mt-1 h-4 w-4 border-slate-300 text-violet-600 focus:ring-violet-500"
                                    >

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold text-slate-900">
                                                Development test provider
                                            </p>

                                            <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                                Local testing
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            Import a sample $250,000 account with Apple and Vanguard holdings to verify the synchronization flow.
                                        </p>
                                    </div>
                                </div>
                            </label>
                        @endif
                    </div>

                    @error('provider')
                        <p class="mt-3 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </section>

                <section
                    x-show="provider === 'fake'"
                    x-cloak
                    class="rounded-2xl border border-violet-200 bg-violet-50 p-5"
                >
                    <p class="font-semibold text-violet-950">
                        Development connection
                    </p>

                    <p class="mt-2 text-sm leading-6 text-violet-800">
                        This does not connect to a real financial institution. It creates deterministic sample data for development and automated testing.
                    </p>
                </section>

                <section
                    x-show="provider === 'snaptrade'"
                    x-cloak
                    class="rounded-2xl border border-blue-200 bg-blue-50 p-5"
                >
                    <p class="font-semibold text-blue-950">
                        Secure hosted connection
                    </p>

                    <p class="mt-2 text-sm leading-6 text-blue-800">
                        You will leave Helmio briefly to choose your brokerage and authenticate in the secure SnapTrade connection portal. You will return automatically when the connection is complete.
                    </p>
                </section>

                <section class="grid gap-6 md:grid-cols-2">
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
                            value="{{ old('brokerage_name') }}"
                            placeholder="Example: Retirement Accounts"
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Optional label to help identify this connection inside Helmio.
                        </p>

                        @error('brokerage_name')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div
                        x-show="provider === 'snaptrade'"
                        x-cloak
                    >
                        <label
                            for="brokerage_slug"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Brokerage slug
                        </label>

                        <input
                            id="brokerage_slug"
                            name="brokerage_slug"
                            value="{{ old('brokerage_slug') }}"
                            placeholder="Optional"
                            class="mt-2 block w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                        >

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Leave blank to let the user choose from all supported brokerages.
                        </p>

                        @error('brokerage_slug')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </section>

                <section class="rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-900">
                        Helmio will synchronize
                    </p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            [
                                'title' => 'Account balances',
                                'description' => 'Current account and cash values',
                            ],
                            [
                                'title' => 'Current holdings',
                                'description' => 'Positions, quantities, and market values',
                            ],
                            [
                                'title' => 'Transaction history',
                                'description' => 'Trades, fees, dividends, and transfers',
                            ],
                        ] as $capability)
                            <div class="rounded-xl bg-white p-4 ring-1 ring-slate-200">
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $capability['title'] }}
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $capability['description'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                    <div class="flex gap-4">
                        <svg
                            class="mt-0.5 h-6 w-6 shrink-0 text-emerald-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>

                        <div>
                            <p class="font-semibold text-emerald-950">
                                No trading authority
                            </p>

                            <p class="mt-1 text-sm leading-6 text-emerald-800">
                                Helmio requests read-only access. The connection cannot buy, sell, transfer, withdraw, or modify your brokerage account.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
                    <a
                        href="{{ route('brokerage-connections.index') }}"
                        class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-500"
                    >
                        <span x-show="provider === 'snaptrade'">
                            Continue to secure portal
                        </span>

                        <span
                            x-show="provider === 'fake'"
                            x-cloak
                        >
                            Create test connection
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>