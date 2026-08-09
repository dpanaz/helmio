<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Getting started
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Connect Your Investments
            </h2>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >
                <div class="p-6 sm:p-8 lg:p-10">

                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold text-blue-400">
                                Step 3 of 4
                            </p>

                            <h1
                                class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl"
                            >
                                Connect your first investment account.
                            </h1>

                            <p
                                class="mt-3 max-w-2xl text-sm leading-7 text-slate-400"
                            >
                                Securely link a brokerage, retirement, or
                                managed investment account. Helmio will import
                                balances, holdings, and transaction history.
                            </p>
                        </div>

                        <span
                            class="inline-flex w-fit rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                        >
                            Read-only
                        </span>
                    </div>

                    <div class="mt-7 h-2 overflow-hidden rounded-full bg-slate-800">
                        <div class="h-full w-3/4 rounded-full bg-blue-500"></div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">

                        @foreach ([
                            [
                                'title' => 'Balances',
                                'text' => 'Current account and cash values',
                            ],
                            [
                                'title' => 'Holdings',
                                'text' => 'Stocks, funds, bonds, and positions',
                            ],
                            [
                                'title' => 'Transactions',
                                'text' => 'Purchases, sales, fees, and income',
                            ],
                        ] as $item)

                            <article
                                class="rounded-2xl border border-slate-800 bg-slate-950 p-5"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
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

                                <p class="mt-4 font-semibold text-white">
                                    {{ $item['title'] }}
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    {{ $item['text'] }}
                                </p>
                            </article>

                        @endforeach
                    </div>

                    <div
                        class="mt-8 rounded-2xl border border-blue-500/20 bg-blue-500/[0.06] p-5"
                    >
                        <div class="flex items-start gap-4">

                            <svg
                                class="mt-0.5 h-6 w-6 shrink-0 text-blue-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v7.5A1.5 1.5 0 0 1 17.25 21H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z"
                                />
                            </svg>

                            <div>
                                <p class="font-semibold text-white">
                                    Helmio never receives trading authority.
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-400">
                                    The connection cannot place trades, move
                                    money, change beneficiaries, or modify
                                    your brokerage account.
                                </p>
                            </div>

                        </div>
                    </div>

                    <div
                        class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-between"
                    >
                        <a
                            href="{{ route('onboarding.profile') }}"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-5 py-3 font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white sm:w-auto"
                        >
                            Back
                        </a>

                        <a
                            href="{{ route('brokerage-connections.create', [
                                'onboarding' => 1,
                            ]) }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
                        >
                            Connect Account

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