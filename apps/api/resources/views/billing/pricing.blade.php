<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Helmio Membership
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                Choose your plan
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Continuous portfolio monitoring, advisor oversight, and AI-powered insights.
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

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-3xl bg-slate-950 px-5 py-8 text-white shadow-xl sm:px-8 lg:px-10">
                <div class="max-w-3xl">
                    <span class="inline-flex rounded-full bg-blue-500/20 px-3 py-1 text-xs font-semibold text-blue-200">
                        {{ number_format((int) $trialDays) }}-day free trial
                    </span>

                    <h1 class="mt-5 text-3xl font-semibold tracking-tight sm:text-4xl">
                        Give your portfolio a second set of eyes.
                    </h1>

                    <p class="mt-4 text-sm leading-7 text-slate-300 sm:text-base">
                        Helmio monitors your accounts, audits advisor behavior,
                        identifies risk and fee issues, and explains what matters
                        in plain English.
                    </p>
                </div>
            </section>

            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-blue-600">
                                Monthly
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold text-slate-950">
                                Helmio Premium
                            </h2>
                        </div>

                        @if ($currentPlan === 'monthly')
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Current plan
                            </span>
                        @endif
                    </div>

                    <div class="mt-6 flex items-end gap-2">
                        <span class="text-5xl font-bold tracking-tight text-slate-950">
                            $19
                        </span>

                        <span class="pb-2 text-slate-500">
                            / month
                        </span>
                    </div>

                    <p class="mt-3 text-sm text-slate-500">
                        Cancel anytime. Your trial starts today.
                    </p>

                    <ul class="mt-7 space-y-4 text-sm text-slate-700">
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
                                    class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
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

                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        @if ($hasAccess)
                            <a
                                href="{{ route('billing.index') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white hover:bg-slate-800"
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
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:bg-slate-300"
                                >
                                    Start Monthly Trial
                                </button>
                            </form>
                        @endif
                    </div>
                </section>

                <section class="relative overflow-hidden rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-6 shadow-sm sm:p-8">
                    <div class="absolute right-0 top-0 rounded-bl-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white">
                        Best value
                    </div>

                    <div class="flex items-start justify-between gap-4 pr-20">
                        <div>
                            <p class="text-sm font-semibold text-blue-600">
                                Annual
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold text-slate-950">
                                Helmio Premium
                            </h2>
                        </div>

                        @if ($currentPlan === 'annual')
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Current plan
                            </span>
                        @endif
                    </div>

                    <div class="mt-6 flex items-end gap-2">
                        <span class="text-5xl font-bold tracking-tight text-slate-950">
                            $190
                        </span>

                        <span class="pb-2 text-slate-500">
                            / year
                        </span>
                    </div>

                    <p class="mt-3 text-sm font-medium text-blue-700">
                        Save $38 compared with monthly billing.
                    </p>

                    <ul class="mt-7 space-y-4 text-sm text-slate-700">
                        @foreach ([
                            'Everything in the monthly plan',
                            'Two months free each year',
                            'Priority access to new features',
                            'Annual advisor-review summary',
                            'One simple annual payment',
                        ] as $feature)
                            <li class="flex items-start gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-blue-600"
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

                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        @if ($hasAccess)
                            <a
                                href="{{ route('billing.index') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white hover:bg-slate-800"
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
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:bg-slate-300"
                                >
                                    Start Annual Trial
                                </button>
                            </form>
                        @endif
                    </div>
                </section>
            </div>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <p class="font-semibold text-slate-950">
                            Read-only access
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Helmio cannot move money, place trades, or modify your brokerage accounts.
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-950">
                            Secure checkout
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Payment details are collected and managed by Stripe’s hosted checkout.
                        </p>
                    </div>

                    <div>
                        <p class="font-semibold text-slate-950">
                            App compatible
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Your subscription works across the web app, installed PWA, and future mobile app.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>