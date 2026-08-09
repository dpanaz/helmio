<x-app-layout>
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                >
                    Portfolio accounts
                </p>

                <h2
                    class="mt-2 text-2xl font-semibold tracking-tight text-white"
                >
                    Investment Accounts
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-400"
                >
                    Review each account and the brokerage connection that
                    supplies its portfolio data.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('brokerage-connections.index') }}"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                >
                    Brokerage Connections
                </a>

                <a
                    href="{{ route('investor-profile.edit') }}"
                    class="rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2.5 text-sm font-semibold text-blue-300 transition hover:bg-blue-500/15"
                >
                    Investor Profile
                </a>

                <a
                    href="{{ route('accounts.create') }}"
                    class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                >
                    Add Account
                </a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div
                    class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm font-medium text-emerald-300"
                >
                    {{ session('success') }}
                </div>
            @endif

            <section class="grid gap-4 sm:grid-cols-3">
                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Total value
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        ${{ number_format($totalValue, 2) }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-6"
                >
                    <p class="text-sm text-blue-300">
                        Cash
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        ${{ number_format($totalCash, 2) }}
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                >
                    <p class="text-sm text-slate-500">
                        Investment accounts
                    </p>

                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight text-white"
                    >
                        {{ $accounts->count() }}
                    </p>
                </article>
            </section>

            @if ($accounts->isEmpty())
                <section
                    class="rounded-3xl border border-dashed border-slate-700 bg-slate-900 p-12 text-center"
                >
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10 text-xl font-bold text-blue-300"
                    >
                        H
                    </div>

                    <h3
                        class="mt-5 text-xl font-semibold text-white"
                    >
                        Add your first investment account
                    </h3>

                    <p
                        class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500"
                    >
                        Connect a brokerage for automatic read-only
                        synchronization or add a manual account.
                    </p>

                    <div
                        class="mt-7 flex flex-wrap justify-center gap-3"
                    >
                        <a
                            href="{{ route('brokerage-connections.create') }}"
                            class="inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500"
                        >
                            Connect Brokerage
                        </a>

                        <a
                            href="{{ route('accounts.create') }}"
                            class="inline-flex rounded-xl border border-slate-700 bg-slate-950 px-5 py-3 font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                        >
                            Add Manually
                        </a>
                    </div>
                </section>
            @else
                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="border-b border-slate-800 px-6 py-5"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h3 class="font-semibold text-white">
                                    Your accounts
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Brokerage connection actions and account
                                    analytics are kept separate.
                                </p>
                            </div>

                            <span
                                class="inline-flex w-fit rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
                            >
                                Investor Profile supplies defaults
                            </span>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-800">
                        @foreach ($accounts as $account)
                            @php
                                $profile =
                                    $account->profile;

                                $connection =
                                    $account->brokerageConnection;

                                $usesOverrides =
                                    $profile?->risk_tolerance_override
                                    || $profile?->objective_override
                                    || $profile?->time_horizon_years_override
                                    || $profile?->liquidity_needs_override;

                                $purposeLabel =
                                    $profile?->purpose
                                        ? str($profile->purpose)
                                            ->replace('_', ' ')
                                            ->title()
                                        : 'Not specified';

                                $connectionStatus =
                                    $connection?->status;

                                $connectionBadgeClasses =
                                    match ($connectionStatus) {
                                        'active' =>
                                            'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

                                        'syncing' =>
                                            'border-blue-500/20 bg-blue-500/10 text-blue-300',

                                        'error' =>
                                            'border-red-500/20 bg-red-500/10 text-red-300',

                                        'disabled',
                                        'disconnected' =>
                                            'border-slate-700 bg-slate-800 text-slate-400',

                                        default =>
                                            'border-amber-500/20 bg-amber-500/10 text-amber-300',
                                    };
                            @endphp

                            <article
                                id="account-{{ $account->id }}"
                                class="scroll-mt-24 px-6 py-6 transition hover:bg-slate-800/20"
                            >
                                <div
                                    class="flex flex-col gap-7 lg:flex-row lg:items-start lg:justify-between"
                                >
                                    <div
                                        class="flex min-w-0 items-start gap-4"
                                    >
                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-700 bg-slate-950 font-semibold text-slate-300"
                                        >
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

                                        <div class="min-w-0">
                                            <a
                                                href="{{ route(
                                                    'accounts.holdings.index',
                                                    $account
                                                ) }}"
                                                class="text-lg font-semibold text-white transition hover:text-blue-400"
                                            >
                                                {{ $account->name }}
                                            </a>

                                            <p
                                                class="mt-1 text-sm text-slate-500"
                                            >
                                                {{ $account->institution?->name
                                                    ?? 'Manual account' }}

                                                <span class="mx-1 text-slate-700">
                                                    •
                                                </span>

                                                {{ str($account->account_type)
                                                    ->replace('_', ' ')
                                                    ->title() }}

                                                @if ($account->account_number_mask)
                                                    <span class="mx-1 text-slate-700">
                                                        •
                                                    </span>

                                                    ••••
                                                    {{ $account->account_number_mask }}
                                                @endif
                                            </p>

                                            <div
                                                class="mt-4 flex flex-wrap items-center gap-2"
                                            >
                                                @if ($connection)
                                                    <a
                                                        href="{{ route(
                                                            'brokerage-connections.index'
                                                        ) }}#connection-{{ $connection->id }}"
                                                        class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-950 px-3 py-1 text-xs font-semibold text-slate-300 transition hover:border-blue-500/50 hover:text-white"
                                                    >
                                                        {{ $connection->brokerage_name
                                                            ?: str($connection->provider)->title() }}

                                                        <span
                                                            class="h-1.5 w-1.5 rounded-full bg-current opacity-60"
                                                        ></span>

                                                        {{ str($connection->provider)->title() }}
                                                    </a>

                                                    <span
                                                        class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $connectionBadgeClasses }}"
                                                    >
                                                        {{ str($connectionStatus)
                                                            ->replace('_', ' ')
                                                            ->title() }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-400"
                                                    >
                                                        Manual account
                                                    </span>
                                                @endif

                                                <span
                                                    @class([
                                                        'inline-flex rounded-full border px-3 py-1 text-xs font-semibold',

                                                        'border-violet-500/20 bg-violet-500/10 text-violet-300' =>
                                                            $usesOverrides,

                                                        'border-slate-700 bg-slate-800 text-slate-400' =>
                                                            ! $usesOverrides,
                                                    ])
                                                >
                                                    {{ $usesOverrides
                                                        ? 'Uses account overrides'
                                                        : 'Inherits investor profile' }}
                                                </span>

                                                <span
                                                    class="inline-flex rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
                                                >
                                                    Purpose:
                                                    {{ $purposeLabel }}
                                                </span>
                                            </div>

                                            @if ($connection)
                                                <p
                                                    class="mt-3 text-xs text-slate-600"
                                                >
                                                    Last successful connection sync:
                                                    {{ $connection->last_successful_sync_at
                                                        ? $connection->last_successful_sync_at->diffForHumans()
                                                        : 'Never' }}
                                                </p>
                                            @endif

                                            <div
                                                class="mt-5 flex flex-wrap gap-2"
                                            >
                                                <a
                                                    href="{{ route(
                                                        'accounts.holdings.index',
                                                        $account
                                                    ) }}"
                                                    class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-medium text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                                                >
                                                    Holdings
                                                </a>

                                                <a
                                                    href="{{ route(
                                                        'accounts.transactions.index',
                                                        $account
                                                    ) }}"
                                                    class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-medium text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                                                >
                                                    Transactions
                                                </a>

                                                <a
                                                    href="{{ route(
                                                        'accounts.performance-data.index',
                                                        $account
                                                    ) }}"
                                                    class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-medium text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                                                >
                                                    Performance
                                                </a>

                                                <a
                                                    href="{{ route(
                                                        'accounts.profile.edit',
                                                        $account
                                                    ) }}"
                                                    class="rounded-xl border border-blue-500/20 bg-blue-500/10 px-3 py-2 text-sm font-semibold text-blue-300 transition hover:bg-blue-500/15"
                                                >
                                                    Suitability Profile
                                                </a>

                                                @if ($connection)
                                                    <a
                                                        href="{{ route(
                                                            'brokerage-connections.index'
                                                        ) }}#connection-{{ $connection->id }}"
                                                        class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-medium text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                                                    >
                                                        Connection Details
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="shrink-0 rounded-2xl border border-slate-800 bg-slate-950 p-5 lg:min-w-60 lg:text-right"
                                    >
                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-slate-600"
                                        >
                                            Current value
                                        </p>

                                        <div
                                            class="mt-2 text-3xl font-semibold tracking-tight text-white"
                                        >
                                            ${{ number_format(
                                                (float) $account->current_value,
                                                2
                                            ) }}
                                        </div>

                                        <div
                                            class="mt-3 text-sm text-slate-500"
                                        >
                                            Cash:
                                            <span
                                                class="font-medium text-slate-300"
                                            >
                                                ${{ number_format(
                                                    (float) $account->cash_value,
                                                    2
                                                ) }}
                                            </span>
                                        </div>

                                        @if ($account->provider_synced_at)
                                            <p
                                                class="mt-3 text-xs text-slate-600"
                                            >
                                                Account data updated
                                                {{ $account->provider_synced_at->diffForHumans() }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>