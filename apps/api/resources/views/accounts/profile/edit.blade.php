<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                    Account Suitability
                </p>

                <h2 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ $account->name }}
                </h2>

                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                    Set the purpose and risk profile for this account. Leave an override blank to inherit the investor-level default.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('investor-profile.edit') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    Investor Profile
                </a>

                <a
                    href="{{ route('accounts.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    Back to Accounts
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $householdRisk =
            $investorProfile?->risk_tolerance;

        $householdObjective =
            $investorProfile?->primary_objective;

        $householdTimeHorizon =
            $investorProfile?->time_horizon_years;

        $householdLiquidity =
            $investorProfile?->liquidity_needs;

        $effectiveRisk =
            old(
                'risk_tolerance_override',
                $profile->risk_tolerance_override
            )
            ?: $householdRisk;

        $effectiveObjective =
            old(
                'objective_override',
                $profile->objective_override
            )
            ?: $householdObjective;

        $effectiveTimeHorizon =
            old(
                'time_horizon_years_override',
                $profile->time_horizon_years_override
            )
            ?: $householdTimeHorizon;

        $effectiveLiquidity =
            old(
                'liquidity_needs_override',
                $profile->liquidity_needs_override
            )
            ?: $householdLiquidity;
    @endphp

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

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Institution
                    </p>

                    <p class="mt-2 text-sm font-semibold text-slate-900">
                        {{ $account->institution?->name
                            ?? 'Manual account' }}
                    </p>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Account type
                    </p>

                    <p class="mt-2 text-sm font-semibold text-slate-900">
                        {{ str(
                            $account->account_type
                            ?? 'investment'
                        )
                            ->replace('_', ' ')
                            ->title() }}
                    </p>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Current value
                    </p>

                    <p class="mt-2 text-sm font-semibold text-slate-900">
                        {{ money(
                            $account->current_value
                            ?? 0
                        ) }}
                    </p>
                </div>

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Suitability mode
                    </p>

                    <p class="mt-2 text-sm font-semibold text-slate-900">
                        {{ (
                            $profile->risk_tolerance_override
                            || $profile->objective_override
                            || $profile->time_horizon_years_override
                            || $profile->liquidity_needs_override
                        )
                            ? 'Account overrides'
                            : 'Investor defaults' }}
                    </p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'accounts.profile.update',
                    $account
                ) }}"
                class="space-y-8"
            >
                @csrf
                @method('PUT')

                <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-200 px-8 py-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Account Purpose
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            The purpose and target date help Helmio judge whether this account’s allocation and liquidity are appropriate.
                        </p>
                    </div>

                    <div class="grid gap-6 p-8 md:grid-cols-2">
                        <div>
                            <label
                                for="purpose"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Account Purpose
                            </label>

                            <select
                                id="purpose"
                                name="purpose"
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    Not specified
                                </option>

                                @foreach ($purposeOptions as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old(
                                                'purpose',
                                                $profile->purpose
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
                                for="target_date"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Target Date
                            </label>

                            <input
                                id="target_date"
                                type="date"
                                name="target_date"
                                value="{{ old(
                                    'target_date',
                                    $profile->target_date?->format('Y-m-d')
                                ) }}"
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >

                            <p class="mt-1 text-xs text-slate-500">
                                Optional date when the account is expected to begin funding its intended goal.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-200 px-8 py-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Account Overrides
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Leave any field blank to inherit the investor-level profile.
                        </p>
                    </div>

                    <div class="grid gap-6 p-8 md:grid-cols-2">
                        <div>
                            <label
                                for="risk_tolerance_override"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Risk Tolerance
                            </label>

                            <select
                                id="risk_tolerance_override"
                                name="risk_tolerance_override"
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    Inherit:
                                    {{ $householdRisk
                                        ? (
                                            $riskOptions[
                                                $householdRisk
                                            ] ?? str($householdRisk)
                                                ->replace('_', ' ')
                                                ->title()
                                        )
                                        : 'Not set' }}
                                </option>

                                @foreach ($riskOptions as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old(
                                                'risk_tolerance_override',
                                                $profile->risk_tolerance_override
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
                                for="objective_override"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Investment Objective
                            </label>

                            <select
                                id="objective_override"
                                name="objective_override"
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    Inherit:
                                    {{ $householdObjective
                                        ? (
                                            $objectiveOptions[
                                                $householdObjective
                                            ] ?? str($householdObjective)
                                                ->replace('_', ' ')
                                                ->title()
                                        )
                                        : 'Not set' }}
                                </option>

                                @foreach ($objectiveOptions as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old(
                                                'objective_override',
                                                $profile->objective_override
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
                                for="time_horizon_years_override"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Time Horizon (Years)
                            </label>

                            <input
                                id="time_horizon_years_override"
                                type="number"
                                min="1"
                                max="60"
                                name="time_horizon_years_override"
                                value="{{ old(
                                    'time_horizon_years_override',
                                    $profile->time_horizon_years_override
                                ) }}"
                                placeholder="{{ $householdTimeHorizon
                                    ? 'Inherit: '
                                        .$householdTimeHorizon
                                        .' years'
                                    : 'Not set' }}"
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                        </div>

                        <div>
                            <label
                                for="liquidity_needs_override"
                                class="block text-sm font-medium text-slate-700"
                            >
                                Liquidity Needs
                            </label>

                            <select
                                id="liquidity_needs_override"
                                name="liquidity_needs_override"
                                class="mt-2 w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    Inherit:
                                    {{ $householdLiquidity
                                        ? (
                                            $liquidityOptions[
                                                $householdLiquidity
                                            ] ?? str($householdLiquidity)
                                                ->replace('_', ' ')
                                                ->title()
                                        )
                                        : 'Not set' }}
                                </option>

                                @foreach ($liquidityOptions as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old(
                                                'liquidity_needs_override',
                                                $profile->liquidity_needs_override
                                            ) === $value
                                        )
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-blue-200 bg-blue-50 p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-blue-950">
                                Effective Suitability Profile
                            </h3>

                            <p class="mt-1 max-w-2xl text-sm leading-6 text-blue-800">
                                This is the profile Helmio will use when evaluating this account.
                            </p>
                        </div>

                        <span class="inline-flex w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold text-blue-800 ring-1 ring-blue-200">
                            {{ (
                                $profile->risk_tolerance_override
                                || $profile->objective_override
                                || $profile->time_horizon_years_override
                                || $profile->liquidity_needs_override
                            )
                                ? 'Uses overrides'
                                : 'Fully inherited' }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg bg-white p-4 ring-1 ring-blue-100">
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                                Risk
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $effectiveRisk
                                    ? (
                                        $riskOptions[
                                            $effectiveRisk
                                        ] ?? str($effectiveRisk)
                                            ->replace('_', ' ')
                                            ->title()
                                    )
                                    : 'Not set' }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-white p-4 ring-1 ring-blue-100">
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                                Objective
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $effectiveObjective
                                    ? (
                                        $objectiveOptions[
                                            $effectiveObjective
                                        ] ?? str($effectiveObjective)
                                            ->replace('_', ' ')
                                            ->title()
                                    )
                                    : 'Not set' }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-white p-4 ring-1 ring-blue-100">
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                                Time Horizon
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $effectiveTimeHorizon
                                    ? $effectiveTimeHorizon
                                        .' years'
                                    : 'Not set' }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-white p-4 ring-1 ring-blue-100">
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                                Liquidity
                            </p>

                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $effectiveLiquidity
                                    ? (
                                        $liquidityOptions[
                                            $effectiveLiquidity
                                        ] ?? str($effectiveLiquidity)
                                            ->replace('_', ' ')
                                            ->title()
                                    )
                                    : 'Not set' }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-200 px-8 py-6">
                        <h3 class="text-lg font-semibold text-slate-900">
                            Account Notes
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Add context that may affect how Helmio interprets this account.
                        </p>
                    </div>

                    <div class="p-8">
                        <label
                            for="notes"
                            class="block text-sm font-medium text-slate-700"
                        >
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="6"
                            class="mt-2 w-full rounded-lg border-slate-300"
                            placeholder="Examples: pension income covers living expenses, account is reserved for a home purchase, trust requires income distributions..."
                        >{{ old('notes', $profile->notes) }}</textarea>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <a
                        href="{{ route('accounts.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    >
                        Save Account Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>