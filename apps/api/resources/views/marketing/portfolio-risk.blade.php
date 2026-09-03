<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Understand how much risk your portfolio is really taking. | Helmio</title>
    <meta name="description" content="Helmio evaluates volatility, drawdowns, downside behavior, beta, concentration, and other risk signals.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
<div class="min-h-screen overflow-hidden bg-slate-950">
<header class="border-b border-white/10 bg-slate-950/90 backdrop-blur">
<div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
<a href="{{ url('/') }}" class="flex items-center gap-3">
<div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg font-bold">H</div>
<div><p class="text-lg font-semibold">Helmio</p><p class="text-[9px] font-semibold uppercase tracking-[.22em] text-slate-500">Investment Oversight</p></div>
</a>
<nav class="hidden items-center gap-7 text-sm text-slate-400 md:flex">
<a href="{{ route('marketing.how-it-works') }}" class="hover:text-white">How It Works</a>
<a href="{{ route('marketing.security') }}" class="hover:text-white">Security</a>
<a href="{{ url('/pricing') }}" class="hover:text-white">Pricing</a>
</nav>
<div class="flex items-center gap-3">
@auth
<a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold">Dashboard</a>
@else
<a href="{{ route('login') }}" class="hidden text-sm font-semibold text-slate-300 sm:inline">Sign In</a>
<a href="{{ route('register') }}" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold hover:bg-blue-500">Get Started</a>
@endauth
</div>
</div>
</header>

<main>
<section class="relative border-b border-white/10">
<div class="absolute inset-0"><div class="absolute left-1/2 top-0 h-[620px] w-[900px] -translate-x-1/2 rounded-full bg-blue-600/10 blur-3xl"></div></div>
<div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-32">
<div class="mx-auto max-w-4xl text-center">
<span class="inline-flex rounded-full border border-blue-500/25 bg-blue-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-[.16em] text-blue-300">PORTFOLIO RISK</span>
<h1 class="mt-7 text-4xl font-semibold tracking-[-.04em] sm:text-5xl lg:text-6xl">Understand how much risk your portfolio is really taking.</h1>
<p class="mx-auto mt-6 max-w-3xl text-base leading-8 text-slate-400 sm:text-lg">Helmio evaluates volatility, drawdowns, downside behavior, beta, concentration, and other risk signals.</p>
<div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
<a href="{{ route('register') }}" class="inline-flex min-w-52 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold hover:bg-blue-500">Analyze My Portfolio Risk →</a>
<a href="{{ route('marketing.how-it-works') }}" class="inline-flex min-w-44 items-center justify-center rounded-xl border border-slate-700 bg-slate-900/60 px-5 py-3 text-sm font-semibold text-slate-200">How Helmio Works</a>
</div>
<div class="mt-8 flex flex-wrap justify-center gap-5 text-xs text-slate-500"><span>Read-only monitoring</span><span>•</span><span>No trading authority</span><span>•</span><span>Independent oversight</span></div>
</div>
</div>
</section>

<section class="border-b border-white/10 bg-slate-900/35">
<div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
<div class="mx-auto max-w-3xl text-center">
<p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-400">Why it matters</p>
<h2 class="mt-3 text-3xl font-semibold tracking-[-.03em] sm:text-4xl">See what is happening beneath the account balance.</h2>
<p class="mt-5 text-base leading-7 text-slate-400">Helmio turns portfolio data into understandable monitoring signals so you can see what deserves a closer look.</p>
</div>
</div>
</section>

<section>
<div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
<div class="mx-auto max-w-3xl text-center">
<p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-400">What Helmio looks for</p>
<h2 class="mt-3 text-3xl font-semibold tracking-[-.03em] sm:text-4xl">More clarity behind your investments.</h2>
</div>
<div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
<article class="group rounded-2xl border border-slate-800 bg-slate-900/65 p-6 transition hover:-translate-y-0.5 hover:border-blue-500/30 hover:bg-slate-900">
<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-sm font-semibold text-blue-300">01</div>
<h3 class="mt-5 text-lg font-semibold text-white">Volatility</h3>
<p class="mt-2 text-sm leading-6 text-slate-400">Measure how widely portfolio returns have moved over time.</p>
</article>
<article class="group rounded-2xl border border-slate-800 bg-slate-900/65 p-6 transition hover:-translate-y-0.5 hover:border-blue-500/30 hover:bg-slate-900">
<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-sm font-semibold text-blue-300">02</div>
<h3 class="mt-5 text-lg font-semibold text-white">Maximum Drawdown</h3>
<p class="mt-2 text-sm leading-6 text-slate-400">See the largest decline observed during available portfolio history.</p>
</article>
<article class="group rounded-2xl border border-slate-800 bg-slate-900/65 p-6 transition hover:-translate-y-0.5 hover:border-blue-500/30 hover:bg-slate-900">
<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-sm font-semibold text-blue-300">03</div>
<h3 class="mt-5 text-lg font-semibold text-white">Downside Risk</h3>
<p class="mt-2 text-sm leading-6 text-slate-400">Focus on negative-return behavior rather than treating all volatility the same.</p>
</article>
<article class="group rounded-2xl border border-slate-800 bg-slate-900/65 p-6 transition hover:-translate-y-0.5 hover:border-blue-500/30 hover:bg-slate-900">
<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-sm font-semibold text-blue-300">04</div>
<h3 class="mt-5 text-lg font-semibold text-white">Beta</h3>
<p class="mt-2 text-sm leading-6 text-slate-400">Estimate how sensitive the portfolio has been relative to its benchmark.</p>
</article>
<article class="group rounded-2xl border border-slate-800 bg-slate-900/65 p-6 transition hover:-translate-y-0.5 hover:border-blue-500/30 hover:bg-slate-900">
<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-sm font-semibold text-blue-300">05</div>
<h3 class="mt-5 text-lg font-semibold text-white">Concentration</h3>
<p class="mt-2 text-sm leading-6 text-slate-400">Identify positions and exposures that may amplify portfolio risk.</p>
</article>
<article class="group rounded-2xl border border-slate-800 bg-slate-900/65 p-6 transition hover:-translate-y-0.5 hover:border-blue-500/30 hover:bg-slate-900">
<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-sm font-semibold text-blue-300">06</div>
<h3 class="mt-5 text-lg font-semibold text-white">Suitability Context</h3>
<p class="mt-2 text-sm leading-6 text-slate-400">Compare observed portfolio risk with the profile you provide to Helmio.</p>
</article>
</div>
</div>
</section>

<section class="border-t border-white/10 bg-slate-900/35">
<div class="mx-auto max-w-5xl px-4 py-20 text-center sm:px-6 lg:px-8">
<div class="rounded-3xl border border-blue-500/20 bg-gradient-to-br from-blue-500/10 via-slate-900 to-slate-950 px-6 py-12">
<p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-400">Independent investment oversight</p>
<h2 class="mx-auto mt-4 max-w-3xl text-3xl font-semibold tracking-[-.03em] sm:text-4xl">Know what your portfolio is doing.</h2>
<p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-400">Connect your accounts read-only and let Helmio continuously monitor the investments behind your statements.</p>
<a href="{{ route('register') }}" class="mt-8 inline-flex rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold hover:bg-blue-500">Analyze My Portfolio Risk →</a>
</div>
</div>
</section>
</main>

<footer class="border-t border-white/10">
<div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-xs text-slate-500 sm:px-6 md:flex-row md:justify-between lg:px-8">
<p>© {{ now()->year }} Helmio.</p>
<div class="flex gap-5"><a href="{{ url('/security') }}">Security</a><a href="{{ url('/privacy') }}">Privacy</a><a href="{{ url('/terms') }}">Terms</a><a href="{{ url('/contact') }}">Contact</a></div>
</div>
</footer>
</div>
</body>
</html>
