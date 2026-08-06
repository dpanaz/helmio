<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Billing
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                Activating Helmio
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
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
                                throw new Error('Status request failed.');
                            }

                            const status = await response.json();

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
                class="overflow-hidden rounded-3xl border border-emerald-200 bg-white shadow-xl"
            >
                <div class="bg-emerald-50 px-6 py-8 text-center sm:px-10">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
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

                    <h1
                        class="mt-5 text-3xl font-semibold tracking-tight text-slate-950"
                        x-text="activated
                            ? 'Your membership is active.'
                            : 'Activating your membership…'"
                    ></h1>

                    <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-slate-600">
                        Stripe is confirming your subscription. Helmio will begin
                        onboarding automatically as soon as activation is complete.
                    </p>
                </div>

                <div class="px-6 py-6 sm:px-10">
                    <div
                        x-show="delayed"
                        x-cloak
                        class="rounded-2xl border border-amber-200 bg-amber-50 p-5"
                    >
                        <p class="font-semibold text-amber-950">
                            Activation is taking longer than expected.
                        </p>

                        <p class="mt-2 text-sm leading-6 text-amber-800">
                            Your checkout was successful. The Stripe webhook may still
                            be processing.
                        </p>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            <button
                                type="button"
                                x-on:click="
                                    delayed = false;
                                    attempts = 0;
                                    checkStatus();
                                "
                                class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-500"
                            >
                                Check Again
                            </button>

                            <a
                                href="{{ route('billing.index') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-white px-5 py-3 font-semibold text-amber-800 hover:bg-amber-50"
                            >
                                View Billing
                            </a>
                        </div>
                    </div>

                    <p
                        x-show="! delayed"
                        class="text-center text-sm text-slate-500"
                    >
                        Please keep this page open.
                    </p>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>