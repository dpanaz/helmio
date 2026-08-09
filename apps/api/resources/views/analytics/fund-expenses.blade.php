<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                Fund costs
            </p>

            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                Fund Expense Analysis
            </h2>

            <p class="mt-2 text-sm text-slate-400">
                Review expense ratios and identify lower-cost comparable candidates.
            </p>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-analytics.metric-card label="Fund assets analyzed">
                    ${{ number_format($analytics['total_fund_value'], 2) }}
                </x-analytics.metric-card>

                <x-analytics.metric-card label="Weighted expense ratio">
                    @if ($analytics['weighted_expense_ratio'] !== null)
                        {{ number_format(
                            $analytics['weighted_expense_ratio'] * 100,
                            2
                        ) }}%
                    @else
                        —
                    @endif
                </x-analytics.metric-card>

                <x-analytics.metric-card label="Annual fund expenses">
                    ${{ number_format($analytics['annual_expense_cost'], 2) }}
                </x-analytics.metric-card>

                <x-analytics.metric-card
                    label="Potential annual savings"
                    tone="good"
                >
                    ${{ number_format($analytics['estimated_savings'], 2) }}
                </x-analytics.metric-card>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <x-analytics.metric-card label="Expense data coverage">
                    @if ($analytics['expense_data_coverage_rate'] !== null)
                        {{ number_format(
                            $analytics['expense_data_coverage_rate'] * 100,
                            1
                        ) }}%
                    @else
                        —
                    @endif
                </x-analytics.metric-card>

                <x-analytics.metric-card label="Funds analyzed">
                    {{ $analytics['fund_count'] }}
                </x-analytics.metric-card>

                <x-analytics.metric-card
                    label="Missing expense ratios"
                    tone="warning"
                >
                    {{ $analytics['missing_expense_ratio_count'] }}
                </x-analytics.metric-card>
            </div>

            <x-analytics.panel
                title="Fund Costs & Comparable Candidates"
                subtitle="Comparisons are limited to securities assigned to the same comparison group."
                :padding="false"
            >
                @if ($analytics['holdings']->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-semibold text-white">
                            No mutual funds or ETFs found
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-slate-800">
                        @foreach ($analytics['holdings'] as $row)
                            <article class="p-6">
                                <div
                                    class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between"
                                >
                                    <div>
                                        <h4 class="font-semibold text-white">
                                            {{ $row['symbol'] ?: $row['name'] }}
                                        </h4>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $row['name'] }}
                                            · {{ $row['account_name'] }}
                                        </p>

                                        @if ($row['comparison_group'])
                                            <p class="mt-2 text-xs text-slate-600">
                                                Comparison group:
                                                {{ $row['comparison_group'] }}
                                            </p>
                                        @endif
                                    </div>

                                    <div
                                        class="grid grid-cols-2 gap-6 sm:grid-cols-4"
                                    >
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-600">
                                                Value
                                            </p>

                                            <p class="mt-1 font-semibold text-white">
                                                ${{ number_format($row['market_value'], 2) }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-600">
                                                Expense ratio
                                            </p>

                                            <p class="mt-1 font-semibold text-white">
                                                @if ($row['expense_ratio'] !== null)
                                                    {{ number_format(
                                                        $row['expense_ratio'] * 100,
                                                        2
                                                    ) }}%
                                                @else
                                                    Missing
                                                @endif
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-600">
                                                Annual cost
                                            </p>

                                            <p class="mt-1 font-semibold text-white">
                                                @if ($row['annual_expense_cost'] !== null)
                                                    ${{ number_format(
                                                        $row['annual_expense_cost'],
                                                        2
                                                    ) }}
                                                @else
                                                    —
                                                @endif
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-600">
                                                Best savings
                                            </p>

                                            <p class="mt-1 font-semibold text-emerald-300">
                                                @if ($row['best_estimated_savings'] !== null)
                                                    ${{ number_format(
                                                        $row['best_estimated_savings'],
                                                        2
                                                    ) }}
                                                @else
                                                    —
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if ($row['alternatives']->isNotEmpty())
                                    <div
                                        class="mt-6 rounded-2xl border border-slate-800 bg-slate-950 p-5"
                                    >
                                        <p class="text-sm font-semibold text-white">
                                            Lower-cost candidates
                                        </p>

                                        <div class="mt-4 space-y-3">
                                            @foreach ($row['alternatives'] as $alternative)
                                                <div
                                                    class="flex flex-col gap-4 rounded-xl border border-slate-800 bg-slate-900 p-4 sm:flex-row sm:items-center sm:justify-between"
                                                >
                                                    <div>
                                                        <p class="font-medium text-white">
                                                            {{ $alternative['symbol'] ?: $alternative['name'] }}
                                                        </p>

                                                        <p class="mt-1 text-sm text-slate-500">
                                                            {{ $alternative['name'] }}
                                                        </p>
                                                    </div>

                                                    <div class="text-right">
                                                        <p class="font-semibold text-white">
                                                            {{ number_format(
                                                                $alternative['expense_ratio'] * 100,
                                                                2
                                                            ) }}%
                                                        </p>

                                                        <p class="mt-1 text-sm text-emerald-300">
                                                            Estimated savings:
                                                            ${{ number_format(
                                                                $alternative['estimated_annual_savings'],
                                                                2
                                                            ) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-analytics.panel>

            <x-analytics.methodology
                title="Important methodology note"
                :formula-version="$analytics['formula_version']"
            >
                A lower expense ratio does not automatically make a fund
                appropriate or superior. Helmio currently compares only
                funds assigned to the same comparison group. Performance,
                tax consequences, risk, trading costs, availability, and
                the investor’s objectives must also be considered.
            </x-analytics.methodology>
        </div>
    </div>
</x-app-layout>