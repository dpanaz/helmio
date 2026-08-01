<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-blue-600">
                Phase 2 analytics
            </p>

            <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                Fund expense analysis
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Fund assets analyzed
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format(
                            $analytics['total_fund_value'],
                            2
                        ) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Weighted expense ratio
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if ($analytics['weighted_expense_ratio'] !== null)
                            {{ number_format(
                                $analytics['weighted_expense_ratio'] * 100,
                                2
                            ) }}%
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Annual fund expenses
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format(
                            $analytics['annual_expense_cost'],
                            2
                        ) }}
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Potential annual savings
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-emerald-700">
                        ${{ number_format(
                            $analytics['estimated_savings'],
                            2
                        ) }}
                    </p>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Expense data coverage
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if (
                            $analytics['expense_data_coverage_rate']
                            !== null
                        )
                            {{ number_format(
                                $analytics[
                                    'expense_data_coverage_rate'
                                ] * 100,
                                1
                            ) }}%
                        @else
                            —
                        @endif
                    </p>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Funds analyzed
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $analytics['fund_count'] }}
                    </p>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Missing expense ratios
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-amber-700">
                        {{ $analytics['missing_expense_ratio_count'] }}
                    </p>
                </article>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="font-semibold text-slate-900">
                        Fund costs and comparable candidates
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Comparisons are limited to securities assigned to
                        the same comparison group.
                    </p>
                </div>

                @if ($analytics['holdings']->isEmpty())
                    <div class="p-12 text-center">
                        <p class="font-semibold text-slate-900">
                            No mutual funds or ETFs found
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-slate-200">
                        @foreach ($analytics['holdings'] as $row)
                            <article class="p-6">
                                <div class="flex flex-wrap justify-between gap-5">
                                    <div>
                                        <h4 class="font-semibold text-slate-900">
                                            {{ $row['symbol']
                                                ?: $row['name'] }}
                                        </h4>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $row['name'] }}
                                            · {{ $row['account_name'] }}
                                        </p>

                                        @if ($row['comparison_group'])
                                            <p class="mt-1 text-xs text-slate-400">
                                                Group:
                                                {{ $row['comparison_group'] }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-right">
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                                Value
                                            </p>

                                            <p class="mt-1 font-semibold text-slate-900">
                                                ${{ number_format(
                                                    $row['market_value'],
                                                    2
                                                ) }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                                Expense ratio
                                            </p>

                                            <p class="mt-1 font-semibold text-slate-900">
                                                @if (
                                                    $row['expense_ratio']
                                                    !== null
                                                )
                                                    {{ number_format(
                                                        $row[
                                                            'expense_ratio'
                                                        ] * 100,
                                                        2
                                                    ) }}%
                                                @else
                                                    Missing
                                                @endif
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                                Annual cost
                                            </p>

                                            <p class="mt-1 font-semibold text-slate-900">
                                                @if (
                                                    $row[
                                                        'annual_expense_cost'
                                                    ] !== null
                                                )
                                                    ${{ number_format(
                                                        $row[
                                                            'annual_expense_cost'
                                                        ],
                                                        2
                                                    ) }}
                                                @else
                                                    —
                                                @endif
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                                Best savings
                                            </p>

                                            <p class="mt-1 font-semibold text-emerald-700">
                                                @if (
                                                    $row[
                                                        'best_estimated_savings'
                                                    ] !== null
                                                )
                                                    ${{ number_format(
                                                        $row[
                                                            'best_estimated_savings'
                                                        ],
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
                                    <div class="mt-6 rounded-2xl bg-slate-50 p-5">
                                        <p class="text-sm font-semibold text-slate-900">
                                            Lower-cost candidates
                                        </p>

                                        <div class="mt-4 space-y-3">
                                            @foreach (
                                                $row['alternatives']
                                                as $alternative
                                            )
                                                <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4">
                                                    <div>
                                                        <p class="font-medium text-slate-900">
                                                            {{ $alternative['symbol']
                                                                ?: $alternative['name'] }}
                                                        </p>

                                                        <p class="mt-1 text-sm text-slate-500">
                                                            {{ $alternative['name'] }}
                                                        </p>
                                                    </div>

                                                    <div class="text-right">
                                                        <p class="font-semibold text-slate-900">
                                                            {{ number_format(
                                                                $alternative[
                                                                    'expense_ratio'
                                                                ] * 100,
                                                                2
                                                            ) }}%
                                                        </p>

                                                        <p class="mt-1 text-sm text-emerald-700">
                                                            Estimated savings:
                                                            ${{ number_format(
                                                                $alternative[
                                                                    'estimated_annual_savings'
                                                                ],
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
            </section>

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <p class="text-sm font-medium text-blue-300">
                    Important methodology note
                </p>

                <p class="mt-4 max-w-4xl text-sm leading-7 text-slate-300">
                    A lower expense ratio does not automatically make a
                    fund appropriate or superior. Helmio currently compares
                    only funds assigned to the same comparison group.
                    Performance, tax consequences, risk, trading costs,
                    availability and the investor’s objectives must also
                    be considered.
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Formula version:
                    {{ $analytics['formula_version'] }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
