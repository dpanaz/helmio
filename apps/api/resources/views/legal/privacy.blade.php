<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="Helmio Privacy Policy. Learn how Helmio collects, uses, protects, and processes account, portfolio, brokerage, payment, and technical information."
    >

    <meta
        name="theme-color"
        content="#020617"
    >

    <title>
        Privacy Policy — Helmio
    </title>

    <link
        rel="icon"
        type="image/x-icon"
        href="{{ asset('favicon.ico') }}"
    >

    <link
        rel="shortcut icon"
        href="{{ asset('favicon.ico') }}"
    >

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('icons/icon-192.png') }}"
    >

    <link
        rel="apple-touch-icon"
        href="{{ asset('apple-touch-icon.png') }}"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body
    class="min-h-screen bg-slate-950 text-white antialiased"
>
    <div class="relative min-h-screen">

        {{-- Background glow --}}
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-[40rem] overflow-hidden"
        >
            <div
                class="absolute -left-40 -top-48 h-[34rem] w-[34rem] rounded-full bg-blue-600/20 blur-3xl"
            ></div>

            <div
                class="absolute -right-40 top-0 h-[30rem] w-[30rem] rounded-full bg-cyan-400/10 blur-3xl"
            ></div>
        </div>

        {{-- Header --}}
        <header
            class="relative z-30 border-b border-white/10 bg-slate-950/80 backdrop-blur"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8"
            >
                <a
                    href="/"
                    class="flex min-w-0 items-center gap-3"
                >
                    <img
                        src="{{ asset('icons/icon-192.png') }}"
                        alt="Helmio"
                        class="h-11 w-11 shrink-0 rounded-xl shadow-lg shadow-blue-950/40"
                    >

                    <div>
                        <p
                            class="text-lg font-semibold leading-5 tracking-tight text-white"
                        >
                            Helmio
                        </p>

                        <p
                            class="mt-1 text-[10px] uppercase tracking-[0.18em] text-slate-400"
                        >
                            Investment oversight
                        </p>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <a
                        href="/"
                        class="hidden text-sm font-semibold text-slate-400 transition hover:text-white sm:inline-flex"
                    >
                        Home
                    </a>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-slate-100"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="hidden px-3 py-2 text-sm font-semibold text-slate-300 transition hover:text-white sm:inline-flex"
                        >
                            Log in
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                        >
                            Start free trial
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="relative">

            {{-- Hero --}}
            <section
                class="mx-auto max-w-7xl px-4 pb-10 pt-16 sm:px-6 sm:pb-14 sm:pt-20 lg:px-8"
            >
                <div class="mx-auto max-w-4xl text-center">
                    <p
                        class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-400"
                    >
                        Helmio Privacy
                    </p>

                    <h1
                        class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-5xl"
                    >
                        Privacy Policy
                    </h1>

                    <p
                        class="mt-4 text-sm text-slate-500"
                    >
                        Last updated: August 2026
                    </p>

                    <p
                        class="mx-auto mt-6 max-w-3xl text-lg leading-8 text-slate-400"
                    >
                        Your financial information deserves careful treatment.
                        This policy explains what information Helmio collects,
                        how we use it, and how we work to protect it.
                    </p>
                </div>
            </section>

            {{-- Privacy highlights --}}
            <section
                class="mx-auto max-w-6xl px-4 pb-12 sm:px-6 lg:px-8"
            >
                <div
                    class="grid gap-5 md:grid-cols-3"
                >
                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-6"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300 ring-1 ring-emerald-400/20"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3 5.25 5.25v5.625c0 4.065 2.73 7.83 6.75 9.375 4.02-1.545 6.75-5.31 6.75-9.375V5.25L12 3Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m9 12 2 2 4-4"
                                />
                            </svg>
                        </div>

                        <h2
                            class="mt-5 text-lg font-semibold text-white"
                        >
                            Read-only connections
                        </h2>

                        <p
                            class="mt-2 text-sm leading-7 text-slate-400"
                        >
                            Brokerage connections are intended for monitoring
                            and analysis. Helmio does not use them to place
                            trades or move money.
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-6"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-300 ring-1 ring-blue-400/20"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75m6-1.5c0 5.25-3.438 10.125-9 11.625C6.438 18.375 3 13.5 3 8.25V5.625A2.625 2.625 0 0 1 5.625 3h12.75A2.625 2.625 0 0 1 21 5.625V8.25Z"
                                />
                            </svg>
                        </div>

                        <h2
                            class="mt-5 text-lg font-semibold text-white"
                        >
                            We don't sell your data
                        </h2>

                        <p
                            class="mt-2 text-sm leading-7 text-slate-400"
                        >
                            Helmio does not sell your personal information.
                            Information may be shared with service providers
                            only as needed to operate Helmio.
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-6"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-500/10 text-violet-300 ring-1 ring-violet-400/20"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                                />
                            </svg>
                        </div>

                        <h2
                            class="mt-5 text-lg font-semibold text-white"
                        >
                            Controlled AI processing
                        </h2>

                        <p
                            class="mt-2 text-sm leading-7 text-slate-400"
                        >
                            Relevant portfolio context may be processed by
                            third-party AI providers when needed to generate
                            Helmio insights and explanations.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Policy --}}
            <section
                class="border-t border-white/10 bg-slate-900/40"
            >
                <div
                    class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20"
                >
                    <article
                        class="rounded-3xl border border-white/10 bg-slate-950/70 px-6 py-8 shadow-2xl shadow-black/10 sm:px-10 sm:py-12"
                    >
                        <div
                            class="space-y-12 text-sm leading-7 text-slate-400"
                        >
                            <section>
                                <p class="text-base leading-8 text-slate-300">
                                    Helmio respects your privacy. This Privacy
                                    Policy explains the types of information we
                                    may collect, how we use it, and the choices
                                    available to you.
                                </p>
                            </section>

                            {{-- 1 --}}
                            <section>
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    1. Information We Collect
                                </h2>

                                <div class="mt-6 space-y-7">
                                    <div>
                                        <h3
                                            class="font-semibold text-slate-200"
                                        >
                                            Account Information
                                        </h3>

                                        <p class="mt-2">
                                            When you create an account, we may
                                            collect information such as your
                                            name, email address, account
                                            credentials, and related account
                                            information.
                                        </p>
                                    </div>

                                    <div>
                                        <h3
                                            class="font-semibold text-slate-200"
                                        >
                                            Portfolio and Brokerage Information
                                        </h3>

                                        <p class="mt-2">
                                            When you connect investment
                                            accounts, Helmio may process
                                            information including account
                                            balances, holdings, securities,
                                            transactions, portfolio
                                            allocations, performance history,
                                            account metadata, and
                                            synchronization information.
                                        </p>

                                        <p class="mt-3">
                                            Brokerage connections used by
                                            Helmio are intended to operate with
                                            read-only permissions.
                                        </p>
                                    </div>

                                    <div>
                                        <h3
                                            class="font-semibold text-slate-200"
                                        >
                                            Payment Information
                                        </h3>

                                        <p class="mt-2">
                                            Payments may be processed by Stripe
                                            or another third-party payment
                                            provider. Helmio does not store your
                                            full payment-card details.
                                        </p>
                                    </div>

                                    <div>
                                        <h3
                                            class="font-semibold text-slate-200"
                                        >
                                            Technical Information
                                        </h3>

                                        <p class="mt-2">
                                            We may collect IP address, browser
                                            type, operating system, device
                                            information, usage activity,
                                            security events, and application
                                            error logs.
                                        </p>
                                    </div>
                                </div>
                            </section>

                            {{-- 2 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    2. How We Use Information
                                </h2>

                                <p class="mt-4">
                                    We may use information to provide and
                                    improve Helmio, calculate portfolio
                                    analytics, generate AI-powered
                                    explanations, process subscriptions,
                                    provide support, maintain security, prevent
                                    abuse, monitor application reliability, and
                                    comply with legal obligations.
                                </p>
                            </section>

                            {{-- 3 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    3. AI Processing
                                </h2>

                                <p class="mt-4">
                                    Helmio may securely provide relevant
                                    portfolio context to third-party AI service
                                    providers for the purpose of generating
                                    summaries, explanations, and portfolio
                                    insights.
                                </p>

                                <p class="mt-3">
                                    Helmio is designed to limit AI processing
                                    to information necessary to provide the
                                    requested service.
                                </p>
                            </section>

                            {{-- 4 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    4. Information Sharing
                                </h2>

                                <p class="mt-4 font-medium text-slate-300">
                                    Helmio does not sell your personal
                                    information.
                                </p>

                                <p class="mt-3">
                                    We may share information with service
                                    providers when necessary to operate Helmio,
                                    including providers supporting:
                                </p>

                                <div
                                    class="mt-5 grid gap-3 sm:grid-cols-2"
                                >
                                    @foreach ([
                                        'Payment processing',
                                        'Cloud hosting and infrastructure',
                                        'Brokerage connectivity',
                                        'Artificial-intelligence functionality',
                                        'Email and customer communications',
                                        'Security and fraud prevention',
                                    ] as $item)
                                        <div
                                            class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-4 py-3"
                                        >
                                            <svg
                                                class="mt-0.5 h-4 w-4 shrink-0 text-blue-400"
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

                                            <span>
                                                {{ $item }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            {{-- 5 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    5. Data Security
                                </h2>

                                <p class="mt-4">
                                    We use administrative, technical, and
                                    organizational safeguards designed to
                                    protect personal and financial information.
                                </p>

                                <p class="mt-3">
                                    However, no method of electronic
                                    transmission or storage can be guaranteed
                                    to be completely secure.
                                </p>
                            </section>

                            {{-- 6 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    6. Data Retention
                                </h2>

                                <p class="mt-4">
                                    We retain information for as long as
                                    reasonably necessary to provide Helmio,
                                    maintain account records, comply with legal
                                    obligations, resolve disputes, prevent
                                    fraud, and enforce our agreements.
                                </p>
                            </section>

                            {{-- 7 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    7. Your Privacy Rights
                                </h2>

                                <p class="mt-4">
                                    Depending on where you live, applicable law
                                    may provide rights related to your personal
                                    information, including rights to access,
                                    correct, delete, obtain a copy of, object
                                    to, or restrict certain processing of your
                                    information.
                                </p>

                                <p class="mt-3">
                                    Requests may be sent to
                                    <a
                                        href="mailto:privacy@myhelmio.com"
                                        class="font-semibold text-blue-400 transition hover:text-blue-300"
                                    >
                                        privacy@myhelmio.com
                                    </a>.
                                </p>
                            </section>

                            {{-- 8 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    8. Cookies
                                </h2>

                                <p class="mt-4">
                                    Helmio may use cookies and similar
                                    technologies to authenticate users,
                                    maintain sessions, remember preferences,
                                    protect account security, improve
                                    performance, and understand application
                                    usage.
                                </p>
                            </section>

                            {{-- 9 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    9. Children's Privacy
                                </h2>

                                <p class="mt-4">
                                    Helmio is not intended for children under
                                    18, and we do not knowingly collect personal
                                    information from children under 18.
                                </p>
                            </section>

                            {{-- 10 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    10. Changes to This Privacy Policy
                                </h2>

                                <p class="mt-4">
                                    We may update this Privacy Policy from time
                                    to time. Material changes will be reflected
                                    on this page along with an updated effective
                                    date.
                                </p>
                            </section>

                            {{-- 11 --}}
                            <section
                                class="border-t border-white/10 pt-10"
                            >
                                <h2
                                    class="text-2xl font-semibold text-white"
                                >
                                    11. Contact
                                </h2>

                                <p class="mt-4">
                                    Questions about this Privacy Policy may be
                                    sent to
                                    <a
                                        href="mailto:privacy@myhelmio.com"
                                        class="font-semibold text-blue-400 transition hover:text-blue-300"
                                    >
                                        privacy@myhelmio.com
                                    </a>.
                                </p>
                            </section>
                        </div>
                    </article>
                </div>
            </section>
        </main>

        {{-- Footer --}}
        <footer
            class="border-t border-white/10 bg-slate-950"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 text-sm sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8"
            >
                <div
                    class="flex items-center gap-3"
                >
                    <img
                        src="{{ asset('icons/icon-192.png') }}"
                        alt="Helmio"
                        class="h-8 w-8 rounded-lg"
                    >

                    <div>
                        <p
                            class="font-semibold text-slate-300"
                        >
                            Helmio
                        </p>

                        <p
                            class="text-xs text-slate-500"
                        >
                            Independent investment oversight
                        </p>
                    </div>
                </div>

                <div
                    class="flex flex-wrap gap-x-6 gap-y-3 text-sm"
                >
                    <a
                        href="/"
                        class="text-slate-500 transition hover:text-white"
                    >
                        Home
                    </a>

                    <a
                        href="{{ route('contact') }}"
                        class="text-slate-500 transition hover:text-white"
                    >
                        Contact
                    </a>

                    <a
                        href="{{ route('privacy') }}"
                        class="text-slate-300"
                    >
                        Privacy
                    </a>

                    <a
                        href="{{ route('terms') }}"
                        class="text-slate-500 transition hover:text-white"
                    >
                        Terms
                    </a>
                </div>
            </div>

            <div
                class="mx-auto max-w-7xl border-t border-white/10 px-4 py-6 text-xs text-slate-600 sm:px-6 lg:px-8"
            >
                <p>
                    © {{ date('Y') }} Helmio. Helmio provides monitoring
                    and informational analysis and does not provide
                    investment, tax, legal, or accounting advice.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>