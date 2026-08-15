<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Portfolio construction
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Diversification Analysis
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Review concentration across securities, sectors, and asset classes.
            </p>
        </div>
    </x-slot>

    @php
        $summary = $analytics['summary'] ?? [];
        $findings = collect($analytics['findings'] ?? []);
        $actions = collect($analytics['actions'] ?? []);
        $warnings = collect($analytics['warnings'] ?? []);

        $score = $analytics['score'] ?? null;
        $scoreLabel = $analytics['label'] ?? 'Insufficient data';

        $severityClasses = static function (?string $severity): string {
            return match ($severity) {
                'critical' =>
                    'border-red-500/30 bg-red-500/10 text-red-300',

                'high' =>
                    'border-orange-500/30 bg-orange-500/10 text-orange-300',

                'moderate' =>
                    'border-amber-500/30 bg-amber-500/10 text-amber-300',

                default =>
                    'border-slate-700 bg-slate-800 text-slate-300',
            };
        };

        $severityDotClasses = static function (?string $severity): string {
            return match ($severity) {
                'critical' => 'bg-red-400',
                'high' => 'bg-orange-400',
                'moderate' => 'bg-amber-400',
                default => 'bg-blue-400',
            };
        };

        $severityTextClasses = static function (?string $severity): string {
            return match ($severity) {
                'critical' => 'text-red-300',
                'high' => 'text-orange-300',
                'moderate' => 'text-amber-300',
                default => 'text-blue-300',
            };
        };

        $lookThroughMetrics =
            data_get(
                $lookThrough ?? [],
                'metrics',
                []
            );

        $effectiveExposures =
            collect(
                data_get(
                    $lookThrough ?? [],
                    'effective_exposures',
                    []
                )
            );

        $fundPairs =
            collect(
                data_get(
                    $lookThrough ?? [],
                    'fund_pairs',
                    []
                )
            );

        $lookThroughFunds =
            collect(
                data_get(
                    $lookThrough ?? [],
                    'funds',
                    []
                )
            );

        $lookThroughWarnings =
            collect(
                data_get(
                    $lookThrough ?? [],
                    'warnings',
                    []
                )
            );

        $portfolioLookThroughCoverage =
            (float) (
                $lookThroughMetrics[
                    'portfolio_coverage'
                ] ?? 0
            );

        $fundLookThroughCoverage =
            (float) (
                $lookThroughMetrics[
                    'fund_coverage'
                ] ?? 0
            );

        $overlapBadgeClasses = static function (
            ?string $rating
        ): string {
            return match ($rating) {
                'very_high' =>
                    'border-red-500/30 bg-red-500/10 text-red-300',

                'high' =>
                    'border-orange-500/30 bg-orange-500/10 text-orange-300',

                'moderate' =>
                    'border-amber-500/30 bg-amber-500/10 text-amber-300',

                'low' =>
                    'border-blue-500/25 bg-blue-500/10 text-blue-300',

                default =>
                    'border-slate-700 bg-slate-800 text-slate-300',
            };
        };

        /*
         * Donut chart data.
         *
         * Show the six largest invested securities individually and
         * combine the remainder into "Other" so the chart stays readable.
         * Cash is already excluded by DiversificationAnalyticsService.
         */
        $chartSecurities = collect($analytics['securities'] ?? []);
        $chartTop = $chartSecurities->take(6);
        $chartOtherWeight = max(
            0,
            1 - (float) $chartTop->sum('weight')
        );

        $chartSegments = $chartTop
            ->map(
                fn (array $security): array => [
                    'label' =>
                        $security['symbol']
                        ?: $security['name'],

                    'weight' =>
                        (float) $security['weight'],
                ]
            )
            ->values();

        if ($chartOtherWeight > 0.001) {
            $chartSegments->push([
                'label' => 'Other',
                'weight' => $chartOtherWeight,
            ]);
        }

        $chartStrokeClasses = [
            'text-blue-400',
            'text-cyan-400',
            'text-indigo-400',
            'text-violet-400',
            'text-sky-300',
            'text-blue-700',
            'text-slate-500',
        ];

        $chartOffset = 0.0;
    @endphp

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Executive summary --}}
            <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-lg">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-4xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-blue-500/25 bg-blue-500/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-blue-300">
                                Helmio diversification review
                            </span>

                            @if ($score !== null)
                                <span class="rounded-full border border-slate-700 bg-slate-950 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                    Score {{ $score }}/100
                                </span>
                            @endif
                        </div>

                        <h3 class="mt-4 text-xl font-semibold tracking-tight text-white">
                            {{ $summary['headline'] ?? $scoreLabel }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            {{ $summary['message']
                                ?? 'Helmio is evaluating security, sector, and asset-class concentration using the current invested holdings.' }}
                        </p>
                    </div>

                    @if (($summary['material_finding_count'] ?? 0) > 0)
                        <div class="shrink-0 rounded-xl border border-slate-800 bg-slate-950 px-5 py-4 text-center">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Material findings
                            </p>

                            <p class="mt-1 text-3xl font-semibold text-white">
                                {{ $summary['material_finding_count'] }}
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- KPI row --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-analytics.metric-card
                    label="Diversification score"
                    description="{{ $scoreLabel }}"
                >
                    {{ $score ?? '—' }}
                </x-analytics.metric-card>

                <x-analytics.metric-card label="Securities">
                    {{ $analytics['metrics']['security_count'] ?? 0 }}
                </x-analytics.metric-card>

                <x-analytics.metric-card label="Largest holding">
                    @if (isset($analytics['metrics']['largest_security_weight']))
                        {{ number_format(
                            $analytics['metrics']['largest_security_weight'] * 100,
                            1
                        ) }}%
                    @else
                        —
                    @endif
                </x-analytics.metric-card>

                <x-analytics.metric-card label="Top five holdings">
                    @if (isset($analytics['metrics']['top_five_weight']))
                        {{ number_format(
                            $analytics['metrics']['top_five_weight'] * 100,
                            1
                        ) }}%
                    @else
                        —
                    @endif
                </x-analytics.metric-card>
            </div>

            {{-- Portfolio allocation intelligence --}}
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                <div class="border-b border-slate-800 px-6 py-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Portfolio allocation
                            </p>

                            <h3 class="mt-1 font-semibold text-white">
                                Concentration at a glance
                            </h3>
                        </div>

                        <p class="text-xs text-slate-500">
                            Non-cash invested holdings
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 p-5 lg:grid-cols-[180px_1fr] lg:items-center">
                    {{-- Smaller donut --}}
                    <div class="mx-auto w-[150px] max-w-full sm:w-[165px]">
                        @if ($chartSegments->isNotEmpty())
                            <div class="relative aspect-square">
                                <svg
                                    class="h-full w-full -rotate-90"
                                    viewBox="0 0 42 42"
                                    role="img"
                                    aria-label="Portfolio allocation donut chart"
                                >
                                    <circle
                                        cx="21"
                                        cy="21"
                                        r="15.9155"
                                        fill="transparent"
                                        stroke="currentColor"
                                        stroke-width="4.75"
                                        class="text-slate-800"
                                    />

                                    @php
                                        $chartOffset = 0.0;
                                    @endphp

                                    @foreach ($chartSegments as $index => $segment)
                                        @php
                                            $segmentPercent =
                                                max(
                                                    0,
                                                    min(
                                                        100,
                                                        $segment['weight'] * 100
                                                    )
                                                );

                                            $dashArray =
                                                $segmentPercent . ' '
                                                . (100 - $segmentPercent);

                                            $dashOffset =
                                                25 - $chartOffset;

                                            $chartOffset +=
                                                $segmentPercent;
                                        @endphp

                                        <circle
                                            cx="21"
                                            cy="21"
                                            r="15.9155"
                                            fill="transparent"
                                            stroke="currentColor"
                                            stroke-width="4.75"
                                            stroke-dasharray="{{ $dashArray }}"
                                            stroke-dashoffset="{{ $dashOffset }}"
                                            class="{{ $chartStrokeClasses[$index % count($chartStrokeClasses)] }}"
                                        />
                                    @endforeach
                                </svg>

                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <p class="text-2xl font-semibold tracking-tight text-white">
                                            {{ $analytics['metrics']['security_count'] ?? 0 }}
                                        </p>

                                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                                            Securities
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex aspect-square items-center justify-center rounded-full border border-slate-800 bg-slate-950 text-sm text-slate-500">
                                No allocation data
                            </div>
                        @endif
                    </div>

                    {{-- Intelligence + compact legend --}}
                    <div class="min-w-0">
                        <div class="grid gap-3 sm:grid-cols-3">
                            <article class="rounded-xl border border-slate-800 bg-slate-950 p-4">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.13em] text-slate-500">
                                    Largest holding
                                </p>

                                <p class="mt-2 text-2xl font-semibold text-white">
                                    @if (isset($analytics['metrics']['largest_security_weight']))
                                        {{ number_format(
                                            $analytics['metrics']['largest_security_weight'] * 100,
                                            1
                                        ) }}%
                                    @else
                                        —
                                    @endif
                                </p>
                            </article>

                            <article class="rounded-xl border border-slate-800 bg-slate-950 p-4">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.13em] text-slate-500">
                                    Top five holdings
                                </p>

                                <p class="mt-2 text-2xl font-semibold text-white">
                                    @if (isset($analytics['metrics']['top_five_weight']))
                                        {{ number_format(
                                            $analytics['metrics']['top_five_weight'] * 100,
                                            1
                                        ) }}%
                                    @else
                                        —
                                    @endif
                                </p>
                            </article>

                            <article class="rounded-xl border border-slate-800 bg-slate-950 p-4">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.13em] text-slate-500">
                                    Largest sector
                                </p>

                                <p class="mt-2 text-2xl font-semibold text-white">
                                    @if (isset($analytics['metrics']['largest_sector_weight']))
                                        {{ number_format(
                                            $analytics['metrics']['largest_sector_weight'] * 100,
                                            1
                                        ) }}%
                                    @else
                                        —
                                    @endif
                                </p>
                            </article>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($chartSegments as $index => $segment)
                                <div class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-3">
                                    <span
                                        class="h-2.5 w-2.5 shrink-0 rounded-full {{ str_replace('text-', 'bg-', $chartStrokeClasses[$index % count($chartStrokeClasses)]) }}"
                                    ></span>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="truncate text-sm font-medium text-slate-300">
                                                {{ $segment['label'] }}
                                            </span>

                                            <span class="shrink-0 text-sm font-semibold text-white">
                                                {{ number_format(
                                                    $segment['weight'] * 100,
                                                    1
                                                ) }}%
                                            </span>
                                        </div>

                                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-blue-300 via-blue-500 to-blue-800"
                                                style="width: {{ min(
                                                    100,
                                                    $segment['weight'] * 100
                                                ) }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 rounded-xl border border-blue-500/15 bg-blue-500/[0.05] p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.13em] text-blue-300">
                                Helmio interpretation
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                {{ $summary['message']
                                    ?? 'Helmio evaluates concentration using position size, top-five exposure, sectors, asset classes, and concentration indices.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Look-through diversification --}}
            @if (
                data_get($lookThrough ?? [], 'status') === 'complete'
                && (
                    $effectiveExposures->isNotEmpty()
                    || $fundPairs->isNotEmpty()
                )
            )
                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                    <div class="border-b border-slate-800 px-6 py-5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">
                                        Look-through diversification
                                    </p>

                                    <span class="rounded-full border border-slate-700 bg-slate-950 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                        Known exposure
                                    </span>
                                </div>

                                <h3 class="mt-2 font-semibold text-white">
                                    Hidden overlap beneath funds
                                </h3>

                                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-400">
                                    Helmio decomposes available ETF holdings to estimate the underlying companies you actually own through multiple positions.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <div class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-right">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Portfolio coverage
                                    </p>

                                    <p class="mt-1 text-lg font-semibold text-white">
                                        {{ number_format(
                                            $portfolioLookThroughCoverage * 100,
                                            1
                                        ) }}%
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-right">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Fund coverage
                                    </p>

                                    <p class="mt-1 text-lg font-semibold text-white">
                                        {{ number_format(
                                            $fundLookThroughCoverage * 100,
                                            1
                                        ) }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-0 xl:grid-cols-[1.05fr_.95fr]">
                        {{-- Effective exposure --}}
                        <div class="border-b border-slate-800 p-6 xl:border-b-0 xl:border-r">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Effective underlying exposure
                                    </p>

                                    <p class="mt-1 text-sm text-slate-400">
                                        Direct positions plus known fund look-through.
                                    </p>
                                </div>

                                @if (($lookThroughMetrics['effective_security_count'] ?? 0) > 0)
                                    <span class="rounded-full border border-slate-700 bg-slate-950 px-2.5 py-1 text-xs font-semibold text-slate-300">
                                        {{ $lookThroughMetrics['effective_security_count'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-5 space-y-3">
                                @forelse ($effectiveExposures->take(10) as $exposure)
                                    @php
                                        $sources =
                                            collect(
                                                $exposure['sources']
                                                ?? []
                                            );

                                        $sourceSymbols =
                                            $sources
                                                ->pluck('symbol')
                                                ->filter()
                                                ->unique()
                                                ->values();
                                    @endphp

                                    <article class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="font-semibold text-white">
                                                        {{ $exposure['symbol'] ?: $exposure['name'] }}
                                                    </p>

                                                    @if ($sourceSymbols->count() > 1)
                                                        <span class="rounded-full border border-orange-500/20 bg-orange-500/[0.08] px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-orange-300">
                                                            Multiple sources
                                                        </span>
                                                    @endif
                                                </div>

                                                <p class="mt-1 truncate text-sm text-slate-500">
                                                    {{ $exposure['name'] }}
                                                </p>

                                                @if ($sourceSymbols->isNotEmpty())
                                                    <p class="mt-2 text-xs leading-5 text-slate-500">
                                                        Through
                                                        <span class="font-medium text-slate-300">
                                                            {{ $sourceSymbols->join(', ') }}
                                                        </span>
                                                    </p>
                                                @endif
                                            </div>

                                            <div class="shrink-0 text-right">
                                                <p class="text-xl font-semibold text-white">
                                                    {{ number_format(
                                                        ($exposure['exposure'] ?? 0) * 100,
                                                        2
                                                    ) }}%
                                                </p>

                                                <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                                    Effective
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-blue-300 via-blue-500 to-blue-800"
                                                style="width: {{ min(
                                                    100,
                                                    ($exposure['exposure'] ?? 0) * 100
                                                ) }}%"
                                            ></div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-5 text-sm text-slate-500">
                                        No effective underlying exposures are currently available.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Fund overlap --}}
                        <div class="p-6">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Fund-to-fund overlap
                                </p>

                                <p class="mt-1 text-sm text-slate-400">
                                    Weighted shared holdings between transparent funds.
                                </p>
                            </div>

                            <div class="mt-5 space-y-3">
                                @forelse ($fundPairs as $pair)
                                    <article class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="font-semibold text-white">
                                                        {{ $pair['left_symbol'] }}
                                                        <span class="mx-1 text-slate-600">↔</span>
                                                        {{ $pair['right_symbol'] }}
                                                    </p>

                                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $overlapBadgeClasses($pair['rating'] ?? null) }}">
                                                        {{ str(
                                                            $pair['rating'] ?? 'unknown'
                                                        )->replace('_', ' ')->title() }}
                                                    </span>
                                                </div>

                                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                                    {{ $pair['shared_constituent_count'] ?? 0 }}
                                                    shared holdings are represented in the current look-through data.
                                                </p>
                                            </div>

                                            <div class="shrink-0 text-right">
                                                <p class="text-2xl font-semibold text-white">
                                                    {{ number_format(
                                                        ($pair['overlap_weight'] ?? 0) * 100,
                                                        1
                                                    ) }}%
                                                </p>

                                                <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                                    Overlap
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-blue-300 via-blue-500 to-orange-500"
                                                style="width: {{ min(
                                                    100,
                                                    ($pair['overlap_weight'] ?? 0) * 100
                                                ) }}%"
                                            ></div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-5 text-sm text-slate-500">
                                        No fund pairs with look-through data are available yet.
                                    </div>
                                @endforelse
                            </div>

                            @if ($fundPairs->isNotEmpty())
                                @php
                                    $highestOverlap =
                                        $fundPairs->first();
                                @endphp

                                <div class="mt-5 rounded-xl border border-orange-500/15 bg-orange-500/[0.05] p-4">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.13em] text-orange-300">
                                        Helmio interpretation
                                    </p>

                                    <p class="mt-2 text-sm leading-6 text-slate-400">
                                        {{ $highestOverlap['left_symbol'] }}
                                        and
                                        {{ $highestOverlap['right_symbol'] }}
                                        currently show
                                        <span class="font-semibold text-slate-200">
                                            {{ number_format(
                                                ($highestOverlap['overlap_weight'] ?? 0) * 100,
                                                1
                                            ) }}%
                                            weighted overlap
                                        </span>.
                                        Owning both may provide less diversification than the top-level holding count suggests.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($lookThroughWarnings->isNotEmpty())
                        <div class="border-t border-slate-800 bg-slate-950/40 px-6 py-4">
                            @foreach ($lookThroughWarnings as $warning)
                                <p class="text-xs leading-5 text-slate-500">
                                    {{ $warning['message'] ?? $warning }}
                                </p>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            {{-- Findings + actions --}}

            <div class="grid gap-6 lg:grid-cols-[1.15fr_.85fr]">
                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                    <div class="border-b border-slate-800 px-6 py-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                    What Helmio found
                                </p>

                                <h3 class="mt-1 font-semibold text-white">
                                    Ranked diversification findings
                                </h3>
                            </div>

                            @if ($findings->isNotEmpty())
                                <span class="rounded-full border border-slate-700 bg-slate-950 px-2.5 py-1 text-xs font-semibold text-slate-300">
                                    {{ $findings->count() }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="divide-y divide-slate-800">
                        @forelse ($findings as $finding)
                            <article class="px-6 py-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] {{ $severityClasses($finding['severity'] ?? null) }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $severityDotClasses($finding['severity'] ?? null) }}"></span>

                                                {{ ucfirst($finding['severity'] ?? 'information') }}
                                            </span>

                                            @if (($finding['metric'] ?? null) !== null)
                                                <span class="text-xs font-semibold {{ $severityTextClasses($finding['severity'] ?? null) }}">
                                                    {{ $finding['metric'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="mt-3 font-semibold text-white">
                                            {{ $finding['title'] ?? 'Diversification finding' }}
                                        </h4>

                                        <p class="mt-2 text-sm leading-6 text-slate-400">
                                            {{ $finding['message'] ?? '' }}
                                        </p>
                                    </div>

                                    @if (($finding['score_impact'] ?? 0) < 0)
                                        <div class="shrink-0 text-right">
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                                Score impact
                                            </p>

                                            <p class="mt-1 text-sm font-semibold text-red-300">
                                                {{ $finding['score_impact'] }} pts
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="p-8 text-center">
                                <p class="font-semibold text-white">
                                    No material concentration concerns
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    Helmio did not identify a ranked diversification finding from the current holdings.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.06] p-6 shadow-lg">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-300">
                        Recommended review
                    </p>

                    <h3 class="mt-2 text-lg font-semibold text-white">
                        What to review next
                    </h3>

                    <div class="mt-5 space-y-4">
                        @forelse ($actions as $action)
                            <article class="rounded-xl border border-blue-500/15 bg-slate-950/40 p-4">
                                <div class="flex gap-3">
                                    <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-blue-500/20 bg-blue-500/10 text-xs font-semibold text-blue-300">
                                        {{ $loop->iteration }}
                                    </div>

                                    <div>
                                        <p class="font-semibold text-slate-100">
                                            {{ $action['title'] ?? 'Review diversification' }}
                                        </p>

                                        <p class="mt-1.5 text-sm leading-6 text-slate-400">
                                            {{ $action['message'] ?? '' }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm leading-6 text-slate-400">
                                Continue monitoring changes in security, sector, and asset-class concentration.
                            </p>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Data quality --}}
            @if ($warnings->isNotEmpty())
                <section class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.05] p-5">
                    <div class="flex gap-3">
                        <svg
                            class="mt-0.5 h-5 w-5 shrink-0 text-amber-300"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.3 3.6 2.6 17a2 2 0 0 0 1.73 3h15.34A2 2 0 0 0 21.4 17L13.7 3.6a2 2 0 0 0-3.4 0Z"
                            />
                        </svg>

                        <div>
                            <h3 class="font-semibold text-amber-200">
                                Data-quality notes
                            </h3>

                            <div class="mt-2 space-y-1.5">
                                @foreach ($warnings as $warning)
                                    <p class="text-sm leading-6 text-amber-200/75">
                                        {{ $warning['message'] ?? $warning }}
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Security + sector exposure --}}
            <div class="grid gap-6 lg:grid-cols-2">
                <x-analytics.panel
                    title="Largest Securities"
                    :padding="false"
                >
                    <div class="divide-y divide-slate-800">
                        @forelse ($analytics['securities']->take(10) as $security)
                            <div class="px-6 py-5">
                                <div class="flex items-center justify-between gap-5">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white">
                                            {{ $security['symbol'] ?: $security['name'] }}
                                        </p>

                                        <p class="mt-1 truncate text-sm text-slate-500">
                                            {{ $security['name'] }}
                                        </p>
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <p class="font-semibold text-white">
                                            {{ number_format(
                                                $security['weight'] * 100,
                                                1
                                            ) }}%
                                        </p>

                                        <p class="mt-1 text-sm text-slate-500">
                                            ${{ number_format(
                                                $security['market_value'],
                                                2
                                            ) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-blue-300 via-blue-500 to-blue-800"
                                        style="width: {{ min(
                                            100,
                                            $security['weight'] * 100
                                        ) }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-500">
                                No holdings available.
                            </div>
                        @endforelse
                    </div>
                </x-analytics.panel>

                <x-analytics.panel
                    title="Sector Exposure"
                    :padding="false"
                >
                    <div class="divide-y divide-slate-800">
                        @forelse ($analytics['sectors'] as $sector)
                            <div class="px-6 py-5">
                                <div class="flex justify-between gap-5">
                                    <div>
                                        <p class="font-medium text-slate-300">
                                            {{ $sector['name'] }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-600">
                                            Classified invested exposure
                                        </p>
                                    </div>

                                    <p class="font-semibold text-white">
                                        {{ number_format(
                                            $sector['weight'] * 100,
                                            1
                                        ) }}%
                                    </p>
                                </div>

                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-blue-300 via-blue-500 to-blue-800"
                                        style="width: {{ min(
                                            100,
                                            $sector['weight'] * 100
                                        ) }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-500">
                                No sector data available.
                            </div>
                        @endforelse
                    </div>
                </x-analytics.panel>
            </div>

            {{-- Asset classes --}}
            <x-analytics.panel title="Asset-Class Exposure">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($analytics['asset_classes'] as $assetClass)
                        <article class="rounded-2xl border border-slate-800 bg-slate-950 p-5">
                            <div class="flex justify-between gap-4">
                                <p class="font-medium text-slate-300">
                                    {{ $assetClass['name'] }}
                                </p>

                                <p class="font-semibold text-white">
                                    {{ number_format(
                                        $assetClass['weight'] * 100,
                                        1
                                    ) }}%
                                </p>
                            </div>

                            <p class="mt-3 text-sm text-slate-500">
                                ${{ number_format(
                                    $assetClass['market_value'],
                                    2
                                ) }}
                            </p>

                            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-blue-300 via-blue-500 to-blue-800"
                                    style="width: {{ min(
                                        100,
                                        $assetClass['weight'] * 100
                                    ) }}%"
                                ></div>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">
                            No asset-class data available.
                        </p>
                    @endforelse
                </div>
            </x-analytics.panel>

            {{-- Methodology --}}
            <x-analytics.methodology
                :formula-version="$analytics['formula_version']"
            >
                Helmio evaluates non-cash security concentration, top-five
                concentration, sector exposure, asset-class exposure,
                classification coverage, and concentration indices. When
                constituent data is available, Helmio also estimates effective
                underlying exposure and weighted fund-to-fund overlap. Coverage
                is shown explicitly because pooled investments without
                look-through data are not treated as fully transparent.
                Geographic exposure, correlation, and factor concentration are
                not yet included.
            </x-analytics.methodology>
        </div>
    </div>
</x-app-layout>