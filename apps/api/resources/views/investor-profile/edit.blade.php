<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Investor suitability
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Investor Profile
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Help Helmio understand your goals, timeline, income,
                    liquidity needs, and risk tolerance so your Advisor Audit
                    can evaluate your portfolio against your personal objectives.
                </p>
            </div>

            <div
                class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.06] px-5 py-4"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400"
                >
                    Advisor Audit
                </p>

                <p
                    class="mt-1 text-sm font-semibold text-white"
                >
                    Personalized
                </p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div
            class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            @if (session('success'))
                <div
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="rounded-2xl border border-red-500/20 bg-red-500/[0.07] p-5"
                >
                    <h3 class="font-semibold text-red-300">
                        Please correct the following:
                    </h3>

                    <ul class="mt-3 space-y-1 text-sm text-slate-400">
                        @foreach ($errors->all() as $error)
                            <li>
                                • {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('investor-profile.update') }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                <input
                    type="hidden"
                    name="return_to"
                    value="{{ old(
                        'return_to',
                        $returnTo ?? request('return_to')
                    ) }}"
                >

                {{-- ================================================= --}}
                {{-- PERSONAL INFORMATION --}}
                {{-- ================================================= --}}

                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="border-b border-slate-800 px-6 py-5 sm:px-8"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Personal context
                        </p>

                        <h3
                            class="mt-2 text-lg font-semibold text-white"
                        >
                            Personal Information
                        </h3>

                        <p
                            class="mt-1 text-sm leading-6 text-slate-500"
                        >
                            Your age, employment, income, and retirement timeline
                            help Helmio evaluate whether the portfolio’s risk
                            and liquidity are appropriate.
                        </p>
                    </div>

                    <div
                        class="grid gap-6 p-6 sm:p-8 md:grid-cols-2"
                    >
                        <div>
                            <label
                                for="date_of_birth"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Date of Birth
                            </label>

                            <input
                                id="date_of_birth"
                                type="date"
                                name="date_of_birth"
                                value="{{ old(
                                    'date_of_birth',
                                    $profile->date_of_birth?->format('Y-m-d')
                                ) }}"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label
                                for="planned_retirement_age"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Planned Retirement Age
                            </label>

                            <input
                                id="planned_retirement_age"
                                type="number"
                                min="40"
                                max="90"
                                name="planned_retirement_age"
                                value="{{ old(
                                    'planned_retirement_age',
                                    $profile->planned_retirement_age
                                ) }}"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label
                                for="employment_status"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Employment Status
                            </label>

                            @php
                                $employmentOptions = [
                                    'employed_full_time' =>
                                        'Employed — Full Time',

                                    'employed_part_time' =>
                                        'Employed — Part Time',

                                    'self_employed' =>
                                        'Self-Employed',

                                    'business_owner' =>
                                        'Business Owner',

                                    'retired' =>
                                        'Retired',

                                    'semi_retired' =>
                                        'Semi-Retired',

                                    'not_employed' =>
                                        'Not Currently Employed',

                                    'student' =>
                                        'Student',

                                    'homemaker' =>
                                        'Homemaker',

                                    'other' =>
                                        'Other',
                                ];
                            @endphp

                            <select
                                id="employment_status"
                                name="employment_status"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">
                                    Select employment status
                                </option>

                                @foreach ($employmentOptions as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old(
                                                'employment_status',
                                                $profile->employment_status
                                            ) === $value
                                        )
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <p
                                class="mt-2 text-xs leading-5 text-slate-600"
                            >
                                Employment status helps Helmio evaluate
                                income stability, liquidity needs, and suitability.
                            </p>
                        </div>

                        <div>
                            <label
                                for="annual_income"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Annual Income
                            </label>

                            <div class="relative mt-2">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500"
                                >
                                    $
                                </span>

                                <input
                                    id="annual_income"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    name="annual_income"
                                    value="{{ old(
                                        'annual_income',
                                        $profile->annual_income
                                    ) }}"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 py-3 pl-8 pr-4 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </div>

                        <div>
                            <label
                                for="estimated_net_worth"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Estimated Net Worth
                            </label>

                            <div class="relative mt-2">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-500"
                                >
                                    $
                                </span>

                                <input
                                    id="estimated_net_worth"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    name="estimated_net_worth"
                                    value="{{ old(
                                        'estimated_net_worth',
                                        $profile->estimated_net_worth
                                    ) }}"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 py-3 pl-8 pr-4 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                        </div>

                        <div>
                            <label
                                for="tax_bracket"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Tax Bracket
                            </label>

                            <div class="relative mt-2">
                                <input
                                    id="tax_bracket"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    name="tax_bracket"
                                    value="{{ old(
                                        'tax_bracket',
                                        $profile->tax_bracket
                                    ) }}"
                                    class="block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 pr-10 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >

                                <span
                                    class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-slate-500"
                                >
                                    %
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ================================================= --}}
                {{-- GOALS --}}
                {{-- ================================================= --}}

                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="border-b border-slate-800 px-6 py-5 sm:px-8"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Portfolio defaults
                        </p>

                        <h3
                            class="mt-2 text-lg font-semibold text-white"
                        >
                            Investment Goals
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            These settings become the default for every
                            investment account unless that account has its
                            own override.
                        </p>
                    </div>

                    <div
                        class="grid gap-6 p-6 sm:p-8 md:grid-cols-2"
                    >
                        <div>
                            <label
                                for="primary_objective"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Primary Objective
                            </label>

                            <select
                                id="primary_objective"
                                name="primary_objective"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                                @foreach ($objectiveOptions as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old(
                                                'primary_objective',
                                                $profile->primary_objective
                                            ) === $value
                                        )
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                for="time_horizon_years"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Time Horizon (Years)
                            </label>

                            <input
                                id="time_horizon_years"
                                type="number"
                                min="1"
                                max="60"
                                name="time_horizon_years"
                                value="{{ old(
                                    'time_horizon_years',
                                    $profile->time_horizon_years
                                ) }}"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p
                                class="mt-2 text-xs leading-5 text-slate-600"
                            >
                                The expected number of years before this
                                money will be needed.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- ================================================= --}}
                {{-- RISK TOLERANCE --}}
                {{-- ================================================= --}}

                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="border-b border-slate-800 px-6 py-5 sm:px-8"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Risk profile
                        </p>

                        <h3
                            class="mt-2 text-lg font-semibold text-white"
                        >
                            Risk Tolerance
                        </h3>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            This is the overarching default risk tolerance
                            for your household portfolio.
                        </p>
                    </div>

                    <div
                        class="grid gap-4 p-6 sm:p-8"
                    >
                        @foreach ($riskOptions as $value => $label)
                            @php
                                $description = match ($value) {
                                    'conservative' =>
                                        'Prioritizes capital preservation and lower volatility, even if long-term growth is reduced.',

                                    'moderately_conservative' =>
                                        'Accepts limited market fluctuations while emphasizing stability and income.',

                                    'moderate' =>
                                        'Balances long-term growth with meaningful downside protection.',

                                    'moderately_aggressive' =>
                                        'Accepts above-average volatility in exchange for stronger long-term growth potential.',

                                    'aggressive' =>
                                        'Prioritizes long-term growth and accepts substantial short-term losses and volatility.',

                                    default =>
                                        '',
                                };

                                $selected =
                                    old(
                                        'risk_tolerance',
                                        $profile->risk_tolerance
                                    ) === $value;
                            @endphp

                            <label
                                @class([
                                    'group flex cursor-pointer items-start gap-4 rounded-2xl border p-5 transition',

                                    'border-blue-500/40 bg-blue-500/[0.08]' =>
                                        $selected,

                                    'border-slate-800 bg-slate-950 hover:border-slate-700 hover:bg-slate-900' =>
                                        ! $selected,
                                ])
                            >
                                <input
                                    type="radio"
                                    name="risk_tolerance"
                                    value="{{ $value }}"
                                    @checked($selected)
                                    class="mt-1 h-4 w-4 border-slate-600 bg-slate-950 text-blue-600 focus:ring-blue-500"
                                >

                                <div>
                                    <p
                                        @class([
                                            'text-sm font-semibold',
                                            'text-blue-300' => $selected,
                                            'text-slate-200' => ! $selected,
                                        ])
                                    >
                                        {{ $label }}
                                    </p>

                                    <p
                                        class="mt-1 text-sm leading-6 text-slate-500"
                                    >
                                        {{ $description }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- ================================================= --}}
                {{-- EXPERIENCE / LIQUIDITY --}}
                {{-- ================================================= --}}

                <div
                    class="grid gap-6 lg:grid-cols-[1fr_0.78fr]"
                >
                    <section
                        class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                    >
                        <div
                            class="border-b border-slate-800 px-6 py-5 sm:px-8"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                            >
                                Additional suitability
                            </p>

                            <h3
                                class="mt-2 text-lg font-semibold text-white"
                            >
                                Experience and Liquidity
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                These details help Helmio interpret complexity,
                                cash needs, and appropriate portfolio behavior.
                            </p>
                        </div>

                        <div class="space-y-6 p-6 sm:p-8">
                            <div>
                                <label
                                    for="investment_experience"
                                    class="block text-sm font-medium text-slate-400"
                                >
                                    Investment Experience
                                </label>

                                <select
                                    id="investment_experience"
                                    name="investment_experience"
                                    class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                                    @foreach ($experienceOptions as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                old(
                                                    'investment_experience',
                                                    $profile->investment_experience
                                                ) === $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label
                                    for="liquidity_needs"
                                    class="block text-sm font-medium text-slate-400"
                                >
                                    Liquidity Needs
                                </label>

                                <select
                                    id="liquidity_needs"
                                    name="liquidity_needs"
                                    class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                                >
                                    @foreach ($liquidityOptions as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                old(
                                                    'liquidity_needs',
                                                    $profile->liquidity_needs
                                                ) === $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                                <p
                                    class="mt-2 text-xs leading-5 text-slate-600"
                                >
                                    Higher liquidity needs may justify a
                                    larger cash allocation or lower account risk.
                                </p>
                            </div>

                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-slate-400"
                                >
                                    Additional Notes
                                </label>

                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="5"
                                    class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Upcoming major expenses, inheritance plans, pension income, health-care needs, or other relevant context..."
                                >{{ old('notes', $profile->notes) }}</textarea>
                            </div>
                        </div>
                    </section>

                    <aside class="space-y-6">
                        <section
                            class="rounded-3xl border border-blue-500/20 bg-blue-500/[0.06] p-6"
                        >
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v6m0 4h.01"
                                    />
                                </svg>
                            </div>

                            <h3
                                class="mt-5 text-base font-semibold text-white"
                            >
                                How Helmio uses this profile
                            </h3>

                            <div
                                class="mt-4 space-y-4 text-sm leading-6 text-slate-400"
                            >
                                <p>
                                    Helmio compares your portfolio’s actual
                                    risk, cash allocation, diversification,
                                    and investment behavior with your stated profile.
                                </p>

                                <p>
                                    Your overall profile becomes the default
                                    for every account. Retirement, trust,
                                    education, or short-term accounts can
                                    later override these settings.
                                </p>

                                <p>
                                    This helps distinguish between a portfolio
                                    that performs well and one that is actually
                                    suitable for your circumstances.
                                </p>
                            </div>
                        </section>

                        <section
                            class="rounded-3xl border border-violet-500/20 bg-violet-500/[0.06] p-6"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
                            >
                                Account-level suitability
                            </p>

                            <h3
                                class="mt-2 text-lg font-semibold text-white"
                            >
                                Different accounts can have different goals.
                            </h3>

                            <p
                                class="mt-3 text-sm leading-6 text-slate-400"
                            >
                                Each account can inherit this profile or use
                                a different purpose, time horizon, objective,
                                liquidity need, and risk tolerance.
                            </p>

                            <a
                                href="{{ route('accounts.index') }}"
                                class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-violet-300 transition hover:text-violet-200"
                            >
                                Review investment accounts

                                <svg
                                    class="h-4 w-4"
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
                        </section>
                    </aside>
                </div>

                {{-- ================================================= --}}
                {{-- ACTIONS --}}
                {{-- ================================================= --}}

                <div
                    class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end"
                >
                    <a
                        href="{{ $returnTo
                            ? route('onboarding.welcome')
                            : route('dashboard') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                    >
                        {{ $returnTo
                            ? 'Back'
                            : 'Cancel' }}
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        {{ $returnTo
                            ? 'Save and Continue'
                            : 'Save Investor Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>