<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                    Read-only synchronization
                </p>

                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                    Connect a Brokerage
                </h2>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
                    Securely connect an investment account so Helmio can
                    synchronize balances, holdings, and transaction history.
                </p>
            </div>

            <a
                href="{{ route('brokerage-connections.index') }}"
                class="inline-flex w-fit items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
            >
                View Connections
            </a>
        </div>
    </x-slot>

    @php
        /*
         * SnapTrade is the safe fallback.
         *
         * The controller already removes the fake provider
         * in production. This Blade also refuses to expose
         * it in production as a second layer of protection.
         */
        $availableProviders = collect(
            $providers ?? ['snaptrade']
        );

        if (app()->environment('production')) {
            $availableProviders =
                $availableProviders
                    ->reject(
                        fn ($provider) =>
                            $provider === 'fake'
                    )
                    ->values();
        }

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
                    : (
                        $fakeAvailable
                            ? 'fake'
                            : ''
                    )
            );
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5">
                    <p class="font-semibold text-red-300">
                        Please correct the following:
                    </p>

                    <ul class="mt-3 space-y-1 text-sm text-slate-400">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('brokerage-connections.connect') }}"
                class="space-y-6"
                x-data="{
                    provider: @js($defaultProvider)
                }"
            >
                @csrf

                @if ($isOnboarding ?? false)
                    <input
                        type="hidden"
                        name="onboarding"
                        value="1"
                    >
                @endif

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                            Connection method
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Connect Your Brokerage
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Helmio uses a secure, read-only connection to synchronize
                            the investment information needed to monitor your portfolio.
                        </p>
                    </div>

                    <div
                        @class([
                            'grid gap-4 p-6 sm:p-8',
                            'md:grid-cols-2' => $fakeAvailable,
                            'grid-cols-1' => ! $fakeAvailable,
                        ])
                    >
                        @if ($snapTradeAvailable)
                            <label
                                class="cursor-pointer rounded-2xl border p-5 transition"
                                :class="provider === 'snaptrade'
                                    ? 'border-blue-500/50 bg-blue-500/[0.08] ring-2 ring-blue-500/10'
                                    : 'border-slate-800 bg-slate-950 hover:border-slate-700'"
                            >
                                <div class="flex items-start gap-4">
                                    <input
                                        type="radio"
                                        name="provider"
                                        value="snaptrade"
                                        x-model="provider"
                                        class="mt-1 h-4 w-4 border-slate-600 bg-slate-950 text-blue-600 focus:ring-blue-500"
                                    >

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold text-white">
                                                Connect a Brokerage
                                            </p>

                                            <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-300">
                                                Secure
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm leading-6 text-slate-400">
                                            Continue to the secure connection portal
                                            to select your financial institution and
                                            authenticate your account.
                                        </p>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @foreach ([
                                                'Read-only access',
                                                'Secure authentication',
                                                'Automatic synchronization',
                                            ] as $benefit)
                                                <span class="rounded-full border border-slate-800 bg-slate-900 px-3 py-1 text-xs font-medium text-slate-400">
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
                                    ? 'border-violet-500/50 bg-violet-500/[0.08] ring-2 ring-violet-500/10'
                                    : 'border-slate-800 bg-slate-950 hover:border-slate-700'"
                            >
                                <div class="flex items-start gap-4">
                                    <input
                                        type="radio"
                                        name="provider"
                                        value="fake"
                                        x-model="provider"
                                        class="mt-1 h-4 w-4 border-slate-600 bg-slate-950 text-violet-600 focus:ring-violet-500"
                                    >

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-semibold text-white">
                                                Development Test Provider
                                            </p>

                                            <span class="rounded-full border border-violet-500/20 bg-violet-500/10 px-2.5 py-1 text-xs font-semibold text-violet-300">
                                                Local testing
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm leading-6 text-slate-400">
                                            Import a sample $250,000 account with
                                            Apple and Vanguard holdings to verify the
                                            synchronization flow.
                                        </p>
                                    </div>
                                </div>
                            </label>
                        @endif

                        @if (! $snapTradeAvailable && ! $fakeAvailable)
                            <div class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] p-5">
                                <p class="font-semibold text-amber-200">
                                    Brokerage connections are temporarily unavailable.
                                </p>

                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                    Please try again later.
                                </p>
                            </div>
                        @endif
                    </div>

                    @error('provider')
                        <div class="px-6 pb-6 sm:px-8">
                            <p class="text-sm text-red-300">
                                {{ $message }}
                            </p>
                        </div>
                    @enderror
                </section>

                @if ($fakeAvailable)
                    <section
                        x-show="provider === 'fake'"
                        x-cloak
                        class="rounded-2xl border border-violet-500/20 bg-violet-500/[0.06] p-5"
                    >
                        <p class="font-semibold text-violet-200">
                            Development Connection
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            This does not connect to a real financial institution.
                            It creates deterministic sample data for development
                            and automated testing.
                        </p>
                    </section>
                @endif

                @if ($snapTradeAvailable)
                    <section
                        x-show="provider === 'snaptrade'"
                        x-cloak
                        class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.06] p-5"
                    >
                        <div class="flex gap-4">
                            <svg
                                class="mt-0.5 h-6 w-6 shrink-0 text-blue-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4"
                                />
                            </svg>

                            <div>
                                <p class="font-semibold text-blue-200">
                                    Secure Hosted Connection
                                </p>

                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                    You'll leave Helmio briefly to select your
                                    brokerage and authenticate through the secure
                                    connection portal. You'll return automatically
                                    when the connection is complete.
                                </p>
                            </div>
                        </div>
                    </section>
                @endif

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                            Connection details
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            Name Your Connection
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Give this brokerage connection an optional name
                            so it's easy to identify inside Helmio.
                        </p>
                    </div>

                    <div class="p-6 sm:p-8">
                        <div class="max-w-xl">
                            <label
                                for="brokerage_name"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Connection Name
                            </label>

                            <input
                                id="brokerage_name"
                                name="brokerage_name"
                                value="{{ old('brokerage_name') }}"
                                placeholder="Example: Retirement Accounts"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-2 text-xs leading-5 text-slate-600">
                                Optional. You can use a name such as
                                "Retirement Accounts" or "Joint Brokerage."
                            </p>

                            @error('brokerage_name')
                                <p class="mt-2 text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if ($snapTradeAvailable)
                            <input
                                type="hidden"
                                name="brokerage_slug"
                                value="{{ old('brokerage_slug') }}"
                            >
                        @endif
                    </div>
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="border-b border-slate-800 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                            Data access
                        </p>

                        <h3 class="mt-2 text-lg font-semibold text-white">
                            What Helmio Synchronizes
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Helmio imports the information needed to monitor
                            and analyze your investments.
                        </p>
                    </div>

                    <div class="grid gap-4 p-6 sm:p-8 sm:grid-cols-3">
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
                            <div class="rounded-2xl border border-slate-800 bg-slate-950 p-5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300">
                                    <svg
                                        class="h-4 w-4"
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
                                </div>

                                <p class="mt-4 text-sm font-semibold text-white">
                                    {{ $capability['title'] }}
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    {{ $capability['description'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.06] p-5">
                    <div class="flex gap-4">
                        <svg
                            class="mt-0.5 h-6 w-6 shrink-0 text-emerald-300"
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
                            <p class="font-semibold text-emerald-200">
                                Read-Only Access
                            </p>

                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Helmio cannot buy, sell, transfer, withdraw,
                                or make changes to your brokerage account.
                                Your connection is used only to synchronize
                                investment data for monitoring and analysis.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('brokerage-connections.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-5 py-3 font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        @disabled(! $snapTradeAvailable && ! $fakeAvailable)
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span x-show="provider === 'snaptrade'">
                            Continue to Secure Portal
                        </span>

                        @if ($fakeAvailable)
                            <span
                                x-show="provider === 'fake'"
                                x-cloak
                            >
                                Create Test Connection
                            </span>
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>