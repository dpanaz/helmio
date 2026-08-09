<x-app-layout>
    <x-slot name="header">
        <div>
            <p
                class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-400"
            >
                Setup complete
            </p>

            <h2
                class="mt-2 text-2xl font-semibold tracking-tight text-white"
            >
                Welcome to Helmio
            </h2>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            <section
                class="relative overflow-hidden rounded-3xl border border-emerald-500/20 bg-slate-900 shadow-2xl"
            >
                <div
                    class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-500/15 blur-3xl"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-emerald-500/10 blur-3xl"
                ></div>

                <div class="relative p-6 text-center sm:p-10 lg:p-12">

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
                    >
                        <svg
                            class="h-8 w-8"
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

                    <p
                        class="mt-5 text-sm font-semibold text-emerald-300"
                    >
                        Setup complete
                    </p>

                    <h1
                        class="mt-2 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                    >
                        You’re ready.
                    </h1>

                    <p
                        class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-400 sm:text-base"
                    >
                        Your investment accounts are connected and Helmio
                        can begin monitoring your portfolio.
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">

                        <div
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-5 text-left"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                            >
                                Connected accounts
                            </p>

                            <p
                                class="mt-2 text-3xl font-semibold text-white"
                            >
                                {{ number_format(
                                    (int) $accountCount
                                ) }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-5 text-left"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-blue-400"
                            >
                                Portfolio value
                            </p>

                            <p
                                class="mt-2 break-words text-3xl font-semibold text-white"
                            >
                                {{ money($portfolioValue) }}
                            </p>
                        </div>

                    </div>

                    <div
                        class="mt-8 rounded-2xl border border-slate-800 bg-slate-950/60 p-5 text-left"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Monitoring begins now
                        </p>

                        <p
                            class="mt-2 text-sm leading-7 text-slate-400"
                        >
                            Helmio can now monitor fees, performance,
                            diversification, risk, trading activity, tax
                            efficiency, portfolio changes, and advisor-related
                            findings as new account data becomes available.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('onboarding.finish') }}"
                        class="mt-8"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white transition hover:bg-blue-500"
                        >
                            Open Dashboard

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
                        </button>
                    </form>

                </div>
            </section>

        </div>
    </div>
</x-app-layout>