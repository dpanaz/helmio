<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full bg-slate-950"
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

    {{-- PWA Manifest --}}
    <link
        rel="manifest"
        href="{{ asset('manifest.webmanifest') }}"
    >

    {{-- App Icons --}}
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

    {{-- Fonts --}}
    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >

    <link
        href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap"
        rel="stylesheet"
    >

    {{-- Application Assets --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>

<body
    class="min-h-full bg-slate-950 font-sans text-slate-100 antialiased"
>
    <div class="min-h-screen bg-slate-950">

        {{-- ========================================================= --}}
        {{-- NAVIGATION --}}
        {{-- ========================================================= --}}

        @include('layouts.navigation')


        {{-- ========================================================= --}}
        {{-- DESKTOP APP CONTENT WRAPPER --}}
        {{-- ========================================================= --}}
        {{--
            The desktop sidebar is fixed at 16rem / 256px.

            All application content gets the matching lg:pl-64 offset
            here instead of individual pages having to account for it.

            Mobile remains full width because the offset only applies
            at the lg breakpoint.
        --}}

        <div class="min-w-0 lg:pl-64">

            {{-- ===================================================== --}}
            {{-- OPTIONAL PAGE HEADER --}}
            {{-- ===================================================== --}}
            {{--
                Only render the header when the page actually provides
                meaningful header content.

                This prevents an empty Breeze header from creating an
                unnecessary strip above Helmio pages.
            --}}

            @if (
                isset($header)
                && trim(strip_tags((string) $header)) !== ''
            )
                <header
                    class="border-b border-slate-800 bg-slate-950"
                >
                    <div
                        class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
                    >
                        {{ $header }}
                    </div>
                </header>
            @endif


            {{-- ===================================================== --}}
            {{-- PAGE CONTENT --}}
            {{-- ===================================================== --}}

            <main
                class="min-h-[calc(100vh-5rem)] min-w-0 bg-slate-950 pb-20 sm:pb-0"
            >
                {{ $slot }}
            </main>

        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT FALLBACK --}}
    {{-- ============================================================= --}}

    <noscript>
        <div
            class="fixed inset-x-0 bottom-0 z-[100] border-t border-amber-300 bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900 lg:left-64"
        >
            Helmio requires JavaScript for account synchronization
            and interactive analytics.
        </div>
    </noscript>


    {{-- ============================================================= --}}
    {{-- PWA INSTALL PROMPT --}}
    {{-- ============================================================= --}}

    <x-pwa-install-prompt />

</body>
</html>