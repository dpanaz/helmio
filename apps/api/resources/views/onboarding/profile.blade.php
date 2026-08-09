<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Getting started
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Investor Profile
            </h2>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >
                <div class="p-6 sm:p-8 lg:p-10">

                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold text-blue-400">
                                Step 2 of 4
                            </p>

                            <h1
                                class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl"
                            >
                                Tell Helmio what matters to you.
                            </h1>

                            <p
                                class="mt-3 max-w-2xl text-sm leading-7 text-slate-400"
                            >
                                Your age, goals, time horizon, liquidity needs,
                                and risk tolerance help Helmio judge whether
                                your portfolio fits your situation.
                            </p>
                        </div>

                        <span
                            class="inline-flex w-fit rounded-full border border-violet-500/20 bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-300"
                        >
                            Suitability
                        </span>
                    </div>

                    <div class="mt-7 h-2 overflow-hidden rounded-full bg-slate-800">
                        <div class="h-full w-2/4 rounded-full bg-blue-500"></div>
                    </div>

                    <div
                        class="mt-8 rounded-2xl border border-slate-800 bg-slate-950 p-6"
                    >
                        @if ($investorProfile)
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
                                >
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
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p class="font-semibold text-white">
                                        Your investor profile is already started.
                                    </p>

                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        Review it for accuracy before connecting your accounts.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
                                >
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
                                            d="M12 6v6m0 4h.01"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p class="font-semibold text-white">
                                        Complete your investor profile.
                                    </p>

                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        This takes about one minute and improves
                                        every suitability, risk, and advisor-audit result.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div
                        class="mt-8 grid gap-4 sm:grid-cols-3"
                    >
                        @foreach ([
                            [
                                'title' => 'Goals',
                                'text' => 'What the money is intended to accomplish.',
                            ],
                            [
                                'title' => 'Time horizon',
                                'text' => 'When you expect to need the money.',
                            ],
                            [
                                'title' => 'Risk tolerance',
                                'text' => 'How much volatility is appropriate for you.',
                            ],
                        ] as $item)
                            <div
                                class="rounded-2xl border border-slate-800 bg-slate-950/60 p-5"
                            >
                                <p class="font-semibold text-white">
                                    {{ $item['title'] }}
                                </p>

                                <p class="mt-2 text-sm leading-6 text-slate-500">
                                    {{ $item['text'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div
                        class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-between"
                    >
                        <a
                            href="{{ route('onboarding.welcome') }}"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-5 py-3 font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white sm:w-auto"
                        >
                            Back
                        </a>

                        <a
                            href="{{ route('investor-profile.edit', [
                                'return_to' => route('onboarding.connect'),
                            ]) }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
                        >
                            {{ $investorProfile
                                ? 'Review Profile'
                                : 'Complete Profile' }}

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