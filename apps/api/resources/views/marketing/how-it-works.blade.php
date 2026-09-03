@extends('layouts.marketing')

@section('title', 'Independent investment oversight in a few simple steps. | Helmio')

@section(
    'meta_description',
    'Connect your accounts read-only. Helmio organizes the data, analyzes the portfolio, and surfaces what deserves attention.'
)

@section('canonical', route('marketing.how-it-works'))

@section('content')
    <section
        class="relative overflow-hidden border-b border-slate-800/70 bg-slate-950"
        style="background: linear-gradient(135deg, #020617 0%, #071225 52%, #020617 100%);"
    >
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-1/2 top-[-20rem] h-[44rem] w-[72rem] -translate-x-1/2 rounded-full bg-blue-600/15 blur-3xl"></div>
            <div class="absolute right-[-12rem] top-24 h-[28rem] w-[28rem] rounded-full bg-cyan-500/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-32">
            <div class="mx-auto max-w-4xl text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-500/10 px-3.5 py-2 text-xs font-semibold text-blue-200">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                    </span>
                    How Helmio works
                </span>

                <h1 class="mt-7 text-4xl font-semibold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Independent investment oversight
                    <span class="block text-blue-400">in a few simple steps.</span>
                </h1>

                <p class="mx-auto mt-6 max-w-3xl text-base leading-8 text-slate-300 sm:text-lg">
                    Connect your accounts read-only. Helmio organizes the data,
                    analyzes the portfolio, and surfaces what deserves attention.
                </p>

                <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex min-w-52 items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:-translate-y-0.5 hover:bg-blue-500"
                        >
                            Open Helmio
                            <span aria-hidden="true">→</span>
                        </a>
                    @else
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex min-w-52 items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:-translate-y-0.5 hover:bg-blue-500"
                        >
                            Start your free trial
                            <span aria-hidden="true">→</span>
                        </a>
                    @endauth

                    <a
                        href="#steps"
                        class="inline-flex min-w-44 items-center justify-center rounded-2xl bg-white/[0.04] px-6 py-3.5 text-base font-semibold text-white ring-1 ring-inset ring-slate-800/70 transition hover:bg-white/[0.08]"
                    >
                        See how it works
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm text-slate-400">
                    @foreach (['Read-only connections', 'No trading authority', 'Independent oversight'] as $trustItem)
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                            {{ $trustItem }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-slate-800/70 bg-white/[0.025]">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-400">Why it matters</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-white sm:text-4xl">
                    See what is happening beneath the account balance.
                </h2>
                <p class="mt-5 text-base leading-7 text-slate-400">
                    Helmio turns portfolio data into understandable monitoring signals
                    so you can see what deserves a closer look.
                </p>
            </div>
        </div>
    </section>

    <section id="steps" class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-1/2 top-16 h-80 w-[56rem] -translate-x-1/2 rounded-full bg-blue-600/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-400">The process</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-white sm:text-4xl">
                    More clarity behind your investments.
                </h2>
                <p class="mt-5 text-base leading-7 text-slate-400">
                    From connection to continuous monitoring, Helmio keeps the process clear and focused.
                </p>
            </div>

            @php
                $steps = [
                    ['01', 'Connect', 'Add investment accounts using supported read-only connections or account data.'],
                    ['02', 'Analyze', 'Helmio evaluates fees, performance, risk, diversification, trading, cash, and taxes.'],
                    ['03', 'Score', 'Your Helm Score summarizes portfolio health across core monitoring categories.'],
                    ['04', 'Prioritize', 'The Action Center organizes findings so you know what deserves attention first.'],
                    ['05', 'Understand', 'AI insights translate portfolio analytics into plain-English observations.'],
                    ['06', 'Monitor', 'Helmio keeps reviewing changes as new holdings, transactions, and valuations arrive.'],
                ];
            @endphp

            <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($steps as $step)
                    <article class="group rounded-2xl bg-slate-900/65 p-6 ring-1 ring-inset ring-slate-800/70 transition hover:-translate-y-1 hover:bg-slate-900 hover:ring-blue-500/30">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-sm font-semibold text-blue-300">
                            {{ $step[0] }}
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-white">{{ $step[1] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">{{ $step[2] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-t border-slate-800/70 bg-white/[0.025]">
        <div class="mx-auto max-w-5xl px-4 py-20 text-center sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[2rem] bg-slate-900/75 px-6 py-12 ring-1 ring-inset ring-blue-500/20 sm:px-10">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-cyan-500/5"></div>
                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-400">Independent investment oversight</p>
                    <h2 class="mx-auto mt-4 max-w-3xl text-3xl font-semibold tracking-[-0.03em] text-white sm:text-4xl">
                        Know what your portfolio is doing.
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-400">
                        Connect your accounts read-only and let Helmio continuously monitor
                        the investments behind your statements.
                    </p>

                    @auth
                        <a href="{{ route('dashboard') }}" class="mt-8 inline-flex rounded-2xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:-translate-y-0.5 hover:bg-blue-500">
                            Open Helmio →
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="mt-8 inline-flex rounded-2xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:-translate-y-0.5 hover:bg-blue-500">
                            Start your free trial →
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
@endsection