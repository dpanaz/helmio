<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">
                    Investor Profile
                </h2>

                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                    Help Helmio understand your goals, timeline, and risk tolerance so your Advisor Audit can evaluate your portfolio against your personal objectives.
                </p>
            </div>

            <div class="rounded-xl bg-blue-50 px-4 py-3 text-right ring-1 ring-blue-100">
                <div class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                    Advisor Audit
                </div>

                <div class="mt-1 text-sm font-semibold text-slate-900">
                    Personalized
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-5">
                    <h3 class="font-semibold text-red-900">
                        Please correct the following:
                    </h3>

                    <ul class="mt-3 space-y-1 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('investor-profile.update') }}"
                class="space-y-8"
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

                <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-200 px-8 py-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Personal Information
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Your age and retirement timeline help determine whether the portfolio’s risk level is appropriate.
                        </p>
                    </div>

                    <div class="grid gap-6 p-8 md:grid-cols-2">
                        <div>
                            <label
                                for="date_of_birth"
                                class="block text-sm font-medium text-slate-700"
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
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                        </div>

                        <div>
                            <label
                                for="planned_retirement_age"
                                class="block text-sm font-medium text-slate-700"
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
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                        </div>

                        <div>
                            <label
                                for="employment_status"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Employment Status
                            </label>

                            <input
                                id="employment_status"
                                type="text"
                                name="employment_status"
                                value="{{ old(
                                    'employment_status',
                                    $profile->employment_status
                                ) }}"
                                placeholder="Employed, retired, self-employed..."
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                        </div>

                        <div>
                            <label
                                for="annual_income"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Annual Income
                            </label>

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
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                        </div>

                        <div>
                            <label
                                for="estimated_net_worth"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Estimated Net Worth
                            </label>

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
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                        </div>

                        <div>
                            <label
                                for="tax_bracket"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Tax Bracket (%)
                            </label>

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
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-200 px-8 py-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Investment Goals
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            These settings become the default for every investment account unless that account has its own override.
                        </p>
                    </div>

                    <div class="grid gap-6 p-8 md:grid-cols-2">
                        <div>
                            <label
                                for="primary_objective"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Primary Objective
                            </label>

                            <select
                                id="primary_objective"
                                name="primary_objective"
                                class="mt-2 w-full rounded-lg border-slate-300"
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
                                class="block text-sm font-medium text-slate-700"
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
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >

                            <p class="mt-1 text-xs text-slate-500">
                                The expected number of years before this money will be needed.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-200 px-8 py-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Risk Tolerance
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            This is the overarching default risk tolerance for your household portfolio.
                        </p>
                    </div>

                    <div class="space-y-4 p-8">
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
                            @endphp

                            <label class="flex cursor-pointer items-start gap-4 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50">
                                <input
                                    type="radio"
                                    name="risk_tolerance"
                                    value="{{ $value }}"
                                    @checked(
                                        old(
                                            'risk_tolerance',
                                            $profile->risk_tolerance
                                        ) === $value
                                    )
                                    class="mt-1 h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500"
                                >

                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $label }}
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-slate-500">
                                        {{ $description }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>

                <div class="grid gap-8 lg:grid-cols-[1fr_0.78fr]">
                    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                        <div class="border-b border-slate-200 px-8 py-6">
                            <h3 class="text-lg font-semibold text-slate-900">
                                Experience and Liquidity
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                These details help Helmio interpret complexity, cash needs, and appropriate portfolio behavior.
                            </p>
                        </div>

                        <div class="space-y-6 p-8">
                            <div>
                                <label
                                    for="investment_experience"
                                    class="block text-sm font-medium text-slate-700"
                                >
                                    Investment Experience
                                </label>

                                <select
                                    id="investment_experience"
                                    name="investment_experience"
                                    class="mt-2 w-full rounded-lg border-slate-300"
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
                                    class="block text-sm font-medium text-slate-700"
                                >
                                    Liquidity Needs
                                </label>

                                <select
                                    id="liquidity_needs"
                                    name="liquidity_needs"
                                    class="mt-2 w-full rounded-lg border-slate-300"
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

                                <p class="mt-1 text-xs text-slate-500">
                                    Higher liquidity needs may justify a larger cash allocation or lower account risk.
                                </p>
                            </div>

                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-slate-700"
                                >
                                    Additional Notes
                                </label>

                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="5"
                                    class="mt-2 w-full rounded-lg border-slate-300"
                                    placeholder="Upcoming major expenses, inheritance plans, pension income, health-care needs, or other relevant context..."
                                >{{ old('notes', $profile->notes) }}</textarea>
                            </div>
                        </div>
                    </section>

                    <aside class="space-y-6">
                        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-6">
                            <h3 class="text-base font-semibold text-blue-950">
                                How Helmio uses this profile
                            </h3>

                            <div class="mt-4 space-y-4 text-sm leading-6 text-blue-900">
                                <p>
                                    Helmio compares your portfolio’s actual risk, cash allocation, diversification, and investment behavior with your stated profile.
                                </p>

                                <p>
                                    Your overall profile becomes the default for every account. Retirement, trust, education, or short-term accounts can later override these settings.
                                </p>

                                <p>
                                    This allows Helmio to distinguish between a high-quality portfolio and a portfolio that is actually suitable for you.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-950 p-6 text-white">
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-300">
                                Coming next
                            </p>

                            <h3 class="mt-2 text-lg font-semibold">
                                Account-level suitability
                            </h3>

                            <p class="mt-3 text-sm leading-6 text-slate-300">
                                Each account can inherit this profile or use a different purpose, time horizon, objective, liquidity need, and risk tolerance.
                            </p>
                        </div>
                    </aside>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <a
                        href="{{ $returnTo
                            ? route('onboarding.welcome')
                            : route('dashboard') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        {{ $returnTo ? 'Back' : 'Cancel' }}
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
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