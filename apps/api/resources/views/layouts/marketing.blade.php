<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="scroll-smooth"
>
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield(
            'title',
            'Helmio | Independent Investment Account Monitoring'
        )
    </title>

    <meta
        name="description"
        content="@yield('meta_description')"
    >

    <meta name="robots" content="index,follow">

    <link
        rel="canonical"
        href="@yield('canonical', url()->current())"
    >

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Helmio">

    <meta
        property="og:title"
        content="@yield('title', 'Helmio')"
    >

    <meta
        property="og:description"
        content="@yield('meta_description')"
    >

    <meta
        property="og:url"
        content="@yield('canonical', url()->current())"
    >

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('head')
</head>

<body
    class="min-h-screen bg-slate-950 text-white antialiased"
>
    <div class="flex min-h-screen flex-col">

        <header
            class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/95 shadow-lg shadow-black/10 backdrop-blur-xl"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8"
            >
                <a
                    href="{{ url('/') }}"
                    class="group flex shrink-0 items-center gap-3"
                    aria-label="Helmio home"
                >
                    <img
                        src="{{ asset('helmio-mark.svg') }}"
                        alt=""
                        class="h-10 w-10 rounded-xl shadow-lg shadow-blue-950/30 transition group-hover:scale-105"
                    >

                    <div>
                        <span
                            class="block text-xl font-semibold tracking-tight text-white"
                        >
                            Helmio
                        </span>

                        <span
                            class="hidden text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500 sm:block"
                        >
                            Investment Oversight
                        </span>
                    </div>
                </a>

                <nav
                    class="hidden items-center gap-8 text-sm font-medium text-slate-300 md:flex"
                    aria-label="Primary navigation"
                >
                    <a
                        href="{{ route('marketing.how-it-works') }}"
                        class="transition hover:text-white"
                    >
                        How it works
                    </a>

                    <a
                        href="{{ route('marketing.advisor-check') }}"
                        class="transition hover:text-white"
                    >
                        Advisor check
                    </a>

                    <a
                        href="{{ route('marketing.security') }}"
                        class="transition hover:text-white"
                    >
                        Security
                    </a>

                    <a
                        href="{{ route('billing.pricing') }}"
                        class="transition hover:text-white"
                    >
                        Pricing
                    </a>
                </nav>

                <div class="flex shrink-0 items-center gap-3">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white transition hover:border-blue-400/40 hover:bg-blue-500/10"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="hidden text-sm font-semibold text-slate-300 transition hover:text-white sm:inline"
                        >
                            Sign in
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-500"
                        >
                            Get started
                        </a>
                    @endauth
                </div>
            </div>

            <nav
                class="flex items-center gap-6 overflow-x-auto border-t border-white/[0.06] px-4 py-3 text-xs font-medium text-slate-400 md:hidden"
                aria-label="Mobile navigation"
            >
                <a
                    href="{{ route('marketing.how-it-works') }}"
                    class="shrink-0 transition hover:text-white"
                >
                    How it works
                </a>

                <a
                    href="{{ route('marketing.advisor-check') }}"
                    class="shrink-0 transition hover:text-white"
                >
                    Advisor check
                </a>

                <a
                    href="{{ route('marketing.security') }}"
                    class="shrink-0 transition hover:text-white"
                >
                    Security
                </a>

                <a
                    href="{{ route('billing.pricing') }}"
                    class="shrink-0 transition hover:text-white"
                >
                    Pricing
                </a>
            </nav>
        </header>

        <main class="flex-1">
            @yield('content')
        </main>

        <footer
            class="border-t border-white/10 bg-slate-950"
        >
            <div
                class="mx-auto grid max-w-7xl gap-10 px-6 py-14 md:grid-cols-3 lg:px-8"
            >
                <div>
                    <a
                        href="{{ url('/') }}"
                        class="flex items-center gap-3"
                    >
                        <img
                            src="{{ asset('helmio-mark.svg') }}"
                            alt=""
                            class="h-9 w-9 rounded-xl"
                        >

                        <div>
                            <span
                                class="block text-lg font-semibold text-white"
                            >
                                Helmio
                            </span>

                            <span
                                class="text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-500"
                            >
                                Investment Oversight
                            </span>
                        </div>
                    </a>

                    <p
                        class="mt-5 max-w-sm text-sm leading-7 text-slate-400"
                    >
                        Independent monitoring for professionally
                        managed investment accounts.
                    </p>

                    <p
                        class="mt-4 max-w-sm text-xs leading-6 text-slate-600"
                    >
                        Helmio provides monitoring and educational
                        information. It does not provide investment,
                        tax, or legal advice.
                    </p>
                </div>

                <div class="text-sm">
                    <h2
                        class="font-semibold uppercase tracking-[0.12em] text-slate-300"
                    >
                        Learn
                    </h2>

                    <div
                        class="mt-5 grid gap-3 text-slate-500"
                    >
                        <a
                            href="{{ route('marketing.advisor-fees') }}"
                            class="transition hover:text-white"
                        >
                            Advisor fees
                        </a>

                        <a
                            href="{{ route('marketing.advisor-performance') }}"
                            class="transition hover:text-white"
                        >
                            Advisor performance
                        </a>

                        <a
                            href="{{ route('marketing.portfolio-churning') }}"
                            class="transition hover:text-white"
                        >
                            Excessive trading
                        </a>

                        <a
                            href="{{ route('marketing.portfolio-diversification') }}"
                            class="transition hover:text-white"
                        >
                            Diversification
                        </a>

                        <a
                            href="{{ route('marketing.portfolio-risk') }}"
                            class="transition hover:text-white"
                        >
                            Portfolio risk
                        </a>

                        <a
                            href="{{ route('marketing.tax-efficiency') }}"
                            class="transition hover:text-white"
                        >
                            Tax efficiency
                        </a>
                    </div>
                </div>

                <div class="text-sm">
                    <h2
                        class="font-semibold uppercase tracking-[0.12em] text-slate-300"
                    >
                        Company
                    </h2>

                    <div
                        class="mt-5 grid gap-3 text-slate-500"
                    >
                        <a
                            href="{{ route('marketing.how-it-works') }}"
                            class="transition hover:text-white"
                        >
                            How it works
                        </a>

                        <a
                            href="{{ route('marketing.security') }}"
                            class="transition hover:text-white"
                        >
                            Security
                        </a>

                        <a
                            href="{{ route('billing.pricing') }}"
                            class="transition hover:text-white"
                        >
                            Pricing
                        </a>

                        <a
                            href="{{ route('contact') }}"
                            class="transition hover:text-white"
                        >
                            Contact
                        </a>

                        <a
                            href="{{ route('privacy') }}"
                            class="transition hover:text-white"
                        >
                            Privacy
                        </a>

                        <a
                            href="{{ route('terms') }}"
                            class="transition hover:text-white"
                        >
                            Terms
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/[0.07]">
                <div
                    class="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-6 text-xs text-slate-600 sm:flex-row sm:items-center sm:justify-between lg:px-8"
                >
                    <p>
                        &copy; {{ now()->year }} Helmio.
                        All rights reserved.
                    </p>

                    <p>
                        Read-only portfolio monitoring.
                    </p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>