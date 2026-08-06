<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Setup complete
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

                    <div class="relative text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-400/10 text-emerald-300 ring-1 ring-emerald-300/20">
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

                        <p class="mt-5 text-sm font-semibold text-emerald-300">
                            Setup complete
                        </p>

                        <h1 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">
                            You’re ready.
                        </h1>

                        <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-300 sm:text-base">
                            Your investment accounts are connected and Helmio can begin
                            monitoring your portfolio.
                        </p>

                        <div class="mt-8 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-5 text-left">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Connected accounts
                                </p>

                                <p class="mt-2 text-3xl font-bold">
                                    {{ number_format((int) $accountCount) }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/10 p-5 text-left">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Portfolio value
                                </p>

                                <p class="mt-2 break-words text-3xl font-bold">
                                    {{ money($portfolioValue) }}
                                </p>
                            </div>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('onboarding.finish') }}"
                            class="mt-8"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white hover:bg-blue-500"
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
                </div>
            </section>
        </div>
    </div>
</x-app-layout>