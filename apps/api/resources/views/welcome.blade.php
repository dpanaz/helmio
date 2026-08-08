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
        content="Helmio provides independent investment oversight by continuously monitoring fees, performance, risk, diversification, trading activity, tax efficiency, advisor behavior, and portfolio changes."
    >

    <meta
        name="theme-color"
        content="#020617"
    >

    <title>
        Helmio — Independent Investment Oversight
    </title>

    {{-- Favicons --}}
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
    class="min-h-screen overflow-x-hidden bg-slate-950 text-white antialiased"
>
    <div class="relative min-h-screen">

        {{-- Background --}}
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-screen overflow-hidden"
        >
            <div
                class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"
            ></div>

            <div
                class="absolute -right-32 top-10 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"
            ></div>
        </div>

        {{-- Header --}}
        <header
            class="relative z-30 border-b border-white/10 bg-slate-950/90 backdrop-blur-lg"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                >
                    <img
                        src="{{ asset('icons/icon-192.png') }}"
                        alt="Helmio"
                        class="h-11 w-11 rounded-xl shadow-lg"
                    >

                    <div>
                        <p
                            class="text-lg font-semibold tracking-tight text-white"
                        >
                            Helmio
                        </p>

                        <p
                            class="text-xs uppercase tracking-widest text-slate-500"
                        >
                            Investment oversight
                        </p>
                    </div>
                </a>

                <nav
                    class="hidden items-center gap-8 lg:flex"
                >
                    <a
                        href="#what-helmio-watches"
                        class="text-sm font-medium text-slate-400 transition hover:text-white"
                    >
                        What We Monitor
                    </a>

                    <a
                        href="#advisor-oversight"
                        class="text-sm font-medium text-slate-400 transition hover:text-white"
                    >
                        Advisor Oversight
                    </a>

                    <a
                        href="#helm-score"
                        class="text-sm font-medium text-slate-400 transition hover:text-white"
                    >
                        Helm Score
                    </a>

                    <a
                        href="#how-it-works"
                        class="text-sm font-medium text-slate-400 transition hover:text-white"
                    >
                        How It Works
                    </a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-slate-100"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="hidden text-sm font-semibold text-slate-300 transition hover:text-white sm:inline-flex"
                        >
                            Log in
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-500"
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
                class="mx-auto flex min-h-screen max-w-7xl items-center px-4 py-20 sm:px-6 lg:px-8"
            >
                <div
                    class="mx-auto max-w-6xl text-center"
                >
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-500/10 px-4 py-2 text-sm font-medium text-blue-200"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-400"
                        ></span>

                        Independent investment oversight
                    </div>

                    <h1
                        class="mt-8 text-4xl font-semibold tracking-tight text-white sm:text-6xl lg:text-7xl"
                    >
                        See what your financial advisor
                        <span
                            class="block text-blue-400 sm:inline"
                        >
                            isn’t telling you.
                        </span>
                    </h1>

                    <p
                        class="mx-auto mt-7 max-w-4xl text-lg leading-8 text-slate-300 sm:text-xl"
                    >
                        Helmio continuously monitors the things that can quietly
                        affect your wealth — advisor fees, fund expenses,
                        performance, risk, diversification, trading activity,
                        portfolio concentration, tax efficiency, cash drag,
                        account changes, and more.
                    </p>

                    <p
                        class="mx-auto mt-4 max-w-3xl text-base leading-7 text-slate-400"
                    >
                        Think of Helmio as an independent second set of eyes on
                        your investments — watching your portfolio and
                        explaining what deserves your attention in plain English.
                    </p>

                    <div
                        class="mt-10 flex flex-col justify-center gap-3 sm:flex-row"
                    >
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-7 py-4 font-semibold text-white shadow-xl transition hover:bg-blue-500"
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
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-7 py-4 font-semibold text-white shadow-xl transition hover:bg-blue-500"
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
                                class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-7 py-4 font-semibold text-white transition hover:border-slate-600 hover:bg-slate-800"
                            >
                                See what Helmio monitors
                            </a>
                        @endauth
                    </div>

                    <div
                        class="mt-10 flex flex-wrap justify-center gap-x-8 gap-y-3 text-sm text-slate-400"
                    >
                        @foreach ([
                            'Read-only connections',
                            'No trading authority',
                            'No ability to move money',
                            'Built for investors',
                        ] as $trustItem)
                            <span
                                class="inline-flex items-center gap-2"
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

                                {{ $trustItem }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- What Helmio Watches --}}
            <section
                id="what-helmio-watches"
                class="border-y border-slate-800 bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28"
                >
                    <div
                        class="mx-auto max-w-3xl text-center"
                    >
                        <p
                            class="text-sm font-semibold uppercase tracking-widest text-blue-400"
                        >
                            What Helmio watches
                        </p>

                        <h2
                            class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                        >
                            Your entire portfolio.
                            <span class="text-slate-400">
                                Not just the balance.
                            </span>
                        </h2>

                        <p
                            class="mt-5 text-base leading-7 text-slate-400"
                        >
                            Helmio continuously evaluates the costs, activity,
                            risks, structure, and performance behind your
                            investment accounts.
                        </p>
                    </div>

                    @php
                        $features = [
                            [
                                'label' => 'COST',
                                'number' => '01',
                                'title' => 'Fees & Costs',
                                'text' => 'See exactly what you are paying across advisory fees, fund expenses, account charges, and trading costs.',
                                'tags' => [
                                    'Advisor Fees',
                                    'Expense Ratios',
                                    'All-In Cost',
                                ],
                                'icon' => 'fees',
                            ],
                            [
                                'label' => 'RETURN',
                                'number' => '02',
                                'title' => 'Performance',
                                'text' => 'Understand how your investments are actually performing and how those results compare with relevant benchmarks.',
                                'tags' => [
                                    'Returns',
                                    'Benchmarks',
                                    'History',
                                ],
                                'icon' => 'performance',
                            ],
                            [
                                'label' => 'RISK',
                                'number' => '03',
                                'title' => 'Portfolio Risk',
                                'text' => 'Identify exposure that may be creating more risk than expected across securities, sectors, and asset classes.',
                                'tags' => [
                                    'Volatility',
                                    'Exposure',
                                    'Allocation',
                                ],
                                'icon' => 'risk',
                            ],
                            [
                                'label' => 'TRADING',
                                'number' => '04',
                                'title' => 'Trading Activity',
                                'text' => 'Watch turnover, transaction volume, round trips, and trading patterns that deserve a closer look.',
                                'tags' => [
                                    'Turnover',
                                    'Trades',
                                    'Round Trips',
                                ],
                                'icon' => 'trading',
                            ],
                            [
                                'label' => 'BALANCE',
                                'number' => '05',
                                'title' => 'Diversification',
                                'text' => 'See whether your portfolio is truly diversified or quietly concentrated in a few securities or market exposures.',
                                'tags' => [
                                    'Top Holdings',
                                    'Sectors',
                                    'Asset Classes',
                                ],
                                'icon' => 'diversification',
                            ],
                            [
                                'label' => 'TAX',
                                'number' => '06',
                                'title' => 'Tax Efficiency',
                                'text' => 'Review gains, holding periods, dividends, tax-exempt income, and other factors that can affect after-tax results.',
                                'tags' => [
                                    'Capital Gains',
                                    'Dividends',
                                    'Holding Period',
                                ],
                                'icon' => 'tax',
                            ],
                            [
                                'label' => 'CASH',
                                'number' => '07',
                                'title' => 'Cash & Cash Drag',
                                'text' => 'Monitor idle cash and understand whether uninvested balances may be holding back portfolio performance.',
                                'tags' => [
                                    'Cash',
                                    'Deposits',
                                    'Withdrawals',
                                ],
                                'icon' => 'cash',
                            ],
                            [
                                'label' => 'POSITIONS',
                                'number' => '08',
                                'title' => 'Holdings',
                                'text' => 'Track every security, position value, cost basis, gain or loss, portfolio weight, sector, and asset class.',
                                'tags' => [
                                    'Cost Basis',
                                    'Market Value',
                                    'Portfolio Weight',
                                ],
                                'icon' => 'holdings',
                            ],
                            [
                                'label' => 'OVERSIGHT',
                                'number' => '09',
                                'title' => 'Advisor Oversight',
                                'text' => 'Surface high costs, unusual activity, concentrated positions, and other issues worth discussing with your advisor.',
                                'tags' => [
                                    'Advisor Audit',
                                    'Red Flags',
                                    'Action Center',
                                ],
                                'icon' => 'oversight',
                            ],
                            [
                                'label' => 'ACTIVITY',
                                'number' => '10',
                                'title' => 'Account Activity',
                                'text' => 'Follow deposits, withdrawals, transfers, purchases, sales, dividends, interest, and account-level fees.',
                                'tags' => [
                                    'Transactions',
                                    'Transfers',
                                    'Income',
                                ],
                                'icon' => 'activity',
                            ],
                            [
                                'label' => 'HISTORY',
                                'number' => '11',
                                'title' => 'Portfolio History',
                                'text' => 'Preserve a history of portfolio changes so you can see how your holdings and risk profile evolve over time.',
                                'tags' => [
                                    'Snapshots',
                                    'Timeline',
                                    'Changes',
                                ],
                                'icon' => 'history',
                            ],
                            [
                                'label' => 'AI',
                                'number' => '12',
                                'title' => 'AI Insights',
                                'text' => 'Turn complex portfolio analytics into clear explanations, priorities, positive signals, and questions worth asking.',
                                'tags' => [
                                    'Insights',
                                    'Priorities',
                                    'Ask Helmio',
                                ],
                                'icon' => 'ai',
                            ],
                        ];
                    @endphp

                    <div
                        class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        @foreach ($features as $feature)
                            <article
                                class="group relative overflow-hidden rounded-3xl border border-slate-700 bg-slate-950 shadow-xl transition duration-300 hover:-translate-y-1 hover:border-blue-400 hover:shadow-2xl"
                            >
                                {{-- top accent --}}
                                <div
                                    class="h-1 w-full bg-gradient-to-r from-blue-600 via-blue-400 to-cyan-400"
                                ></div>

                                <div class="p-7">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="rounded-lg border border-blue-500/30 bg-blue-500/10 px-3 py-1 text-xs font-semibold tracking-widest text-blue-300"
                                        >
                                            {{ $feature['label'] }}
                                        </span>

                                        <span
                                            class="text-xs font-semibold text-slate-600"
                                        >
                                            {{ $feature['number'] }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-6 flex items-start justify-between gap-5"
                                    >
                                        <div
                                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-slate-700 bg-slate-900 text-blue-300 shadow-lg transition group-hover:border-blue-500/50 group-hover:bg-blue-500/10"
                                        >

                                            @switch($feature['icon'])

                                                @case('fees')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 6v12m3-9.5c0-1.4-1.35-2.5-3-2.5s-3 1.1-3 2.5S10.35 11 12 11s3 1.1 3 2.5S13.65 16 12 16s-3-1.1-3-2.5"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('performance')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M4 18 9 13l3 3 7-8"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M15 8h4v4"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('risk')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 3 5 6v5c0 4.5 2.9 8 7 10 4.1-2 7-5.5 7-10V6l-7-3Z"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 8v4m0 3h.01"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('trading')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M7 7h11m0 0-3-3m3 3-3 3"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M17 17H6m0 0 3 3m-3-3 3-3"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('diversification')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 3v9h9A9 9 0 1 1 12 3Z"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M15 3.5A9 9 0 0 1 20.5 9H15V3.5Z"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('tax')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M6 3h9l3 3v15H6V3Z"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M9 15 15 9M9.5 9h.01m5 6h.01"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('cash')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M4 7h16v10H4V7Z"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 10.5c1.1 0 2 .67 2 1.5s-.9 1.5-2 1.5-2-.67-2-1.5.9-1.5 2-1.5Z"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('holdings')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="m12 3 8 4-8 4-8-4 8-4Z"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="m4 12 8 4 8-4M4 17l8 4 8-4"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('oversight')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                                        />

                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="2.5"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('activity')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M3 12h4l2-5 4 10 2-5h6"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('history')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="8"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 8v4l3 2"
                                                        />
                                                    </svg>
                                                    @break

                                                @case('ai')
                                                    <svg
                                                        class="h-7 w-7"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="m12 3 1 3.5L16.5 8 13 9.5 12 13l-1-3.5L7.5 8 11 6.5 12 3Z"
                                                        />

                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="m18 13 .7 2.3L21 16l-2.3.7L18 19l-.7-2.3L15 16l2.3-.7L18 13Z"
                                                        />
                                                    </svg>
                                                    @break

                                            @endswitch
                                        </div>

                                        <div
                                            class="mt-7 h-px flex-1 bg-slate-800 transition group-hover:bg-blue-500/40"
                                        ></div>
                                    </div>

                                    <h3
                                        class="mt-5 text-2xl font-semibold tracking-tight text-white"
                                    >
                                        {{ $feature['title'] }}
                                    </h3>

                                    <p
                                        class="mt-3 text-sm leading-7 text-slate-400"
                                    >
                                        {{ $feature['text'] }}
                                    </p>

                                    <div
                                        class="mt-6 flex flex-wrap gap-2"
                                    >
                                        @foreach ($feature['tags'] as $tag)
                                            <span
                                                class="rounded-lg border border-slate-800 bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-slate-400"
                                            >
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Continuous Monitoring --}}
            <section
                class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28"
            >
                <div
                    class="grid gap-12 lg:grid-cols-2"
                >
                    <div>
                        <p
                            class="text-sm font-semibold uppercase tracking-widest text-blue-400"
                        >
                            Continuous monitoring
                        </p>

                        <h2
                            class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                        >
                            The details that are
                            <span class="text-slate-400">
                                easy to miss.
                            </span>
                        </h2>

                        <p
                            class="mt-6 max-w-xl text-base leading-8 text-slate-400"
                        >
                            A portfolio can look healthy at a glance while
                            unnecessary fees, concentration, tax inefficiency,
                            or excessive trading quietly erode results.
                        </p>

                        <p
                            class="mt-4 max-w-xl text-base leading-8 text-slate-400"
                        >
                            Helmio brings those details together in one
                            independent monitoring system.
                        </p>
                    </div>

                    @php
                        $monitorItems = [
                            'Advisory fees',
                            'Fund expense ratios',
                            'Account fees',
                            'Transaction fees',
                            'Total investment costs',
                            'Portfolio performance',
                            'Benchmark comparisons',
                            'Portfolio value changes',
                            'Concentration risk',
                            'Largest holdings',
                            'Top-five exposure',
                            'Sector exposure',
                            'Asset allocation',
                            'Security diversification',
                            'Trading frequency',
                            'Portfolio turnover',
                            'Round-trip trading',
                            'Potential excessive trading',
                            'Purchases and sales',
                            'Deposits and withdrawals',
                            'Dividends and interest',
                            'Cash balances',
                            'Cash drag',
                            'Cost basis',
                            'Realized gains and losses',
                            'Holding periods',
                            'Qualified dividends',
                            'Tax-exempt income',
                            'Tax withholding',
                            'Tax efficiency',
                            'Historical portfolio snapshots',
                            'Brokerage synchronization',
                            'Missing portfolio data',
                            'Stale account information',
                            'Advisor audit findings',
                            'Portfolio timeline events',
                            'AI executive summaries',
                            'Portfolio strengths',
                            'Priority review items',
                            'Helm Score™',
                        ];
                    @endphp

                    <div
                        class="rounded-3xl border border-slate-700 bg-slate-900 p-6 shadow-2xl sm:p-8"
                    >
                        <div
                            class="grid gap-3 sm:grid-cols-2"
                        >
                            @foreach ($monitorItems as $item)
                                <div
                                    class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950 p-3 transition hover:border-blue-500/40 hover:bg-slate-900"
                                >
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-emerald-500/20 bg-emerald-500/10"
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
                </div>
            </section>

            {{-- Advisor Oversight --}}
            <section
                id="advisor-oversight"
                class="border-y border-slate-800 bg-slate-900"
            >
                <div
                    class="mx-auto grid max-w-7xl gap-14 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-28"
                >
                    <div>
                        <p
                            class="text-sm font-semibold uppercase tracking-widest text-blue-400"
                        >
                            Advisor oversight
                        </p>

                        <h2
                            class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                        >
                            Your advisor has a job.
                            <span class="text-blue-400">
                                Helmio watches how it’s being done.
                            </span>
                        </h2>

                        <p
                            class="mt-6 max-w-xl text-base leading-8 text-slate-400"
                        >
                            Helmio independently reviews activity occurring
                            inside your accounts and highlights patterns that
                            deserve a closer look.
                        </p>
                    </div>

                    <div class="grid gap-4">
                        @foreach ([
                            [
                                'number' => '01',
                                'title' => 'High-cost investments',
                                'text' => 'Identify investments and account structures that may be creating unnecessary ongoing expenses.',
                            ],
                            [
                                'number' => '02',
                                'title' => 'Unusual trading',
                                'text' => 'Surface high turnover, frequent trading, round trips, and other activity worth reviewing.',
                            ],
                            [
                                'number' => '03',
                                'title' => 'Concentration',
                                'text' => 'See when individual securities, sectors, or asset classes begin dominating the portfolio.',
                            ],
                            [
                                'number' => '04',
                                'title' => 'Suitability & risk',
                                'text' => 'Compare portfolio characteristics with the goals, time horizon, and risk profile you provide to Helmio.',
                            ],
                            [
                                'number' => '05',
                                'title' => 'Action Center',
                                'text' => 'Turn findings into prioritized questions and topics you can discuss with your advisor.',
                            ],
                        ] as $item)
                            <article
                                class="group rounded-2xl border border-slate-700 bg-slate-950 p-5 shadow-lg transition hover:border-blue-400 hover:bg-slate-900"
                            >
                                <div
                                    class="flex items-start gap-5"
                                >
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-blue-400/30 bg-blue-500/10 text-xs font-bold text-blue-300"
                                    >
                                        {{ $item['number'] }}
                                    </div>

                                    <div class="flex-1">
                                        <h3
                                            class="text-lg font-semibold text-white"
                                        >
                                            {{ $item['title'] }}
                                        </h3>

                                        <p
                                            class="mt-2 text-sm leading-6 text-slate-400"
                                        >
                                            {{ $item['text'] }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Helm Score --}}
            <section
                id="helm-score"
                class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28"
            >
                <div
                    class="overflow-hidden rounded-3xl border border-blue-500/30 bg-slate-900 shadow-2xl"
                >
                    <div
                        class="grid gap-14 p-8 sm:p-10 lg:grid-cols-2 lg:items-center lg:p-16"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold uppercase tracking-widest text-blue-400"
                            >
                                Helm Score™
                            </p>

                            <h2
                                class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                            >
                                One score.
                                <span class="text-slate-400">
                                    Six dimensions of portfolio health.
                                </span>
                            </h2>

                            <p
                                class="mt-6 max-w-2xl text-base leading-8 text-slate-400"
                            >
                                Helm Score brings multiple portfolio analytics
                                together so you can quickly understand where your
                                investments are strong and where they may need attention.
                            </p>

                            <div
                                class="mt-8 grid gap-3 sm:grid-cols-2"
                            >
                                @foreach ([
                                    'Cost efficiency',
                                    'Diversification',
                                    'Performance',
                                    'Risk',
                                    'Trading discipline',
                                    'Tax efficiency',
                                ] as $item)
                                    <div
                                        class="flex items-center gap-3 rounded-xl border border-slate-700 bg-slate-950 px-4 py-3"
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full bg-blue-400"
                                        ></span>

                                        <span
                                            class="text-sm font-medium text-slate-200"
                                        >
                                            {{ $item }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Animated score --}}
                        <div class="flex justify-center">
                            <div
                                data-helm-score-wrapper
                                class="flex h-72 w-72 items-center justify-center rounded-full border-4 border-blue-500/20 bg-slate-950 p-4 shadow-2xl"
                            >
                                <div
                                    class="flex h-60 w-60 flex-col items-center justify-center rounded-full border border-blue-400/40 bg-slate-900"
                                >
                                    <p
                                        class="text-xs font-bold uppercase tracking-widest text-blue-400"
                                    >
                                        Helm Score
                                    </p>

                                    <div
                                        class="mt-2 flex items-baseline"
                                    >
                                        <span
                                            data-helm-score
                                            data-score="84"
                                            class="text-7xl font-semibold text-white"
                                        >
                                            0
                                        </span>

                                        <span
                                            class="ml-1 text-lg text-slate-600"
                                        >
                                            /100
                                        </span>
                                    </div>

                                    <div
                                        class="mt-4 flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1"
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full bg-emerald-400"
                                        ></span>

                                        <span
                                            class="text-xs font-semibold text-emerald-300"
                                        >
                                            Strong
                                        </span>
                                    </div>

                                    <p
                                        class="mt-4 text-xs text-slate-600"
                                    >
                                        Example portfolio
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- AI --}}
            <section
                class="border-y border-slate-800 bg-slate-900"
            >
                <div
                    class="mx-auto grid max-w-7xl gap-14 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-28"
                >
                    <div>
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl border border-violet-400/30 bg-violet-500/10 text-violet-300"
                        >
                            <svg
                                class="h-7 w-7"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09Z"
                                />
                            </svg>
                        </div>

                        <p
                            class="mt-6 text-sm font-semibold uppercase tracking-widest text-violet-400"
                        >
                            AI-powered explanations
                        </p>

                        <h2
                            class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                        >
                            Understand what the numbers
                            <span class="text-slate-400">
                                actually mean.
                            </span>
                        </h2>

                        <p
                            class="mt-6 max-w-xl text-base leading-8 text-slate-400"
                        >
                            Helmio turns portfolio analytics into concise,
                            plain-English explanations grounded in your actual
                            account data.
                        </p>
                    </div>

                    <article
                        class="rounded-3xl border border-violet-500/30 bg-slate-950 p-7 shadow-2xl sm:p-9"
                    >
                        <div
                            class="flex items-center justify-between gap-4"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-widest text-violet-400"
                            >
                                Executive Insight
                            </p>

                            <span
                                class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                            >
                                AI analyzed
                            </span>
                        </div>

                        <div
                            class="mt-6 rounded-2xl border border-slate-800 bg-slate-900 p-6"
                        >
                            <h3
                                class="text-2xl font-semibold leading-9 text-white"
                            >
                                Your portfolio is diversified, but your costs deserve attention.
                            </h3>

                            <p
                                class="mt-4 text-sm leading-7 text-slate-400"
                            >
                                Helmio identified several areas where ongoing fund
                                expenses and advisory costs may be reducing your
                                long-term returns. Your diversification remains
                                relatively strong, but fees would be the first area
                                worth discussing with your advisor.
                            </p>
                        </div>

                        <div
                            class="mt-5 rounded-2xl border border-violet-500/20 bg-violet-500/10 p-5"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-widest text-violet-400"
                            >
                                Ask Helmio
                            </p>

                            <p
                                class="mt-3 text-sm font-medium leading-6 text-slate-200"
                            >
                                “Why are my investment costs higher than expected?”
                            </p>
                        </div>
                    </article>
                </div>
            </section>

            {{-- How it works --}}
            <section
                id="how-it-works"
                class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28"
            >
                <div
                    class="mx-auto max-w-3xl text-center"
                >
                    <p
                        class="text-sm font-semibold uppercase tracking-widest text-blue-400"
                    >
                        How it works
                    </p>

                    <h2
                        class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                    >
                        Connect once.
                        <span class="text-slate-400">
                            Keep watch continuously.
                        </span>
                    </h2>
                </div>

                <div
                    class="mt-14 grid gap-6 md:grid-cols-4"
                >
                    @foreach ([
                        [
                            'number' => '01',
                            'title' => 'Create your account',
                            'text' => 'Start your Helmio membership and create your secure investor profile.',
                        ],
                        [
                            'number' => '02',
                            'title' => 'Connect accounts',
                            'text' => 'Securely connect your investment accounts using read-only access.',
                        ],
                        [
                            'number' => '03',
                            'title' => 'Helmio analyzes',
                            'text' => 'Your portfolio is evaluated across fees, risk, diversification, trading, tax efficiency, and performance.',
                        ],
                        [
                            'number' => '04',
                            'title' => 'Stay informed',
                            'text' => 'Review findings, AI explanations, portfolio changes, alerts, and ongoing monitoring.',
                        ],
                    ] as $step)
                        <article
                            class="rounded-3xl border border-slate-700 bg-slate-950 p-6 shadow-xl transition duration-300 hover:-translate-y-1 hover:border-blue-400"
                        >
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-400/30 bg-blue-500/10 text-sm font-bold text-blue-300"
                            >
                                {{ $step['number'] }}
                            </span>

                            <h3
                                class="mt-7 text-xl font-semibold text-white"
                            >
                                {{ $step['title'] }}
                            </h3>

                            <p
                                class="mt-3 text-sm leading-7 text-slate-400"
                            >
                                {{ $step['text'] }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- Trust --}}
            <section
                class="border-y border-slate-800 bg-slate-900"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8"
                >
                    <div
                        class="grid gap-6 md:grid-cols-3"
                    >
                        @foreach ([
                            [
                                'title' => 'Read-only by design',
                                'text' => 'Helmio is designed to analyze your accounts without trading authority or permission to move funds.',
                            ],
                            [
                                'title' => 'Independent perspective',
                                'text' => 'Helmio is built to help investors independently understand how their portfolios are being managed.',
                            ],
                            [
                                'title' => 'Data-driven insights',
                                'text' => 'AI explanations are grounded in Helmio’s underlying portfolio analytics and connected account data.',
                            ],
                        ] as $item)
                            <article
                                class="rounded-3xl border border-slate-700 bg-slate-950 p-7 shadow-xl"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10"
                                >
                                    <svg
                                        class="h-5 w-5 text-emerald-400"
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

                                <h3
                                    class="mt-5 text-xl font-semibold text-white"
                                >
                                    {{ $item['title'] }}
                                </h3>

                                <p
                                    class="mt-3 text-sm leading-7 text-slate-400"
                                >
                                    {{ $item['text'] }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- CTA --}}
            <section
                class="px-4 py-20 sm:px-6 lg:px-8 lg:py-28"
            >
                <div
                    class="mx-auto max-w-5xl rounded-3xl border border-blue-400/30 bg-blue-600 px-6 py-12 text-center shadow-2xl sm:px-10 lg:py-16"
                >
                    <p
                        class="text-sm font-semibold uppercase tracking-widest text-blue-100"
                    >
                        Independent investment oversight
                    </p>

                    <h2
                        class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                    >
                        Stop wondering.
                        Start knowing.
                    </h2>

                    <p
                        class="mx-auto mt-5 max-w-2xl text-base leading-7 text-blue-100"
                    >
                        Give your portfolio an independent second set of eyes
                        and see what deserves your attention.
                    </p>

                    <div class="mt-8">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-white px-7 py-4 font-semibold text-blue-800 shadow-lg transition hover:bg-blue-50"
                            >
                                Open Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-white px-7 py-4 font-semibold text-blue-800 shadow-lg transition hover:bg-blue-50"
                            >
                                Start your free trial
                            </a>
                        @endauth
                    </div>
                </div>
            </section>
        </main>

        {{-- Footer --}}
        <footer
            class="border-t border-slate-800 bg-slate-950"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8"
            >
                <div
                    class="grid gap-10 md:grid-cols-4"
                >
                    <div>
                        <div
                            class="flex items-center gap-3"
                        >
                            <img
                                src="{{ asset('icons/icon-192.png') }}"
                                alt="Helmio"
                                class="h-10 w-10 rounded-xl"
                            >

                            <div>
                                <p
                                    class="font-semibold text-white"
                                >
                                    Helmio
                                </p>

                                <p
                                    class="text-xs uppercase tracking-widest text-slate-500"
                                >
                                    Investment oversight
                                </p>
                            </div>
                        </div>

                        <p
                            class="mt-5 max-w-sm text-sm leading-7 text-slate-500"
                        >
                            Independent investment oversight designed to help
                            investors understand how their money is being managed.
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-sm font-semibold text-white"
                        >
                            Product
                        </p>

                        <div class="mt-4 space-y-3 text-sm">
                            <a
                                href="#what-helmio-watches"
                                class="block text-slate-500 transition hover:text-white"
                            >
                                What We Monitor
                            </a>

                            <a
                                href="#advisor-oversight"
                                class="block text-slate-500 transition hover:text-white"
                            >
                                Advisor Oversight
                            </a>

                            <a
                                href="#helm-score"
                                class="block text-slate-500 transition hover:text-white"
                            >
                                Helm Score
                            </a>

                            <a
                                href="#how-it-works"
                                class="block text-slate-500 transition hover:text-white"
                            >
                                How It Works
                            </a>
                        </div>
                    </div>

                    <div>
                        <p
                            class="text-sm font-semibold text-white"
                        >
                            Account
                        </p>

                        <div class="mt-4 space-y-3 text-sm">
                            @guest
                                <a
                                    href="{{ route('register') }}"
                                    class="block text-slate-500 transition hover:text-white"
                                >
                                    Start Free Trial
                                </a>

                                <a
                                    href="{{ route('login') }}"
                                    class="block text-slate-500 transition hover:text-white"
                                >
                                    Log In
                                </a>

                                <a
                                    href="{{ route('billing.pricing') }}"
                                    class="block text-slate-500 transition hover:text-white"
                                >
                                    Pricing
                                </a>
                            @else
                                <a
                                    href="{{ route('dashboard') }}"
                                    class="block text-slate-500 transition hover:text-white"
                                >
                                    Dashboard
                                </a>

                                <a
                                    href="{{ route('billing.index') }}"
                                    class="block text-slate-500 transition hover:text-white"
                                >
                                    Billing
                                </a>
                            @endguest
                        </div>
                    </div>

                    <div>
                        <p
                            class="text-sm font-semibold text-white"
                        >
                            Company
                        </p>

                        <div class="mt-4 space-y-3 text-sm">
                            <a
                                href="{{ route('contact') }}"
                                class="block text-slate-500 transition hover:text-white"
                            >
                                Contact
                            </a>

                            <a
                                href="{{ route('privacy') }}"
                                class="block text-slate-500 transition hover:text-white"
                            >
                                Privacy Policy
                            </a>

                            <a
                                href="{{ route('terms') }}"
                                class="block text-slate-500 transition hover:text-white"
                            >
                                Terms of Service
                            </a>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-10 flex flex-col gap-5 border-t border-slate-800 pt-7 text-xs text-slate-600 md:flex-row md:items-center md:justify-between"
                >
                    <p>
                        © {{ date('Y') }} Helmio. All rights reserved.
                    </p>

                    <p
                        class="max-w-2xl md:text-right"
                    >
                        Helmio provides monitoring and informational analysis.
                        Helmio does not provide investment, tax, legal, or
                        accounting advice and does not have trading authority
                        over connected accounts.
                    </p>
                </div>
            </div>
        </footer>
    </div>

    {{-- Helm Score animation --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scoreElement =
                document.querySelector('[data-helm-score]');

            const scoreWrapper =
                document.querySelector('[data-helm-score-wrapper]');

            if (!scoreElement || !scoreWrapper) {
                return;
            }

            const target =
                Number(scoreElement.dataset.score || 0);

            let hasAnimated = false;

            function animateScore() {
                if (hasAnimated) {
                    return;
                }

                hasAnimated = true;

                const reducedMotion =
                    window.matchMedia(
                        '(prefers-reduced-motion: reduce)'
                    ).matches;

                if (reducedMotion) {
                    scoreElement.textContent = target;
                    return;
                }

                const duration = 1500;
                const startTime = performance.now();

                function update(currentTime) {
                    const elapsed =
                        currentTime - startTime;

                    const progress =
                        Math.min(
                            elapsed / duration,
                            1
                        );

                    const eased =
                        1 - Math.pow(
                            1 - progress,
                            3
                        );

                    scoreElement.textContent =
                        Math.round(
                            target * eased
                        );

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        scoreElement.textContent =
                            target;
                    }
                }

                requestAnimationFrame(update);
            }

            if (!('IntersectionObserver' in window)) {
                animateScore();
                return;
            }

            const observer =
                new IntersectionObserver(
                    function (entries) {
                        entries.forEach(
                            function (entry) {
                                if (entry.isIntersecting) {
                                    animateScore();

                                    observer.unobserve(
                                        scoreWrapper
                                    );
                                }
                            }
                        );
                    },
                    {
                        threshold: 0.4,
                    }
                );

            observer.observe(scoreWrapper);
        });
    </script>
</body>
</html>