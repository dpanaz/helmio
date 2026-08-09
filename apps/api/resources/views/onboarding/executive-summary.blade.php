<x-app-layout>
    <x-slot name="header">
        <div>
            <p
                class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
            >
                Portfolio intelligence
            </p>

            <h2
                class="mt-2 text-2xl font-semibold tracking-tight text-white"
            >
                Executive Summary
            </h2>
        </div>
    </x-slot>

    @php
        $status =
            data_get(
                $insight,
                'status'
            );

        $isCompleted =
            $status ===
            \App\Models\AiInsightRun::STATUS_COMPLETED;

        $isBlocked =
            $status ===
            \App\Models\AiInsightRun::STATUS_BLOCKED;

        $isFailed =
            $status ===
            \App\Models\AiInsightRun::STATUS_FAILED;

        $headline =
            data_get(
                $insight,
                'headline',
                'Your portfolio summary is ready.'
            );

        $summary =
            data_get(
                $insight,
                'summary',
                'Helmio reviewed your portfolio and prepared a concise executive summary.'
            );

        $priorities =
            collect(
                data_get(
                    $insight,
                    'priorities',
                    []
                )
            );

        $positiveChanges =
            collect(
                data_get(
                    $insight,
                    'positive_changes',
                    []
                )
            );

        $limitations =
            collect(
                data_get(
                    $insight,
                    'limitations',
                    []
                )
            );

        $portfolioValue =
            data_get(
                $insight,
                'portfolio_value_at_generation'
            );

        $accountCount =
            data_get(
                $insight,
                'account_count_at_generation'
            );
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            <section
                x-data="{
                    showHeader: false,
                    showSummary: false,
                    showSections: false,
                    showNext: false,

                    typedSummary: '',
                    summaryText: @js($summary),

                    typeSummary() {
                        let index = 0;

                        const tick = () => {
                            if (
                                index >=
                                this.summaryText.length
                            ) {
                                return;
                            }

                            this.typedSummary +=
                                this.summaryText.charAt(
                                    index++
                                );

                            setTimeout(
                                tick,
                                12
                            );
                        };

                        tick();
                    },

                    init() {
                        setTimeout(
                            () => this.showHeader = true,
                            250
                        );

                        setTimeout(() => {
                            this.showSummary = true;
                            this.typeSummary();
                        }, 700);

                        setTimeout(
                            () => this.showSections = true,
                            1800
                        );

                        setTimeout(
                            () => this.showNext = true,
                            2500
                        );
                    }
                }"
                class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl"
            >
                <div class="p-6 sm:p-8 lg:p-12">

                    <div
                        x-show="showHeader"
                        x-transition.opacity.duration.700ms
                        class="text-center"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-violet-500/20 bg-violet-500/10 text-violet-300"
                        >
                            <svg
                                class="h-8 w-8"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                                />
                            </svg>
                        </div>

                        <p
                            class="mt-5 text-sm font-semibold text-violet-300"
                        >
                            AI executive summary
                        </p>

                        <h1
                            class="mt-2 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                        >
                            {{ $headline }}
                        </h1>

                        @if (
                            $portfolioValue !== null
                            || $accountCount !== null
                        )
                            <div
                                class="mt-6 flex flex-wrap justify-center gap-2"
                            >
                                @if ($portfolioValue !== null)
                                    <span
                                        class="rounded-full border border-slate-800 bg-slate-950 px-4 py-2 text-sm text-slate-400"
                                    >
                                        Based on
                                        {{ money($portfolioValue) }}
                                    </span>
                                @endif

                                @if ($accountCount !== null)
                                    <span
                                        class="rounded-full border border-slate-800 bg-slate-950 px-4 py-2 text-sm text-slate-400"
                                    >
                                        {{ number_format(
                                            (int) $accountCount
                                        ) }}

                                        {{ Str::plural(
                                            'account',
                                            (int) $accountCount
                                        ) }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if ($isCompleted)

                        <div
                            x-show="showSummary"
                            x-transition.opacity.duration.700ms
                            class="mx-auto mt-10 max-w-3xl rounded-3xl border border-violet-500/20 bg-violet-500/[0.05] p-6 sm:p-8"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
                            >
                                What Helmio would tell you
                            </p>

                            <p
                                class="mt-4 whitespace-pre-line text-base leading-8 text-slate-200 sm:text-lg"
                                x-text="typedSummary"
                            ></p>
                        </div>

                        <div
                            x-show="showSections"
                            x-transition.opacity.duration.700ms
                            class="mt-8 grid gap-4 lg:grid-cols-2"
                        >
                            <section
                                class="rounded-3xl border border-amber-500/20 bg-amber-500/[0.06] p-6"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-300"
                                >
                                    Priority actions
                                </p>

                                <h2
                                    class="mt-2 text-xl font-semibold text-white"
                                >
                                    Where to focus first
                                </h2>

                                <div class="mt-5 space-y-3">

                                    @forelse ($priorities->take(3) as $priority)

                                        <div
                                            class="flex items-start gap-3 rounded-2xl border border-amber-500/10 bg-slate-950/60 p-4"
                                        >
                                            <div
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-xs font-bold text-amber-300"
                                            >
                                                {{ $loop->iteration }}
                                            </div>

                                            <p
                                                class="text-sm leading-6 text-slate-400"
                                            >
                                                {{ is_array($priority)
                                                    ? data_get(
                                                        $priority,
                                                        'message',
                                                        data_get(
                                                            $priority,
                                                            'title',
                                                            'Review this portfolio priority.'
                                                        )
                                                    )
                                                    : $priority }}
                                            </p>
                                        </div>

                                    @empty

                                        <p
                                            class="text-sm leading-6 text-slate-500"
                                        >
                                            No urgent priority actions were identified.
                                        </p>

                                    @endforelse
                                </div>
                            </section>

                            <section
                                class="rounded-3xl border border-emerald-500/20 bg-emerald-500/[0.06] p-6"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-300"
                                >
                                    Positive signals
                                </p>

                                <h2
                                    class="mt-2 text-xl font-semibold text-white"
                                >
                                    What looks encouraging
                                </h2>

                                <div class="mt-5 space-y-3">

                                    @forelse ($positiveChanges->take(3) as $positive)

                                        <div
                                            class="flex items-start gap-3 rounded-2xl border border-emerald-500/10 bg-slate-950/60 p-4"
                                        >
                                            <svg
                                                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-300"
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

                                            <p
                                                class="text-sm leading-6 text-slate-400"
                                            >
                                                {{ is_array($positive)
                                                    ? data_get(
                                                        $positive,
                                                        'message',
                                                        data_get(
                                                            $positive,
                                                            'title',
                                                            'Positive portfolio signal detected.'
                                                        )
                                                    )
                                                    : $positive }}
                                            </p>
                                        </div>

                                    @empty

                                        <p
                                            class="text-sm leading-6 text-slate-500"
                                        >
                                            Helmio will identify positive changes
                                            as more history becomes available.
                                        </p>

                                    @endforelse
                                </div>
                            </section>
                        </div>

                        @if ($limitations->isNotEmpty())

                            <section
                                x-show="showSections"
                                x-transition.opacity.duration.700ms
                                class="mt-4 rounded-3xl border border-slate-800 bg-slate-950/70 p-6"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"
                                >
                                    Keep in mind
                                </p>

                                <div
                                    class="mt-4 grid gap-3 sm:grid-cols-2"
                                >
                                    @foreach ($limitations->take(4) as $limitation)

                                        <div
                                            class="rounded-2xl border border-slate-800 bg-slate-900 p-4 text-sm leading-6 text-slate-500"
                                        >
                                            {{ is_array($limitation)
                                                ? data_get(
                                                    $limitation,
                                                    'message',
                                                    data_get(
                                                        $limitation,
                                                        'title',
                                                        'Some portfolio data may be incomplete.'
                                                    )
                                                )
                                                : $limitation }}
                                        </div>

                                    @endforeach
                                </div>
                            </section>

                        @endif

                    @elseif ($isBlocked)

                        <div
                            class="mx-auto mt-10 max-w-2xl rounded-3xl border border-amber-500/20 bg-amber-500/[0.06] p-8 text-center"
                        >
                            <p
                                class="text-xl font-semibold text-amber-300"
                            >
                                More account data is needed.
                            </p>

                            <p
                                class="mt-3 text-sm leading-7 text-slate-400"
                            >
                                {{ $summary }}
                            </p>
                        </div>

                    @elseif ($isFailed)

                        <div
                            class="mx-auto mt-10 max-w-2xl rounded-3xl border border-red-500/20 bg-red-500/[0.06] p-8 text-center"
                        >
                            <p
                                class="text-xl font-semibold text-red-300"
                            >
                                The AI summary could not be generated.
                            </p>

                            <p
                                class="mt-3 text-sm leading-7 text-slate-400"
                            >
                                Your portfolio analysis is still available,
                                and you can continue into Helmio.
                            </p>
                        </div>

                    @endif

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
                                    Analysis complete
                                </p>

                                <h2
                                    class="mt-2 text-xl font-semibold text-white"
                                >
                                    Your Helmio workspace is ready.
                                </h2>

                                <p
                                    class="mt-2 max-w-2xl text-sm leading-6 text-slate-400"
                                >
                                    Finish setup to enable ongoing monitoring
                                    and open your dashboard.
                                </p>
                            </div>

                            <a
                                href="{{ route('onboarding.complete') }}"
                                class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
                            >
                                Finish Setup

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
                AI-generated insights are informational and may be incomplete.
                Review important decisions with a qualified professional.
            </p>

        </div>
    </div>
</x-app-layout>