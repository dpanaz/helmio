<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Account
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                Billing and subscription
            </h2>
        </div>
    </x-slot>

    @php
        $hasAccess = (bool) data_get(
            $billingStatus,
            'has_access',
            false
        );

        $plan = data_get(
            $billingStatus,
            'plan'
        );

        $status = data_get(
            $billingStatus,
            'status',
            'none'
        );

        $onTrial = (bool) data_get(
            $billingStatus,
            'on_trial',
            false
        );

        $onGracePeriod = (bool) data_get(
            $billingStatus,
            'on_grace_period',
            false
        );

        $cancelled = (bool) data_get(
            $billingStatus,
            'cancelled',
            false
        );

        $trialEndsAt = data_get(
            $billingStatus,
            'trial_ends_at'
        );

        $endsAt = data_get(
            $billingStatus,
            'ends_at'
        );
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <section class="overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-300">
                            Helmio Membership
                        </p>

                        <h1 class="mt-2 text-3xl font-semibold tracking-tight">
                            @if ($hasAccess)
                                Your subscription is active
                            @else
                                No active subscription
                            @endif
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                            Manage your plan, payment method, invoices, and cancellation settings.
                        </p>
                    </div>

                    <span
                        class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold
                            {{ $hasAccess
                                ? 'bg-emerald-400/20 text-emerald-200'
                                : 'bg-slate-700 text-slate-300' }}"
                    >
                        {{ str($status)->replace('_', ' ')->title() }}
                    </span>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-950">
                        Current plan
                    </h2>

                    @if ($hasAccess)
                        <div class="mt-5 rounded-2xl bg-slate-50 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-blue-600">
                                        Helmio Premium
                                    </p>

                                    <p class="mt-2 text-2xl font-semibold text-slate-950">
                                        {{ $plan === 'annual'
                                            ? 'Annual billing'
                                            : 'Monthly billing' }}
                                    </p>
                                </div>

                                @if ($onTrial)
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Trial
                                    </span>
                                @elseif ($onGracePeriod)
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                        Cancels soon
                                    </span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        Active
                                    </span>
                                @endif
                            </div>

                            <dl class="mt-6 space-y-4 text-sm">
                                @if ($onTrial && $trialEndsAt)
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500">
                                            Trial ends
                                        </dt>

                                        <dd class="text-right font-medium text-slate-950">
                                            {{ $trialEndsAt->format('M j, Y') }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($cancelled && $endsAt)
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500">
                                            Access ends
                                        </dt>

                                        <dd class="text-right font-medium text-slate-950">
                                            {{ $endsAt->format('M j, Y') }}
                                        </dd>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-500">
                                        Billing status
                                    </dt>

                                    <dd class="text-right font-medium text-slate-950">
                                        {{ str($status)->replace('_', ' ')->title() }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('billing.portal') }}"
                            data-external-checkout
                            class="mt-5"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-slate-950 px-5 py-3 font-semibold text-white hover:bg-slate-800 sm:w-auto"
                            >
                                Open Billing Portal
                            </button>
                        </form>
                    @else
                        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                            <p class="font-semibold text-slate-950">
                                Start your Helmio membership
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Unlock continuous monitoring, advisor audits, AI insights, and automated reviews.
                            </p>

                            <a
                                href="{{ route('billing.pricing') }}"
                                class="mt-5 inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                            >
                                View Plans
                            </a>
                        </div>
                    @endif
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-950">
                        Billing help
                    </h2>

                    <div class="mt-5 space-y-4 text-sm text-slate-600">
                        <p>
                            Use the Stripe billing portal to update your payment method, download invoices, or cancel your subscription.
                        </p>

                        <p>
                            Your Helmio login and portfolio data remain separate from your Stripe payment details.
                        </p>
                    </div>

                    <a
                        href="{{ route('billing.pricing') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Compare plans →
                    </a>
                </section>
            </div>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">
                            Invoices
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Your recent Stripe invoices and receipts.
                        </p>
                    </div>
                </div>

                <div class="mt-5">
                    @if (collect($invoices)->isEmpty())
                        <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-600">
                            No invoices are available yet.
                        </div>
                    @else
                        <div class="divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200">
                            @foreach ($invoices as $invoice)
                                <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-medium text-slate-950">
                                            {{ $invoice->date()->format('M j, Y') }}
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $invoice->total() }}
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route(
                                            'billing.invoices.download',
                                            [
                                                'invoice' => $invoice->id,
                                            ]
                                        ) }}"
                                        class="inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                                    >
                                        Download PDF
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>