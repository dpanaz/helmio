<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Portfolio accounts
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Investment accounts
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Review each account and the brokerage connection that supplies its data.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('brokerage-connections.index') }}"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Brokerage Connections
                </a>

                <a
                    href="{{ route('investor-profile.edit') }}"
                    class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                >
                    Investor Profile
                </a>

                <a
                    href="{{ route('accounts.create') }}"
                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
                >
                    Add Account
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <section class="grid gap-5 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Total value
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($totalValue, 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Cash
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($totalCash, 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Investment accounts
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $accounts->count() }}
                    </p>
                </div>
            </section>

            @if ($accounts->isEmpty())
                <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-xl font-bold text-blue-700">
                        H
                    </div>

                    <h3 class="mt-5 text-xl font-semibold text-slate-900">
                        Add your first investment account
                    </h3>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-600">
                        Connect a brokerage for automatic read-only synchronization or add a manual account.
                    </p>

                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a
                            href="{{ route('brokerage-connections.create') }}"
                            class="inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                        >
                            Connect Brokerage
                        </a>

                        <a
                            href="{{ route('accounts.create') }}"
                            class="inline-flex rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Add Manually
                        </a>
                    </div>
                </section>
            @else
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-900">
                                    Your accounts
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Brokerage connection actions and account analytics are kept separate.
                                </p>
                            </div>

                            <span class="inline-flex w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                                Overall defaults come from Investor Profile
                            </span>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach ($accounts as $account)
                            @php
                                $profile = $account->profile;
                                $connection = $account->brokerageConnection;

                                $usesOverrides =
                                    $profile?->risk_tolerance_override
                                    || $profile?->objective_override
                                    || $profile?->time_horizon_years_override
                                    || $profile?->liquidity_needs_override;

                                $purposeLabel = $profile?->purpose
                                    ? str($profile->purpose)
                                        ->replace('_', ' ')
                                        ->title()
                                    : 'Not specified';

                                $connectionStatus = $connection?->status;

                                $connectionBadgeClasses = match ($connectionStatus) {
                                    'active' => 'bg-emerald-100 text-emerald-700',
                                    'syncing' => 'bg-blue-100 text-blue-700',
                                    'error' => 'bg-red-100 text-red-700',
                                    'disabled',
                                    'disconnected' => 'bg-slate-100 text-slate-600',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp

                            <article
                                id="account-{{ $account->id }}"
                                class="scroll-mt-24 flex flex-col gap-6 px-6 py-6 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 font-semibold text-slate-700">
                                        {{ strtoupper(
                                            substr(
                                                $account->institution?->name
                                                    ?? $connection?->brokerage_name
                                                    ?? 'H',
                                                0,
                                                1
                                            )
                                        ) }}
                                    </div>

                                    <div>
                                        <a
                                            href="{{ route(
                                                'accounts.holdings.index',
                                                $account
                                            ) }}"
                                            class="text-lg font-semibold text-slate-900 hover:text-blue-600"
                                        >
                                            {{ $account->name }}
                                        </a>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $account->institution?->name ?? 'Manual account' }}
                                            •
                                            {{ str($account->account_type)
                                                ->replace('_', ' ')
                                                ->title() }}

                                            @if ($account->account_number_mask)
                                                •••• {{ $account->account_number_mask }}
                                            @endif
                                        </p>

                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            @if ($connection)
                                                <a
                                                    href="{{ route('brokerage-connections.index') }}#connection-{{ $connection->id }}"
                                                    class="inline-flex items-center gap-2 rounded-full bg-slate-950 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-800"
                                                >
                                                    {{ $connection->brokerage_name
                                                        ?: str($connection->provider)->title() }}

                                                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>

                                                    {{ str($connection->provider)->title() }}
                                                </a>

                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $connectionBadgeClasses }}">
                                                    {{ str($connectionStatus)->title() }}
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                    Manual account
                                                </span>
                                            @endif

                                            <span
                                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                                    {{ $usesOverrides
                                                        ? 'bg-violet-100 text-violet-700'
                                                        : 'bg-slate-100 text-slate-700' }}"
                                            >
                                                {{ $usesOverrides
                                                    ? 'Uses account overrides'
                                                    : 'Inherits investor profile' }}
                                            </span>

                                            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                                Purpose: {{ $purposeLabel }}
                                            </span>
                                        </div>

                                        @if ($connection)
                                            <p class="mt-3 text-xs text-slate-400">
                                                Last successful connection sync:
                                                {{ $connection->last_successful_sync_at
                                                    ? $connection->last_successful_sync_at->diffForHumans()
                                                    : 'Never' }}
                                            </p>
                                        @endif

                                        <div class="mt-4 flex flex-wrap gap-3">
                                            <a
                                                href="{{ route('accounts.holdings.index', $account) }}"
                                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                                            >
                                                Holdings
                                            </a>

                                            <a
                                                href="{{ route('accounts.transactions.index', $account) }}"
                                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                                            >
                                                Transactions
                                            </a>

                                            <a
                                                href="{{ route('accounts.performance-data.index', $account) }}"
                                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                                            >
                                                Performance
                                            </a>

                                            <a
                                                href="{{ route('accounts.profile.edit', $account) }}"
                                                class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                                            >
                                                Suitability Profile
                                            </a>

                                            @if ($connection)
                                                <a
                                                    href="{{ route('brokerage-connections.index') }}#connection-{{ $connection->id }}"
                                                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                                                >
                                                    Connection Details
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="text-left lg:text-right">
                                    <div class="text-3xl font-bold text-slate-900">
                                        ${{ number_format(
                                            (float) $account->current_value,
                                            2
                                        ) }}
                                    </div>

                                    <div class="mt-2 text-sm text-slate-500">
                                        Cash:
                                        ${{ number_format(
                                            (float) $account->cash_value,
                                            2
                                        ) }}
                                    </div>

                                    @if ($account->provider_synced_at)
                                        <p class="mt-2 text-xs text-slate-400">
                                            Account data updated
                                            {{ $account->provider_synced_at->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>