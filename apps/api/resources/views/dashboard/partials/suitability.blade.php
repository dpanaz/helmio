<section
    class="overflow-hidden rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-50 via-white to-white p-5 shadow-sm sm:p-6"
>
    <div
        class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
    >
        <div class="min-w-0 max-w-3xl">
            <div class="flex flex-wrap items-center gap-3">
                <p class="text-sm font-semibold text-blue-700">
                    Investor Suitability
                </p>

                <span
                    class="max-w-full rounded-full bg-white px-3 py-1 text-xs font-semibold text-blue-800 ring-1 ring-blue-200"
                >
                    {{ $suitabilityLabel }}
                </span>
            </div>

            <div class="mt-4 flex items-end gap-3">
                <p
                    class="text-5xl font-bold tracking-tight text-slate-950"
                >
                    {{ $suitabilityScore ?? '—' }}
                </p>

                @if ($suitabilityScore !== null)
                    <p
                        class="pb-1 text-base font-medium text-slate-400"
                    >
                        / 100
                    </p>
                @endif
            </div>

            <p
                class="mt-4 max-w-2xl text-sm leading-6 text-slate-600"
            >
                Helmio compares measured portfolio risk with your age,
                time horizon, objective, liquidity needs, and account-level
                settings.
            </p>
        </div>

        <div
            class="grid w-full min-w-0 gap-3 sm:grid-cols-2 lg:max-w-xl"
        >
            <div
                class="min-w-0 rounded-2xl bg-white p-4 ring-1 ring-blue-100"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-wide text-blue-600"
                >
                    Expected Risk
                </p>

                <p
                    class="mt-2 break-words font-semibold text-slate-900"
                >
                    {{ $riskLabel($expectedRiskTolerance) }}
                </p>
            </div>

            <div
                class="min-w-0 rounded-2xl bg-white p-4 ring-1 ring-blue-100"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-wide text-blue-600"
                >
                    Measured Risk
                </p>

                <p
                    class="mt-2 break-words font-semibold text-slate-900"
                >
                    {{ $riskLabel($actualRiskLevel) }}
                </p>
            </div>

            <div
                class="min-w-0 rounded-2xl bg-white p-4 ring-1 ring-blue-100"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-wide text-blue-600"
                >
                    Risk Gap
                </p>

                <p
                    class="mt-2 break-words font-semibold text-slate-900"
                >
                    @if ($riskGap === null)
                        Not available
                    @elseif ((int) $riskGap === 0)
                        Aligned
                    @elseif ((int) $riskGap > 0)
                        +{{ $riskGap }} level(s) aggressive
                    @else
                        {{ abs((int) $riskGap) }} level(s) conservative
                    @endif
                </p>
            </div>

            <div
                class="min-w-0 rounded-2xl bg-white p-4 ring-1 ring-blue-100"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-wide text-blue-600"
                >
                    Account Overrides
                </p>

                <p
                    class="mt-2 break-words font-semibold text-slate-900"
                >
                    {{ number_format($accountOverrideCount) }}
                </p>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <div
            class="flex items-center justify-between gap-4 text-sm"
        >
            <span class="text-slate-600">
                Investor profile completeness
            </span>

            <span class="shrink-0 font-semibold text-slate-900">
                {{ number_format($profileCompleteness * 100) }}%
            </span>
        </div>

        <div
            class="mt-2 h-2 overflow-hidden rounded-full bg-blue-100"
        >
            <div
                class="h-full rounded-full bg-blue-600 transition-all duration-300"
                style="width: {{ min(
                    100,
                    max(
                        0,
                        $profileCompleteness * 100
                    )
                ) }}%"
            ></div>
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <a
                href="{{ route('investor-profile.edit') }}"
                class="inline-flex w-full items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 sm:w-auto"
            >
                Update Investor Profile
            </a>

            <a
                href="{{ route('accounts.index') }}"
                class="inline-flex w-full items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 sm:w-auto"
            >
                Review Account Profiles
            </a>
        </div>
    </div>
</section>