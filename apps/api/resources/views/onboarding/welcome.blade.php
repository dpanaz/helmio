<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Getting started
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Welcome to Helmio
            </h2>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            <section
                class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl"
            >
                <div
                    class="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-28 -left-28 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"
                ></div>

                <div class="relative p-6 sm:p-8 lg:p-10">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-center gap-4">
                            <img
                                src="{{ asset('icons/icon-192.png') }}"
                                alt="Helmio"
                                class="h-12 w-12 rounded-2xl shadow-lg"
                            >

                            <div>
                                <p class="text-sm font-semibold text-blue-300">
                                    Step 1 of 4
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    About two minutes
                                </p>
                            </div>
                        </div>

                        <span
                            class="inline-flex w-fit rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                        >
                            Secure setup
                        </span>
                    </div>

                    <div class="mt-7 h-2 overflow-hidden rounded-full bg-slate-800">
                        <div class="h-full w-1/4 rounded-full bg-blue-500"></div>
                    </div>

                    <div class="mt-9 max-w-3xl">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Independent investment oversight
                        </p>

                        <h1
                            class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                        >
                            Let’s build your financial command center.
                        </h1>

                        <p
                            class="mt-4 max-w-2xl text-sm leading-7 text-slate-400 sm:text-base"
                        >
                            Helmio connects to your investment accounts using
                            read-only access, then monitors fees, risk,
                            performance, trading activity, diversification,
                            tax efficiency, and advisor behavior.
                        </p>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">

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

                            <article
                                class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5"
                            >
                                <div class="flex items-start gap-4">

                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
                                    >
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

                                        <p class="mt-1 text-sm leading-6 text-slate-500">
                                            {{ $item['text'] }}
                                        </p>
                                    </div>

                                </div>
                            </article>

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
            </section>

        </div>
    </div>
</x-app-layout>