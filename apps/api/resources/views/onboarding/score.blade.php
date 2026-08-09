<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Portfolio analysis
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Your Helm Score
            </h2>
        </div>
    </x-slot>

    @php
        $overallScore =
            data_get(
                $helmScore,
                'overall_score'
            );

        $overallLabel =
            data_get(
                $helmScore,
                'overall_label',
                'Building your score'
            );

        $dataCompleteness =
            (float) data_get(
                $helmScore,
                'data_completeness',
                0
            );

        $categories = collect([
            'cost' => [
                'label' => 'Cost',
                'score' => data_get(
                    $helmScore,
                    'categories.cost.score'
                ),
            ],

            'diversification' => [
                'label' => 'Diversification',
                'score' => data_get(
                    $helmScore,
                    'categories.diversification.score'
                ),
            ],

            'performance' => [
                'label' => 'Performance',
                'score' => data_get(
                    $helmScore,
                    'categories.performance.score'
                ),
            ],

            'risk' => [
                'label' => 'Risk',
                'score' => data_get(
                    $helmScore,
                    'categories.risk.score'
                ),
            ],

            'trading' => [
                'label' => 'Trading',
                'score' => data_get(
                    $helmScore,
                    'categories.trading.score'
                ),
            ],

            'cash' => [
                'label' => 'Cash',
                'score' => data_get(
                    $helmScore,
                    'categories.cash.score'
                ),
            ],

            'tax' => [
                'label' => 'Tax',
                'score' => data_get(
                    $helmScore,
                    'categories.tax.score'
                ),
            ],
        ]);
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            <section
                x-data="{
                    reveal: false,
                    showLabel: false,
                    showCategories: false,
                    showNext: false,

                    displayedScore: 0,
                    targetScore: {{ $overallScore ?? 0 }},

                    animateScore() {
                        const duration = 1800;
                        const start =
                            performance.now();

                        const tick = (now) => {
                            const elapsed =
                                now - start;

                            const progress =
                                Math.min(
                                    elapsed / duration,
                                    1
                                );

                            const eased =
                                1 - Math.pow(
                                    1 - progress,
                                    3
                                );

                            this.displayedScore =
                                Math.round(
                                    this.targetScore * eased
                                );

                            if (progress < 1) {
                                requestAnimationFrame(
                                    tick
                                );
                            } else {
                                this.displayedScore =
                                    this.targetScore;
                            }
                        };

                        requestAnimationFrame(
                            tick
                        );
                    },

                    scoreColor(score) {
                        if (score >= 90) {
                            return 'text-emerald-300';
                        }

                        if (score >= 80) {
                            return 'text-green-300';
                        }

                        if (score >= 70) {
                            return 'text-blue-300';
                        }

                        if (score >= 60) {
                            return 'text-amber-300';
                        }

                        return 'text-orange-300';
                    },

                    init() {
                        window.setTimeout(() => {
                            this.reveal = true;
                            this.animateScore();
                        }, 300);

                        window.setTimeout(() => {
                            this.showLabel = true;
                        }, 1400);

                        window.setTimeout(() => {
                            this.showCategories = true;
                        }, 2000);

                        window.setTimeout(() => {
                            this.showNext = true;
                        }, 2700);
                    },
                }"
                class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl"
            >
                <div class="relative p-6 sm:p-8 lg:p-12">

                    <div class="text-center">

                        <p class="text-sm font-semibold text-blue-400">
                            Your portfolio health
                        </p>

                        <h1
                            class="mt-2 text-3xl font-semibold tracking-tight text-white sm:text-5xl"
                        >
                            Your Helm Score
                        </h1>

                        <p
                            class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-400 sm:text-base"
                        >
                            Helmio combines cost, diversification, performance,
                            risk, trading, cash, and tax analysis into one clear score.
                        </p>
                    </div>

                    @if ($overallScore !== null)

                        <div
                            x-show="reveal"
                            x-transition.opacity.duration.700ms
                            class="mt-10 text-center"
                        >
                            <div
                                class="relative mx-auto flex h-64 w-64 items-center justify-center rounded-full border border-slate-800 bg-slate-950 shadow-inner sm:h-72 sm:w-72"
                            >
                                <div
                                    class="absolute inset-5 rounded-full"
                                    style="
                                        background:
                                            conic-gradient(
                                                rgb(37 99 235)
                                                {{ min(
                                                    100,
                                                    max(
                                                        0,
                                                        $overallScore
                                                    )
                                                ) }}%,
                                                rgb(30 41 59)
                                                0
                                            );
                                    "
                                ></div>

                                <div
                                    class="absolute inset-9 rounded-full border border-slate-800 bg-slate-950"
                                ></div>

                                <div class="relative z-10">
                                    <p
                                        class="text-7xl font-semibold tracking-tight sm:text-8xl"
                                        x-bind:class="scoreColor(displayedScore)"
                                        x-text="displayedScore"
                                    ></p>

                                    <p class="mt-2 text-sm text-slate-500">
                                        out of 100
                                    </p>
                                </div>
                            </div>

                            <div
                                x-show="showLabel"
                                x-transition.opacity.duration.500ms
                                class="mt-6"
                            >
                                <p
                                    class="text-2xl font-semibold text-white"
                                >
                                    {{ $overallLabel }}
                                </p>

                                <p
                                    class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500"
                                >
                                    Your score reflects the categories Helmio
                                    could evaluate from the account data currently available.
                                </p>
                            </div>
                        </div>

                    @else

                        <div
                            class="mt-10 rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] p-6 text-center"
                        >
                            <p
                                class="text-xl font-semibold text-amber-300"
                            >
                                Your score is still being built.
                            </p>

                            <p
                                class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-400"
                            >
                                Helmio needs enough complete analytics categories
                                before calculating an overall score.
                            </p>
                        </div>

                    @endif

                    <div
                        x-show="showCategories"
                        x-transition.opacity.duration.700ms
                        class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        @foreach ($categories as $category)

                            @php
                                $categoryScore =
                                    data_get(
                                        $category,
                                        'score'
                                    );

                                $categoryClasses =
                                    match (true) {
                                        $categoryScore === null =>
                                            'text-slate-500',

                                        $categoryScore >= 90 =>
                                            'text-emerald-300',

                                        $categoryScore >= 80 =>
                                            'text-green-300',

                                        $categoryScore >= 70 =>
                                            'text-blue-300',

                                        $categoryScore >= 60 =>
                                            'text-amber-300',

                                        default =>
                                            'text-orange-300',
                                    };
                            @endphp

                            <div
                                class="rounded-2xl border border-slate-800 bg-slate-950 p-5"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                                >
                                    {{ $category['label'] }}
                                </p>

                                <div class="mt-3 flex items-end gap-2">
                                    <p
                                        class="text-3xl font-semibold {{ $categoryClasses }}"
                                    >
                                        {{ $categoryScore ?? '—' }}
                                    </p>

                                    @if ($categoryScore !== null)
                                        <span class="pb-1 text-xs text-slate-600">
                                            /100
                                        </span>
                                    @endif
                                </div>
                            </div>

                        @endforeach
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
                                    Analysis coverage
                                </p>

                                <p
                                    class="mt-2 text-xl font-semibold text-white"
                                >
                                    {{ number_format(
                                        $dataCompleteness * 100
                                    ) }}% complete
                                </p>

                                <p
                                    class="mt-2 max-w-xl text-sm leading-6 text-slate-400"
                                >
                                    Next, Helmio will show the most important
                                    findings that deserve your attention first.
                                </p>
                            </div>

                            <a
                                href="{{ route('onboarding.findings') }}"
                                class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
                            >
                                See Top Findings

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
                The Helm Score is an informational portfolio-monitoring metric,
                not investment advice or a guarantee of future performance.
            </p>

        </div>
    </div>
</x-app-layout>