<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-400">
                Billing
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Activating Helmio
            </h2>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                Your checkout is complete. Helmio is confirming your membership.
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-10">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

            <section
                x-data="{
                    attempts: 0,
                    activated: false,
                    delayed: false,

                    async checkStatus() {
                        try {
                            const response = await fetch(
                                '{{ route('billing.status') }}',
                                {
                                    headers: {
                                        Accept: 'application/json',
                                    },
                                }
                            );

                            if (! response.ok) {
                                throw new Error(
                                    'Status request failed.'
                                );
                            }

                            const status =
                                await response.json();

                            if (status.has_access) {
                                this.activated = true;

                                window.setTimeout(() => {
                                    window.location.href =
                                        '{{ route('onboarding.index') }}';
                                }, 700);

                                return;
                            }

                            this.attempts++;

                            if (this.attempts < 30) {
                                window.setTimeout(
                                    () => this.checkStatus(),
                                    2000
                                );
                            } else {
                                this.delayed = true;
                            }

                        } catch (error) {

                            this.attempts++;

                            if (this.attempts < 30) {
                                window.setTimeout(
                                    () => this.checkStatus(),
                                    2000
                                );
                            } else {
                                this.delayed = true;
                            }
                        }
                    },

                    init() {
                        this.checkStatus();
                    },
                }"
                class="relative overflow-hidden rounded-3xl border border-emerald-500/20 bg-slate-900 shadow-2xl"
            >
                <div
                    class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-500/10 blur-3xl"
                ></div>

                <div class="relative px-6 py-10 text-center sm:px-10">

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-300"
                    >
                        <svg
                            x-show="! activated"
                            class="h-8 w-8 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                            ></path>
                        </svg>

                        <svg
                            x-show="activated"
                            x-cloak
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
                        class="mt-5 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-400"
                        x-text="activated
                            ? 'Membership active'
                            : 'Confirming subscription'"
                    ></p>

                    <h1
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                        x-text="activated
                            ? 'Your membership is active.'
                            : 'Activating your membership…'"
                    ></h1>

                    <p class="mx-auto mt-4 max-w-lg text-sm leading-7 text-slate-400">
                        Stripe is confirming your subscription.
                        Helmio will begin onboarding automatically as soon
                        as activation is complete.
                    </p>

                    <div
                        x-show="! delayed && ! activated"
                        class="mx-auto mt-7 max-w-md"
                    >
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-800">
                            <div
                                class="h-full w-1/2 animate-pulse rounded-full bg-emerald-500"
                            ></div>
                        </div>

                        <p class="mt-3 text-xs text-slate-600">
                            Please keep this page open.
                        </p>
                    </div>

                    <div
                        x-show="activated"
                        x-cloak
                        class="mt-7 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.06] p-5"
                    >
                        <p class="font-semibold text-emerald-200">
                            Subscription confirmed
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Redirecting you to Helmio onboarding…
                        </p>
                    </div>
                </div>

                <div
                    x-show="delayed"
                    x-cloak
                    class="relative border-t border-slate-800 px-6 py-6 sm:px-10"
                >
                    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] p-5">

                        <p class="font-semibold text-amber-300">
                            Activation is taking longer than expected.
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Your checkout was successful. The Stripe webhook
                            may still be processing.
                        </p>

                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">

                            <button
                                type="button"
                                x-on:click="
                                    delayed = false;
                                    attempts = 0;
                                    checkStatus();
                                "
                                class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white transition hover:bg-amber-500"
                            >
                                Check Again
                            </button>

                            <a
                                href="{{ route('billing.index') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-5 py-3 font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                            >
                                View Billing
                            </a>

                        </div>
                    </div>
                </div>

            </section>

            <p class="mt-5 text-center text-xs text-slate-600">
                Billing is securely processed by Stripe.
            </p>

        </div>
    </div>
</x-app-layout>