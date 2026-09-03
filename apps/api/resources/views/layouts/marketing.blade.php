<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Helmio | Independent Investment Account Monitoring')</title>
    <meta name="description" content="@yield('meta_description')">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Helmio">
    <meta property="og:title" content="@yield('title', 'Helmio')">
    <meta property="og:description" content="@yield('meta_description')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta name="twitter:card" content="summary_large_image">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-950 antialiased">
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <img src="{{ asset('helmio-mark.svg') }}" alt="" class="h-9 w-9">
            <span class="text-xl font-semibold tracking-tight">Helmio</span>
        </a>
        <nav class="hidden items-center gap-7 text-sm font-medium text-slate-700 md:flex">
            <a href="{{ url('/how-it-works') }}">How it works</a>
            <a href="{{ url('/check-my-financial-advisor') }}">Advisor check</a>
            <a href="{{ url('/security') }}">Security</a>
            <a href="{{ url('/pricing') }}">Pricing</a>
        </nav>
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="hidden text-sm font-medium sm:inline">Sign in</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white">Get started</a>
            @endauth
        </div>
    </div>
</header>

<main>@yield('content')</main>

<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-6 py-12 md:grid-cols-3 lg:px-8">
        <div>
            <div class="flex items-center gap-3">
                <img src="{{ asset('helmio-mark.svg') }}" alt="" class="h-8 w-8">
                <span class="font-semibold">Helmio</span>
            </div>
            <p class="mt-4 max-w-sm text-sm leading-6 text-slate-600">Independent monitoring for professionally managed investment accounts.</p>
        </div>
        <div class="text-sm">
            <div class="font-medium">Learn</div>
            <div class="mt-4 grid gap-2 text-slate-600">
                <a href="{{ url('/financial-advisor-fees') }}">Advisor fees</a>
                <a href="{{ url('/financial-advisor-performance') }}">Advisor performance</a>
                <a href="{{ url('/portfolio-churning') }}">Excessive trading</a>
                <a href="{{ url('/portfolio-diversification') }}">Diversification</a>
                <a href="{{ url('/portfolio-risk') }}">Portfolio risk</a>
                <a href="{{ url('/investment-tax-efficiency') }}">Tax efficiency</a>
            </div>
        </div>
        <div class="text-sm">
            <div class="font-medium">Company</div>
            <div class="mt-4 grid gap-2 text-slate-600">
                <a href="{{ url('/how-it-works') }}">How it works</a>
                <a href="{{ url('/security') }}">Security</a>
                <a href="{{ url('/pricing') }}">Pricing</a>
                <a href="{{ url('/contact') }}">Contact</a>
                <a href="{{ url('/privacy') }}">Privacy</a>
                <a href="{{ url('/terms') }}">Terms</a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
