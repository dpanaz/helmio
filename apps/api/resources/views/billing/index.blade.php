<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Account
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Billing & Subscription
            </h2>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                Manage your Helmio membership, payment method,
                invoices, and subscription status.
            </p>
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

        $statusClasses = match ($status) {
            'active' =>
                'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

            'trialing' =>
                'border-blue-500/20 bg-blue-500/10 text-blue-300',

            'past_due' =>
                'border-amber-500/20 bg-amber-500/10 text-amber-300',

            'cancelled',
            'canceled' =>
                'border-red-500/20 bg-red-500/10 text-red-300',

            default =>
                'border-slate-700 bg-slate-800 text-slate-400',
        };
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Membership summary --}}
            <section
                class="relative overflow-hidden rounded-3xl border border-blue-500/20 bg-slate-900 shadow-xl"
            >
                <div
                    class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"
                ></div>

                <div class="relative p-6 sm:p-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                                Helmio Membership
                            </p>

                            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white">
                                @if ($hasAccess)
                                    Your subscription is active
                                @else
                                    No active subscription
                                @endif
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-400">
                                Manage your plan, payment method,
                                invoices, and cancellation settings.
                            </p>
                        </div>

                        <span
                            class="inline-flex w-fit rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusClasses }}"
                        >
                            {{ str($status)
                                ->replace('_', ' ')
                                ->title() }}
                        </span>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">

                {{-- Current plan --}}
                <section
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">
                        Subscription
                    </p>

                    <h2 class="mt-2 text-lg font-semibold text-white">
                        Current Plan
                    </h2>

                    @if ($hasAccess)

                        <div class="mt-5 rounded-2xl border border-slate-800 bg-slate-950 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                <div>
                                    <p class="text-sm font-semibold text-blue-300">
                                        Helmio Premium
                                    </p>

                                    <p class="mt-2 text-2xl font-semibold text-white">
                                        {{ $plan === 'annual'
                                            ? 'Annual Billing'
                                            : 'Monthly Billing' }}
                                    </p>
                                </div>

                                @if ($onTrial)
                                    <span class="rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300">
                                        Trial
                                    </span>
                                @elseif ($onGracePeriod)
                                    <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300">
                                        Cancels Soon
                                    </span>
                                @else
                                    <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">
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

                                        <dd class="text-right font-medium text-white">
                                            {{ $trialEndsAt->format('M j, Y') }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($cancelled && $endsAt)
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-slate-500">
                                            Access ends
                                        </dt>

                                        <dd class="text-right font-medium text-white">
                                            {{ $endsAt->format('M j, Y') }}
                                        </dd>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-slate-500">
                                        Billing status
                                    </dt>

                                    <dd class="text-right font-medium text-white">
                                        {{ str($status)
                                            ->replace('_', ' ')
                                            ->title() }}
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
                                class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
                            >
                                Open Billing Portal
                            </button>
                        </form>

                    @else

                        <div class="mt-5 rounded-2xl border border-dashed border-slate-700 bg-slate-950 p-6 text-center">
                            <div
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v12m6-6H6"
                                    />
                                </svg>
                            </div>

                            <p class="mt-4 font-semibold text-white">
                                Start Your Helmio Membership
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Unlock continuous monitoring, Advisor Audit,
                                AI insights, notifications, and monthly reviews.
                            </p>

                            <a
                                href="{{ route('billing.pricing') }}"
                                class="mt-5 inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500"
                            >
                                View Plans
                            </a>
                        </div>

                    @endif
                </section>

                {{-- Billing help --}}
                <section
                    class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                        Support
                    </p>

                    <h2 class="mt-2 text-lg font-semibold text-white">
                        Billing Help
                    </h2>

                    <div class="mt-5 space-y-4 text-sm leading-7 text-slate-400">
                        <p>
                            Use the Stripe billing portal to update your
                            payment method, download invoices, or cancel
                            your subscription.
                        </p>

                        <p>
                            Your Helmio login and portfolio data remain
                            separate from your Stripe payment details.
                        </p>
                    </div>

                    <a
                        href="{{ route('billing.pricing') }}"
                        class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-blue-400 transition hover:text-blue-300"
                    >
                        Compare Plans

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
                                d="m9 18 6-6-6-6"
                            />
                        </svg>
                    </a>
                </section>
            </div>

            {{-- Invoices --}}
            <section
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
            >
                <div class="border-b border-slate-800 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">
                        Billing history
                    </p>

                    <h2 class="mt-1 text-lg font-semibold text-white">
                        Invoices
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Your recent Stripe invoices and receipts.
                    </p>
                </div>

                <div>
                    @if (collect($invoices)->isEmpty())
                        <div class="px-6 py-10 text-center">
                            <p class="font-semibold text-white">
                                No invoices yet
                            </p>

                            <p class="mt-2 text-sm text-slate-500">
                                Billing receipts will appear here after your first charge.
                            </p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-800">
                            @foreach ($invoices as $invoice)
                                <div
                                    class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div>
                                        <p class="font-medium text-white">
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
                                        class="inline-flex text-sm font-semibold text-blue-400 transition hover:text-blue-300"
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