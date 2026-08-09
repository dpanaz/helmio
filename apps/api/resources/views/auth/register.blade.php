<x-guest-layout>
    <div
        class="mx-auto grid w-full max-w-7xl gap-12 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-16"
    >
        {{-- Marketing --}}
        <div class="hidden lg:block">
            <div
                class="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-4 py-2 text-sm font-medium text-blue-300"
            >
                <span
                    class="h-2 w-2 rounded-full bg-emerald-400"
                ></span>

                Independent investment oversight
            </div>

            <h1
                class="mt-7 max-w-xl text-5xl font-semibold tracking-tight text-white"
            >
                Give your portfolio a
                <span class="text-blue-400">
                    second set of eyes.
                </span>
            </h1>

            <p
                class="mt-6 max-w-xl text-lg leading-8 text-slate-400"
            >
                Helmio independently monitors the details that can quietly
                affect your wealth and makes them easier to understand.
            </p>

            <div
                class="mt-10 grid gap-4"
            >
                @foreach ([
                    [
                        'title' => 'Fees & Costs',
                        'text' => 'See advisor fees, fund expenses, transaction costs, and your all-in cost.',
                    ],
                    [
                        'title' => 'Risk & Performance',
                        'text' => 'Monitor diversification, concentration, risk, returns, and benchmark comparisons.',
                    ],
                    [
                        'title' => 'Advisor Oversight',
                        'text' => 'Surface unusual trading, high-cost investments, and findings worth discussing.',
                    ],
                    [
                        'title' => 'AI Insights',
                        'text' => 'Turn complicated portfolio analytics into clear, plain-English explanations.',
                    ],
                ] as $item)
                    <div
                        class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5"
                    >
                        <div
                            class="flex items-start gap-4"
                        >
                            <div
                                class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10"
                            >
                                <svg
                                    class="h-4 w-4 text-blue-400"
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
                            </div>

                            <div>
                                <h3
                                    class="font-semibold text-white"
                                >
                                    {{ $item['title'] }}
                                </h3>

                                <p
                                    class="mt-1 text-sm leading-6 text-slate-500"
                                >
                                    {{ $item['text'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div
                class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-xs text-slate-500"
            >
                <span class="flex items-center gap-2">
                    <span
                        class="h-2 w-2 rounded-full bg-emerald-400"
                    ></span>

                    Read-only connections
                </span>

                <span class="flex items-center gap-2">
                    <span
                        class="h-2 w-2 rounded-full bg-emerald-400"
                    ></span>

                    No trading authority
                </span>

                <span class="flex items-center gap-2">
                    <span
                        class="h-2 w-2 rounded-full bg-emerald-400"
                    ></span>

                    Cannot move money
                </span>
            </div>
        </div>

        {{-- Registration card --}}
        <div
            class="mx-auto w-full max-w-md"
        >
            <div
                class="overflow-hidden rounded-3xl border border-slate-700 bg-slate-900 shadow-2xl"
            >
                <div
                    class="h-1 bg-gradient-to-r from-blue-600 via-blue-400 to-cyan-400"
                ></div>

                <div class="p-6 sm:p-8">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-widest text-blue-400"
                        >
                            Create account
                        </p>

                        <h2
                            class="mt-3 text-3xl font-semibold tracking-tight text-white"
                        >
                            Start with Helmio
                        </h2>

                        <p
                            class="mt-3 text-sm leading-6 text-slate-500"
                        >
                            Create your account and begin building your
                            independent portfolio oversight profile.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('register') }}"
                        class="mt-8 space-y-5"
                    >
                        @csrf

                        {{-- Name --}}
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-medium text-slate-300"
                            >
                                Full name
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                class="mt-2 block w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="Your name"
                            >

                            <x-input-error
                                :messages="$errors->get('name')"
                                class="mt-2"
                            />
                        </div>

                        {{-- Email --}}
                        <div>
                            <label
                                for="email"
                                class="block text-sm font-medium text-slate-300"
                            >
                                Email address
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="username"
                                class="mt-2 block w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="you@example.com"
                            >

                            <x-input-error
                                :messages="$errors->get('email')"
                                class="mt-2"
                            />
                        </div>

                        {{-- Password --}}
                        <div>
                            <label
                                for="password"
                                class="block text-sm font-medium text-slate-300"
                            >
                                Password
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="mt-2 block w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="Create a password"
                            >

                            <x-input-error
                                :messages="$errors->get('password')"
                                class="mt-2"
                            />
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label
                                for="password_confirmation"
                                class="block text-sm font-medium text-slate-300"
                            >
                                Confirm password
                            </label>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="mt-2 block w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="Confirm your password"
                            >
                        </div>

                        <div
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-4"
                        >
                            <div
                                class="flex items-start gap-3"
                            >
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 3 5 6v5c0 4 2.5 7.5 7 10 4.5-2.5 7-6 7-10V6l-7-3Zm-3 9 2 2 4-4"
                                    />
                                </svg>

                                <p
                                    class="text-xs leading-6 text-slate-500"
                                >
                                    Helmio uses read-only access for connected
                                    brokerage accounts. Helmio cannot place
                                    trades or move your money.
                                </p>
                            </div>
                        </div>

                        <p
                            class="text-xs leading-5 text-slate-600"
                        >
                            By creating an account, you agree to the

                            <a
                                href="{{ route('terms') }}"
                                class="font-medium text-slate-400 hover:text-white"
                            >
                                Terms of Service
                            </a>

                            and acknowledge the

                            <a
                                href="{{ route('privacy') }}"
                                class="font-medium text-slate-400 hover:text-white"
                            >
                                Privacy Policy
                            </a>.
                        </p>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900"
                        >
                            Create Helmio Account

                            <svg
                                class="ml-2 h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m9 18 6-6-6-6"
                                />
                            </svg>
                        </button>
                    </form>

                    <div
                        class="mt-7 border-t border-slate-800 pt-6 text-center"
                    >
                        <p class="text-sm text-slate-500">
                            Already have an account?

                            <a
                                href="{{ route('login') }}"
                                class="ml-1 font-semibold text-blue-400 transition hover:text-blue-300"
                            >
                                Sign in
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>