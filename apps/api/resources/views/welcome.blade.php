<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta
        name="description"
        content="Helmio provides independent investment oversight by continuously monitoring fees, performance, risk, diversification, trading activity, tax efficiency, advisor behavior, and portfolio changes."
    >

    <meta
        name="theme-color"
        content="#020617"
    >

    <title>Helmio — Independent Investment Oversight</title>

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

<body class="min-h-screen overflow-x-hidden bg-slate-950 text-white antialiased">

<div class="relative min-h-screen">

    {{-- =========================================================
        BACKGROUND
    ========================================================== --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div
            class="absolute -left-40 -top-40 h-[32rem] w-[32rem]
                   rounded-full bg-blue-600/15 blur-3xl"
        ></div>

        <div
            class="absolute -right-40 top-32 h-[28rem] w-[28rem]
                   rounded-full bg-cyan-500/10 blur-3xl"
        ></div>
    </div>


    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <header
        class="relative z-50 border-b border-white/10
               bg-slate-950/95 backdrop-blur-xl"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl
                   items-center justify-between
                   px-4 sm:h-20 sm:px-6 lg:px-8"
        >

            {{-- Logo --}}
            <a
                href="/"
                class="flex min-w-0 items-center gap-2.5 sm:gap-3"
            >
                <img
                    src="{{ asset('icons/icon-192.png') }}"
                    alt="Helmio"
                    class="h-9 w-9 shrink-0 rounded-xl shadow-lg
                           sm:h-11 sm:w-11"
                >

                <div class="min-w-0">
                    <div
                        class="text-lg font-semibold tracking-tight
                               text-white sm:text-xl"
                    >
                        Helmio
                    </div>

                    <div
                        class="hidden text-[10px] uppercase
                               tracking-[0.22em] text-slate-500
                               sm:block"
                    >
                        Investment oversight
                    </div>
                </div>
            </a>


            {{-- Desktop navigation --}}
            <nav class="hidden items-center gap-8 lg:flex">
                <a
                    href="#what-helmio-watches"
                    class="text-sm font-medium text-slate-400
                           transition hover:text-white"
                >
                    What We Monitor
                </a>

                <a
                    href="#advisor-oversight"
                    class="text-sm font-medium text-slate-400
                           transition hover:text-white"
                >
                    Advisor Oversight
                </a>

                <a
                    href="#helm-score"
                    class="text-sm font-medium text-slate-400
                           transition hover:text-white"
                >
                    Helm Score
                </a>

                <a
                    href="#how-it-works"
                    class="text-sm font-medium text-slate-400
                           transition hover:text-white"
                >
                    How It Works
                </a>
            </nav>


            {{-- Account actions --}}
            <div class="flex shrink-0 items-center gap-1 sm:gap-3">

                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-white
                               px-3.5 py-2
                               text-sm font-semibold text-slate-950
                               transition hover:bg-slate-100
                               sm:px-5 sm:py-2.5"
                    >
                        Dashboard
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center
                               px-2 py-2
                               text-sm font-semibold text-slate-300
                               transition hover:text-white
                               sm:px-3"
                    >
                        Log in
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center justify-center
                               rounded-xl bg-blue-600
                               px-3.5 py-2
                               text-sm font-semibold text-white
                               shadow-lg shadow-blue-950/30
                               transition hover:bg-blue-500
                               sm:px-5 sm:py-2.5"
                    >
                        <span class="sm:hidden">
                            Get started
                        </span>

                        <span class="hidden sm:inline">
                            Start free trial
                        </span>
                    </a>
                @endauth

            </div>
        </div>
    </header>


    <main class="relative z-10">

        {{-- =====================================================
            HERO
        ====================================================== --}}
        <section
            class="mx-auto max-w-7xl
                   px-4 pb-14 pt-10
                   sm:px-6 sm:pb-24 sm:pt-20
                   lg:px-8 lg:pb-28 lg:pt-28"
        >
            <div class="mx-auto max-w-5xl text-center">

                {{-- Eyebrow --}}
                <div
                    class="inline-flex items-center gap-2
                           rounded-full
                           border border-blue-400/25
                           bg-blue-500/10
                           px-3 py-1.5
                           text-xs font-medium text-blue-200
                           sm:px-4 sm:py-2 sm:text-sm"
                >
                    <span
                        class="h-2 w-2 rounded-full bg-emerald-400"
                    ></span>

                    Independent investment oversight
                </div>


                {{-- Headline --}}
                <h1
                    class="mx-auto mt-6 max-w-5xl
                           text-[2.45rem] font-semibold
                           leading-[1.04] tracking-[-0.04em]
                           text-white
                           sm:mt-9 sm:text-6xl
                           lg:text-7xl"
                >
                    See what your financial advisor

                    <span
                        class="block min-h-[2.15em] text-blue-400 sm:min-h-[1.15em]"
                        data-hero-rotator
                        aria-live="polite"
                    >
                        <span
                            class="inline-block transition-all duration-500 ease-out"
                            data-hero-rotator-text
                        >
                            isn’t telling you.
                        </span>
                    </span>
                </h1>


                {{-- Mobile description --}}
                <p
                    class="mx-auto mt-5 max-w-sm
                           text-base leading-7 text-slate-300
                           sm:hidden"
                >
                    Independent oversight of your fees, performance,
                    risk, trading, and portfolio decisions.
                </p>


                {{-- Desktop description --}}
                <p
                    class="mx-auto mt-7 hidden max-w-4xl
                           text-lg leading-8 text-slate-300
                           sm:block sm:text-xl"
                >
                    Helmio continuously monitors the things that can
                    quietly affect your wealth — advisor fees, fund
                    expenses, performance, risk, diversification,
                    trading activity, portfolio concentration,
                    tax efficiency, cash drag, account changes,
                    and more.
                </p>

                <p
                    class="mx-auto mt-4 hidden max-w-3xl
                           text-base leading-7 text-slate-400
                           sm:block"
                >
                    Think of Helmio as an independent second set of
                    eyes on your investments — watching your portfolio
                    and explaining what deserves your attention in
                    plain English.
                </p>


                {{-- Hero CTA --}}
                <div
                    class="mx-auto mt-7 max-w-sm
                           sm:mt-10 sm:max-w-none"
                >

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex w-full items-center
                                   justify-center gap-2
                                   rounded-2xl bg-blue-600
                                   px-6 py-3.5
                                   text-base font-semibold text-white
                                   shadow-xl shadow-blue-950/30
                                   transition hover:bg-blue-500
                                   sm:w-auto sm:px-7 sm:py-4"
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
                        <div
                            class="flex flex-col items-center
                                   justify-center gap-4
                                   sm:flex-row sm:gap-3"
                        >
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex w-full items-center
                                       justify-center gap-2
                                       rounded-2xl bg-blue-600
                                       px-6 py-3.5
                                       text-base font-semibold text-white
                                       shadow-xl shadow-blue-950/30
                                       transition hover:bg-blue-500
                                       sm:w-auto sm:px-7 sm:py-4"
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
                                class="hidden items-center justify-center
                                       rounded-2xl border border-slate-700
                                       bg-slate-900/70
                                       px-7 py-4
                                       font-semibold text-white
                                       transition
                                       hover:border-slate-600
                                       hover:bg-slate-800
                                       sm:inline-flex"
                            >
                                See what Helmio monitors
                            </a>
                        </div>

                        <p
                            class="mt-4 text-sm text-slate-500
                                   sm:hidden"
                        >
                            Already have an account?

                            <a
                                href="{{ route('login') }}"
                                class="ml-1 font-semibold text-blue-400"
                            >
                                Sign in
                            </a>
                        </p>
                    @endauth

                </div>


                {{-- Trust signals --}}
                <div
                    class="mx-auto mt-7
                           grid max-w-sm grid-cols-1 gap-2.5
                           border-t border-white/10 pt-5
                           text-sm text-slate-400
                           sm:mt-12 sm:max-w-none
                           sm:flex sm:flex-wrap sm:justify-center
                           sm:gap-x-8 sm:gap-y-3
                           sm:border-0 sm:pt-0"
                >
                    @foreach ([
                        'Read-only connections',
                        'No trading authority',
                        'Built for investors',
                    ] as $trustItem)
                        <div
                            class="flex items-center justify-center gap-2"
                        >
                            <svg
                                class="h-4 w-4 shrink-0 text-emerald-400"
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
                                {{ $trustItem }}
                            </span>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>


        {{-- =====================================================
            WHAT HELMIO WATCHES
        ====================================================== --}}
        <section
            id="what-helmio-watches"
            class="border-y border-slate-800 bg-slate-900"
        >
            <div
                class="mx-auto max-w-7xl
                       px-4 py-14
                       sm:px-6 sm:py-20
                       lg:px-8 lg:py-28"
            >
                <div class="mx-auto max-w-3xl text-center">

                    <p
                        class="text-xs font-semibold uppercase
                               tracking-[0.2em] text-blue-400
                               sm:text-sm"
                    >
                        What Helmio watches
                    </p>

                    <h2
                        class="mt-4 text-3xl font-semibold
                               tracking-tight text-white
                               sm:text-5xl"
                    >
                        Your entire portfolio.

                        <span class="text-slate-400">
                            Not just the balance.
                        </span>
                    </h2>

                    <p
                        class="mt-4 text-base leading-7
                               text-slate-400 sm:mt-5"
                    >
                        Helmio evaluates the costs, activity,
                        risks, structure, and performance behind
                        your investment accounts.
                    </p>

                </div>


                @php
                    $features = [
                        [
                            'label' => 'COST',
                            'number' => '01',
                            'title' => 'Fees & Costs',
                            'text' => 'See what you are paying across advisory fees, fund expenses, account charges, and trading costs.',
                            'tags' => [
                                'Advisor Fees',
                                'Expense Ratios',
                                'All-In Cost',
                            ],
                        ],
                        [
                            'label' => 'RETURN',
                            'number' => '02',
                            'title' => 'Performance',
                            'text' => 'Understand how your investments are performing and how the results compare with relevant benchmarks.',
                            'tags' => [
                                'Returns',
                                'Benchmarks',
                                'History',
                            ],
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
                        ],
                        [
                            'label' => 'BALANCE',
                            'number' => '05',
                            'title' => 'Diversification',
                            'text' => 'See whether your portfolio is truly diversified or quietly concentrated.',
                            'tags' => [
                                'Top Holdings',
                                'Sectors',
                                'Asset Classes',
                            ],
                        ],
                        [
                            'label' => 'TAX',
                            'number' => '06',
                            'title' => 'Tax Efficiency',
                            'text' => 'Review gains, holding periods, dividends, tax-exempt income, and other after-tax considerations.',
                            'tags' => [
                                'Capital Gains',
                                'Dividends',
                                'Holding Period',
                            ],
                        ],
                        [
                            'label' => 'CASH',
                            'number' => '07',
                            'title' => 'Cash & Cash Drag',
                            'text' => 'Monitor idle cash and understand whether uninvested balances may be holding back performance.',
                            'tags' => [
                                'Cash',
                                'Deposits',
                                'Withdrawals',
                            ],
                        ],
                        [
                            'label' => 'POSITIONS',
                            'number' => '08',
                            'title' => 'Holdings',
                            'text' => 'Track positions, market value, cost basis, gains, portfolio weight, sector, and asset class.',
                            'tags' => [
                                'Cost Basis',
                                'Market Value',
                                'Portfolio Weight',
                            ],
                        ],
                        [
                            'label' => 'OVERSIGHT',
                            'number' => '09',
                            'title' => 'Advisor Oversight',
                            'text' => 'Surface high costs, unusual activity, concentration, and other issues worth discussing.',
                            'tags' => [
                                'Advisor Audit',
                                'Red Flags',
                                'Action Center',
                            ],
                        ],
                        [
                            'label' => 'ACTIVITY',
                            'number' => '10',
                            'title' => 'Account Activity',
                            'text' => 'Follow deposits, withdrawals, transfers, purchases, sales, dividends, interest, and fees.',
                            'tags' => [
                                'Transactions',
                                'Transfers',
                                'Income',
                            ],
                        ],
                        [
                            'label' => 'HISTORY',
                            'number' => '11',
                            'title' => 'Portfolio History',
                            'text' => 'See how your holdings, activity, and portfolio risk evolve over time.',
                            'tags' => [
                                'Snapshots',
                                'Timeline',
                                'Changes',
                            ],
                        ],
                        [
                            'label' => 'AI',
                            'number' => '12',
                            'title' => 'AI Insights',
                            'text' => 'Turn complex analytics into clear explanations, priorities, and questions worth asking.',
                            'tags' => [
                                'Insights',
                                'Priorities',
                                'Ask Helmio',
                            ],
                        ],
                    ];
                @endphp


                <div
                    class="mt-9 grid gap-4
                           sm:mt-14 sm:gap-6
                           md:grid-cols-2
                           lg:grid-cols-3"
                >
                    @foreach ($features as $feature)
                        <article
                            class="group relative overflow-hidden
                                   rounded-2xl
                                   border border-slate-700
                                   bg-slate-950
                                   shadow-xl
                                   transition duration-300
                                   hover:-translate-y-1
                                   hover:border-blue-400
                                   sm:rounded-3xl"
                        >
                            <div
                                class="h-1 w-full
                                       bg-gradient-to-r
                                       from-blue-600
                                       via-blue-400
                                       to-cyan-400"
                            ></div>

                            <div class="p-5 sm:p-7">

                                <div
                                    class="flex items-center justify-between"
                                >
                                    <span
                                        class="rounded-lg
                                               border border-blue-500/30
                                               bg-blue-500/10
                                               px-3 py-1
                                               text-[10px] font-semibold
                                               tracking-widest text-blue-300
                                               sm:text-xs"
                                    >
                                        {{ $feature['label'] }}
                                    </span>

                                    <span
                                        class="text-xs font-semibold
                                               text-slate-600"
                                    >
                                        {{ $feature['number'] }}
                                    </span>
                                </div>

                                <h3
                                    class="mt-5 text-xl font-semibold
                                           tracking-tight text-white
                                           sm:text-2xl"
                                >
                                    {{ $feature['title'] }}
                                </h3>

                                <p
                                    class="mt-2 text-sm leading-6
                                           text-slate-400
                                           sm:mt-3 sm:leading-7"
                                >
                                    {{ $feature['text'] }}
                                </p>

                                <div
                                    class="mt-5 hidden flex-wrap
                                           gap-2 sm:flex"
                                >
                                    @foreach ($feature['tags'] as $tag)
                                        <span
                                            class="rounded-lg
                                                   border border-slate-800
                                                   bg-slate-900
                                                   px-2.5 py-1.5
                                                   text-xs font-medium
                                                   text-slate-400"
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


        {{-- =====================================================
            ADVISOR OVERSIGHT
        ====================================================== --}}
        <section
            id="advisor-oversight"
            class="mx-auto max-w-7xl
                   px-4 py-14
                   sm:px-6 sm:py-20
                   lg:px-8 lg:py-28"
        >
            <div
                class="grid gap-9
                       lg:grid-cols-2 lg:items-center lg:gap-14"
            >

                <div>
                    <p
                        class="text-xs font-semibold uppercase
                               tracking-[0.2em] text-blue-400
                               sm:text-sm"
                    >
                        Advisor oversight
                    </p>

                    <h2
                        class="mt-4 text-3xl font-semibold
                               tracking-tight text-white
                               sm:text-5xl"
                    >
                        Your advisor has a job.

                        <span class="text-blue-400">
                            Helmio watches how it’s being done.
                        </span>
                    </h2>

                    <p
                        class="mt-4 max-w-xl
                               text-base leading-7 text-slate-400
                               sm:mt-6 sm:leading-8"
                    >
                        Helmio independently reviews activity inside
                        your accounts and highlights patterns that
                        deserve a closer look.
                    </p>
                </div>


                <div class="grid gap-3 sm:gap-4">

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
                            'text' => 'Compare portfolio characteristics with the goals, time horizon, and risk profile you provide.',
                        ],
                        [
                            'number' => '05',
                            'title' => 'Action Center',
                            'text' => 'Turn findings into prioritized questions and topics you can discuss with your advisor.',
                        ],
                    ] as $item)

                        <article
                            class="rounded-2xl
                                   border border-slate-800
                                   bg-slate-900/60
                                   p-5"
                        >
                            <div class="flex items-start gap-4">

                                <div
                                    class="flex h-10 w-10 shrink-0
                                           items-center justify-center
                                           rounded-xl
                                           border border-blue-400/25
                                           bg-blue-500/10
                                           text-xs font-bold
                                           text-blue-300"
                                >
                                    {{ $item['number'] }}
                                </div>

                                <div>
                                    <h3
                                        class="font-semibold text-white
                                               sm:text-lg"
                                    >
                                        {{ $item['title'] }}
                                    </h3>

                                    <p
                                        class="mt-1.5 text-sm
                                               leading-6 text-slate-400"
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


        {{-- =====================================================
            HELM SCORE
        ====================================================== --}}
        <section
            id="helm-score"
            class="border-y border-slate-800 bg-slate-900"
        >
            <div
                class="mx-auto max-w-7xl
                       px-4 py-14
                       sm:px-6 sm:py-20
                       lg:px-8 lg:py-28"
            >
                <div
                    class="overflow-hidden rounded-3xl
                           border border-blue-500/25
                           bg-slate-950 shadow-2xl"
                >
                    <div
                        class="grid gap-9 p-5
                               sm:p-10
                               lg:grid-cols-2
                               lg:items-center lg:p-16"
                    >

                        <div>
                            <p
                                class="text-xs font-semibold uppercase
                                       tracking-[0.2em] text-blue-400
                                       sm:text-sm"
                            >
                                Helm Score™
                            </p>

                            <h2
                                class="mt-4 text-3xl font-semibold
                                       tracking-tight text-white
                                       sm:text-5xl"
                            >
                                One score.

                                <span class="text-slate-400">
                                    Six dimensions of portfolio health.
                                </span>
                            </h2>

                            <p
                                class="mt-4 text-base leading-7
                                       text-slate-400
                                       sm:mt-5 sm:leading-8"
                            >
                                Quickly understand where your investments
                                are strong and where they may need attention.
                            </p>

                            <div
                                class="mt-6 grid grid-cols-2 gap-2
                                       sm:mt-7 sm:gap-3"
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
                                        class="flex items-center gap-2
                                               rounded-xl
                                               border border-slate-800
                                               bg-slate-900
                                               px-3 py-3"
                                    >
                                        <span
                                            class="h-2 w-2 shrink-0
                                                   rounded-full bg-blue-400"
                                        ></span>

                                        <span
                                            class="text-xs font-medium
                                                   text-slate-300
                                                   sm:text-sm"
                                        >
                                            {{ $item }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        {{-- Score --}}
                        <div class="flex justify-center">

                            <div
                                data-helm-score-wrapper
                                class="flex h-52 w-52
                                       items-center justify-center
                                       rounded-full
                                       border-[13px]
                                       border-slate-800
                                       bg-slate-950
                                       shadow-2xl
                                       sm:h-72 sm:w-72
                                       sm:border-[18px]"
                            >
                                <div
                                    class="flex h-40 w-40
                                           flex-col items-center
                                           justify-center
                                           rounded-full
                                           border border-blue-400/30
                                           bg-slate-900
                                           sm:h-56 sm:w-56"
                                >
                                    <p
                                        class="text-[10px] font-bold
                                               uppercase tracking-[0.2em]
                                               text-blue-400
                                               sm:text-xs"
                                    >
                                        Helm Score
                                    </p>

                                    <div
                                        class="mt-1 flex items-baseline"
                                    >
                                        <span
                                            data-helm-score
                                            data-score="84"
                                            class="text-6xl font-semibold
                                                   tracking-tight text-white
                                                   sm:text-7xl"
                                        >
                                            0
                                        </span>

                                        <span
                                            class="ml-1 text-sm
                                                   text-slate-600
                                                   sm:text-lg"
                                        >
                                            /100
                                        </span>
                                    </div>

                                    <div
                                        class="mt-3 flex items-center
                                               gap-2 rounded-full
                                               border border-emerald-500/20
                                               bg-emerald-500/10
                                               px-3 py-1"
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full
                                                   bg-emerald-400"
                                        ></span>

                                        <span
                                            class="text-xs font-semibold
                                                   text-emerald-300"
                                        >
                                            Strong
                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>


        {{-- =====================================================
            HOW IT WORKS
        ====================================================== --}}
        <section
            id="how-it-works"
            class="mx-auto max-w-7xl
                   px-4 py-14
                   sm:px-6 sm:py-20
                   lg:px-8 lg:py-28"
        >
            <div class="mx-auto max-w-3xl text-center">

                <p
                    class="text-xs font-semibold uppercase
                           tracking-[0.2em] text-blue-400
                           sm:text-sm"
                >
                    How it works
                </p>

                <h2
                    class="mt-4 text-3xl font-semibold
                           tracking-tight text-white
                           sm:text-5xl"
                >
                    Connect once.

                    <span class="text-slate-400">
                        Keep watch continuously.
                    </span>
                </h2>

            </div>


            <div
                class="mt-9 grid gap-3
                       sm:mt-14 sm:gap-6
                       md:grid-cols-2
                       lg:grid-cols-4"
            >
                @foreach ([
                    [
                        'number' => '01',
                        'title' => 'Create your account',
                        'text' => 'Create your secure Helmio investor profile.',
                    ],
                    [
                        'number' => '02',
                        'title' => 'Connect accounts',
                        'text' => 'Connect investment accounts using read-only access.',
                    ],
                    [
                        'number' => '03',
                        'title' => 'Helmio analyzes',
                        'text' => 'Helmio evaluates fees, performance, risk, trading, tax, and diversification.',
                    ],
                    [
                        'number' => '04',
                        'title' => 'Stay informed',
                        'text' => 'Review findings, explanations, changes, and ongoing monitoring.',
                    ],
                ] as $step)

                    <article
                        class="rounded-2xl
                               border border-slate-800
                               bg-slate-900/60
                               p-5
                               sm:rounded-3xl sm:p-6"
                    >
                        <span
                            class="flex h-9 w-9 items-center
                                   justify-center rounded-xl
                                   border border-blue-400/30
                                   bg-blue-500/10
                                   text-xs font-bold text-blue-300"
                        >
                            {{ $step['number'] }}
                        </span>

                        <h3
                            class="mt-4 text-lg font-semibold
                                   text-white sm:mt-5 sm:text-xl"
                        >
                            {{ $step['title'] }}
                        </h3>

                        <p
                            class="mt-2 text-sm leading-6
                                   text-slate-400 sm:leading-7"
                        >
                            {{ $step['text'] }}
                        </p>
                    </article>

                @endforeach
            </div>

        </section>


        {{-- =====================================================
            FINAL CTA
        ====================================================== --}}
        <section
            class="px-4 pb-14
                   sm:px-6 sm:pb-20
                   lg:px-8 lg:pb-28"
        >
            <div
                class="mx-auto max-w-5xl
                       rounded-3xl
                       border border-blue-400/30
                       bg-blue-600
                       px-5 py-9
                       text-center shadow-2xl
                       sm:px-10 sm:py-14
                       lg:py-16"
            >
                <p
                    class="text-xs font-semibold uppercase
                           tracking-[0.2em] text-blue-100
                           sm:text-sm"
                >
                    Independent investment oversight
                </p>

                <h2
                    class="mt-4 text-3xl font-semibold
                           tracking-tight text-white
                           sm:text-5xl"
                >
                    Stop wondering.
                    Start knowing.
                </h2>

                <p
                    class="mx-auto mt-4 max-w-2xl
                           text-sm leading-6 text-blue-100
                           sm:mt-5 sm:text-base sm:leading-7"
                >
                    Give your portfolio an independent second set
                    of eyes.
                </p>

                <div class="mt-7 sm:mt-8">

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex w-full items-center
                                   justify-center rounded-xl
                                   bg-white px-7 py-3.5
                                   font-semibold text-blue-800
                                   shadow-lg transition
                                   hover:bg-blue-50
                                   sm:w-auto sm:py-4"
                        >
                            Open Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex w-full items-center
                                   justify-center rounded-xl
                                   bg-white px-7 py-3.5
                                   font-semibold text-blue-800
                                   shadow-lg transition
                                   hover:bg-blue-50
                                   sm:w-auto sm:py-4"
                        >
                            Start your free trial
                        </a>

                        <p class="mt-4 text-sm text-blue-100">
                            Already have an account?

                            <a
                                href="{{ route('login') }}"
                                class="ml-1 font-semibold text-white
                                       underline underline-offset-4"
                            >
                                Log in
                            </a>
                        </p>
                    @endauth

                </div>
            </div>
        </section>

    </main>


    {{-- =========================================================
        FOOTER
    ========================================================== --}}
    <footer
        class="relative z-10
               border-t border-slate-800
               bg-slate-950"
    >
        <div
            class="mx-auto max-w-7xl
                   px-4 py-9
                   sm:px-6 sm:py-12
                   lg:px-8"
        >

            {{-- Mobile footer --}}
            <div class="sm:hidden">

                <div class="flex items-center gap-3">

                    <img
                        src="{{ asset('icons/icon-192.png') }}"
                        alt="Helmio"
                        class="h-9 w-9 rounded-xl"
                    >

                    <div>
                        <p class="font-semibold text-white">
                            Helmio
                        </p>

                        <p
                            class="text-[10px] uppercase
                                   tracking-[0.2em] text-slate-600"
                        >
                            Investment oversight
                        </p>
                    </div>

                </div>


                <div
                    class="mt-6 grid grid-cols-2
                           gap-x-6 gap-y-3 text-sm"
                >
                    @guest
                        <a
                            href="{{ route('login') }}"
                            class="text-slate-400 transition hover:text-white"
                        >
                            Log in
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="text-slate-400 transition hover:text-white"
                        >
                            Get started
                        </a>
                    @else
                        <a
                            href="{{ route('dashboard') }}"
                            class="text-slate-400 transition hover:text-white"
                        >
                            Dashboard
                        </a>

                        <a
                            href="{{ route('billing.index') }}"
                            class="text-slate-400 transition hover:text-white"
                        >
                            Billing
                        </a>
                    @endguest

                    <a
                        href="{{ route('contact') }}"
                        class="text-slate-400 transition hover:text-white"
                    >
                        Contact
                    </a>

                    <a
                        href="{{ route('privacy') }}"
                        class="text-slate-400 transition hover:text-white"
                    >
                        Privacy
                    </a>

                    <a
                        href="{{ route('terms') }}"
                        class="text-slate-400 transition hover:text-white"
                    >
                        Terms
                    </a>

                    @guest
                        <a
                            href="{{ route('billing.pricing') }}"
                            class="text-slate-400 transition hover:text-white"
                        >
                            Pricing
                        </a>
                    @endguest
                </div>

            </div>


            {{-- Desktop footer --}}
            <div
                class="hidden gap-10
                       sm:grid sm:grid-cols-2
                       lg:grid-cols-4"
            >

                <div>
                    <div class="flex items-center gap-3">

                        <img
                            src="{{ asset('icons/icon-192.png') }}"
                            alt="Helmio"
                            class="h-10 w-10 rounded-xl"
                        >

                        <div>
                            <p class="font-semibold text-white">
                                Helmio
                            </p>

                            <p
                                class="text-xs uppercase
                                       tracking-widest text-slate-500"
                            >
                                Investment oversight
                            </p>
                        </div>

                    </div>

                    <p
                        class="mt-5 max-w-sm
                               text-sm leading-7 text-slate-500"
                    >
                        Independent investment oversight designed
                        to help investors understand how their
                        money is being managed.
                    </p>
                </div>


                <div>
                    <p class="text-sm font-semibold text-white">
                        Product
                    </p>

                    <div class="mt-4 space-y-3 text-sm">

                        <a
                            href="#what-helmio-watches"
                            class="block text-slate-500
                                   transition hover:text-white"
                        >
                            What We Monitor
                        </a>

                        <a
                            href="#advisor-oversight"
                            class="block text-slate-500
                                   transition hover:text-white"
                        >
                            Advisor Oversight
                        </a>

                        <a
                            href="#helm-score"
                            class="block text-slate-500
                                   transition hover:text-white"
                        >
                            Helm Score
                        </a>

                        <a
                            href="#how-it-works"
                            class="block text-slate-500
                                   transition hover:text-white"
                        >
                            How It Works
                        </a>

                    </div>
                </div>


                <div>
                    <p class="text-sm font-semibold text-white">
                        Account
                    </p>

                    <div class="mt-4 space-y-3 text-sm">

                        @guest
                            <a
                                href="{{ route('register') }}"
                                class="block text-slate-500
                                       transition hover:text-white"
                            >
                                Start Free Trial
                            </a>

                            <a
                                href="{{ route('login') }}"
                                class="block text-slate-500
                                       transition hover:text-white"
                            >
                                Log In
                            </a>

                            <a
                                href="{{ route('billing.pricing') }}"
                                class="block text-slate-500
                                       transition hover:text-white"
                            >
                                Pricing
                            </a>
                        @else
                            <a
                                href="{{ route('dashboard') }}"
                                class="block text-slate-500
                                       transition hover:text-white"
                            >
                                Dashboard
                            </a>

                            <a
                                href="{{ route('billing.index') }}"
                                class="block text-slate-500
                                       transition hover:text-white"
                            >
                                Billing
                            </a>
                        @endguest

                    </div>
                </div>


                <div>
                    <p class="text-sm font-semibold text-white">
                        Company
                    </p>

                    <div class="mt-4 space-y-3 text-sm">

                        <a
                            href="{{ route('contact') }}"
                            class="block text-slate-500
                                   transition hover:text-white"
                        >
                            Contact
                        </a>

                        <a
                            href="{{ route('privacy') }}"
                            class="block text-slate-500
                                   transition hover:text-white"
                        >
                            Privacy Policy
                        </a>

                        <a
                            href="{{ route('terms') }}"
                            class="block text-slate-500
                                   transition hover:text-white"
                        >
                            Terms of Service
                        </a>

                    </div>
                </div>

            </div>


            <div
                class="mt-8 border-t border-slate-800
                       pt-6 text-xs leading-5
                       text-slate-600
                       sm:mt-10 sm:flex
                       sm:items-start sm:justify-between
                       sm:gap-8 sm:pt-7"
            >
                <p class="shrink-0">
                    © {{ date('Y') }} Helmio.
                    All rights reserved.
                </p>

                <p
                    class="mt-4 max-w-2xl
                           sm:mt-0 sm:text-right"
                >
                    Helmio provides monitoring and informational
                    analysis. Helmio does not provide investment,
                    tax, legal, or accounting advice and does not
                    have trading authority over connected accounts.
                </p>
            </div>

        </div>
    </footer>

</div>



{{-- =============================================================
    HERO HEADLINE ROTATION
============================================================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rotator =
            document.querySelector('[data-hero-rotator]');

        const textElement =
            document.querySelector('[data-hero-rotator-text]');

        if (!rotator || !textElement) {
            return;
        }

        const messages = [
            'isn’t telling you.',
            'is really costing you.',
            'is doing with your money.',
            'isn’t showing you.',
        ];

        const reducedMotion =
            window.matchMedia(
                '(prefers-reduced-motion: reduce)'
            ).matches;

        if (reducedMotion) {
            textElement.textContent = messages[0];
            return;
        }

        let index = 0;
        let intervalId = null;

        function showNextMessage() {
            textElement.classList.add(
                'opacity-0',
                '-translate-y-2'
            );

            window.setTimeout(function () {
                index = (index + 1) % messages.length;
                textElement.textContent = messages[index];

                textElement.classList.remove('-translate-y-2');
                textElement.classList.add('translate-y-2');

                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        textElement.classList.remove(
                            'opacity-0',
                            'translate-y-2'
                        );
                    });
                });
            }, 500);
        }

        function startRotation() {
            if (intervalId !== null) {
                return;
            }

            intervalId = window.setInterval(
                showNextMessage,
                3500
            );
        }

        function stopRotation() {
            if (intervalId === null) {
                return;
            }

            window.clearInterval(intervalId);
            intervalId = null;
        }

        document.addEventListener(
            'visibilitychange',
            function () {
                if (document.hidden) {
                    stopRotation();
                } else {
                    startRotation();
                }
            }
        );

        startRotation();
    });
</script>

{{-- =============================================================
    HELM SCORE ANIMATION
============================================================= --}}
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
                    threshold: 0.35,
                }
            );


        observer.observe(scoreWrapper);

    });
</script>

</body>
</html>