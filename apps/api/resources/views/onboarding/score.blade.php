<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <p class="text-sm font-medium text-blue-600">
                Portfolio analysis
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                Your Helm Score
            </h2>
        </div>
    </x-slot>

    @php
        $overallScore = data_get(
            $helmScore,
            'overall_score'
        );

        $overallLabel = data_get(
            $helmScore,
            'overall_label',
            'Building your score'
        );

        $dataCompleteness = (float) data_get(
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

    <div class="py-8 sm:py-12">
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
                        const start = performance.now();

                        const tick = (now) => {
                            const elapsed = now - start;
                            const progress = Math.min(
                                elapsed / duration,
                                1
                            );

                            const eased =
                                1 - Math.pow(
                                    1 - progress,
                                    3
                                );

                            this.displayedScore = Math.round(
                                this.targetScore * eased
                            );

                            if (progress < 1) {
                                requestAnimationFrame(tick);
                            } else {
                                this.displayedScore =
                                    this.targetScore;
                            }
                        };

                        requestAnimationFrame(tick);
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
                class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-2xl"
            >
                <div class="relative overflow-hidden px-6 py-8 sm:px-10 sm:py-12 lg:px-14 lg:py-14">
                    <div class="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-28 -left-28 h-80 w-80 rounded-full bg-violet-500/10 blur-3xl"></div>

                    <div class="relative">
                        <div class="text-center">
                            <p class="text-sm font-semibold text-blue-300">
                                Your portfolio health
                            </p>

                            <h1 class="mt-2 text-3xl font-semibold tracking-tight sm:text-5xl">
                                Your Helm Score
                            </h1>

                            <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
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
                                <div class="relative mx-auto flex h-64 w-64 items-center justify-center rounded-full border border-white/10 bg-white/5 shadow-inner sm:h-72 sm:w-72">
                                    <div
                                        class="absolute inset-5 rounded-full"
                                        style="
                                            background:
                                                conic-gradient(
                                                    rgb(37 99 235)
                                                    {{ min(100, max(0, $overallScore)) }}%,
                                                    rgb(30 41 59)
                                                    0
                                                );
                                        "
                                    ></div>

                                    <div class="absolute inset-9 rounded-full bg-slate-950"></div>

                                    <div class="relative z-10">
                                        <p
                                            class="text-7xl font-bold tracking-tight sm:text-8xl"
                                            x-bind:class="scoreColor(displayedScore)"
                                            x-text="displayedScore"
                                        ></p>

                                        <p class="mt-2 text-sm text-slate-400">
                                            out of 100
                                        </p>
                                    </div>
                                </div>

                                <div
                                    x-show="showLabel"
                                    x-transition.opacity.duration.500ms
                                    class="mt-6"
                                >
                                    <p class="text-2xl font-semibold text-white">
                                        {{ $overallLabel }}
                                    </p>

                                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-400">
                                        Your score reflects the categories Helmio could
                                        evaluate from the account data currently available.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="mt-10 rounded-2xl border border-amber-400/20 bg-amber-400/10 p-6 text-center">
                                <p class="text-xl font-semibold text-amber-200">
                                    Your score is still being built.
                                </p>

                                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-amber-100/70">
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
                                    $categoryScore = data_get(
                                        $category,
                                        'score'
                                    );

                                    $categoryClasses = match (true) {
                                        $categoryScore === null =>
                                            'text-slate-400',

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

                                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        {{ $category['label'] }}
                                    </p>

                                    <div class="mt-3 flex items-end gap-2">
                                        <p class="text-3xl font-bold {{ $categoryClasses }}">
                                            {{ $categoryScore ?? '—' }}
                                        </p>

                                        @if ($categoryScore !== null)
                                            <span class="pb-1 text-xs text-slate-500">
                                                / 100
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div
                            x-show="showNext"
                            x-transition.opacity.duration.700ms
                            class="mt-10 rounded-2xl border border-blue-400/20 bg-blue-500/10 p-5"
                        >
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-300">
                                        Analysis coverage
                                    </p>

                                    <p class="mt-2 text-xl font-semibold text-white">
                                        {{ number_format(
                                            $dataCompleteness * 100
                                        ) }}% complete
                                    </p>

                                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-300">
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
                </div>
            </section>

            <p class="mt-5 text-center text-xs text-slate-500">
                The Helm Score is an informational portfolio-monitoring metric,
                not investment advice or a guarantee of future performance.
            </p>
        </div>
    </div>
</x-app-layout>