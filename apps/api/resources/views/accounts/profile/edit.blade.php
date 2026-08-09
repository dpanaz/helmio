<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Account suitability
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    {{ $account->name }}
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Set the purpose and risk profile for this account.
                    Leave an override blank to inherit the investor-level default.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('investor-profile.edit') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                >
                    Investor Profile
                </a>

                <a
                    href="{{ route('accounts.index') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
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

        $usesOverrides =
            $profile->risk_tolerance_override
            || $profile->objective_override
            || $profile->time_horizon_years_override
            || $profile->liquidity_needs_override;
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

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
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    [
                        'label' => 'Institution',
                        'value' => $account->institution?->name ?? 'Manual account',
                    ],
                    [
                        'label' => 'Account type',
                        'value' => str(
                            $account->account_type ?? 'investment'
                        )->replace('_', ' ')->title(),
                    ],
                    [
                        'label' => 'Current value',
                        'value' => money(
                            $account->current_value ?? 0
                        ),
                    ],
                    [
                        'label' => 'Suitability mode',
                        'value' => $usesOverrides
                            ? 'Account overrides'
                            : 'Investor defaults',
                    ],
                ] as $item)
                    <article
                        class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                        >
                            {{ $item['label'] }}
                        </p>

                        <p
                            class="mt-2 text-sm font-semibold text-white"
                        >
                            {{ $item['value'] }}
                        </p>
                    </article>
                @endforeach
            </section>

            <form
                method="POST"
                action="{{ route(
                    'accounts.profile.update',
                    $account
                ) }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="border-b border-slate-800 px-6 py-5 sm:px-8"
                    >
                        <h3 class="text-lg font-semibold text-white">
                            Account Purpose
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            The purpose and target date help Helmio evaluate
                            whether this account’s allocation and liquidity
                            are appropriate.
                        </p>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 md:grid-cols-2">
                        <div>
                            <label
                                for="purpose"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Account Purpose
                            </label>

                            <select
                                id="purpose"
                                name="purpose"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
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
                                class="block text-sm font-medium text-slate-400"
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
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >

                            <p class="mt-2 text-xs leading-5 text-slate-600">
                                Optional date when the account is expected
                                to begin funding its intended goal.
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="border-b border-slate-800 px-6 py-5 sm:px-8"
                    >
                        <h3 class="text-lg font-semibold text-white">
                            Account Overrides
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Leave any field blank to inherit the investor-level profile.
                        </p>
                    </div>

                    <div class="grid gap-6 p-6 sm:p-8 md:grid-cols-2">
                        <div>
                            <label
                                for="risk_tolerance_override"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Risk Tolerance
                            </label>

                            <select
                                id="risk_tolerance_override"
                                name="risk_tolerance_override"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">
                                    Inherit:
                                    {{ $householdRisk
                                        ? (
                                            $riskOptions[$householdRisk]
                                            ?? str($householdRisk)
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
                                class="block text-sm font-medium text-slate-400"
                            >
                                Investment Objective
                            </label>

                            <select
                                id="objective_override"
                                name="objective_override"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">
                                    Inherit:
                                    {{ $householdObjective
                                        ? (
                                            $objectiveOptions[$householdObjective]
                                            ?? str($householdObjective)
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
                                class="block text-sm font-medium text-slate-400"
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
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <div>
                            <label
                                for="liquidity_needs_override"
                                class="block text-sm font-medium text-slate-400"
                            >
                                Liquidity Needs
                            </label>

                            <select
                                id="liquidity_needs_override"
                                name="liquidity_needs_override"
                                class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">
                                    Inherit:
                                    {{ $householdLiquidity
                                        ? (
                                            $liquidityOptions[$householdLiquidity]
                                            ?? str($householdLiquidity)
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

                <section
                    class="rounded-3xl border border-blue-500/20 bg-blue-500/[0.06] p-6 sm:p-8"
                >
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                            >
                                Effective profile
                            </p>

                            <h3
                                class="mt-2 text-lg font-semibold text-white"
                            >
                                Effective Suitability Profile
                            </h3>

                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-slate-400"
                            >
                                This is the profile Helmio will use when
                                evaluating this account.
                            </p>
                        </div>

                        <span
                            class="inline-flex w-fit rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
                        >
                            {{ $usesOverrides
                                ? 'Uses overrides'
                                : 'Fully inherited' }}
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                            [
                                'label' => 'Risk',
                                'value' => $effectiveRisk
                                    ? (
                                        $riskOptions[$effectiveRisk]
                                        ?? str($effectiveRisk)
                                            ->replace('_', ' ')
                                            ->title()
                                    )
                                    : 'Not set',
                            ],
                            [
                                'label' => 'Objective',
                                'value' => $effectiveObjective
                                    ? (
                                        $objectiveOptions[$effectiveObjective]
                                        ?? str($effectiveObjective)
                                            ->replace('_', ' ')
                                            ->title()
                                    )
                                    : 'Not set',
                            ],
                            [
                                'label' => 'Time Horizon',
                                'value' => $effectiveTimeHorizon
                                    ? $effectiveTimeHorizon.' years'
                                    : 'Not set',
                            ],
                            [
                                'label' => 'Liquidity',
                                'value' => $effectiveLiquidity
                                    ? (
                                        $liquidityOptions[$effectiveLiquidity]
                                        ?? str($effectiveLiquidity)
                                            ->replace('_', ' ')
                                            ->title()
                                    )
                                    : 'Not set',
                            ],
                        ] as $item)
                            <div
                                class="rounded-2xl border border-blue-500/10 bg-slate-950/60 p-5"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-wide text-blue-400"
                                >
                                    {{ $item['label'] }}
                                </p>

                                <p
                                    class="mt-2 text-sm font-semibold text-white"
                                >
                                    {{ $item['value'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="border-b border-slate-800 px-6 py-5 sm:px-8"
                    >
                        <h3 class="text-lg font-semibold text-white">
                            Account Notes
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Add context that may affect how Helmio interprets this account.
                        </p>
                    </div>

                    <div class="p-6 sm:p-8">
                        <label
                            for="notes"
                            class="block text-sm font-medium text-slate-400"
                        >
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="6"
                            class="mt-2 block w-full rounded-xl border-slate-700 bg-slate-950 px-4 py-3 text-slate-200 placeholder-slate-600 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Examples: pension income covers living expenses, account is reserved for a home purchase, trust requires income distributions..."
                        >{{ old('notes', $profile->notes) }}</textarea>
                    </div>
                </section>

                <div
                    class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end"
                >
                    <a
                        href="{{ route('accounts.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-900 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        Save Account Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>