<x-guest-layout>
    <div
        class="mx-auto grid w-full max-w-7xl gap-12 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-20"
    >
        {{-- Marketing side --}}
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
                Welcome back to
                <span class="text-blue-400">
                    Helmio.
                </span>
            </h1>

            <p
                class="mt-6 max-w-xl text-lg leading-8 text-slate-400"
            >
                See what’s happening inside your investment portfolio,
                understand what deserves attention, and stay informed
                about how your money is being managed.
            </p>

            <div class="mt-10 space-y-5">
                @foreach ([
                    'Monitor investment fees and expenses',
                    'Track performance, risk, and diversification',
                    'Review trading activity and advisor behavior',
                    'Understand your portfolio with Helmio AI',
                    'Read-only access — Helmio cannot move money',
                ] as $item)
                    <div
                        class="flex items-center gap-4"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10"
                        >
                            <svg
                                class="h-4 w-4 text-emerald-400"
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

                        <span
                            class="text-sm font-medium text-slate-300"
                        >
                            {{ $item }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Login card --}}
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
                            Sign in
                        </p>

                        <h2
                            class="mt-3 text-3xl font-semibold tracking-tight text-white"
                        >
                            Access your dashboard
                        </h2>

                        <p
                            class="mt-3 text-sm leading-6 text-slate-500"
                        >
                            Sign in to continue monitoring your portfolio.
                        </p>
                    </div>

                    <x-auth-session-status
                        class="mt-6"
                        :status="session('status')"
                    />

                    <form
                        method="POST"
                        action="{{ route('login') }}"
                        class="mt-8 space-y-5"
                    >
                        @csrf

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
                                autofocus
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
                            <div
                                class="flex items-center justify-between"
                            >
                                <label
                                    for="password"
                                    class="block text-sm font-medium text-slate-300"
                                >
                                    Password
                                </label>

                                @if (Route::has('password.request'))
                                    <a
                                        href="{{ route('password.request') }}"
                                        class="text-xs font-semibold text-blue-400 transition hover:text-blue-300"
                                    >
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                class="mt-2 block w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder-slate-600 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="Enter your password"
                            >

                            <x-input-error
                                :messages="$errors->get('password')"
                                class="mt-2"
                            />
                        </div>

                        {{-- Remember --}}
                        <label
                            for="remember_me"
                            class="flex cursor-pointer items-center gap-3"
                        >
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="rounded border-slate-600 bg-slate-950 text-blue-600 focus:ring-blue-500"
                            >

                            <span
                                class="text-sm text-slate-400"
                            >
                                Keep me signed in
                            </span>
                        </label>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900"
                        >
                            Sign in to Helmio

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
                            New to Helmio?

                            <a
                                href="{{ route('register') }}"
                                class="ml-1 font-semibold text-blue-400 transition hover:text-blue-300"
                            >
                                Create your account
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="mt-5 flex items-center justify-center gap-2 text-xs text-slate-600"
            >
                <svg
                    class="h-4 w-4 text-emerald-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v7.5A1.5 1.5 0 0 1 17.25 21H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z"
                    />
                </svg>

                Secure access to your Helmio account
            </div>
        </div>
    </div>
</x-guest-layout>