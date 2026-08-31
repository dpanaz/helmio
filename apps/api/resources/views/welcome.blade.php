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

<body class="min-h-screen overflow-x-hidden bg-slate-950 text-white antialiased" style="background-color:#020617;color:#ffffff;">

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
        HEADER — REDESIGNED
    ========================================================== --}}
    <header class="sticky top-0 z-50 border-b border-slate-800/70 bg-slate-950/80 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:h-20 sm:px-6 lg:px-8">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('icons/icon-192.png') }}" alt="Helmio" class="h-9 w-9 rounded-xl shadow-lg shadow-blue-950/30 sm:h-10 sm:w-10">
                <div>
                    <div class="text-lg font-semibold tracking-[-0.02em] text-white">Helmio</div>
                    <div class="hidden text-[9px] font-semibold uppercase tracking-[0.24em] text-slate-500 sm:block">Independent oversight</div>
                </div>
            </a>

            <nav class="hidden items-center gap-7 lg:flex">
                <a href="#why-helmio" class="text-sm font-medium text-slate-400 transition hover:text-white">Why Helmio</a>
                <a href="#what-helmio-watches" class="text-sm font-medium text-slate-400 transition hover:text-white">What we monitor</a>
                <a href="#product-tour" class="text-sm font-medium text-slate-400 transition hover:text-white">Product tour</a>
                <a href="#how-it-works" class="text-sm font-medium text-slate-400 transition hover:text-white">How it works</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-slate-100 sm:px-5">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden px-3 py-2 text-sm font-semibold text-slate-300 transition hover:text-white sm:inline-flex">Log in</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-500 sm:px-5">Start free</a>
                @endauth
            </div>
        </div>
    </header>


    <main class="relative z-10">

        {{-- =====================================================
            HERO — REDESIGNED
        ====================================================== --}}
        <section class="relative overflow-hidden bg-slate-950" style="background:linear-gradient(135deg,#020617 0%,#071225 52%,#020617 100%);">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-1/2 top-[-20rem] h-[44rem] w-[72rem] -translate-x-1/2 rounded-full bg-blue-600/15 blur-3xl"></div>
                <div class="absolute right-[-12rem] top-24 h-[28rem] w-[28rem] rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto grid max-w-7xl gap-12 px-4 pb-16 pt-14 sm:px-6 sm:pb-24 sm:pt-20 lg:grid-cols-2 lg:items-center lg:gap-14 lg:px-8 lg:pb-28 lg:pt-24">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-500/10 px-3.5 py-2 text-xs font-semibold text-blue-200 ">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        </span>
                        Independent oversight for your investments
                    </div>

                    <h1 class="mt-7 text-5xl font-semibold leading-tight tracking-tight text-white sm:text-6xl lg:text-7xl" style="color:#ffffff;">
                        Know what’s really happening
                        <span class="block text-blue-400" style="color:#60a5fa;">inside your portfolio.</span>
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-7 text-slate-300 sm:text-lg sm:leading-8" style="color:#cbd5e1;">
                        Helmio independently monitors fees, performance, risk, diversification, trading activity, tax efficiency, and advisor behavior—then shows you what deserves attention.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:-translate-y-0.5 hover:bg-blue-500">
                                Open Helmio
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:-translate-y-0.5 hover:bg-blue-500">
                                Start your free trial
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                            </a>
                            <a href="#product-tour" class="inline-flex items-center justify-center rounded-2xl ring-1 ring-inset ring-slate-800/70 bg-white/[0.04] px-6 py-3.5 text-base font-semibold text-white transition hover:bg-white/[0.08]">See the product</a>
                        @endauth
                    </div>

                    <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-slate-400">
                        @foreach (['Read-only connections', 'No trading authority', 'Built for investors'] as $trustItem)
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                                {{ $trustItem }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-2xl lg:max-w-none">
                    <div class="absolute -inset-8 rounded-[3rem] bg-blue-600/10 blur-3xl"></div>
                    <div class="relative overflow-hidden rounded-[2rem] ring-1 ring-inset ring-slate-800/70 bg-slate-900/75 p-2 shadow-[0_35px_100px_rgba(0,0,0,.55)] backdrop-blur">
                        <div class="rounded-3xl border border-slate-800 bg-slate-950 p-4 sm:p-6" style="background-color:#061022;">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-500">Portfolio oversight</p>
                                    <p class="mt-1 text-sm font-semibold text-white">Your portfolio today</p>
                                </div>
                                <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold text-emerald-300">Monitoring active</span>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="flex min-h-[220px] flex-col items-center justify-center rounded-2xl bg-slate-950/70 p-5 shadow-inner shadow-black/20">
                                    <div class="w-full max-w-[250px]">
                                        <div class="relative mx-auto w-full max-w-[250px]">
                                            <svg viewBox="0 0 360 210" class="block h-auto w-full" role="img" aria-label="Helm Score 84 out of 100">
                                                <defs>
                                                    <linearGradient id="heroHelmGaugeGradient" x1="0" y1="0" x2="1" y2="0">
                                                        <stop offset="0%" stop-color="#60a5fa" />
                                                        <stop offset="55%" stop-color="#3b82f6" />
                                                        <stop offset="100%" stop-color="#1d4ed8" />
                                                    </linearGradient>
                                                </defs>
                                                <path d="M 30 180 A 150 150 0 0 1 330 180" pathLength="100" fill="none" stroke="#172033" stroke-width="28" stroke-linecap="round" />
                                                <path d="M 30 180 A 150 150 0 0 1 330 180" pathLength="100" fill="none" stroke="url(#heroHelmGaugeGradient)" stroke-width="28" stroke-linecap="round" stroke-dasharray="84 100" />
                                                <g transform="rotate(151.2 180 180)">
                                                    <line x1="180" y1="180" x2="70" y2="180" stroke="#ffffff" stroke-width="6" stroke-linecap="round" />
                                                </g>
                                                <circle cx="180" cy="180" r="10" fill="#cbd5e1" />
                                                <circle cx="180" cy="180" r="17" fill="none" stroke="#1e293b" stroke-width="7" />
                                                <text x="22" y="202" fill="#64748b" font-size="13" font-weight="700">0</text>
                                                <text x="318" y="202" fill="#64748b" font-size="13" font-weight="700">100</text>
                                            </svg>
                                        </div>
                                        <div class="-mt-1 text-center">
                                            <span class="text-[9px] font-bold uppercase tracking-[0.22em] text-blue-400">Helm Score</span>
                                            <div class="mt-1 text-5xl font-semibold tracking-tight text-white">84</div>
                                            <div class="mt-1 text-xs font-semibold text-emerald-300">Strong</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="rounded-2xl border border-amber-400/20 bg-amber-500/[0.07] p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-amber-300">Needs attention</span>
                                            <span class="text-xs font-semibold text-amber-200">Trading</span>
                                        </div>
                                        <p class="mt-2 text-sm font-semibold text-white">Portfolio turnover is elevated.</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-400">Review recent trading activity and whether the added activity is improving results.</p>
                                    </div>
                                    @foreach ([
                                        ['Cost efficiency','90','Excellent','text-emerald-300'],
                                        ['Performance','80','Very good','text-blue-300'],
                                        ['Diversification','74','Good','text-cyan-300'],
                                    ] as $row)
                                        <div class="flex items-center justify-between rounded-xl ring-1 ring-inset ring-slate-800/60 bg-white/[0.025] px-4 py-3">
                                            <span class="text-sm font-medium text-slate-300">{{ $row[0] }}</span>
                                            <div class="flex items-center gap-3"><span class="text-sm font-semibold text-white">{{ $row[1] }}</span><span class="hidden text-xs {{ $row[3] }} sm:inline">{{ $row[2] }}</span></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-3 gap-3">
                                @foreach ([['12','areas monitored'],['3','priority findings'],['24/7','ongoing oversight']] as $stat)
                                    <div class="rounded-xl ring-1 ring-inset ring-slate-800/60 bg-white/[0.025] px-3 py-3 text-center">
                                        <div class="text-lg font-semibold text-white">{{ $stat[0] }}</div>
                                        <div class="mt-0.5 text-[9px] uppercase tracking-[0.12em] text-slate-500">{{ $stat[1] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="why-helmio" class="border-y border-slate-800/70 bg-white/[0.025]">
            <div class="mx-auto grid max-w-7xl gap-5 px-4 py-7 sm:grid-cols-3 sm:px-6 lg:px-8">
                @foreach ([
                    ['01','See the whole picture','Balance alone does not tell you what your portfolio costs, how much risk you are taking, or how efficiently it is being managed.'],
                    ['02','Spot what gets buried','Helmio surfaces fees, concentration, trading patterns, cash drag, and other issues that are easy to miss in ordinary statements.'],
                    ['03','Ask better questions','Turn portfolio analytics into clear discussion points so you can have a more informed conversation with your advisor.'],
                ] as $item)
                    <div class="flex gap-4 py-2">
                        <span class="mt-0.5 text-xs font-semibold text-blue-400">{{ $item[0] }}</span>
                        <div><h2 class="font-semibold text-white">{{ $item[1] }}</h2><p class="mt-1.5 text-sm leading-6 text-slate-500">{{ $item[2] }}</p></div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- =====================================================
            WHAT HELMIO WATCHES — VISUAL MONITOR GRID
        ====================================================== --}}
        <section id="what-helmio-watches" class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-1/2 top-16 h-80 w-[56rem] -translate-x-1/2 rounded-full bg-blue-600/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-400 sm:text-sm">
                        Complete portfolio oversight
                    </p>

                    <h2 class="mt-4 text-3xl font-semibold tracking-[-0.04em] text-white sm:text-5xl lg:text-6xl">
                        One portfolio.
                        <span class="text-blue-400">Every angle covered.</span>
                    </h2>

                    <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-400 sm:text-lg sm:leading-8">
                        Helmio continuously watches the signals that can affect your wealth — then brings the important ones together in one clear oversight system.
                    </p>
                </div>

                <div class="mx-auto mt-10 grid max-w-6xl gap-4 sm:mt-14 sm:grid-cols-2 lg:grid-cols-3">
                    <article class="group rounded-3xl bg-slate-900/55 p-5 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/70 transition duration-300 hover:-translate-y-1 hover:bg-slate-900/80 hover:ring-blue-500/20 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M14.8 8.7c-.6-.5-1.5-.8-2.5-.8-1.5 0-2.7.7-2.7 1.8 0 2.8 5.6 1.4 5.6 4.3 0 1.2-1.1 2.1-2.9 2.1-1.1 0-2.1-.3-2.9-1"/><path d="M12 6.6v10.8"/></svg>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Monitored
                            </span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-100">Fees &amp; Costs</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Advisor fees, fund expenses, account charges and trading costs.</p>
                    </article>

                    <article class="group rounded-3xl bg-slate-900/55 p-5 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/70 transition duration-300 hover:-translate-y-1 hover:bg-slate-900/80 hover:ring-blue-500/20 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 17 9 12l3 3 7-8"/><path d="M14 7h5v5"/></svg>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Monitored</span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-100">Performance</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Returns, benchmarks and historical portfolio results.</p>
                    </article>

                    <article class="group rounded-3xl bg-slate-900/55 p-5 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/70 transition duration-300 hover:-translate-y-1 hover:bg-slate-900/80 hover:ring-blue-500/20 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.7 2.7 8 7 10 4.3-2 7-5.3 7-10V6l-7-3Z"/><path d="m9 13 2-2 2 2 3-4"/></svg>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Monitored</span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-100">Portfolio Risk</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Volatility, drawdowns, exposure and risk characteristics.</p>
                    </article>

                    <article class="group rounded-3xl bg-slate-900/55 p-5 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/70 transition duration-300 hover:-translate-y-1 hover:bg-slate-900/80 hover:ring-blue-500/20 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v9h9"/><path d="M19.8 15a8 8 0 1 1-10.8-10"/></svg>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Monitored</span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-100">Diversification</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Concentration across holdings, sectors and asset classes.</p>
                    </article>

                    <article class="relative overflow-hidden rounded-3xl bg-blue-600/10 p-5 shadow-2xl shadow-blue-950/20 ring-1 ring-inset ring-blue-400/25 sm:p-6">
                        <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-blue-500/15 blur-3xl"></div>
                        <div class="relative">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/15 text-blue-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.4-5 9.5-5 9.5 5 9.5 5-3.4 5-9.5 5-9.5-5-9.5-5Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-blue-300"><span class="h-1.5 w-1.5 rounded-full bg-blue-400"></span>Core oversight</span>
                            </div>
                            <h3 class="mt-5 text-lg font-semibold tracking-tight text-white">Advisor Oversight</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-400">Helmio connects costs, activity, risk and structure into one independent review.</p>
                            <div class="mt-5 flex items-center gap-2 text-sm font-semibold text-blue-300">
                                <span>All signals, one independent review</span>
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                            </div>
                        </div>
                    </article>

                    <article class="group rounded-3xl bg-slate-900/55 p-5 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/70 transition duration-300 hover:-translate-y-1 hover:bg-slate-900/80 hover:ring-blue-500/20 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h12"/><path d="m13 4 3 3-3 3"/><path d="M20 17H8"/><path d="m11 14-3 3 3 3"/></svg>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Monitored</span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-100">Trading Activity</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Turnover, transaction volume, round trips and unusual patterns.</p>
                    </article>

                    <article class="group rounded-3xl bg-slate-900/55 p-5 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/70 transition duration-300 hover:-translate-y-1 hover:bg-slate-900/80 hover:ring-blue-500/20 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h8l3 3v15H7z"/><path d="M15 3v4h4"/><path d="M10 11h5M10 15h5"/></svg>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Monitored</span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-100">Tax Efficiency</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Realized gains, holding periods, dividends and after-tax considerations.</p>
                    </article>

                    <article class="group rounded-3xl bg-slate-900/55 p-5 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/70 transition duration-300 hover:-translate-y-1 hover:bg-slate-900/80 hover:ring-blue-500/20 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M7 9H5v2M17 15h2v-2"/></svg>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Monitored</span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-100">Cash Drag</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Idle cash, deposits, withdrawals and uninvested balances.</p>
                    </article>

                    <article class="group rounded-3xl bg-slate-900/55 p-5 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/70 transition duration-300 hover:-translate-y-1 hover:bg-slate-900/80 hover:ring-blue-500/20 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14v16H5z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Monitored</span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight text-slate-100">Account Activity</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Purchases, sales, transfers, dividends, interest and fees.</p>
                    </article>
                </div>

                <div class="mx-auto mt-8 flex max-w-3xl items-center justify-center gap-3 text-center sm:mt-10">
                    <span class="hidden h-px flex-1 bg-gradient-to-r from-transparent to-slate-800 sm:block"></span>
                    <p class="text-sm font-medium text-slate-400 sm:text-base">
                        If something changes, <span class="text-white">Helmio helps you see it.</span>
                    </p>
                    <span class="hidden h-px flex-1 bg-gradient-to-l from-transparent to-slate-800 sm:block"></span>
                </div>
            </div>
        </section>

        {{-- =====================================================
            ADVISOR OVERSIGHT + HELM SCORE — REDESIGNED
        ====================================================== --}}
        <section id="advisor-oversight" class="border-y border-slate-800/70 bg-slate-900/40">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center lg:gap-16">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-400">Advisor oversight</p>
                        <h2 class="mt-4 text-3xl font-semibold tracking-[-0.04em] text-white sm:text-5xl">Your advisor manages the portfolio. <span class="text-blue-400">Helmio independently reviews it.</span></h2>
                        <p class="mt-5 max-w-xl text-base leading-7 text-slate-400">The goal is not to replace your advisor. It is to give you independent visibility into the activity, costs, risk, and decisions occurring inside your accounts.</p>

                        <div class="mt-8 grid gap-3 sm:grid-cols-2">
                            @foreach ([
                                ['High-cost investments','Find expenses that deserve explanation.'],
                                ['Unusual trading','Surface elevated turnover and trading patterns.'],
                                ['Concentration','Spot oversized positions and exposures.'],
                                ['Action Center','Turn findings into questions worth asking.'],
                            ] as $item)
                                <div class="rounded-2xl ring-1 ring-inset ring-slate-800/60 bg-slate-950/60 p-4">
                                    <div class="flex items-center gap-2"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-500/10 text-blue-300"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><h3 class="text-sm font-semibold text-white">{{ $item[0] }}</h3></div>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ $item[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute -inset-6 rounded-[2.5rem] bg-blue-600/10 blur-3xl"></div>
                        <div class="relative rounded-[2rem] ring-1 ring-inset ring-slate-800/70 bg-slate-950 p-5 shadow-2xl sm:p-7">
                            <div class="flex items-center justify-between border-b border-slate-800/70 pb-5">
                                <div><p class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-400">Advisor Audit</p><h3 class="mt-1 text-lg font-semibold text-white">Independent review</h3></div>
                                <div class="text-right"><span class="text-3xl font-semibold text-white">78</span><span class="text-sm text-slate-600">/100</span></div>
                            </div>
                            <div class="mt-5 space-y-3">
                                @foreach ([
                                    ['Critical','High portfolio turnover detected','red'],
                                    ['Important','Diversification deserves review','amber'],
                                    ['Opportunity','Fund expenses may be reducible','blue'],
                                ] as $finding)
                                    <div class="flex gap-4 rounded-2xl ring-1 ring-inset ring-slate-800/60 bg-white/[0.025] p-4">
                                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $finding[2] === 'red' ? 'bg-red-400' : ($finding[2] === 'amber' ? 'bg-amber-400' : 'bg-blue-400') }}"></span>
                                        <div><p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">{{ $finding[0] }}</p><p class="mt-1 text-sm font-semibold text-slate-200">{{ $finding[1] }}</p></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-5 rounded-2xl border border-blue-400/15 bg-blue-500/[0.06] p-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-blue-400">Question to ask</p>
                                <p class="mt-2 text-sm leading-6 text-blue-100">“What is driving the recent turnover, and how has the additional trading affected my net performance?”</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="helm-score" class="relative overflow-hidden bg-slate-950">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-1/2 top-1/2 h-80 w-80 -translate-x-1/2 -translate-y-1/2 rounded-full bg-blue-600/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
                <div class="mb-10 max-w-3xl sm:mb-14">
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-400">One clear signal</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl">
                        Understand your portfolio health
                        <span class="text-slate-500">at a glance.</span>
                    </h2>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-400">
                        The Helm Score brings the most important dimensions of portfolio health together in one simple view, while still showing you exactly what is driving the result.
                    </p>
                </div>

                <div class="grid gap-8 lg:grid-cols-2 lg:items-stretch lg:gap-10">
                    {{-- Gauge card --}}
                    <div class="relative overflow-hidden rounded-3xl bg-slate-900/70 p-6 shadow-2xl shadow-black/20 ring-1 ring-inset ring-slate-800/70 sm:p-8">
                        <div class="pointer-events-none absolute inset-x-10 top-0 h-40 rounded-full bg-blue-600/10 blur-3xl"></div>

                        <div class="relative flex h-full flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-400">Helm Score™</p>
                                    <p class="mt-1 text-sm text-slate-500">Overall portfolio health</p>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-300 ring-1 ring-inset ring-emerald-500/20">
                                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                    Strong
                                </span>
                            </div>

                            <div class="flex flex-1 items-center justify-center py-6 sm:py-8">
                                <div style="width:100%; max-width:420px; margin:0 auto;">
                                    <svg viewBox="0 0 420 260" style="display:block; width:100%; height:auto;" role="img" aria-label="Animated Helm Score gauge">
                                        <defs>
                                            <linearGradient id="helmGaugeGradientMain" x1="0" y1="0" x2="1" y2="0">
                                                <stop offset="0%" stop-color="#60a5fa" />
                                                <stop offset="55%" stop-color="#3b82f6" />
                                                <stop offset="100%" stop-color="#1d4ed8" />
                                            </linearGradient>
                                        </defs>

                                        <path d="M 48 205 A 162 162 0 0 1 372 205"
                                              pathLength="100"
                                              fill="none"
                                              stroke="#1e293b"
                                              stroke-width="30"
                                              stroke-linecap="round" />

                                        <path data-helm-score-arc
                                              d="M 48 205 A 162 162 0 0 1 372 205"
                                              pathLength="100"
                                              fill="none"
                                              stroke="url(#helmGaugeGradientMain)"
                                              stroke-width="30"
                                              stroke-linecap="round"
                                              stroke-dasharray="0 100" />

                                        <g data-helm-score-needle transform="rotate(0 210 205)">
                                            <line x1="210" y1="205" x2="72" y2="205" stroke="#f8fafc" stroke-width="6" stroke-linecap="round" />
                                        </g>

                                        <circle cx="210" cy="205" r="20" fill="#0f172a" stroke="#334155" stroke-width="6" />
                                        <circle cx="210" cy="205" r="8" fill="#cbd5e1" />

                                        <text x="42" y="244" fill="#64748b" font-size="12" font-weight="700">0</text>
                                        <text x="201" y="31" fill="#64748b" font-size="12" font-weight="700">50</text>
                                        <text x="354" y="244" fill="#64748b" font-size="12" font-weight="700">100</text>
                                    </svg>

                                    <div class="-mt-8 text-center sm:-mt-10">
                                        <div class="flex items-baseline justify-center">
                                            <span data-helm-score data-score="84" class="text-6xl font-semibold tracking-tight text-white sm:text-7xl">0</span>
                                            <span class="ml-1 text-sm text-slate-600">/100</span>
                                        </div>
                                        <p class="mt-2 text-sm text-slate-500">Your portfolio's current oversight score</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Score breakdown --}}
                    <div class="rounded-3xl bg-slate-900/50 p-6 shadow-xl shadow-black/10 ring-1 ring-inset ring-slate-800/60 sm:p-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">What goes into it</p>
                                <h3 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">Six dimensions. One clear picture.</h3>
                            </div>
                            <span class="hidden rounded-xl bg-blue-500/10 px-3 py-2 text-xs font-semibold text-blue-300 sm:inline-flex">84 / 100</span>
                        </div>

                        <div class="mt-7 space-y-3">
                            <div class="flex items-center justify-between rounded-2xl bg-slate-950/70 px-4 py-3.5">
                                <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span><span class="text-sm font-semibold text-slate-200">Cost efficiency</span></div>
                                <span class="text-sm font-semibold text-emerald-300">Excellent</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-950/70 px-4 py-3.5">
                                <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span><span class="text-sm font-semibold text-slate-200">Performance</span></div>
                                <span class="text-sm font-semibold text-emerald-300">Very good</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-950/70 px-4 py-3.5">
                                <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-amber-400"></span><span class="text-sm font-semibold text-slate-200">Risk</span></div>
                                <span class="text-sm font-semibold text-amber-300">Review</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-950/70 px-4 py-3.5">
                                <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-blue-400"></span><span class="text-sm font-semibold text-slate-200">Diversification</span></div>
                                <span class="text-sm font-semibold text-blue-300">Good</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-950/70 px-4 py-3.5">
                                <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-amber-400"></span><span class="text-sm font-semibold text-slate-200">Trading discipline</span></div>
                                <span class="text-sm font-semibold text-amber-300">Review</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-950/70 px-4 py-3.5">
                                <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span><span class="text-sm font-semibold text-slate-200">Tax efficiency</span></div>
                                <span class="text-sm font-semibold text-emerald-300">Good</span>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl bg-blue-500/5 p-4 ring-1 ring-inset ring-blue-400/10">
                            <p class="text-sm leading-6 text-slate-400">
                                The score is only the starting point. Helmio shows you the categories and findings behind it so you can understand <span class="font-semibold text-slate-200">why the score moved.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =====================================================
            PRODUCT TOUR — REDESIGNED
        ====================================================== --}}
        <section
            id="product-tour"
            class="relative overflow-hidden border-y border-slate-800/70 bg-slate-950"
        >
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-1/2 top-0 h-[34rem] w-[70rem] -translate-x-1/2 rounded-full bg-blue-600/10 blur-3xl"></div>
                <div class="absolute -left-40 top-1/3 h-96 w-96 rounded-full bg-indigo-500/10 blur-3xl"></div>
                <div class="absolute -right-40 bottom-0 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"></div>
            </div>

            <div
                class="relative mx-auto max-w-[96rem] px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-28"
                data-product-tour
            >
                <div class="mx-auto max-w-4xl text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-400 sm:text-sm">
                        See Helmio in action
                    </p>

                    <h2 class="mt-5 text-3xl font-semibold tracking-[-0.035em] text-white sm:text-5xl lg:text-6xl">
                        Everything you need for
                        <span class="block bg-gradient-to-r from-blue-400 via-indigo-300 to-white bg-clip-text text-transparent">
                            independent investment oversight.
                        </span>
                    </h2>

                    <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-400 sm:text-lg sm:leading-8">
                        See the tools Helmio uses to monitor your portfolio, evaluate what deserves attention,
                        and explain the results in plain English.
                    </p>
                </div>

                @php
                    $productTour = [
                        [
                            'eyebrow' => 'DASHBOARD',
                            'title' => 'See what deserves your attention first.',
                            'text' => 'Your command center for portfolio oversight. The Helm Score, category breakdown, priority findings, and latest AI insight show you where to focus.',
                            'image' => 'images/welcome/Dashboard.png',
                            'alt' => 'Helmio portfolio dashboard showing Helm Score and category scores',
                            'features' => [
                                ['title' => 'Helm Score', 'text' => 'Overall portfolio health at a glance'],
                                ['title' => 'Priority findings', 'text' => 'Focus on what matters most'],
                                ['title' => 'AI summary', 'text' => 'Key insights, explained clearly'],
                            ],
                            'tags' => ['Helm Score', 'Priority Findings', 'AI Summary'],
                        ],
                        [
                            'eyebrow' => 'ADVISOR AUDIT',
                            'title' => 'Get an independent second look.',
                            'text' => 'Advisor Audit turns Helmio’s analytics into an oversight-focused review, separating critical concerns from important findings and opportunities.',
                            'image' => 'images/welcome/AdvisorAudit.png',
                            'alt' => 'Helmio Advisor Audit showing an advisor audit score and findings',
                            'features' => [
                                ['title' => 'Independent review', 'text' => 'Evaluate the portfolio beyond the advisor report'],
                                ['title' => 'Finding severity', 'text' => 'Separate critical concerns from lower-priority issues'],
                                ['title' => 'Discussion points', 'text' => 'Know what to ask your financial advisor'],
                            ],
                            'tags' => ['Advisor Audit', 'Critical Findings', 'Questions'],
                        ],
                        [
                            'eyebrow' => 'COST ANALYSIS',
                            'title' => 'Know what you paid — and what you got.',
                            'text' => 'Put portfolio costs beside benchmark performance so advisory fees, fund expenses, transaction costs, and relative results can be evaluated together.',
                            'image' => 'images/welcome/CostAnalysis.png',
                            'alt' => 'Helmio cost analysis comparing portfolio performance, costs, and a benchmark',
                            'features' => [
                                ['title' => 'All-in cost', 'text' => 'See available portfolio costs in one place'],
                                ['title' => 'Benchmark context', 'text' => 'Compare results against the selected benchmark'],
                                ['title' => 'Value gap', 'text' => 'See when cost and performance deserve review'],
                            ],
                            'tags' => ['Costs', 'Benchmark', 'Value Gap'],
                        ],
                        [
                            'eyebrow' => 'AI INSIGHTS',
                            'title' => 'Turn analytics into plain-English insight.',
                            'text' => 'Helmio’s AI explanation layer translates stored scores, findings, and data limitations into an executive summary without replacing the analytics underneath it.',
                            'image' => 'images/welcome/AIInsights.png',
                            'alt' => 'Helmio AI Insights page with explanation layer and insight history',
                            'features' => [
                                ['title' => 'Plain English', 'text' => 'Understand what the numbers are telling you'],
                                ['title' => 'Explainable output', 'text' => 'AI explains stored analytical results'],
                                ['title' => 'Insight history', 'text' => 'Review prior explanations over time'],
                            ],
                            'tags' => ['AI Insights', 'Explainable AI', 'History'],
                        ],
                        [
                            'eyebrow' => 'ASK HELMIO',
                            'title' => 'Ask questions about your actual portfolio.',
                            'text' => 'Ask Helmio in everyday language and get answers grounded in the scores, findings, activity, and review history already stored for your portfolio.',
                            'image' => 'images/welcome/AskHelmio.png',
                            'alt' => 'Ask Helmio portfolio assistant answering a portfolio-specific question',
                            'features' => [
                                ['title' => 'Portfolio Q&A', 'text' => 'Ask about your own stored portfolio data'],
                                ['title' => 'Advisor questions', 'text' => 'Turn findings into useful conversations'],
                                ['title' => 'Context-aware', 'text' => 'Answers use available Helmio records'],
                            ],
                            'tags' => ['Portfolio Q&A', 'Advisor Questions', 'Context'],
                        ],
                        [
                            'eyebrow' => 'MONTHLY REVIEWS',
                            'title' => 'See what changed — without rebuilding the story.',
                            'text' => 'Monthly Reviews preserve a month-by-month record of portfolio value, Helm Score movement, Advisor Audit results, material changes, and items that deserve review.',
                            'image' => 'images/welcome/MonthlyReviews.png',
                            'alt' => 'Helmio Monthly Reviews page describing monthly portfolio intelligence',
                            'features' => [
                                ['title' => 'Change tracking', 'text' => 'See meaningful portfolio changes over time'],
                                ['title' => 'Score movement', 'text' => 'Follow changes in portfolio health'],
                                ['title' => 'Review history', 'text' => 'Keep prior monthly reviews in one place'],
                            ],
                            'tags' => ['Monthly Review', 'Changes', 'History'],
                        ],
                        [
                            'eyebrow' => 'WHAT IF?',
                            'title' => 'Model changes before you make them.',
                            'text' => 'Explore hypothetical buys, sells, and allocation changes without changing your real holdings, then see how the scenario could affect the portfolio.',
                            'image' => 'images/welcome/WhatIf.png',
                            'alt' => 'Helmio What If portfolio simulator for modeling hypothetical portfolio changes',
                            'features' => [
                                ['title' => 'Hypothetical only', 'text' => 'Your actual portfolio remains unchanged'],
                                ['title' => 'Scenario modeling', 'text' => 'Test potential portfolio changes'],
                                ['title' => 'No trade execution', 'text' => 'Helmio analyzes — it does not place trades'],
                            ],
                            'tags' => ['What If', 'Scenarios', 'Read Only'],
                        ],
                    ];
                @endphp

                {{-- Top step navigation --}}
                <div class="mx-auto mt-10 flex max-w-3xl items-center justify-center gap-2 sm:mt-12 sm:gap-3">
                    @foreach ($productTour as $index => $item)
                        <button
                            type="button"
                            data-tour-step="{{ $index }}"
                            aria-label="Show {{ $item['eyebrow'] }}"
                            class="group flex min-w-0 items-center gap-2 rounded-full px-1.5 py-1.5 transition sm:px-2"
                        >
                            <span
                                data-tour-step-number
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-slate-500 ring-1 ring-inset ring-slate-700 transition sm:h-9 sm:w-9"
                            >
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span
                                data-tour-step-bar
                                class="hidden h-1 w-10 rounded-full bg-slate-800 transition-all duration-300 sm:block lg:w-14"
                            ></span>
                        </button>
                    @endforeach
                </div>

                {{-- Carousel shell --}}
                <div class="relative mt-8 sm:mt-10">
                    <div class="overflow-hidden rounded-[1.75rem] bg-slate-900/35 shadow-[0_30px_100px_rgba(2,6,23,0.65)] ring-1 ring-inset ring-blue-400/10 sm:rounded-[2rem]">
                        <div
                            data-tour-track
                            class="flex transition-transform duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
                        >
                            @foreach ($productTour as $index => $item)
                                <article
                                    data-tour-slide
                                    class="w-full shrink-0"
                                    aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                                >
                                    <div class="grid min-h-[560px] lg:grid-cols-2 lg:min-h-[650px]">
                                        {{-- Copy panel --}}
                                        <div class="relative flex flex-col justify-center overflow-hidden px-6 py-9 sm:px-9 sm:py-12 lg:px-12 lg:py-14">
                                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-blue-600/10 via-transparent to-transparent"></div>
                                            <div class="pointer-events-none absolute -left-24 top-0 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>

                                            <div class="relative">
                                                <div class="flex items-center gap-3">
                                                    <span class="rounded-full bg-blue-500/10 px-3 py-1.5 text-xs font-semibold tracking-[0.16em] text-blue-300 ring-1 ring-inset ring-blue-400/20">
                                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }} / {{ str_pad(count($productTour), 2, '0', STR_PAD_LEFT) }}
                                                    </span>
                                                    <span class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-400">
                                                        {{ $item['eyebrow'] }}
                                                    </span>
                                                </div>

                                                <h3 class="mt-6 text-3xl font-semibold leading-tight tracking-[-0.03em] text-white sm:text-4xl lg:text-[2.65rem]">
                                                    {{ $item['title'] }}
                                                </h3>

                                                <p class="mt-5 max-w-lg text-base leading-7 text-slate-400">
                                                    {{ $item['text'] }}
                                                </p>

                                                <div class="mt-7 h-px w-full bg-gradient-to-r from-slate-700/80 to-transparent"></div>

                                                <div class="mt-7 space-y-5">
                                                    @foreach ($item['features'] as $feature)
                                                        <div class="flex items-start gap-3.5">
                                                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-400/15">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4 4L19 6.5" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-semibold text-slate-100">{{ $feature['title'] }}</p>
                                                                <p class="mt-0.5 text-sm leading-6 text-slate-500">{{ $feature['text'] }}</p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="mt-8 flex flex-wrap gap-2">
                                                    @foreach ($item['tags'] as $tag)
                                                        <span class="rounded-full bg-slate-950/70 px-3 py-1.5 text-xs font-medium text-slate-400 ring-1 ring-inset ring-slate-700/70">
                                                            {{ $tag }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Screenshot panel --}}
                                        <div class="relative flex items-center justify-center overflow-hidden bg-[#020817] p-4 sm:p-6 lg:p-8">
                                            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_55%_45%,rgba(37,99,235,0.16),transparent_45%)]"></div>
                                            <div class="relative w-full">
                                                <div class="overflow-hidden rounded-2xl bg-slate-950/90 shadow-[0_24px_80px_rgba(0,0,0,0.48)] ring-1 ring-inset ring-slate-700/55">
                                                    <div class="flex h-9 items-center justify-between bg-slate-950/90 px-4">
                                                        <div class="flex gap-1.5">
                                                            <span class="h-2.5 w-2.5 rounded-full bg-slate-600"></span>
                                                            <span class="h-2.5 w-2.5 rounded-full bg-slate-700"></span>
                                                            <span class="h-2.5 w-2.5 rounded-full bg-slate-700"></span>
                                                        </div>
                                                        <span class="text-[9px] font-semibold uppercase tracking-[0.32em] text-slate-600">Helmio</span>
                                                    </div>
                                                    <div class="bg-slate-950">
                                                        <img
                                                            src="{{ asset($item['image']) }}"
                                                            alt="{{ $item['alt'] }}"
                                                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                                            decoding="async"
                                                            class="block max-h-[610px] w-full object-contain object-top"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    {{-- Arrow controls --}}
                    <button
                        type="button"
                        data-tour-prev
                        aria-label="Previous Helmio feature"
                        class="absolute left-3 top-1/2 z-20 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-slate-950/90 text-white shadow-xl ring-1 ring-inset ring-blue-400/25 backdrop-blur transition hover:bg-blue-600 lg:flex xl:-left-6"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                        </svg>
                    </button>

                    <button
                        type="button"
                        data-tour-next
                        aria-label="Next Helmio feature"
                        class="absolute right-3 top-1/2 z-20 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-slate-950/90 text-white shadow-xl ring-1 ring-inset ring-blue-400/25 backdrop-blur transition hover:bg-blue-600 lg:flex xl:-right-6"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>

                {{-- Bottom controls --}}
                <div class="mt-7 flex flex-col items-center justify-center gap-5 sm:flex-row sm:justify-between">
                    <div class="flex items-center gap-2" aria-label="Product tour pagination">
                        @foreach ($productTour as $index => $item)
                            <button
                                type="button"
                                data-tour-dot="{{ $index }}"
                                aria-label="Go to {{ $item['eyebrow'] }}"
                                class="h-2.5 w-2.5 rounded-full bg-slate-700 transition-all duration-300"
                            ></button>
                        @endforeach
                    </div>

                    <div class="flex gap-2 lg:hidden">
                        <button
                            type="button"
                            data-tour-prev-mobile
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 ring-1 ring-inset ring-slate-700 transition hover:bg-slate-800 hover:text-white"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                            </svg>
                            Previous
                        </button>
                        <button
                            type="button"
                            data-tour-next-mobile
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-950/30 transition hover:bg-blue-500"
                        >
                            Next
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mx-auto mt-7 flex max-w-3xl items-center justify-center gap-2 text-center text-xs leading-5 text-slate-600">
                    <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.8 2.8 8.1 7 10 4.2-1.9 7-5.2 7-10V6l-7-3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.5 12 1.7 1.7 3.6-3.9" />
                    </svg>
                    <span>Product screenshots use a demonstration portfolio. Results vary based on connected data, available history, and analytical coverage.</span>
                </div>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const root = document.querySelector('[data-product-tour]');
                if (!root) return;

                const track = root.querySelector('[data-tour-track]');
                const slides = Array.from(root.querySelectorAll('[data-tour-slide]'));
                const stepButtons = Array.from(root.querySelectorAll('[data-tour-step]'));
                const dots = Array.from(root.querySelectorAll('[data-tour-dot]'));
                const prevButtons = [
                    root.querySelector('[data-tour-prev]'),
                    root.querySelector('[data-tour-prev-mobile]'),
                ].filter(Boolean);
                const nextButtons = [
                    root.querySelector('[data-tour-next]'),
                    root.querySelector('[data-tour-next-mobile]'),
                ].filter(Boolean);

                if (!track || slides.length === 0) return;

                let current = 0;
                let touchStartX = null;

                function render() {
                    track.style.transform = `translate3d(-${current * 100}%, 0, 0)`;

                    slides.forEach((slide, index) => {
                        slide.setAttribute('aria-hidden', index === current ? 'false' : 'true');
                    });

                    stepButtons.forEach((button, index) => {
                        const number = button.querySelector('[data-tour-step-number]');
                        const bar = button.querySelector('[data-tour-step-bar]');
                        const active = index === current;

                        if (number) {
                            number.classList.toggle('bg-blue-600', active);
                            number.classList.toggle('text-white', active);
                            number.classList.toggle('ring-blue-400/40', active);
                            number.classList.toggle('bg-slate-900', !active);
                            number.classList.toggle('text-slate-500', !active);
                            number.classList.toggle('ring-slate-700', !active);
                        }

                        if (bar) {
                            bar.classList.toggle('w-16', active);
                            bar.classList.toggle('lg:w-20', active);
                            bar.classList.toggle('bg-blue-500', active);
                            bar.classList.toggle('w-10', !active);
                            bar.classList.toggle('lg:w-14', !active);
                            bar.classList.toggle('bg-slate-800', !active);
                        }
                    });

                    dots.forEach((dot, index) => {
                        const active = index === current;
                        dot.classList.toggle('w-7', active);
                        dot.classList.toggle('bg-blue-500', active);
                        dot.classList.toggle('w-2.5', !active);
                        dot.classList.toggle('bg-slate-700', !active);
                    });
                }

                function goTo(index) {
                    current = (index + slides.length) % slides.length;
                    render();
                }

                stepButtons.forEach((button, index) => {
                    button.addEventListener('click', () => goTo(index));
                });

                dots.forEach((dot, index) => {
                    dot.addEventListener('click', () => goTo(index));
                });

                prevButtons.forEach(button => {
                    button.addEventListener('click', () => goTo(current - 1));
                });

                nextButtons.forEach(button => {
                    button.addEventListener('click', () => goTo(current + 1));
                });

                track.addEventListener('touchstart', event => {
                    touchStartX = event.touches[0]?.clientX ?? null;
                }, { passive: true });

                track.addEventListener('touchend', event => {
                    if (touchStartX === null) return;
                    const touchEndX = event.changedTouches[0]?.clientX ?? touchStartX;
                    const delta = touchEndX - touchStartX;
                    touchStartX = null;

                    if (Math.abs(delta) < 45) return;
                    goTo(delta < 0 ? current + 1 : current - 1);
                }, { passive: true });

                root.addEventListener('keydown', event => {
                    if (event.key === 'ArrowLeft') goTo(current - 1);
                    if (event.key === 'ArrowRight') goTo(current + 1);
                });

                render();
            });
        </script>

        {{-- =====================================================
            HOW IT WORKS — REDESIGNED
        ====================================================== --}}
        <section id="how-it-works" class="border-y border-slate-800/70 bg-white/[0.02]">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-400">How it works</p>
                    <h2 class="mt-4 text-3xl font-semibold tracking-[-0.04em] text-white sm:text-5xl">Connect once. <span class="text-slate-500">Stay informed continuously.</span></h2>
                    <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-400">Helmio turns account data into ongoing oversight without taking control of your investments.</p>
                </div>

                <div class="relative mt-12 grid gap-4 md:grid-cols-4">
                    <div class="pointer-events-none absolute left-[12.5%] right-[12.5%] top-7 hidden h-px bg-gradient-to-r from-transparent via-blue-500/40 to-transparent md:block"></div>
                    @foreach ([
                        ['01','Create your profile','Tell Helmio about your investment goals and risk preferences.'],
                        ['02','Connect accounts','Link supported investment accounts using read-only access.'],
                        ['03','Helmio analyzes','Fees, performance, risk, trading, tax, diversification and more are evaluated.'],
                        ['04','You stay informed','Review scores, findings, explanations, changes and questions to ask.'],
                    ] as $step)
                        <article class="relative rounded-3xl ring-1 ring-inset ring-slate-800/60 bg-slate-950 p-5 sm:p-6">
                            <span class="relative z-10 flex h-14 w-14 items-center justify-center rounded-2xl border border-blue-400/20 bg-blue-500/10 text-sm font-bold text-blue-300 shadow-lg shadow-blue-950/30">{{ $step[0] }}</span>
                            <h3 class="mt-5 text-lg font-semibold text-white">{{ $step[1] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $step[2] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- =====================================================
            FINAL CTA — REDESIGNED
        ====================================================== --}}
        <section class="px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
            <div class="relative mx-auto max-w-6xl overflow-hidden rounded-[2rem] border border-blue-400/20 bg-gradient-to-br from-blue-600 via-blue-700 to-slate-950 px-6 py-12 shadow-[0_35px_100px_rgba(30,64,175,.25)] sm:px-10 sm:py-16 lg:px-16">
                <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-cyan-300/20 blur-3xl"></div>
                <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100">Independent investment oversight</p>
                        <h2 class="mt-4 max-w-3xl text-3xl font-semibold tracking-[-0.04em] text-white sm:text-5xl">You shouldn’t have to wonder what’s happening with your money.</h2>
                        <p class="mt-4 max-w-2xl text-base leading-7 text-blue-100/80">Give your portfolio an independent second set of eyes—and know what deserves your attention.</p>
                    </div>
                    <div>
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-7 py-4 font-semibold text-blue-800 shadow-xl transition hover:-translate-y-0.5 hover:bg-blue-50">Open dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-7 py-4 font-semibold text-blue-800 shadow-xl transition hover:-translate-y-0.5 hover:bg-blue-50">Start your free trial</a>
                            <p class="mt-3 text-center text-xs text-blue-100/70">Read-only. No trading authority.</p>
                        @endauth
                    </div>
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
                            href="#product-tour"
                            class="block text-slate-500
                                   transition hover:text-white"
                        >
                            Product Tour
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
            'a second set of eyes.',
            'more clarity.',
            'ongoing oversight.',
            'independent insight.',
            'your full attention.',
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
        const scoreElement = document.querySelector('[data-helm-score]');
        const scoreArc = document.querySelector('[data-helm-score-arc]');
        const scoreNeedle = document.querySelector('[data-helm-score-needle]');

        if (!scoreElement || !scoreArc || !scoreNeedle) {
            return;
        }

        const target = Math.max(0, Math.min(100, Number(scoreElement.dataset.score || 0)));
        let hasAnimated = false;

        function renderDial(score) {
            const clampedScore = Math.max(0, Math.min(100, score));
            scoreArc.setAttribute('stroke-dasharray', `${clampedScore} 100`);

            // SVG gauge runs from the left endpoint (0) through the top to the right endpoint (100).
            const needleAngle = (clampedScore / 100) * 180;
            scoreNeedle.setAttribute('transform', `rotate(${needleAngle} 210 205)`);
        }

        function animateScore() {
            if (hasAnimated) return;
            hasAnimated = true;

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                scoreElement.textContent = Math.round(target);
                renderDial(target);
                return;
            }

            const duration = 1400;
            const startTime = performance.now();

            function update(currentTime) {
                const progress = Math.min((currentTime - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const value = target * eased;

                scoreElement.textContent = Math.round(value);
                renderDial(value);

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    scoreElement.textContent = Math.round(target);
                    renderDial(target);
                }
            }

            requestAnimationFrame(update);
        }

        renderDial(0);

        const scoreSection = document.querySelector('#helm-score');
        if ('IntersectionObserver' in window && scoreSection) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animateScore();
                        observer.disconnect();
                    }
                });
            }, { threshold: 0.25 });

            observer.observe(scoreSection);
        } else {
            animateScore();
        }
    });
</script>

</body>
</html>