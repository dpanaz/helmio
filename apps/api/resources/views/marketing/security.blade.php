@extends('layouts.marketing')

@section('title', 'Built for oversight. Not trading. | Helmio')

@section(
    'meta_description',
    'Helmio is designed around read-only investment monitoring without authority to buy, sell, transfer, or withdraw your assets.'
)

@section('canonical', route('marketing.security'))

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
                    Security & privacy
                </span>

                <h1 class="mt-7 text-4xl font-semibold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Built for oversight.
                    <span class="block text-blue-400">Not trading.</span>
                </h1>

                <p class="mx-auto mt-6 max-w-3xl text-base leading-8 text-slate-300 sm:text-lg">
                    Helmio is designed around read-only investment monitoring without
                    authority to buy, sell, transfer, or withdraw your assets.
                </p>

                <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('marketing.how-it-works') }}" class="inline-flex min-w-56 items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:-translate-y-0.5 hover:bg-blue-500">
                        Learn how Helmio works <span aria-hidden="true">→</span>
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex min-w-44 items-center justify-center rounded-2xl bg-white/[0.04] px-6 py-3.5 text-base font-semibold text-white ring-1 ring-inset ring-slate-800/70 transition hover:bg-white/[0.08]">Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex min-w-44 items-center justify-center rounded-2xl bg-white/[0.04] px-6 py-3.5 text-base font-semibold text-white ring-1 ring-inset ring-slate-800/70 transition hover:bg-white/[0.08]">Start free</a>
                    @endauth
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm text-slate-400">
                    @foreach (['Read-only connections', 'No trading authority', 'No money movement'] as $trustItem)
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
                <h2 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-white sm:text-4xl">Your portfolio access should match the job.</h2>
                <p class="mt-5 text-base leading-7 text-slate-400">Helmio needs information to monitor your portfolio—not permission to control it.</p>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-1/2 top-16 h-80 w-[56rem] -translate-x-1/2 rounded-full bg-blue-600/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-400">Designed for safer oversight</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-white sm:text-4xl">Monitoring without control of your assets.</h2>
            </div>

            @php
                $protections = [
                    ['01', 'Read-Only Approach', 'Helmio is designed to monitor investment information without requesting trading authority.'],
                    ['02', 'No Money Movement', 'Helmio is not built to transfer, withdraw, or move assets from your investment accounts.'],
                    ['03', 'Secure Connections', 'Supported connections use established financial-data connection methods.'],
                    ['04', 'Minimal Access', 'The platform is built around information needed for oversight rather than transaction authority.'],
                    ['05', 'Private Portfolio Data', 'Portfolio information is used to provide monitoring and analytics features.'],
                    ['06', 'Ongoing Monitoring', 'Security and privacy are part of the product architecture, not an optional add-on.'],
                ];
            @endphp

            <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($protections as $protection)
                    <article class="group rounded-2xl bg-slate-900/65 p-6 ring-1 ring-inset ring-slate-800/70 transition hover:-translate-y-1 hover:bg-slate-900 hover:ring-blue-500/30">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-sm font-semibold text-blue-300">{{ $protection[0] }}</div>
                        <h3 class="mt-5 text-lg font-semibold text-white">{{ $protection[1] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">{{ $protection[2] }}</p>
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
                    <h2 class="mx-auto mt-4 max-w-3xl text-3xl font-semibold tracking-[-0.03em] text-white sm:text-4xl">See what Helmio monitors.</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-400">Learn how Helmio turns read-only account information into clear portfolio insights.</p>
                    <a href="{{ route('marketing.how-it-works') }}" class="mt-8 inline-flex rounded-2xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-950/30 transition hover:-translate-y-0.5 hover:bg-blue-500">Learn how Helmio works →</a>
                </div>
            </div>
        </div>
    </section>
@endsection