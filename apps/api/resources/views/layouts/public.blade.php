<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="@yield('meta_description', 'Helmio provides independent investment oversight for investors.')"
    >

    <meta
        name="theme-color"
        content="#020617"
    >

    <title>
        @yield('title', 'Helmio')
    </title>

    {{-- Favicons --}}
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

<body
    class="min-h-screen bg-slate-950 text-white antialiased"
>
    <div class="relative min-h-screen">

        {{-- Background glow --}}
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-screen overflow-hidden"
        >
            <div
                class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"
            ></div>

            <div
                class="absolute -right-32 top-10 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"
            ></div>
        </div>

        {{-- Header --}}
        <header
            class="relative z-30 border-b border-white/10 bg-slate-950/90 backdrop-blur-lg"
        >
            <div
                class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                >
                    <img
                        src="{{ asset('icons/icon-192.png') }}"
                        alt="Helmio"
                        class="h-11 w-11 rounded-xl shadow-lg"
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

                <div class="flex items-center gap-3">
                    <a
                        href="/"
                        class="hidden text-sm font-semibold text-slate-400 transition hover:text-white sm:inline-flex"
                    >
                        Home
                    </a>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-slate-100"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="hidden text-sm font-semibold text-slate-300 transition hover:text-white sm:inline-flex"
                        >
                            Log in
                        </a>

                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-500"
                        >
                            Start free trial
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="relative">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer
            class="border-t border-slate-800 bg-slate-950"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
            >
                <div
                    class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between"
                >
                    <div
                        class="flex items-center gap-3"
                    >
                        <img
                            src="{{ asset('icons/icon-192.png') }}"
                            alt="Helmio"
                            class="h-9 w-9 rounded-lg"
                        >

                        <div>
                            <p
                                class="font-semibold text-white"
                            >
                                Helmio
                            </p>

                            <p
                                class="text-xs text-slate-500"
                            >
                                Independent investment oversight
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-wrap gap-x-6 gap-y-3 text-sm"
                    >
                        <a
                            href="/"
                            class="text-slate-500 transition hover:text-white"
                        >
                            Home
                        </a>

                        <a
                            href="{{ route('contact') }}"
                            class="text-slate-500 transition hover:text-white"
                        >
                            Contact
                        </a>

                        <a
                            href="{{ route('privacy') }}"
                            class="text-slate-500 transition hover:text-white"
                        >
                            Privacy
                        </a>

                        <a
                            href="{{ route('terms') }}"
                            class="text-slate-500 transition hover:text-white"
                        >
                            Terms
                        </a>
                    </div>
                </div>

                <div
                    class="mt-8 border-t border-slate-800 pt-6 text-xs leading-6 text-slate-600"
                >
                    <p>
                        © {{ date('Y') }} Helmio. Helmio provides monitoring
                        and informational analysis and does not provide
                        investment, tax, legal, or accounting advice.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>