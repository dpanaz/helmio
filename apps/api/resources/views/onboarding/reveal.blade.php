<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Portfolio analysis
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Your Portfolio Is Ready
            </h2>
        </div>
    </x-slot>

    @php
        $portfolioValue =
            (float) data_get(
                $summary,
                'portfolio_value',
                0
            );

        $accountCount =
            (int) data_get(
                $summary,
                'account_count',
                0
            );

        $holdingCount =
            (int) data_get(
                $summary,
                'holding_count',
                0
            );

        $transactionCount =
            (int) data_get(
                $summary,
                'transaction_count',
                0
            );

        $connectedAccounts =
            collect(
                data_get(
                    $summary,
                    'connected_accounts',
                    []
                )
            );
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            <section
                x-data="{
                    started: false,
                    showAccounts: false,
                    showMetrics: false,
                    showNext: false,

                    portfolioValue: 0,
                    accountCount: 0,
                    holdingCount: 0,
                    transactionCount: 0,

                    targets: {
                        portfolioValue: {{ $portfolioValue }},
                        accountCount: {{ $accountCount }},
                        holdingCount: {{ $holdingCount }},
                        transactionCount: {{ $transactionCount }},
                    },

                    animateValue(
                        key,
                        duration = 1400
                    ) {
                        const start =
                            performance.now();

                        const target =
                            this.targets[key];

                        const tick = (now) => {
                            const elapsed =
                                now - start;

                            const progress =
                                Math.min(
                                    elapsed / duration,
                                    1
                                );

                            const eased =
                                1 - Math.pow(
                                    1 - progress,
                                    3
                                );

                            this[key] =
                                Math.round(
                                    target * eased
                                );

                            if (progress < 1) {
                                requestAnimationFrame(
                                    tick
                                );
                            } else {
                                this[key] =
                                    target;
                            }
                        };

                        requestAnimationFrame(
                            tick
                        );
                    },

                    formatMoney(value) {
                        return new Intl.NumberFormat(
                            'en-US',
                            {
                                style: 'currency',
                                currency: 'USD',
                                maximumFractionDigits: 0,
                            }
                        ).format(value);
                    },

                    formatNumber(value) {
                        return new Intl.NumberFormat(
                            'en-US'
                        ).format(value);
                    },

                    init() {
                        window.setTimeout(() => {
                            this.started = true;
                            this.animateValue(
                                'portfolioValue',
                                1800
                            );
                        }, 250);

                        window.setTimeout(() => {
                            this.showMetrics = true;

                            this.animateValue(
                                'accountCount',
                                800
                            );

                            this.animateValue(
                                'holdingCount',
                                1000
                            );

                            this.animateValue(
                                'transactionCount',
                                1200
                            );
                        }, 900);

                        window.setTimeout(() => {
                            this.showAccounts = true;
                        }, 1300);

                        window.setTimeout(() => {
                            this.showNext = true;
                        }, 1800);
                    },
                }"
                class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl"
            >
                <div
                    class="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-28 -left-28 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"
                ></div>

                <div class="relative p-6 sm:p-8 lg:p-12">

                    <div
                        x-show="started"
                        x-transition.opacity.duration.700ms
                        class="text-center"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
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

                        <p class="mt-5 text-sm font-semibold text-emerald-300">
                            Analysis complete
                        </p>

                        <h1
                            class="mt-2 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                        >
                            Your portfolio is ready.
                        </h1>

                        <p
                            class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-400 sm:text-base"
                        >
                            Helmio successfully reviewed every connected
                            account, holding, and transaction currently available.
                        </p>
                    </div>

                    <div
                        x-show="started"
                        x-transition.opacity.duration.900ms
                        class="mt-10 text-center sm:mt-12"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                        >
                            Assets successfully analyzed
                        </p>

                        <p
                            class="mt-3 break-words text-5xl font-semibold tracking-tight text-white sm:text-7xl"
                            x-text="formatMoney(portfolioValue)"
                        ></p>
                    </div>

                    <div
                        x-show="showMetrics"
                        x-transition.opacity.duration.700ms
                        class="mt-10 grid gap-3 sm:grid-cols-3"
                    >
                        <div
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-5 text-center"
                        >
                            <p
                                class="text-3xl font-semibold text-white"
                                x-text="formatNumber(accountCount)"
                            ></p>

                            <p class="mt-2 text-sm text-slate-500">
                                Connected accounts
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-5 text-center"
                        >
                            <p
                                class="text-3xl font-semibold text-white"
                                x-text="formatNumber(holdingCount)"
                            ></p>

                            <p class="mt-2 text-sm text-slate-500">
                                Holdings reviewed
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-5 text-center"
                        >
                            <p
                                class="text-3xl font-semibold text-white"
                                x-text="formatNumber(transactionCount)"
                            ></p>

                            <p class="mt-2 text-sm text-slate-500">
                                Transactions reviewed
                            </p>
                        </div>
                    </div>

                    @if ($connectedAccounts->isNotEmpty())
                        <div
                            x-show="showAccounts"
                            x-transition.opacity.duration.700ms
                            class="mt-10"
                        >
                            <p class="text-sm font-semibold text-white">
                                Connected successfully
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                These accounts are included in your analysis.
                            </p>

                            <div class="mt-4 space-y-3">

                                @foreach ($connectedAccounts->take(5) as $account)

                                    <div
                                        class="flex min-w-0 items-center justify-between gap-4 rounded-2xl border border-slate-800 bg-slate-950 px-4 py-4"
                                    >
                                        <div class="flex min-w-0 items-center gap-3">

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

                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-white">
                                                    {{ data_get(
                                                        $account,
                                                        'name',
                                                        'Investment Account'
                                                    ) }}
                                                </p>

                                                <p class="mt-1 truncate text-sm text-slate-500">
                                                    {{ data_get(
                                                        $account,
                                                        'institution',
                                                        'Connected Institution'
                                                    ) }}
                                                </p>
                                            </div>

                                        </div>

                                        <p
                                            class="shrink-0 text-sm font-semibold text-slate-300"
                                        >
                                            {{ money(
                                                data_get(
                                                    $account,
                                                    'value',
                                                    0
                                                )
                                            ) }}
                                        </p>
                                    </div>

                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div
                        x-show="showNext"
                        x-transition.opacity.duration.700ms
                        class="mt-10 rounded-2xl border border-blue-500/20 bg-blue-500/[0.06] p-5"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Next
                        </p>

                        <h2 class="mt-2 text-xl font-semibold text-white">
                            See how healthy your portfolio is.
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Next, Helmio will reveal your overall score,
                            advisor-audit results, and the areas that deserve
                            your attention first.
                        </p>

                        <div class="mt-5">
                            <a
                                href="{{ route('onboarding.score') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white transition hover:bg-blue-500"
                            >
                                Continue to Your Results

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

            <p class="mt-5 text-center text-xs text-slate-600">
                Values reflect the most recent data received from your connected accounts.
            </p>

        </div>
    </div>
</x-app-layout>