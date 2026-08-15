<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-blue-400">Cost intelligence</p>
                <h2 class="mt-1 text-2xl font-semibold text-white">Cost & Value Analysis</h2>
                <p class="mt-2 max-w-3xl text-sm text-slate-400">
                    Compare what your portfolio costs with what it delivered relative to the selected benchmark.
                </p>
            </div>

            <a
                href="{{ route('analytics.performance') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-200 shadow-lg transition hover:border-slate-600 hover:bg-slate-800"
            >
                View Performance →
            </a>
        </div>
    </x-slot>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @php
        $comparisonReady = data_get($costPerformance, 'status') === 'complete';

        $usingRecommendedBenchmark =
            (bool) ($usingRecommendedBenchmark ?? false);

        $recommendedBenchmarkName =
            data_get(
                $benchmarkRecommendation ?? [],
                'name'
            );

        $recommendedBenchmarkSymbol =
            data_get(
                $benchmarkRecommendation ?? [],
                'symbol'
            );

        $recommendedBenchmarkReason =
            data_get(
                $benchmarkRecommendation ?? [],
                'reason'
            );

        $recommendedProfile =
            data_get(
                $benchmarkRecommendation ?? [],
                'profile',
                []
            );

        $recommendedRiskTolerance =
            data_get(
                $recommendedProfile,
                'risk_tolerance'
            );

        $recommendedObjective =
            data_get(
                $recommendedProfile,
                'primary_objective'
            );

        $recommendedTimeHorizon =
            data_get(
                $recommendedProfile,
                'time_horizon_years'
            );

        $profileLabel = static function (
            ?string $value
        ): ?string {
            if ($value === null || $value === '') {
                return null;
            }

            return str($value)
                ->replace('_', ' ')
                ->title()
                ->toString();
        };

        $portfolioValue = (float) data_get(
            $costPerformance,
            'portfolio.value',
            $analytics['portfolio_value'] ?? 0
        );

        $portfolioReturn = data_get($costPerformance, 'portfolio.return');
        $portfolioAnnualizedReturn = data_get($costPerformance, 'portfolio.annualized_return');

        $portfolioAnnualCost = data_get(
            $costPerformance,
            'portfolio.annual_cost',
            $analytics['total_annual_cost'] ?? null
        );

        $portfolioCostRate = data_get(
            $costPerformance,
            'portfolio.cost_rate',
            $analytics['all_in_cost_rate'] ?? null
        );

        $benchmarkName = data_get(
            $costPerformance,
            'benchmark.name',
            $benchmark?->name ?? 'Benchmark'
        );

        $benchmarkSymbol = data_get(
            $costPerformance,
            'benchmark.symbol',
            $benchmark?->symbol
        );

        $benchmarkReturn = data_get($costPerformance, 'benchmark.return');
        $benchmarkAnnualizedReturn = data_get($costPerformance, 'benchmark.annualized_return');
        $benchmarkExpenseRatio = data_get($costPerformance, 'benchmark.expense_ratio');
        $benchmarkAnnualCost = data_get($costPerformance, 'benchmark.estimated_annual_cost');

        $relativeReturn = data_get($costPerformance, 'comparison.relative_return');
        $incrementalCostRate = data_get($costPerformance, 'comparison.incremental_cost_rate');
        $incrementalAnnualCost = data_get($costPerformance, 'comparison.incremental_annual_cost');
        $performanceValueGap = data_get($costPerformance, 'comparison.performance_value_gap');

        $assessmentStatus = data_get(
            $costPerformance,
            'assessment.status',
            'insufficient_data'
        );

        $assessmentLabel = data_get(
            $costPerformance,
            'assessment.label',
            'More data needed'
        );

        $assessmentMessage = data_get(
            $costPerformance,
            'assessment.message',
            data_get(
                $costPerformance,
                'message',
                'More data is needed to complete this comparison.'
            )
        );

        $assessmentClasses = match ($assessmentStatus) {
            'strong_value',
            'cost_justified_by_return' =>
                'border-emerald-500/25 bg-emerald-500/[0.06] text-emerald-200',

            'mixed_value' =>
                'border-amber-500/25 bg-amber-500/[0.06]0/[0.06] text-amber-200',

            'value_gap',
            'underperformance' =>
                'border-red-500/30 bg-red-500/[0.06] text-red-200',

            default =>
                'border-slate-800 bg-slate-950/50 text-slate-300',
        };

        $assessmentBadgeClasses = match ($assessmentStatus) {
            'strong_value',
            'cost_justified_by_return' =>
                'border-emerald-500/25 bg-emerald-500/10 text-emerald-300',

            'mixed_value' =>
                'border-amber-500/25 bg-amber-500/[0.06]0/10 text-amber-300',

            'value_gap',
            'underperformance' =>
                'border-red-500/30 bg-red-500/10 text-red-300',

            default =>
                'border-slate-700 bg-slate-800 text-slate-300',
        };

        $feeRows = [
            ['label' => 'Advisory fees', 'amount' => (float) ($analytics['advisory_fees'] ?? 0)],
            ['label' => 'Fund expenses', 'amount' => (float) ($analytics['fund_expenses'] ?? 0)],
            ['label' => 'Trading costs', 'amount' => (float) ($analytics['transaction_fees'] ?? 0)],
            ['label' => 'Account fees', 'amount' => (float) ($analytics['account_fees'] ?? 0)],
        ];

        $largestFee = max(
            1,
            ...array_map(fn ($row) => $row['amount'], $feeRows)
        );

        $totalAnnualCost = (float) ($analytics['total_annual_cost'] ?? 0);

        $primaryCostDriver = collect($feeRows)
            ->sortByDesc('amount')
            ->first();

        $formatPercent = function (
            $value,
            int $decimals = 2,
            bool $signed = false
        ): string {
            if ($value === null || ! is_numeric($value)) {
                return '—';
            }

            $percent = (float) $value * 100;
            $prefix = $signed && $percent > 0 ? '+' : '';

            return $prefix
                . number_format($percent, $decimals)
                . '%';
        };

        $returnClass = function ($value): string {
            if ($value === null || ! is_numeric($value)) {
                return 'text-slate-400';
            }

            return (float) $value >= 0
                ? 'text-emerald-300'
                : 'text-red-300';
        };
    @endphp

    <div class="bg-slate-950 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">

            {{-- Filters --}}
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                <form
                    method="GET"
                    action="{{ route('analytics.costs') }}"
                    x-data="{ computing: false }"
                    x-on:submit="computing = true"
                    class="grid gap-4 lg:grid-cols-[1fr_1fr_1.2fr_auto]"
                >
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Start date
                        </span>

                        <input
                            type="date"
                            name="start_date"
                            value="{{ $startDate }}"
                            class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-sm text-slate-200 shadow-lg focus:border-blue-500 focus:ring-blue-500"
                        >
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            End date
                        </span>

                        <input
                            type="date"
                            name="end_date"
                            value="{{ $endDate }}"
                            class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-sm text-slate-200 shadow-lg focus:border-blue-500 focus:ring-blue-500"
                        >
                    </label>

                    <label class="block">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Benchmark
                            </span>

                            @if ($usingRecommendedBenchmark)
                                <span class="rounded-full border border-blue-500/25 bg-blue-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-300">
                                    Helmio recommended
                                </span>
                            @endif
                        </div>

                        <select
                            name="benchmark_id"
                            class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-sm text-slate-200 shadow-lg focus:border-blue-500 focus:ring-blue-500"
                        >
                            @foreach ($benchmarks as $item)
                                <option
                                    value="{{ $item->id }}"
                                    @selected(
                                        (int) ($benchmark?->id ?? 0)
                                        === (int) $item->id
                                    )
                                >
                                    {{ $item->name }}
                                    @if ($item->symbol)
                                        ({{ $item->symbol }})
                                    @endif
                                    @if (
                                        $recommendedBenchmarkSymbol
                                        && $item->symbol === $recommendedBenchmarkSymbol
                                    )
                                        — Recommended
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="flex items-end">
                        <button
                            type="submit"
                            x-bind:disabled="computing"
                            x-bind:class="
                                computing
                                    ? 'cursor-not-allowed border border-blue-900 bg-blue-950/70 text-blue-300 opacity-70'
                                    : 'border border-blue-500/40 bg-blue-600 text-white hover:bg-blue-500'
                            "
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold shadow-lg transition duration-200 lg:w-auto"
                        >
                            <span x-show="!computing" x-cloak>
                                Compare
                            </span>

                            <span
                                x-show="computing"
                                x-cloak
                                class="inline-flex items-center gap-2"
                            >
                                <svg
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
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
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    ></path>
                                </svg>

                                Computing…
                            </span>
                        </button>
                    </div>
                </form>
            </section>

            @if (
                $recommendedBenchmarkName
                && $recommendedBenchmarkSymbol
            )
                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-blue-500/25 bg-blue-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-blue-300">
                                    Helmio recommended benchmark
                                </span>

                                @if (! $usingRecommendedBenchmark)
                                    <span class="rounded-full border border-slate-700 bg-slate-800 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                        Viewing alternate comparison
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                <h3 class="text-lg font-semibold text-white">
                                    {{ $recommendedBenchmarkName }}
                                </h3>

                                <span class="text-sm font-medium text-blue-300">
                                    {{ $recommendedBenchmarkSymbol }}
                                </span>
                            </div>

                            @if ($recommendedBenchmarkReason)
                                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                                    {{ $recommendedBenchmarkReason }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            @if ($recommendedRiskTolerance)
                                <span class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-400">
                                    Risk:
                                    <strong class="font-semibold text-slate-200">
                                        {{ $profileLabel($recommendedRiskTolerance) }}
                                    </strong>
                                </span>
                            @endif

                            @if ($recommendedObjective)
                                <span class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-400">
                                    Objective:
                                    <strong class="font-semibold text-slate-200">
                                        {{ $profileLabel($recommendedObjective) }}
                                    </strong>
                                </span>
                            @endif

                            @if ($recommendedTimeHorizon)
                                <span class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-400">
                                    Horizon:
                                    <strong class="font-semibold text-slate-200">
                                        {{ $recommendedTimeHorizon }} years
                                    </strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            {{-- Data warnings --}}
            @if (count($analytics['data_warnings'] ?? []) > 0)
                <section class="rounded-2xl border border-amber-500/25 bg-amber-500/[0.06] p-5">
                    <h3 class="font-semibold text-amber-200">
                        Data-quality notice
                    </h3>

                    <ul class="mt-3 space-y-2 text-sm text-amber-300">
                        @foreach ($analytics['data_warnings'] as $warning)
                            <li>• {{ $warning }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Executive value summary --}}
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                <div class="border-b border-slate-800 px-6 py-6 sm:px-8">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                Cost-adjusted performance
                            </p>

                            <h3 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                                What did you pay — and what did you get?
                            </h3>

                            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                                Helmio compares observed portfolio performance with the selected benchmark while showing the cost difference alongside it.
                            </p>
                        </div>

                        <span class="inline-flex self-start rounded-full border px-3 py-1.5 text-xs font-semibold {{ $assessmentBadgeClasses }}">
                            {{ $assessmentLabel }}
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    @if ($comparisonReady)
                        <div class="grid overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 lg:grid-cols-[1.15fr_1fr_1fr]">
                            <div class="bg-slate-950 p-6 text-white sm:p-7">
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                    Comparison result
                                </p>

                                <p class="mt-4 text-lg font-semibold">
                                    {{ $assessmentLabel }}
                                </p>

                                <p class="mt-3 text-sm leading-6 text-slate-300">
                                    {{ $assessmentMessage }}
                                </p>

                                @if ($performanceValueGap !== null)
                                    <div class="mt-6 rounded-xl border border-slate-700 bg-slate-900 p-4">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                            Benchmark performance gap
                                        </p>

                                        <p class="mt-2 text-3xl font-semibold tracking-tight text-white">
                                            ${{ number_format(
                                                abs((float) $performanceValueGap),
                                                0
                                            ) }}
                                        </p>

                                        <p class="mt-2 text-xs leading-5 text-slate-400">
                                            Estimated difference in portfolio value attributable to performance relative to the selected benchmark over this period.
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="border-b border-slate-800 bg-slate-900/60 p-6 lg:border-b-0 lg:border-r">
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                    Your portfolio
                                </p>

                                <div class="mt-5 space-y-5">
                                    <div>
                                        <p class="text-xs text-slate-400">Period return</p>

                                        <p class="mt-1 text-3xl font-semibold tracking-tight {{ $returnClass($portfolioReturn) }}">
                                            {{ $formatPercent($portfolioReturn, 2, true) }}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 border-t border-slate-800 pt-5">
                                        <div>
                                            <p class="text-xs text-slate-400">Annual cost</p>

                                            <p class="mt-1 text-lg font-semibold text-white">
                                                @if ($portfolioAnnualCost !== null)
                                                    ${{ number_format((float) $portfolioAnnualCost, 0) }}
                                                @else
                                                    —
                                                @endif
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-400">Cost rate</p>

                                            <p class="mt-1 text-lg font-semibold text-white">
                                                {{ $formatPercent($portfolioCostRate) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-400">Annualized return</p>

                                        <p class="mt-1 text-sm font-semibold {{ $returnClass($portfolioAnnualizedReturn) }}">
                                            {{ $formatPercent($portfolioAnnualizedReturn, 2, true) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-900/40 p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                    {{ $benchmarkName }}
                                    @if ($benchmarkSymbol)
                                        · {{ $benchmarkSymbol }}
                                    @endif
                                </p>

                                <div class="mt-5 space-y-5">
                                    <div>
                                        <p class="text-xs text-slate-400">Period return</p>

                                        <p class="mt-1 text-3xl font-semibold tracking-tight {{ $returnClass($benchmarkReturn) }}">
                                            {{ $formatPercent($benchmarkReturn, 2, true) }}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 border-t border-slate-800 pt-5">
                                        <div>
                                            <p class="text-xs text-slate-400">Estimated annual cost</p>

                                            <p class="mt-1 text-lg font-semibold text-white">
                                                @if ($benchmarkAnnualCost !== null)
                                                    ${{ number_format((float) $benchmarkAnnualCost, 0) }}
                                                @else
                                                    —
                                                @endif
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-slate-400">Expense ratio</p>

                                            <p class="mt-1 text-lg font-semibold text-white">
                                                {{ $formatPercent($benchmarkExpenseRatio) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-xs text-slate-400">Annualized return</p>

                                        <p class="mt-1 text-sm font-semibold {{ $returnClass($benchmarkAnnualizedReturn) }}">
                                            {{ $formatPercent($benchmarkAnnualizedReturn, 2, true) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <article class="rounded-2xl border border-slate-800 bg-slate-950/50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Relative performance
                                </p>

                                <p class="mt-2 text-2xl font-semibold {{ $returnClass($relativeReturn) }}">
                                    {{ $formatPercent($relativeReturn, 2, true) }}
                                </p>

                                <p class="mt-2 text-xs leading-5 text-slate-400">
                                    Portfolio return minus benchmark return.
                                </p>
                            </article>

                            <article class="rounded-2xl border border-slate-800 bg-slate-950/50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Additional annual cost
                                </p>

                                <p class="mt-2 text-2xl font-semibold text-white">
                                    @if ($incrementalAnnualCost !== null)
                                        ${{ number_format((float) $incrementalAnnualCost, 0) }}
                                    @else
                                        —
                                    @endif
                                </p>

                                <p class="mt-2 text-xs leading-5 text-slate-400">
                                    Portfolio annual cost minus estimated benchmark cost.
                                </p>
                            </article>

                            <article class="rounded-2xl border border-slate-800 bg-slate-950/50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Additional cost rate
                                </p>

                                <p class="mt-2 text-2xl font-semibold text-white">
                                    {{ $formatPercent($incrementalCostRate) }}
                                </p>

                                <p class="mt-2 text-xs leading-5 text-slate-400">
                                    Difference between portfolio and benchmark cost rates.
                                </p>
                            </article>

                            <article class="rounded-2xl border border-slate-800 bg-slate-950/50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Portfolio value
                                </p>

                                <p class="mt-2 text-2xl font-semibold text-white">
                                    ${{ number_format($portfolioValue, 0) }}
                                </p>

                                <p class="mt-2 text-xs leading-5 text-slate-400">
                                    Value used for annual cost context.
                                </p>
                            </article>
                        </div>
                    @else
                        <div class="rounded-2xl border p-6 {{ $assessmentClasses }}">
                            <p class="font-semibold">{{ $assessmentLabel }}</p>
                            <p class="mt-2 text-sm leading-6">{{ $assessmentMessage }}</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Cost intelligence --}}
            <section class="grid gap-6 lg:grid-cols-[1.25fr_.75fr]">
                <article class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-lg sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                Cost breakdown
                            </p>

                            <h3 class="mt-2 text-xl font-semibold text-white">
                                Where your annual cost comes from
                            </h3>
                        </div>

                        <div class="sm:text-right">
                            <p class="text-xs text-slate-400">Estimated annual total</p>

                            <p class="mt-1 text-2xl font-semibold text-white">
                                ${{ number_format($totalAnnualCost, 0) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-5">
                        @foreach ($feeRows as $fee)
                            @php
                                $share = $totalAnnualCost > 0
                                    ? ($fee['amount'] / $totalAnnualCost) * 100
                                    : 0;

                                $barWidth = $largestFee > 0
                                    ? ($fee['amount'] / $largestFee) * 100
                                    : 0;
                            @endphp

                            <div>
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-300">
                                            {{ $fee['label'] }}
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-400">
                                            {{ number_format($share, 1) }}% of identified cost
                                        </p>
                                    </div>

                                    <p class="text-sm font-semibold text-white">
                                        ${{ number_format($fee['amount'], 2) }}
                                    </p>
                                </div>

                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-blue-200 via-blue-500 to-blue-800"
                                        style="width: {{ min(100, max(0, $barWidth)) }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-800 bg-slate-950 p-6 text-white shadow-lg sm:p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-300">
                        What Helmio sees
                    </p>

                    <h3 class="mt-3 text-xl font-semibold">
                        Cost intelligence summary
                    </h3>

                    <div class="mt-6 space-y-5">
                        <div>
                            <p class="text-xs text-slate-400">Largest cost driver</p>

                            <p class="mt-1 text-lg font-semibold text-white">
                                {{ $primaryCostDriver['label'] ?? '—' }}
                            </p>

                            @if (($primaryCostDriver['amount'] ?? null) !== null)
                                <p class="mt-1 text-sm text-slate-400">
                                    ${{ number_format((float) $primaryCostDriver['amount'], 0) }}
                                    per year
                                </p>
                            @endif
                        </div>

                        <div class="border-t border-slate-800 pt-5">
                            <p class="text-xs text-slate-400">All-in annual cost rate</p>

                            <p class="mt-1 text-3xl font-semibold text-white">
                                @if (($analytics['all_in_cost_rate'] ?? null) !== null)
                                    {{ number_format(
                                        (float) $analytics['all_in_cost_rate'] * 100,
                                        2
                                    ) }}%
                                @else
                                    —
                                @endif
                            </p>
                        </div>

                        @if ($comparisonReady)
                            <div class="border-t border-slate-800 pt-5">
                                <p class="text-xs text-slate-400">Benchmark relationship</p>

                                <p class="mt-2 text-sm leading-6 text-slate-300">
                                    @if (
                                        $relativeReturn !== null
                                        && $relativeReturn < 0
                                        && $incrementalAnnualCost !== null
                                        && $incrementalAnnualCost > 0
                                    )
                                        The portfolio cost more than the selected benchmark implementation and also trailed it during this period.
                                    @elseif (
                                        $relativeReturn !== null
                                        && $relativeReturn >= 0
                                    )
                                        The portfolio outperformed the selected benchmark during this period. Review whether that excess return was sufficient relative to the additional cost.
                                    @else
                                        Helmio has calculated the cost comparison, but additional context may be needed to evaluate value.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </article>
            </section>

            {{-- Costs by account --}}
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                <div class="border-b border-slate-800 px-6 py-5">
                    <h3 class="font-semibold text-white">Costs by account</h3>

                    <p class="mt-1 text-sm text-slate-400">
                        Estimated annualized account costs as of
                        {{ $analytics['as_of'] ?? $endDate }}.
                    </p>
                </div>

                @if (($analytics['accounts'] ?? collect())->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-semibold text-white">No accounts available</p>

                        <p class="mt-2 text-sm text-slate-400">
                            Add an investment account to begin cost analysis.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead class="bg-slate-950">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Account
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Advisory
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Fund costs
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Trading
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Total
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Cost rate
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-800">
                                @foreach ($analytics['accounts'] as $account)
                                    <tr class="transition hover:bg-slate-950/50">
                                        <td class="px-6 py-5">
                                            <p class="font-semibold text-white">
                                                {{ $account['account_name'] }}
                                            </p>

                                            <p class="mt-1 text-sm text-slate-400">
                                                {{ $account['institution_name'] }}
                                            </p>
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-slate-300">
                                            ${{ number_format($account['advisory_fee'], 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-slate-300">
                                            ${{ number_format($account['fund_expense_cost'], 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right text-sm text-slate-300">
                                            ${{ number_format($account['transaction_fees'], 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right font-semibold text-white">
                                            ${{ number_format($account['total_cost'], 2) }}
                                        </td>

                                        <td class="px-6 py-5 text-right font-semibold text-white">
                                            @if ($account['cost_rate'] !== null)
                                                {{ number_format($account['cost_rate'] * 100, 2) }}%
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Methodology --}}
            <section
                x-data="{ open: false }"
                class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
            >
                <button
                    type="button"
                    x-on:click="open = !open"
                    class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
                >
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Methodology
                        </p>

                        <h3 class="mt-1 font-semibold text-white">
                            How Helmio calculated this
                        </h3>
                    </div>

                    <svg
                        class="h-5 w-5 text-slate-400 transition"
                        x-bind:class="{ 'rotate-180': open }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m6 9 6 6 6-6"
                        />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-collapse
                    class="border-t border-slate-800"
                >
                    <div class="grid gap-8 p-6 lg:grid-cols-2">
                        <div>
                            <h4 class="font-semibold text-white">
                                Annual portfolio cost
                            </h4>

                            <div class="mt-4 space-y-3 text-sm">
                                @foreach ($feeRows as $fee)
                                    <div class="flex justify-between gap-4">
                                        <span class="text-slate-400">
                                            {{ $fee['label'] }}
                                        </span>

                                        <span class="font-medium text-slate-200">
                                            ${{ number_format($fee['amount'], 2) }}
                                        </span>
                                    </div>
                                @endforeach

                                <div class="flex justify-between gap-4 border-t border-slate-800 pt-3">
                                    <span class="font-semibold text-white">
                                        Estimated annual total
                                    </span>

                                    <span class="font-semibold text-white">
                                        ${{ number_format($totalAnnualCost, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold text-white">
                                Important comparison notes
                            </h4>

                            <div class="mt-4 space-y-4 text-sm leading-6 text-slate-400">
                                <p>
                                    Portfolio performance uses Helmio's time-weighted return engine. Investor cash deposits and withdrawals are separated from investment return.
                                </p>

                                <p>
                                    Observed fund and ETF returns already reflect embedded operating expenses in their market prices or NAV history. Helmio displays those expense ratios as costs but does not subtract them a second time from observed return.
                                </p>

                                <p>
                                    Benchmark implementation cost is available only when an expense ratio has been stored for that benchmark.
                                </p>

                                <p>
                                    The performance opportunity-cost figure is Helmio's existing benchmark-relative performance calculation. It should not be interpreted as a guarantee, damages estimate, or forecast.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-800 px-6 py-4 text-xs text-slate-400">
                        Cost formula:
                        {{ $analytics['formula_version'] ?? '—' }}
                        · Cost/value formula:
                        {{ data_get($costPerformance, 'formula_version', '—') }}
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>