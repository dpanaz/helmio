<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Getting started
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                Connect your investments
            </h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-600">
                                Step 3 of 4
                            </p>

                            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">
                                Connect your first investment account.
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                                Securely link a brokerage, retirement, or managed investment
                                account. Helmio will import balances, holdings, and transaction history.
                            </p>
                        </div>

                        <span class="inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                            Read-only
                        </span>
                    </div>

                    <div class="mt-6 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full w-3/4 rounded-full bg-blue-600"></div>
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
                            <div class="rounded-2xl bg-slate-50 p-5">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
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

                                <p class="mt-4 font-semibold text-slate-950">
                                    {{ $item['title'] }}
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-600">
                                    {{ $item['text'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 rounded-2xl border border-blue-200 bg-blue-50 p-5">
                        <div class="flex items-start gap-4">
                            <svg
                                class="mt-0.5 h-6 w-6 shrink-0 text-blue-700"
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
                                <p class="font-semibold text-blue-950">
                                    Helmio never receives trading authority.
                                </p>

                                <p class="mt-1 text-sm leading-6 text-blue-800">
                                    The connection cannot place trades, move money,
                                    change beneficiaries, or modify your brokerage account.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-between">
                        <a
                            href="{{ route('onboarding.profile') }}"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto"
                        >
                            Back
                        </a>

                        <a
                            href="{{ route('brokerage-connections.create', [
                                'onboarding' => 1,
                            ]) }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white shadow-sm hover:bg-blue-500 sm:w-auto"
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