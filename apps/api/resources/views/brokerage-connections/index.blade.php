<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400">
                    Read-only account access
                </p>

                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                    Brokerage Connections
                </h2>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-400">
                    Manage imported accounts, synchronization health,
                    and provider access.
                </p>
            </div>

            <a
                href="{{ route('brokerage-connections.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 4.5v15m7.5-7.5h-15"
                    />
                </svg>

                Connect Brokerage
            </a>
        </div>
    </x-slot>

    @php
        $connectionCount =
            $connections->count();

        $activeConnectionCount =
            $connections
                ->where('status', 'active')
                ->count();

        $disconnectedConnectionCount =
            $connections
                ->whereIn('status', [
                    'disconnected',
                    'disabled',
                ])
                ->count();

        $attentionConnectionCount =
            $connections
                ->where('status', 'error')
                ->count();

        $importedAccountCount =
            $connections
                ->where('status', 'active')
                ->sum('investment_accounts_count');

        $latestSuccessfulSync =
            $connections
                ->where('status', 'active')
                ->pluck('last_successful_sync_at')
                ->filter()
                ->sortDesc()
                ->first();

        $totalImportedValue =
            $connections
                ->where('status', 'active')
                ->flatMap(
                    fn ($connection) =>
                        $connection->investmentAccounts
                )
                ->sum('current_value');
    @endphp

    <div class="min-h-screen bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.07] px-5 py-4 text-sm text-emerald-300">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m4.5 12.75 6 6 9-13.5"
                        />
                    </svg>

                    <p class="font-medium">
                        {{ session('success') }}
                    </p>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-start gap-3 rounded-2xl border border-red-500/20 bg-red-500/[0.07] px-5 py-4 text-sm text-red-300">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m9-3.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 7.5h.008v.008H12V16.5Z"
                        />
                    </svg>

                    <p class="font-medium">
                        {{ session('error') }}
                    </p>
                </div>
            @endif

            <section class="overflow-hidden rounded-3xl border border-blue-500/20 bg-slate-900 shadow-xl">
                <div class="grid gap-8 p-7 lg:grid-cols-[1.4fr_1fr] lg:p-10">
                    <div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-500/20 bg-blue-500/10 text-blue-300">
                            <svg
                                class="h-7 w-7"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3 4.5 6v5.25c0 4.64 3.125 8.872 7.5 9.75 4.375-.878 7.5-5.11 7.5-9.75V6L12 3Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m9 12 2 2 4-4"
                                />
                            </svg>
                        </div>

                        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-blue-400">
                            Secure synchronization
                        </p>

                        <h3 class="mt-3 max-w-2xl text-3xl font-semibold tracking-tight text-white">
                            Your brokerage data, connected without trading authority.
                        </h3>

                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-400">
                            Helmio imports balances, holdings, and transaction
                            activity for analysis. Connections are read-only and
                            cannot place trades, transfer funds, or modify brokerage settings.
                        </p>

                        <div class="mt-7 flex flex-wrap gap-3">
                            @foreach ([
                                'Read-only access',
                                'Secure provider connection',
                                'Automatic analytics',
                            ] as $item)
                                <span class="rounded-full border border-slate-800 bg-slate-950 px-4 py-2 text-sm text-slate-400">
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950 p-6">
                        <p class="text-sm font-medium text-slate-500">
                            Connected portfolio value
                        </p>

                        <p class="mt-3 break-words text-4xl font-semibold tracking-tight text-white">
                            ${{ number_format($totalImportedValue, 2) }}
                        </p>

                        <div class="mt-7 space-y-4 border-t border-slate-800 pt-6">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-500">
                                    Active connections
                                </span>

                                <span class="font-semibold text-white">
                                    {{ $activeConnectionCount }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-500">
                                    Imported accounts
                                </span>

                                <span class="font-semibold text-white">
                                    {{ $importedAccountCount }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-500">
                                    Latest healthy sync
                                </span>

                                <span class="text-right text-sm font-semibold text-white">
                                    {{ $latestSuccessfulSync
                                        ? $latestSuccessfulSync->diffForHumans()
                                        : 'No sync yet' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-300">
                        Active Connections
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-white">
                        {{ $activeConnectionCount }}
                    </p>
                </article>

                <article class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-300">
                        Healthy
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-white">
                        {{ $activeConnectionCount }}
                    </p>
                </article>

                <article class="rounded-2xl border border-red-500/20 bg-red-500/[0.05] p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-red-300">
                        Needs Attention
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-white">
                        {{ $attentionConnectionCount }}
                    </p>
                </article>

                <article class="rounded-2xl border border-violet-500/20 bg-violet-500/[0.05] p-6 shadow-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-violet-300">
                        Imported Accounts
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-white">
                        {{ $importedAccountCount }}
                    </p>
                </article>
            </section>

            @if ($connections->isEmpty())
                <section class="rounded-3xl border border-dashed border-slate-700 bg-slate-900 p-12 text-center shadow-xl">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-800 bg-slate-950 text-slate-500">
                        <svg
                            class="h-8 w-8"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3.75 9h16.5M5.25 9V6.75A2.25 2.25 0 0 1 7.5 4.5h9a2.25 2.25 0 0 1 2.25 2.25V9M5.25 9v10.5h13.5V9M9 13.5h6"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-xl font-semibold text-white">
                        No Brokerage Connections Yet
                    </h3>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">
                        Connect a brokerage to begin importing account balances,
                        holdings, and transactions for Helmio analysis.
                    </p>

                    <a
                        href="{{ route('brokerage-connections.create') }}"
                        class="mt-7 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        Connect Your First Brokerage
                    </a>
                </section>
            @else
                <section class="space-y-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">
                                Connected providers
                            </p>

                            <h3 class="mt-1 text-xl font-semibold text-white">
                                Connection Health & Imported Accounts
                            </h3>
                        </div>

                        <p class="text-sm text-slate-500">
                            {{ $activeConnectionCount }}
                            active connection{{ $activeConnectionCount === 1 ? '' : 's' }}
                        </p>
                    </div>

                    @foreach (
                        $connections->whereNotIn('status', [
                            'disconnected',
                            'disabled',
                        ]) as $connection
                    )
                        @php
                            $statusClasses = match ($connection->status) {
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

                            $statusDotClasses = match ($connection->status) {
                                'active' => 'bg-emerald-400',
                                'syncing' => 'bg-blue-400',
                                'error' => 'bg-red-400',
                                'disabled',
                                'disconnected' => 'bg-slate-500',
                                default => 'bg-amber-400',
                            };

                            $latestRun =
                                $connection->syncRuns->first();

                            $isStale =
                                $connection->isStale();

                            $healthStatus =
                                $connection->healthStatus();
                        @endphp

                        <article
                            id="connection-{{ $connection->id }}"
                            class="scroll-mt-24 overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                        >
                            <div class="p-6 sm:p-8">
                                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="flex min-w-0 items-start gap-4">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-slate-800 bg-slate-950 text-lg font-semibold text-white">
                                            {{ strtoupper(
                                                substr(
                                                    $connection->brokerage_name
                                                        ?: $connection->provider,
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <h4 class="text-xl font-semibold text-white">
                                                    {{ $connection->brokerage_name
                                                        ?: str($connection->provider)->title() }}
                                                </h4>

                                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                                    <span class="h-2 w-2 rounded-full {{ $statusDotClasses }}"></span>

                                                    {{ str($connection->status)->title() }}
                                                </span>

                                                @if ($connection->read_only)
                                                    <span class="rounded-full border border-blue-500/20 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300">
                                                        Read-only
                                                    </span>
                                                @endif

                                                @if (
                                                    $connection->status === 'active'
                                                    && $isStale
                                                )
                                                    <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300">
                                                        Sync may be stale
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="mt-2 text-sm text-slate-500">
                                                {{ str($connection->provider)->title() }}
                                                provider ·
                                                {{ $connection->investment_accounts_count }}
                                                imported account{{ $connection->investment_accounts_count === 1 ? '' : 's' }}
                                            </p>

                                            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-slate-600">
                                                <span>
                                                    Connected:
                                                    {{ $connection->connected_at
                                                        ? $connection->connected_at->format('M j, Y g:i A')
                                                        : 'Pending' }}
                                                </span>

                                                <span>
                                                    Last successful sync:
                                                    {{ $connection->last_successful_sync_at
                                                        ? $connection->last_successful_sync_at->diffForHumans()
                                                        : 'Never' }}
                                                </span>

                                                @if ($latestRun)
                                                    <span>
                                                        Latest duration:
                                                        {{ $latestRun->duration_ms !== null
                                                            ? number_format($latestRun->duration_ms / 1000, 2).'s'
                                                            : 'Running' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            href="{{ route('accounts.index') }}#connection-{{ $connection->id }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2.5 text-sm font-semibold text-blue-300 transition hover:bg-blue-500/15"
                                        >
                                            View Accounts
                                        </a>

                                        @if (
                                            ! in_array(
                                                $connection->status,
                                                [
                                                    'disconnected',
                                                    'disabled',
                                                ],
                                                true
                                            )
                                        )
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'brokerage-connections.sync',
                                                    $connection
                                                ) }}"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                                                >
                                                    Sync Now
                                                </button>
                                            </form>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'brokerage-connections.disconnect',
                                                    $connection
                                                ) }}"
                                                onsubmit="return confirm('Disconnect this brokerage? Imported account data will be retained.');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-400 transition hover:border-red-500/40 hover:text-red-300"
                                                >
                                                    Disconnect
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if ($connection->last_error)
                                    <div class="mt-6 rounded-2xl border border-red-500/20 bg-red-500/[0.06] p-4">
                                        <p class="font-semibold text-red-300">
                                            Synchronization Issue
                                        </p>

                                        <p class="mt-1 text-sm leading-6 text-slate-400">
                                            {{ $connection->last_error }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if ($connection->investmentAccounts->isNotEmpty())
                                <div class="border-t border-slate-800 bg-slate-950/40">
                                    <div class="border-b border-slate-800 px-6 py-4 sm:px-8">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-semibold text-white">
                                                    Imported Accounts
                                                </p>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    Accounts currently associated with this connection.
                                                </p>
                                            </div>

                                            <span class="rounded-full border border-slate-800 bg-slate-900 px-3 py-1 text-xs font-semibold text-slate-400">
                                                {{ $connection->investmentAccounts->count() }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-slate-800">
                                        @foreach ($connection->investmentAccounts as $account)
                                            <div class="flex flex-col gap-5 px-6 py-5 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                                                <div class="flex min-w-0 items-center gap-4">
                                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-slate-400">
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
                                                                d="M3.75 9h16.5M5.25 9V6.75A2.25 2.25 0 0 1 7.5 4.5h9a2.25 2.25 0 0 1 2.25 2.25V9M5.25 9v10.5h13.5V9M9 13.5h6"
                                                            />
                                                        </svg>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <a
                                                            href="{{ route(
                                                                'accounts.holdings.index',
                                                                $account
                                                            ) }}"
                                                            class="font-semibold text-white transition hover:text-blue-400"
                                                        >
                                                            {{ $account->name }}
                                                        </a>

                                                        <p class="mt-1 text-sm text-slate-500">
                                                            {{ $account->institution?->name
                                                                ?: 'Imported brokerage account' }}
                                                            ·
                                                            {{ str($account->account_type)
                                                                ->replace('_', ' ')
                                                                ->title() }}
                                                            ·••••
                                                            {{ $account->account_number_mask
                                                                ?: '----' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-5">
                                                    <div class="text-left lg:text-right">
                                                        <p class="font-semibold text-white">
                                                            ${{ number_format(
                                                                $account->current_value,
                                                                2
                                                            ) }}
                                                        </p>

                                                        <p class="mt-1 text-xs text-slate-600">
                                                            Cash:
                                                            ${{ number_format(
                                                                $account->cash_value,
                                                                2
                                                            ) }}
                                                        </p>
                                                    </div>

                                                    <a
                                                        href="{{ route(
                                                            'accounts.profile.edit',
                                                            $account
                                                        ) }}"
                                                        class="rounded-lg border border-blue-500/20 bg-blue-500/10 px-3 py-2 text-xs font-semibold text-blue-300 transition hover:bg-blue-500/15"
                                                    >
                                                        Suitability
                                                    </a>

                                                    <a
                                                        href="{{ route(
                                                            'accounts.holdings.index',
                                                            $account
                                                        ) }}"
                                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-slate-500 transition hover:border-blue-500/40 hover:text-blue-300"
                                                        aria-label="Open account"
                                                    >
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
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="border-t border-slate-800">
                                <div class="px-6 py-5 sm:px-8">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-white">
                                                Recent Sync History
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                Latest synchronization attempts for this connection.
                                            </p>
                                        </div>

                                        @if ($connection->syncRuns->isNotEmpty())
                                            <span class="w-fit rounded-full border border-slate-800 bg-slate-950 px-3 py-1 text-xs font-semibold text-slate-400">
                                                {{ $connection->syncRuns->count() }}
                                                recent
                                            </span>
                                        @endif
                                    </div>

                                    @if ($connection->syncRuns->isEmpty())
                                        <div class="mt-5 rounded-2xl border border-dashed border-slate-700 bg-slate-950 p-6 text-center">
                                            <p class="font-medium text-white">
                                                No Sync History Yet
                                            </p>

                                            <p class="mt-2 text-sm text-slate-500">
                                                Run the first synchronization to create a health record.
                                            </p>
                                        </div>
                                    @else
                                        <div class="mt-5 space-y-3">
                                            @foreach ($connection->syncRuns as $run)
                                                @php
                                                    $syncStatusClasses = match ($run->status) {
                                                        'success' =>
                                                            'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

                                                        'failed' =>
                                                            'border-red-500/20 bg-red-500/10 text-red-300',

                                                        default =>
                                                            'border-blue-500/20 bg-blue-500/10 text-blue-300',
                                                    };
                                                @endphp

                                                <div class="flex flex-col gap-5 rounded-2xl border border-slate-800 bg-slate-950 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                                    <div>
                                                        <div class="flex flex-wrap items-center gap-3">
                                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $syncStatusClasses }}">
                                                                {{ str($run->status)->title() }}
                                                            </span>

                                                            <span class="text-sm font-medium text-white">
                                                                {{ $run->started_at->format(
                                                                    'M j, Y g:i A'
                                                                ) }}
                                                            </span>
                                                        </div>

                                                        <p class="mt-2 text-sm text-slate-500">
                                                            {{ $run->accounts_imported }}
                                                            account{{ $run->accounts_imported === 1 ? '' : 's' }}
                                                            ·
                                                            {{ $run->positions_imported }}
                                                            holding{{ $run->positions_imported === 1 ? '' : 's' }}
                                                            ·
                                                            {{ $run->transactions_imported }}
                                                            transaction{{ $run->transactions_imported === 1 ? '' : 's' }}
                                                        </p>

                                                        @if ($run->error_message)
                                                            <div class="mt-3 rounded-xl border border-red-500/20 bg-red-500/[0.06] px-4 py-3 text-sm text-red-300">
                                                                {{ $run->error_message }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="text-left sm:text-right">
                                                        <p class="text-sm font-semibold text-white">
                                                            @if ($run->duration_ms !== null)
                                                                {{ number_format(
                                                                    $run->duration_ms / 1000,
                                                                    2
                                                                ) }}s
                                                            @else
                                                                Running
                                                            @endif
                                                        </p>

                                                        <p class="mt-1 text-xs text-slate-600">
                                                            Duration
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif

            <section class="rounded-3xl border border-slate-800 bg-slate-900 p-7 shadow-xl">
                <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <p class="text-sm font-semibold text-white">
                            About Connection Freshness
                        </p>

                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                            Helmio records the provider sync time separately
                            from the date of the underlying brokerage data.
                            A successful sync confirms that Helmio completed
                            the import, but some providers may return cached
                            or delayed balances and transactions.
                        </p>
                    </div>

                    <a
                        href="{{ route('accounts.index') }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-blue-400 transition hover:text-blue-300"
                    >
                        View All Accounts

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
                </div>
            </section>
        </div>
    </div>
</x-app-layout>