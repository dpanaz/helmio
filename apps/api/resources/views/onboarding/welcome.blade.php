<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Getting started
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                Welcome to Helmio
            </h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-xl">
                <div class="relative overflow-hidden px-6 py-8 sm:px-10 sm:py-12">
                    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"></div>

                    <div class="relative">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ asset('icons/icon-192.png') }}"
                                    alt="Helmio"
                                    class="h-12 w-12 rounded-2xl"
                                >

                                <div>
                                    <p class="text-sm font-semibold text-blue-300">
                                        Step 1 of 4
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        About two minutes
                                    </p>
                                </div>
                            </div>

                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-slate-300">
                                Secure setup
                            </span>
                        </div>

                        <div class="mt-7 h-2 overflow-hidden rounded-full bg-white/10">
                            <div class="h-full w-1/4 rounded-full bg-blue-500"></div>
                        </div>

                        <h1 class="mt-8 text-3xl font-semibold tracking-tight sm:text-4xl">
                            Let’s build your financial command center.
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                            Helmio connects to your investment accounts using read-only access,
                            then monitors fees, risk, performance, trading activity, and advisor behavior.
                        </p>

                        <div class="mt-8 grid gap-3 sm:grid-cols-2">
                            @foreach ([
                                [
                                    'title' => 'Read-only access',
                                    'text' => 'Helmio can review account data but cannot change it.',
                                ],
                                [
                                    'title' => 'No trading authority',
                                    'text' => 'Helmio cannot buy, sell, transfer, or withdraw funds.',
                                ],
                                [
                                    'title' => 'Independent monitoring',
                                    'text' => 'Analysis is designed around the investor’s interests.',
                                ],
                                [
                                    'title' => 'Continuous oversight',
                                    'text' => 'Your portfolio can be reviewed as account data changes.',
                                ],
                            ] as $item)
                                <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-400/10 text-emerald-300">
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m5 12 4 4L19 6"
                                                />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="font-semibold text-white">
                                                {{ $item['title'] }}
                                            </p>

                                            <p class="mt-1 text-sm leading-6 text-slate-300">
                                                {{ $item['text'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex justify-end">
                            <a
                                href="{{ route('onboarding.profile') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
                            >
                                Continue

                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>