<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="theme-color"
        content="#0F172A"
    >

    <meta
        name="application-name"
        content="Helmio"
    >

    <meta
        name="description"
        content="Monitor your investments. Audit your advisor. Protect your future."
    >

    <meta
        name="mobile-web-app-capable"
        content="yes"
    >

    <meta
        name="apple-mobile-web-app-capable"
        content="yes"
    >

    <meta
        name="apple-mobile-web-app-title"
        content="Helmio"
    >

    <meta
        name="apple-mobile-web-app-status-bar-style"
        content="black-translucent"
    >

    <meta
        name="format-detection"
        content="telephone=no"
    >

    <title>
        {{ config('app.name', 'Helmio') }}
    </title>

    <link
    rel="manifest"
    href="{{ asset('manifest.webmanifest') }}"
>

    <link
        rel="icon"
        type="image/png"
        sizes="192x192"
        href="{{ asset('icons/icon-192.png') }}"
    >

    <link
        rel="icon"
        type="image/png"
        sizes="512x512"
        href="{{ asset('icons/icon-512.png') }}"
    >

    <link
        rel="apple-touch-icon"
        sizes="180x180"
        href="{{ asset('apple-touch-icon.png') }}"
    >

    <link
        rel="shortcut icon"
        href="{{ asset('favicon.ico') }}"
    >

    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >

    <link
        href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body
    class="min-h-full overflow-x-hidden bg-slate-50 font-sans text-slate-900 antialiased"
>
    <div
        id="app"
        class="min-h-screen bg-slate-50"
    >
        @include('layouts.navigation')

        @isset($header)
            <header
                class="border-b border-slate-200 bg-white"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
                >
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="pb-20 sm:pb-0">
    {{ $slot }}
</main>
    </div>

    <noscript>
        <div
            class="fixed inset-x-0 bottom-0 z-50 border-t border-amber-300 bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900"
        >
            Helmio requires JavaScript for account synchronization and interactive analytics.
        </div>
    </noscript>
    <x-pwa-install-prompt />
</body>
</html>