<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Phase 2 analytics
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Diversification analysis
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Diversification score
                    </p>

                    <p class="mt-3 text-4xl font-semibold text-slate-900">
                        {{ $analytics['score'] ?? '—' }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ $analytics['label'] }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Securities
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $analytics['metrics']['security_count'] ?? 0 }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Largest holding
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
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

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Largest sector
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
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
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">
                        Why this score?
                    </h3>

                    <div class="mt-5 space-y-3">
                        @foreach ($analytics['reasons'] as $reason)
                            <p class="text-sm leading-6 text-slate-600">
                                {{ $reason }}
                            </p>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-3xl border border-blue-200 bg-blue-50 p-7">
                    <h3 class="text-lg font-semibold text-blue-950">
                        Recommended review
                    </h3>

                    <div class="mt-5 space-y-3">
                        @foreach ($analytics['recommendations'] as $recommendation)
                            <p class="text-sm leading-6 text-blue-900">
                                {{ $recommendation }}
                            </p>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="font-semibold text-slate-900">
                            Largest securities
                        </h3>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @forelse ($analytics['securities']->take(10) as $security)
                            <div class="flex items-center justify-between gap-5 px-6 py-5">
                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ $security['symbol'] ?: $security['name'] }}
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $security['name'] }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-semibold text-slate-900">
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
                </article>

                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="font-semibold text-slate-900">
                            Sector exposure
                        </h3>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @forelse ($analytics['sectors'] as $sector)
                            <div class="px-6 py-5">
                                <div class="flex items-center justify-between gap-5">
                                    <p class="font-medium text-slate-900">
                                        {{ $sector['name'] }}
                                    </p>

                                    <p class="font-semibold text-slate-900">
                                        {{ number_format(
                                            $sector['weight'] * 100,
                                            1
                                        ) }}%
                                    </p>
                                </div>

                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div
                                        class="h-full rounded-full bg-blue-600"
                                        style="width: {{ min(100, $sector['weight'] * 100) }}%"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-500">
                                No sector data available.
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="font-semibold text-slate-900">
                        Asset-class exposure
                    </h3>
                </div>

                <div class="grid gap-5 p-6 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($analytics['asset_classes'] as $assetClass)
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-medium text-slate-900">
                                    {{ $assetClass['name'] }}
                                </p>

                                <p class="font-semibold text-slate-900">
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
            </section>

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <p class="text-sm font-medium text-blue-300">
                    Methodology
                </p>

                <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-300">
                    The initial diversification score evaluates individual
                    security concentration, top-five concentration, sector
                    exposure, asset-class exposure and classification
                    completeness. It does not yet measure correlations,
                    geographic exposure, look-through fund holdings or factor
                    concentration.
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Formula version:
                    {{ $analytics['formula_version'] }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
