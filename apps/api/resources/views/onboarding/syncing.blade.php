<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Getting started
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                Building your portfolio
            </h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <section
                x-data="{
                    current: 0,
                    ready: false,
                    steps: [
                        'Connection verified',
                        'Importing account balances',
                        'Importing holdings',
                        'Importing transactions',
                        'Preparing portfolio analysis',
                        'Preparing your dashboard',
                    ],

                    init() {
                        const advance = () => {
                            if (this.current < this.steps.length) {
                                this.current++;

                                window.setTimeout(
                                    advance,
                                    this.current === this.steps.length
                                        ? 800
                                        : 650
                                );
                            } else {
                                this.ready = true;
                            }
                        };

                        window.setTimeout(advance, 500);
                    },
                }"
                class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
            >
                <div class="text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
                        <svg
                            x-show="! ready"
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
                            x-show="ready"
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

                    <p class="mt-5 text-sm font-semibold text-blue-600">
                        Step 4 of 4
                    </p>

                    <h1
                        class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl"
                        x-text="ready
                            ? 'Your portfolio is ready.'
                            : 'Connecting your portfolio…'"
                    ></h1>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600">
                        Helmio is organizing your account data and preparing the
                        first view of your portfolio.
                    </p>
                </div>

                <div class="mt-8 space-y-3">
                    <template x-for="(step, index) in steps" x-bind:key="step">
                        <div
                            class="flex items-center gap-3 rounded-xl border px-4 py-3 transition"
                            x-bind:class="index < current
                                ? 'border-emerald-200 bg-emerald-50'
                                : 'border-slate-200 bg-slate-50'"
                        >
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                x-bind:class="index < current
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-slate-200 text-slate-400'"
                            >
                                <svg
                                    x-show="index < current"
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

                                <span
                                    x-show="index >= current"
                                    class="text-xs font-bold"
                                    x-text="index + 1"
                                ></span>
                            </div>

                            <p
                                class="text-sm font-medium"
                                x-bind:class="index < current
                                    ? 'text-emerald-900'
                                    : 'text-slate-600'"
                                x-text="step"
                            ></p>
                        </div>
                    </template>
                </div>

                <div class="mt-8">
                    <a
                        x-show="ready"
                        x-cloak
                        href="{{ route('onboarding.reveal') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white hover:bg-blue-500"
                    >
                        Continue

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
            </section>
        </div>
    </div>
</x-app-layout>