<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Getting started
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                Investor profile
            </h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-blue-600">
                            Step 2 of 4
                        </p>

                        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">
                            Tell Helmio what matters to you.
                        </h1>
                    </div>

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                        Suitability
                    </span>
                </div>

                <div class="mt-6 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full w-2/4 rounded-full bg-blue-600"></div>
                </div>

                <p class="mt-6 text-sm leading-7 text-slate-600">
                    Your age, goals, time horizon, liquidity needs, and risk tolerance
                    help Helmio judge whether your portfolio fits your situation.
                </p>

                <div class="mt-6 rounded-2xl bg-slate-50 p-5">
                    @if ($investorProfile)
                        <p class="font-semibold text-slate-950">
                            Your investor profile is already started.
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Review it for accuracy before connecting your accounts.
                        </p>
                    @else
                        <p class="font-semibold text-slate-950">
                            Complete your investor profile.
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            This takes about one minute and improves every suitability,
                            risk, and advisor-audit result.
                        </p>
                    @endif
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <a
                        href="{{ route('onboarding.welcome') }}"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto"
                    >
                        Back
                    </a>

                    <a
                        href="{{ route('investor-profile.edit', [
                            'return_to' => route('onboarding.connect'),
                        ]) }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500 sm:w-auto"
                    >
                        {{ $investorProfile ? 'Review Profile' : 'Complete Profile' }}

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