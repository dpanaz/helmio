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
        content="Contact Helmio for customer support, business inquiries, security concerns, and questions about independent investment oversight."
    >

    <meta
        name="theme-color"
        content="#020617"
    >

    <title>
        Contact Helmio
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
                class="mx-auto max-w-7xl px-4 pb-12 pt-16 sm:px-6 sm:pb-16 sm:pt-20 lg:px-8"
            >
                <div class="mx-auto max-w-3xl text-center">
                    <p
                        class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-400"
                    >
                        Get in touch
                    </p>

                    <h1
                        class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-5xl"
                    >
                        Contact Helmio
                    </h1>

                    <p
                        class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-slate-400"
                    >
                        Have a question about Helmio, need help with your
                        account, or want to learn more about our investment
                        oversight platform? We’re here to help.
                    </p>

                    <a
                        href="mailto:contact@myhelmio.com"
                        class="mt-7 inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500"
                    >
                        contact@myhelmio.com
                    </a>
                </div>
            </section>

            {{-- Contact cards --}}
            <section
                class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8"
            >
                <div
                    class="grid gap-5 lg:grid-cols-3"
                >
                    {{-- Support --}}
                    <article
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-7"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-300 ring-1 ring-blue-400/20"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0-8.69 5.793a2 2 0 0 1-2.12 0L2.25 6.75"
                                />
                            </svg>
                        </div>

                        <h2
                            class="mt-6 text-xl font-semibold text-white"
                        >
                            Customer Support
                        </h2>

                        <p
                            class="mt-3 text-sm leading-7 text-slate-400"
                        >
                            Questions about your account, subscription,
                            connected accounts, portfolio data, or using Helmio.
                        </p>

                        <a
                            href="mailto:contact@myhelmio.com"
                            class="mt-5 inline-flex font-semibold text-blue-400 transition hover:text-blue-300"
                        >
                            contact@myhelmio.com
                        </a>
                    </article>

                    {{-- Business --}}
                    <article
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-7"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-300 ring-1 ring-blue-400/20"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6.75 7.5h10.5m-10.5 4.5h10.5m-10.5 4.5h6.75M4.5 3.75h15A1.5 1.5 0 0 1 21 5.25v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V5.25a1.5 1.5 0 0 1 1.5-1.5Z"
                                />
                            </svg>
                        </div>

                        <h2
                            class="mt-6 text-xl font-semibold text-white"
                        >
                            Business Inquiries
                        </h2>

                        <p
                            class="mt-3 text-sm leading-7 text-slate-400"
                        >
                            Partnerships, brokerage integrations, media
                            inquiries, strategic opportunities, and other
                            business questions.
                        </p>

                        <a
                            href="mailto:contact@myhelmio.com"
                            class="mt-5 inline-flex font-semibold text-blue-400 transition hover:text-blue-300"
                        >
                            contact@myhelmio.com
                        </a>
                    </article>

                    {{-- Security --}}
                    <article
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-7"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300 ring-1 ring-emerald-400/20"
                        >
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
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
                            class="mt-6 text-xl font-semibold text-white"
                        >
                            Security
                        </h2>

                        <p
                            class="mt-3 text-sm leading-7 text-slate-400"
                        >
                            Report a security vulnerability, suspicious
                            activity, or concern involving your Helmio account.
                        </p>

                        <a
                            href="mailto:contact@myhelmio.com"
                            class="mt-5 inline-flex font-semibold text-emerald-400 transition hover:text-emerald-300"
                        >
                            contact@myhelmio.com
                        </a>
                    </article>
                </div>
            </section>

            {{-- About --}}
            <section
                class="border-y border-white/10 bg-slate-900/40"
            >
                <div
                    class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-20"
                >
                    <div>
                        <p
                            class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            About Helmio
                        </p>

                        <h2
                            class="mt-3 text-3xl font-semibold tracking-tight text-white"
                        >
                            Independent oversight for your investments.
                        </h2>

                        <p
                            class="mt-5 text-base leading-8 text-slate-400"
                        >
                            Helmio is an investment oversight platform designed
                            to help investors independently understand how their
                            portfolios are being managed.
                        </p>

                        <p
                            class="mt-4 text-base leading-8 text-slate-400"
                        >
                            Helmio monitors fees, performance, diversification,
                            portfolio risk, trading activity, tax efficiency,
                            concentration, advisor activity, historical changes,
                            and other factors that can affect long-term results.
                        </p>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-7 sm:p-8"
                    >
                        <p
                            class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-400"
                        >
                            Read-only by design
                        </p>

                        <h3
                            class="mt-3 text-2xl font-semibold text-white"
                        >
                            Helmio watches your portfolio.
                            It does not control it.
                        </h3>

                        <div class="mt-6 space-y-4">
                            @foreach ([
                                'Helmio cannot place trades',
                                'Helmio cannot move your money',
                                'Helmio cannot transfer assets',
                                'Helmio does not have trading authority',
                                'Connected accounts are used for monitoring and analysis',
                            ] as $item)
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
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>

                                    <p
                                        class="text-sm leading-6 text-slate-300"
                                    >
                                        {{ $item }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- Mailing / contact --}}
            <section
                class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8"
            >
                <div
                    class="grid gap-6 md:grid-cols-2"
                >
                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-7"
                    >
                        <h2
                            class="text-lg font-semibold text-white"
                        >
                            Mailing Address
                        </h2>

                        <div
                            class="mt-4 text-sm leading-7 text-slate-400"
                        >
                            <p>Helmio</p>
                            <p>New Braunfels, Texas</p>
                            <p>United States</p>
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-white/10 bg-white/[0.04] p-7"
                    >
                        <h2
                            class="text-lg font-semibold text-white"
                        >
                            Contact
                        </h2>

                        <p
                            class="mt-3 text-sm leading-7 text-slate-400"
                        >
                            For support, billing, business, privacy, legal,
                            security, or general inquiries:
                        </p>

                        <a
                            href="mailto:contact@myhelmio.com"
                            class="mt-4 inline-block font-semibold text-blue-400 transition hover:text-blue-300"
                        >
                            contact@myhelmio.com
                        </a>
                    </div>
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
                        class="text-slate-300"
                    >
                        Contact
                    </a>

                    <a
                        href="{{ route('privacy') }}"
                        class="text-slate-500 transition hover:text-white"
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