<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Helmio — Independent Investment Oversight</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-white antialiased">
    <header class="border-b border-white/10">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8">
            <a href="/" class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500 font-bold">
                    H
                </div>

                <div>
                    <div class="text-xl font-semibold tracking-tight">Helmio</div>
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400">
                        Investment oversight
                    </div>
                </div>
            </a>

            <nav class="flex items-center gap-3">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-950"
                    >
                        Dashboard
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white"
                    >
                        Log in
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-400"
                    >
                        Get started
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="mx-auto grid max-w-7xl gap-16 px-6 py-24 lg:grid-cols-2 lg:px-8 lg:py-32">
            <div>
                <div class="mb-6 inline-flex rounded-full border border-blue-400/30 bg-blue-400/10 px-4 py-2 text-sm text-blue-200">
                    Independent monitoring for your investments
                </div>

                <h1 class="max-w-3xl text-5xl font-semibold tracking-tight sm:text-6xl">
                    Know how your money is really being managed.
                </h1>

                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                    Helmio monitors fees, trading activity, portfolio performance,
                    fund expenses and potential compliance concerns from the
                    investor’s point of view.
                </p>

                <div class="mt-10 flex flex-wrap gap-4">
                    <a
                        href="{{ route('register') }}"
                        class="rounded-xl bg-blue-500 px-6 py-3 font-semibold text-white hover:bg-blue-400"
                    >
                        Create your account
                    </a>

                    <a
                        href="{{ route('login') }}"
                        class="rounded-xl border border-white/15 px-6 py-3 font-semibold text-white hover:bg-white/5"
                    >
                        Member login
                    </a>
                </div>

                <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm text-slate-400">
                    <span>Read-only connections</span>
                    <span>Evidence-backed alerts</span>
                    <span>No trading access</span>
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Helm Score</p>
                        <p class="mt-2 text-5xl font-semibold">83</p>
                    </div>

                    <span class="rounded-full bg-emerald-400/10 px-3 py-1 text-sm text-emerald-300">
                        Good
                    </span>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-slate-900/70 p-4">
                        <p class="text-sm text-slate-400">Portfolio value</p>
                        <p class="mt-2 text-xl font-semibold">$1,247,335</p>
                    </div>

                    <div class="rounded-2xl bg-slate-900/70 p-4">
                        <p class="text-sm text-slate-400">Annual fees</p>
                        <p class="mt-2 text-xl font-semibold">$11,842</p>
                    </div>

                    <div class="rounded-2xl bg-slate-900/70 p-4">
                        <p class="text-sm text-slate-400">Potential savings</p>
                        <p class="mt-2 text-xl font-semibold">$4,213</p>
                    </div>

                    <div class="rounded-2xl bg-slate-900/70 p-4">
                        <p class="text-sm text-slate-400">Open alerts</p>
                        <p class="mt-2 text-xl font-semibold">3</p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-amber-400/20 bg-amber-400/10 p-4">
                    <p class="font-medium text-amber-200">Expense review recommended</p>
                    <p class="mt-1 text-sm leading-6 text-amber-100/70">
                        Two funds appear materially more expensive than comparable alternatives.
                    </p>
                </div>
            </div>
        </section>

        <section class="border-t border-white/10 bg-slate-900/50">
            <div class="mx-auto grid max-w-7xl gap-6 px-6 py-20 md:grid-cols-3 lg:px-8">
                <article class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="text-lg font-semibold">Understand every cost</h2>
                    <p class="mt-3 leading-7 text-slate-400">
                        See advisory fees, brokerage charges and fund expenses in dollars and percentages.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="text-lg font-semibold">Monitor trading behavior</h2>
                    <p class="mt-3 leading-7 text-slate-400">
                        Track turnover, holding periods and activity that may deserve closer review.
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <h2 class="text-lg font-semibold">Measure real performance</h2>
                    <p class="mt-3 leading-7 text-slate-400">
                        Compare net results against benchmarks appropriate for your portfolio and risk.
                    </p>
                </article>
            </div>
        </section>
    </main>
</body>
</html>
