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

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-analytics.metric-card
                    label="Diversification score"
                    description="{{ $analytics['label'] }}"
                >
                    {{ $analytics['score'] ?? '—' }}
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

                <x-analytics.metric-card label="Largest sector">
                    @if (isset($analytics['metrics']['largest_sector_weight']))
                        {{ number_format(
                            $analytics['metrics']['largest_sector_weight'] * 100,
                            1
                        ) }}%
                    @else
                        —
                    @endif
                </x-analytics.metric-card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-analytics.panel title="Why This Score?">
                    <div class="space-y-4">
                        @forelse ($analytics['reasons'] as $reason)
                            <div class="flex gap-3">
                                <span
                                    class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400"
                                ></span>

                                <p class="text-sm leading-6 text-slate-400">
                                    {{ $reason }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">
                                No scoring reasons are currently available.
                            </p>
                        @endforelse
                    </div>
                </x-analytics.panel>

                <section
                    class="rounded-3xl border border-blue-500/20 bg-blue-500/[0.06] p-7"
                >
                    <p class="text-sm font-semibold text-blue-300">
                        Recommended Review
                    </p>

                    <div class="mt-5 space-y-4">
                        @forelse ($analytics['recommendations'] as $recommendation)
                            <div class="flex gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-blue-400"
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

                                <p class="text-sm leading-6 text-slate-300">
                                    {{ $recommendation }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">
                                No recommendations currently available.
                            </p>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-analytics.panel
                    title="Largest Securities"
                    :padding="false"
                >
                    <div class="divide-y divide-slate-800">
                        @forelse ($analytics['securities']->take(10) as $security)
                            <div
                                class="flex items-center justify-between gap-5 px-6 py-5"
                            >
                                <div>
                                    <p class="font-semibold text-white">
                                        {{ $security['symbol'] ?: $security['name'] }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $security['name'] }}
                                    </p>
                                </div>

                                <div class="text-right">
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
                                    <p class="font-medium text-slate-300">
                                        {{ $sector['name'] }}
                                    </p>

                                    <p class="font-semibold text-white">
                                        {{ number_format(
                                            $sector['weight'] * 100,
                                            1
                                        ) }}%
                                    </p>
                                </div>

                                <div
                                    class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full bg-blue-500"
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

            <x-analytics.panel title="Asset-Class Exposure">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($analytics['asset_classes'] as $assetClass)
                        <article
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-5"
                        >
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
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">
                            No asset-class data available.
                        </p>
                    @endforelse
                </div>
            </x-analytics.panel>

            <x-analytics.methodology
                :formula-version="$analytics['formula_version']"
            >
                The initial diversification score evaluates individual
                security concentration, top-five concentration, sector
                exposure, asset-class exposure, and classification
                completeness. It does not yet measure correlations,
                geographic exposure, look-through fund holdings, or
                factor concentration.
            </x-analytics.methodology>
        </div>
    </div>
</x-app-layout>