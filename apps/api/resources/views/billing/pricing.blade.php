<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Helmio Membership
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Choose Your Plan
            </h2>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                Continuous portfolio monitoring, advisor oversight,
                monthly reviews, and AI-powered insights.
            </p>
        </div>
    </x-slot>

    @php
        $hasAccess = (bool) data_get(
            $billingStatus,
            'has_access',
            false
        );

        $currentPlan = data_get(
            $billingStatus,
            'plan'
        );
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">

            <section
                class="relative overflow-hidden rounded-3xl border border-blue-500/20 bg-slate-900 px-6 py-8 shadow-xl sm:px-8 lg:px-10"
            >
                <div
                    class="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl"
                ></div>

                <div class="relative max-w-3xl">

                    <span
                        class="inline-flex rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1.5 text-xs font-semibold text-blue-300"
                    >
                        {{ number_format((int) $trialDays) }}-day free trial
                    </span>

                    <h1
                        class="mt-5 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                    >
                        Give your portfolio a second set of eyes.
                    </h1>

                    <p
                        class="mt-4 text-sm leading-7 text-slate-400 sm:text-base"
                    >
                        Helmio monitors your accounts, audits advisor behavior,
                        identifies risk and fee issues, and explains what matters
                        in plain English.
                    </p>
                </div>
            </section>

            @if (session('success'))
                <div
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="rounded-2xl border border-red-500/20 bg-red-500/[0.07] px-5 py-4 text-sm text-red-300"
                >
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Monthly --}}
                <section
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl sm:p-8"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-blue-400">
                                Monthly
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold text-white">
                                Helmio Premium
                            </h2>
                        </div>

                        @if ($currentPlan === 'monthly')
                            <span
                                class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                            >
                                Current Plan
                            </span>
                        @endif
                    </div>

                    <div class="mt-6 flex items-end gap-2">
                        <span class="text-5xl font-bold tracking-tight text-white">
                            $19.95
                        </span>

                        <span class="pb-2 text-slate-500">
                            / month
                        </span>
                    </div>

                    <p class="mt-3 text-sm text-slate-500">
                        Cancel anytime. Your trial starts today.
                    </p>

                    <ul class="mt-7 space-y-4 text-sm text-slate-300">
                        @foreach ([
                            'Unlimited investment accounts',
                            'Automatic brokerage synchronization',
                            'Advisor Audit and Action Center',
                            'Risk and suitability monitoring',
                            'AI portfolio insights',
                            'Monthly portfolio reviews',
                            'Notifications and audit reports',
                        ] as $feature)
                            <li class="flex items-start gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400"
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

                                <span>
                                    {{ $feature }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        @if ($hasAccess)
                            <a
                                href="{{ route('billing.index') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-5 py-3 font-semibold text-white transition hover:border-slate-600"
                            >
                                Manage Subscription
                            </a>
                        @else
                            <form
                                method="POST"
                                action="{{ route('billing.checkout') }}"
                                data-external-checkout
                            >
                                @csrf

                                <input
                                    type="hidden"
                                    name="billing_period"
                                    value="monthly"
                                >

                                <button
                                    type="submit"
                                    @disabled(blank($monthlyPriceId))
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:bg-slate-800 disabled:text-slate-500"
                                >
                                    Start Monthly Trial
                                </button>
                            </form>
                        @endif
                    </div>
                </section>

                {{-- Annual --}}
                <section
                    class="relative overflow-hidden rounded-3xl border border-blue-500/30 bg-blue-500/[0.06] p-6 shadow-xl sm:p-8"
                >
                    <div
                        class="absolute right-0 top-0 rounded-bl-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white"
                    >
                        Best Value
                    </div>

                    <div class="flex items-start justify-between gap-4 pr-20">
                        <div>
                            <p class="text-sm font-semibold text-blue-300">
                                Annual
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold text-white">
                                Helmio Premium
                            </h2>
                        </div>

                        @if ($currentPlan === 'annual')
                            <span
                                class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                            >
                                Current Plan
                            </span>
                        @endif
                    </div>

                    <div class="mt-6 flex items-end gap-2">
                        <span class="text-5xl font-bold tracking-tight text-white">
                            $199.95
                        </span>

                        <span class="pb-2 text-slate-500">
                            / year
                        </span>
                    </div>

                    <p class="mt-3 text-sm font-medium text-blue-300">
                        Save $38 compared with monthly billing.
                    </p>

                    <ul class="mt-7 space-y-4 text-sm text-slate-300">
                        @foreach ([
                            'Everything in the monthly plan',
                            'Two months free each year',
                            'Priority access to new features',
                            'Annual advisor-review summary',
                            'One simple annual payment',
                        ] as $feature)
                            <li class="flex items-start gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-blue-400"
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

                                <span>
                                    {{ $feature }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        @if ($hasAccess)
                            <a
                                href="{{ route('billing.index') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 px-5 py-3 font-semibold text-blue-200 transition hover:bg-blue-500/15"
                            >
                                Manage Subscription
                            </a>
                        @else
                            <form
                                method="POST"
                                action="{{ route('billing.checkout') }}"
                                data-external-checkout
                            >
                                @csrf

                                <input
                                    type="hidden"
                                    name="billing_period"
                                    value="annual"
                                >

                                <button
                                    type="submit"
                                    @disabled(blank($annualPriceId))
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:bg-slate-800 disabled:text-slate-500"
                                >
                                    Start Annual Trial
                                </button>
                            </form>
                        @endif
                    </div>
                </section>
            </div>

            <section
                class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
            >
                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ([
                        [
                            'title' => 'Read-only access',
                            'text' => 'Helmio cannot move money, place trades, or modify your brokerage accounts.',
                        ],
                        [
                            'title' => 'Secure checkout',
                            'text' => 'Payment details are collected and managed by Stripe’s hosted checkout.',
                        ],
                        [
                            'title' => 'App compatible',
                            'text' => 'Your subscription works across the web app, installed PWA, and future mobile app.',
                        ],
                    ] as $item)
                        <div
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-5"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
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

                            <p class="mt-4 font-semibold text-white">
                                {{ $item['title'] }}
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                {{ $item['text'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>