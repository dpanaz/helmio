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
        content="#020617"
    >

    <meta
        name="application-name"
        content="Helmio"
    >

    <meta
        name="description"
        content="Independent investment oversight for your portfolio."
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
    class="min-h-full bg-slate-950 font-sans text-white antialiased"
>
    <div
        class="relative flex min-h-screen flex-col overflow-hidden bg-slate-950"
    >
        {{-- Background --}}
        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
        >
            <div
                class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"
            ></div>

            <div
                class="absolute -right-32 top-24 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"
            ></div>
        </div>

        {{-- Header --}}
        <header
            class="relative z-20 border-b border-slate-800 bg-slate-950/90 backdrop-blur"
        >
            <div
                class="mx-auto flex h-20 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                >
                    <img
                        src="{{ asset('icons/icon-192.png') }}"
                        alt="Helmio"
                        class="h-11 w-11 rounded-xl"
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

                <a
                    href="/"
                    class="text-sm font-medium text-slate-400 transition hover:text-white"
                >
                    Back to Helmio
                </a>
            </div>
        </header>

        {{-- Main --}}
        <main
            class="relative z-10 flex flex-1 items-center"
        >
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer
            class="relative z-10 border-t border-slate-800"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-6 text-xs text-slate-600 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8"
            >
                <p>
                    © {{ date('Y') }} Helmio. All rights reserved.
                </p>

                <div class="flex gap-5">
                    <a
                        href="{{ route('privacy') }}"
                        class="transition hover:text-slate-300"
                    >
                        Privacy
                    </a>

                    <a
                        href="{{ route('terms') }}"
                        class="transition hover:text-slate-300"
                    >
                        Terms
                    </a>

                    <a
                        href="{{ route('contact') }}"
                        class="transition hover:text-slate-300"
                    >
                        Contact
                    </a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>