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
        content="Helmio independently monitors investment fees, risk, trading activity, performance, and advisor behavior."
    >

    <title>
        Helmio — Independent Investment Oversight
    </title>

    <link
        rel="icon"
        href="{{ asset('favicon.ico') }}"
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
    class="min-h-screen overflow-x-hidden bg-slate-950 text-white antialiased"
>
    <div class="relative min-h-screen">
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-[44rem] overflow-hidden"
        >
            <div
                class="absolute -left-40 -top-48 h-[34rem] w-[34rem] rounded-full bg-blue-600/20 blur-3xl"
            ></div>

            <div
                class="absolute -right-40 top-0 h-[30rem] w-[30rem] rounded-full bg-cyan-400/10 blur-3xl"
            ></div>
        </div>

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

                    <div class="min-w-0">
                        <p
                            class="text-lg font-semibold leading-5 tracking-tight text-white"
                        >
                            Helmio
                        </p>

                        <p
                            class="mt-1 truncate text-[10px] uppercase tracking-[0.18em] text-slate-400"
                        >
                            Investment oversight
                        </p>
                    </div>
                </a>

                <nav class="flex shrink-0 items-center gap-2 sm:gap-3">
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
                            class="hidden px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:text-white sm:inline-flex"
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
                </nav>
            </div>
        </header>

        <main class="relative">
            <section
                class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-7xl items-center px-4 py-16 sm:px-6 sm:py-24 lg:px-8"
            >
                <div class="mx-auto max-w-5xl text-center">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-400/10 px-4 py-2 text-sm font-medium text-blue-200"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-400"
                        ></span>

                        Independent investment oversight
                    </div>

                    <h1
                        class="mt-8 text-4xl font-semibold tracking-tight text-white sm:text-6xl lg:text-7xl lg:leading-[1.02]"
                    >
                        See what your financial advisor
                        <span class="text-blue-400">
                            isn’t telling you.
                        </span>
                    </h1>

                    <p
                        class="mx-auto mt-7 max-w-3xl text-lg leading-8 text-slate-300 sm:text-xl"
                    >
                        Helmio independently monitors fees, risk, trading,
                        performance, and portfolio decisions so you can understand
                        how your money is really being managed.
                    </p>

                    <div
                        class="mt-10 flex flex-col justify-center gap-3 sm:flex-row"
                    >
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-7 py-4 font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:bg-blue-500 sm:w-auto"
                            >
                                Open Helmio

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
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>
                            </a>
                        @else
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-7 py-4 font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:bg-blue-500 sm:w-auto"
                            >
                                Start your free trial

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
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>
                            </a>

                            <a
                                href="#what-helmio-watches"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-white/15 bg-white/5 px-7 py-4 font-semibold text-white transition hover:bg-white/10 sm:w-auto"
                            >
                                Learn more
                            </a>
                        @endauth
                    </div>

                    <div
                        class="mt-8 flex flex-wrap justify-center gap-x-8 gap-y-3 text-sm text-slate-400"
                    >
                        <span class="inline-flex items-center gap-2">
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

                            Read-only connections
                        </span>

                        <span class="inline-flex items-center gap-2">
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

                            No trading authority
                        </span>

                        <span class="inline-flex items-center gap-2">
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

                            Built for investors
                        </span>
                    </div>
                </div>
            </section>

            <section
                id="what-helmio-watches"
                class="border-y border-white/10 bg-slate-900/40"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
                >
                    <div class="mx-auto max-w-3xl text-center">
                        <p
                            class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            What Helmio watches
                        </p>

                        <h2
                            class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                        >
                            The things that quietly affect your returns.
                        </h2>
                    </div>

                    <div
                        class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        @foreach ([
                            [
                                'title' => 'Fees',
                                'text' => 'Know exactly what you are paying across advisors, accounts, funds, and trades.',
                                'icon' => 'dollar',
                            ],
                            [
                                'title' => 'Performance',
                                'text' => 'See how your results compare with appropriate benchmarks and risk.',
                                'icon' => 'chart',
                            ],
                            [
                                'title' => 'Risk',
                                'text' => 'Understand whether your portfolio matches your goals and tolerance for loss.',
                                'icon' => 'shield',
                            ],
                            [
                                'title' => 'Advisor behavior',
                                'text' => 'Review trading activity, concentration, conflicts, and issues worth discussing.',
                                'icon' => 'eye',
                            ],
                        ] as $feature)
                            <article
                                class="rounded-3xl border border-white/10 bg-white/[0.04] p-6"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-300 ring-1 ring-blue-400/20"
                                >
                                    @if ($feature['icon'] === 'dollar')
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
                                                d="M12 6v12m3-9.75C14.25 7.5 13.25 7.125 12 7.125s-2.25.375-3 1.125S7.875 9.75 7.875 10.5s.375 1.5 1.125 2.25 1.75 1.125 3 1.125 2.25.375 3 1.125 1.125 1.5 1.125 2.25-.375 1.5-1.125 2.25-1.75 1.125-3 1.125-2.25-.375-3-1.125"
                                            />
                                        </svg>
                                    @elseif ($feature['icon'] === 'chart')
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
                                                d="M4.5 19.5v-6m5.25 6V9m5.25 10.5V5.25m5.25 14.25H3.75"
                                            />
                                        </svg>
                                    @elseif ($feature['icon'] === 'shield')
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
                                        </svg>
                                    @else
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
                                                d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"
                                            />
                                            <circle cx="12" cy="12" r="2.25" />
                                        </svg>
                                    @endif
                                </div>

                                <h3
                                    class="mt-6 text-xl font-semibold text-white"
                                >
                                    {{ $feature['title'] }}
                                </h3>

                                <p
                                    class="mt-3 text-sm leading-7 text-slate-400"
                                >
                                    {{ $feature['text'] }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section
                class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24"
            >
                <div
                    class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center"
                >
                    <div>
                        <p
                            class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            How it works
                        </p>

                        <h2
                            class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                        >
                            Connect once.
                            Keep watch continuously.
                        </h2>

                        <p
                            class="mt-5 text-base leading-7 text-slate-400"
                        >
                            Helmio uses read-only access to review your portfolio
                            and surface the issues that deserve your attention.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ([
                            [
                                'number' => '01',
                                'title' => 'Subscribe',
                                'text' => 'Choose your Helmio plan and activate your membership.',
                            ],
                            [
                                'number' => '02',
                                'title' => 'Connect',
                                'text' => 'Securely link your investment accounts with read-only access.',
                            ],
                            [
                                'number' => '03',
                                'title' => 'Understand',
                                'text' => 'Review prioritized findings, insights, and portfolio activity.',
                            ],
                        ] as $step)
                            <article
                                class="rounded-2xl border border-white/10 bg-white/[0.04] p-5"
                            >
                                <span
                                    class="text-xs font-semibold text-blue-400"
                                >
                                    {{ $step['number'] }}
                                </span>

                                <h3
                                    class="mt-4 text-lg font-semibold text-white"
                                >
                                    {{ $step['title'] }}
                                </h3>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-400"
                                >
                                    {{ $step['text'] }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="px-4 pb-20 sm:px-6 lg:px-8 lg:pb-28">
                <div
                    class="mx-auto max-w-5xl rounded-3xl border border-blue-400/20 bg-blue-600 px-6 py-10 text-center shadow-2xl shadow-blue-950/30 sm:px-10 lg:py-12"
                >
                    <h2
                        class="text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                    >
                        Stop wondering.
                        Start knowing.
                    </h2>

                    <p
                        class="mx-auto mt-4 max-w-2xl text-base leading-7 text-blue-100"
                    >
                        Start your Helmio trial before securely connecting your
                        investment accounts.
                    </p>

                    <div class="mt-7">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-white px-6 py-3.5 font-semibold text-blue-800 transition hover:bg-blue-50 sm:w-auto"
                            >
                                Open Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-white px-6 py-3.5 font-semibold text-blue-800 transition hover:bg-blue-50 sm:w-auto"
                            >
                                Start your free trial
                            </a>
                        @endauth
                    </div>
                </div>
            </section>
        </main>

        <footer
            class="border-t border-white/10 bg-slate-950"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-8 text-sm text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('icons/icon-192.png') }}"
                        alt=""
                        class="h-8 w-8 rounded-lg"
                    >

                    <div>
                        <p class="font-semibold text-slate-300">
                            Helmio
                        </p>

                        <p class="text-xs">
                            Independent investment oversight
                        </p>
                    </div>
                </div>

                <p class="max-w-2xl">
                    Helmio provides monitoring and informational analysis.
                    It does not provide investment, tax, or legal advice.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>