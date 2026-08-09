<x-app-layout>
    <x-slot name="header">
        <div>
            <p
                class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
            >
                Portfolio analysis
            </p>

            <h2
                class="mt-2 text-2xl font-semibold tracking-tight text-white"
            >
                What Helmio Found
            </h2>
        </div>
    </x-slot>

    @php
        $overallScore =
            data_get(
                $audit,
                'overall_score'
            );

        $overallLabel =
            data_get(
                $audit,
                'overall_label',
                'Building your audit'
            );

        $executiveHeadline =
            data_get(
                $audit,
                'executive_summary.headline',
                'Your advisor audit is ready.'
            );

        $executiveSummary =
            data_get(
                $audit,
                'executive_summary.summary',
                'Helmio reviewed the available account data and identified the areas that deserve your attention first.'
            );
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            <section
                x-data="{
                    showHeader: false,
                    showConcern: false,
                    showOpportunity: false,
                    showStrength: false,
                    showNext: false,

                    init() {
                        window.setTimeout(
                            () => this.showHeader = true,
                            250
                        );

                        window.setTimeout(
                            () => this.showConcern = true,
                            800
                        );

                        window.setTimeout(
                            () => this.showOpportunity = true,
                            1350
                        );

                        window.setTimeout(
                            () => this.showStrength = true,
                            1900
                        );

                        window.setTimeout(
                            () => this.showNext = true,
                            2450
                        );
                    },
                }"
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl"
            >
                <div class="p-6 sm:p-8 lg:p-12">

                    <div
                        x-show="showHeader"
                        x-transition.opacity.duration.700ms
                        class="text-center"
                    >
                        <p class="text-sm font-semibold text-blue-400">
                            Your first advisor audit
                        </p>

                        <h1
                            class="mt-2 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                        >
                            Here’s what stood out.
                        </h1>

                        <p
                            class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base"
                        >
                            {{ $executiveHeadline }}
                        </p>

                        <p
                            class="mx-auto mt-3 max-w-3xl text-sm leading-7 text-slate-500"
                        >
                            {{ $executiveSummary }}
                        </p>

                        @if ($overallScore !== null)

                            <div
                                class="mt-6 inline-flex flex-wrap items-center justify-center gap-3 rounded-full border border-slate-800 bg-slate-950 px-4 py-2"
                            >
                                <span class="text-sm text-slate-500">
                                    Advisor score
                                </span>

                                <span class="font-semibold text-white">
                                    {{ $overallScore }} / 100
                                </span>

                                <span class="text-sm text-blue-300">
                                    {{ $overallLabel }}
                                </span>
                            </div>

                        @endif
                    </div>

                    <div class="mt-10 grid gap-4 lg:grid-cols-3">

                        <article
                            x-show="showConcern"
                            x-transition.opacity.duration.600ms
                            class="rounded-3xl border border-red-500/20 bg-red-500/[0.06] p-6"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-500/10 text-red-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M10.3 3.7 2.6 17a2 2 0 0 0 1.73 3h15.34A2 2 0 0 0 21.4 17L13.7 3.7a2 2 0 0 0-3.4 0Z"
                                    />
                                </svg>
                            </div>

                            <p
                                class="mt-5 text-xs font-semibold uppercase tracking-[0.16em] text-red-300"
                            >
                                Top concern
                            </p>

                            @if ($topConcern)

                                <h2 class="mt-2 text-xl font-semibold text-white">
                                    {{ data_get(
                                        $topConcern,
                                        'title',
                                        'Portfolio concern'
                                    ) }}
                                </h2>

                                <p class="mt-3 text-sm leading-7 text-slate-400">
                                    {{ data_get(
                                        $topConcern,
                                        'message',
                                        'This area deserves closer review.'
                                    ) }}
                                </p>

                            @else

                                <h2 class="mt-2 text-xl font-semibold text-white">
                                    No major concern detected
                                </h2>

                                <p class="mt-3 text-sm leading-7 text-slate-400">
                                    Helmio did not identify a critical or
                                    important concern from the data currently available.
                                </p>

                            @endif
                        </article>

                        <article
                            x-show="showOpportunity"
                            x-transition.opacity.duration.600ms
                            class="rounded-3xl border border-amber-500/20 bg-amber-500/[0.06] p-6"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-300"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 3v18m9-9H3"
                                    />
                                </svg>
                            </div>

                            <p
                                class="mt-5 text-xs font-semibold uppercase tracking-[0.16em] text-amber-300"
                            >
                                Best opportunity
                            </p>

                            @if ($topOpportunity)

                                <h2 class="mt-2 text-xl font-semibold text-white">
                                    {{ data_get(
                                        $topOpportunity,
                                        'title',
                                        'Portfolio opportunity'
                                    ) }}
                                </h2>

                                <p class="mt-3 text-sm leading-7 text-slate-400">
                                    {{ data_get(
                                        $topOpportunity,
                                        'message',
                                        'This may be worth discussing with your advisor.'
                                    ) }}
                                </p>

                            @else

                                <h2 class="mt-2 text-xl font-semibold text-white">
                                    No major opportunity yet
                                </h2>

                                <p class="mt-3 text-sm leading-7 text-slate-400">
                                    More complete data may reveal additional
                                    savings, tax, or allocation opportunities.
                                </p>

                            @endif
                        </article>

                        <article
                            x-show="showStrength"
                            x-transition.opacity.duration.600ms
                            class="rounded-3xl border border-emerald-500/20 bg-emerald-500/[0.06] p-6"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300"
                            >
                                <svg
                                    class="h-6 w-6"
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
                                class="mt-5 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-300"
                            >
                                Strongest area
                            </p>

                            @if ($strongestCategory)

                                <div class="mt-2 flex flex-wrap items-end gap-3">
                                    <h2
                                        class="text-xl font-semibold text-white"
                                    >
                                        {{ data_get(
                                            $strongestCategory,
                                            'label',
                                            'Portfolio strength'
                                        ) }}
                                    </h2>

                                    <span
                                        class="pb-0.5 text-sm font-semibold text-emerald-300"
                                    >
                                        {{ data_get(
                                            $strongestCategory,
                                            'score',
                                            0
                                        ) }} / 100
                                    </span>
                                </div>

                                <p class="mt-3 text-sm leading-7 text-slate-400">
                                    {{ data_get(
                                        $strongestCategory,
                                        'reason',
                                        data_get(
                                            $strongestCategory,
                                            'category_label',
                                            'This category appears strong based on the available data.'
                                        )
                                    ) }}
                                </p>

                            @else

                                <h2 class="mt-2 text-xl font-semibold text-white">
                                    Still building
                                </h2>

                                <p class="mt-3 text-sm leading-7 text-slate-400">
                                    Helmio needs more complete category data
                                    before identifying the portfolio’s strongest area.
                                </p>

                            @endif
                        </article>

                    </div>

                    <div
                        x-show="showNext"
                        x-transition.opacity.duration.700ms
                        class="mt-10 rounded-2xl border border-blue-500/20 bg-blue-500/[0.06] p-5"
                    >
                        <div
                            class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                                >
                                    Next
                                </p>

                                <h2
                                    class="mt-2 text-xl font-semibold text-white"
                                >
                                    Get the plain-English explanation.
                                </h2>

                                <p
                                    class="mt-2 max-w-2xl text-sm leading-6 text-slate-400"
                                >
                                    Helmio will turn this analysis into a
                                    concise executive summary and explain
                                    where to focus first.
                                </p>
                            </div>

                            <a
                                href="{{ route('onboarding.executive-summary') }}"
                                class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
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
                    </div>

                </div>
            </section>

            <p class="mt-5 text-center text-xs text-slate-600">
                Findings are informational and should be reviewed alongside
                your advisor, tax professional, or legal counsel where appropriate.
            </p>

        </div>
    </div>
</x-app-layout>