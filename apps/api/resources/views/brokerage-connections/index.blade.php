<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Read-only account access
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Brokerage connections
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Manage imported accounts, synchronization health and provider access.
                </p>
            </div>

            <a
                href="{{ route('brokerage-connections.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500"
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

                Connect brokerage
            </a>
        </div>
    </x-slot>

    @php
    $connectionCount = $connections->count();

    $activeConnectionCount = $connections
        ->where('status', 'active')
        ->count();

    $disconnectedConnectionCount = $connections
        ->whereIn('status', [
            'disconnected',
            'disabled',
        ])
        ->count();

    $attentionConnectionCount = $connections
        ->where('status', 'error')
        ->count();

    $importedAccountCount = $connections
        ->where('status', 'active')
        ->sum('investment_accounts_count');

    $latestSuccessfulSync = $connections
        ->where('status', 'active')
        ->pluck('last_successful_sync_at')
        ->filter()
        ->sortDesc()
        ->first();

    $totalImportedValue = $connections
        ->where('status', 'active')
        ->flatMap(
            fn ($connection) =>
                $connection->investmentAccounts
        )
        ->sum('current_value');
@endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
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
                <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
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

            <section class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-xl">
                <div class="grid gap-8 p-8 lg:grid-cols-[1.4fr_1fr] lg:p-10">
                    <div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/20 text-blue-300">
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

                        <p class="mt-6 text-sm font-semibold uppercase tracking-[0.18em] text-blue-300">
                            Secure synchronization
                        </p>

                        <h3 class="mt-3 max-w-2xl text-3xl font-semibold tracking-tight">
                            Your brokerage data, connected without trading authority.
                        </h3>

                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                            Helmio imports balances, holdings and transaction activity for
                            analysis. Connections are read-only and cannot place trades,
                            transfer funds or modify brokerage settings.
                        </p>

                        <div class="mt-7 flex flex-wrap gap-3">
                            <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200">
                                Read-only access
                            </div>

                            <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200">
                                Encrypted credentials
                            </div>

                            <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200">
                                Automatic analytics
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <p class="text-sm font-medium text-slate-400">
                            Connected portfolio value
                        </p>

                        <p class="mt-3 text-4xl font-semibold tracking-tight">
                            ${{ number_format($totalImportedValue, 2) }}
                        </p>

                        <div class="mt-7 space-y-4 border-t border-white/10 pt-6">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-400">
                                    Active connections
                                </span>

                                <span class="font-semibold">
                                    {{ $activeConnectionCount }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-400">
                                    Imported accounts
                                </span>

                                <span class="font-semibold">
                                    {{ $importedAccountCount }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-slate-400">
                                    Latest healthy sync
                                </span>

                                <span class="text-right text-sm font-semibold">
                                    {{ $latestSuccessfulSync
                                        ? $latestSuccessfulSync->diffForHumans()
                                        : 'No sync yet' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Active connections
                            </p>

                            <p class="mt-3 text-3xl font-semibold text-slate-900">
                                {{ $activeConnectionCount }}
                            </p>
                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8.25 12.75 10.5 15l5.25-6m-9-4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 6.75 4.5Z"
                                />
                            </svg>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Healthy
                            </p>

                            <p class="mt-3 text-3xl font-semibold text-emerald-700">
                                {{ $activeConnectionCount }}
                            </p>
                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m4.5 12.75 6 6 9-13.5"
                                />
                            </svg>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Needs attention
                            </p>

                            <p class="mt-3 text-3xl font-semibold text-red-700">
                                {{ $attentionConnectionCount }}
                            </p>
                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-100 text-red-700">
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m9-3.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 7.5h.008v.008H12V16.5Z"
                                />
                            </svg>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Imported accounts
                            </p>

                            <p class="mt-3 text-3xl font-semibold text-slate-900">
                                {{ $importedAccountCount }}
                            </p>
                        </div>

                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
                            <svg
                                class="h-6 w-6"
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
                    </div>
                </article>
            </section>

            @if ($connections->isEmpty())
                <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
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

                    <h3 class="mt-5 text-xl font-semibold text-slate-900">
                        No brokerage connections yet
                    </h3>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">
                        Connect the development brokerage to verify account,
                        holding and transaction synchronization before enabling
                        a live provider.
                    </p>

                    <a
                        href="{{ route('brokerage-connections.create') }}"
                        class="mt-7 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
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

                        Connect your first brokerage
                    </a>
                </section>
            @else
                <section class="space-y-6">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Connected providers
                            </p>

                            <h3 class="mt-1 text-xl font-semibold text-slate-900">
                                Connection health and imported accounts
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
                                    'bg-emerald-100 text-emerald-800',

                                'syncing' =>
                                    'bg-blue-100 text-blue-800',

                                'error' =>
                                    'bg-red-100 text-red-800',

                                'disabled',
                                'disconnected' =>
                                    'bg-slate-100 text-slate-600',

                                default =>
                                    'bg-amber-100 text-amber-800',
                            };

                            $statusDotClasses = match ($connection->status) {
                                'active' => 'bg-emerald-500',
                                'syncing' => 'bg-blue-500',
                                'error' => 'bg-red-500',
                                'disabled',
                                'disconnected' => 'bg-slate-400',
                                default => 'bg-amber-500',
                            };

                            $latestRun = $connection->syncRuns->first();

                            $isStale = $connection->isStale();
                            $healthStatus = $connection->healthStatus();
                        @endphp

                        <article id="connection-{{ $connection->id }}" class="scroll-mt-24 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="p-7 lg:p-8">
                                <div class="flex flex-wrap items-start justify-between gap-6">
                                    <div class="flex min-w-0 items-start gap-4">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-lg font-semibold text-white">
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
                                                <h4 class="text-xl font-semibold text-slate-900">
                                                    {{ $connection->brokerage_name
                                                        ?: str($connection->provider)->title() }}
                                                </h4>

                                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                                    <span class="h-2 w-2 rounded-full {{ $statusDotClasses }}"></span>

                                                    {{ str($connection->status)->title() }}
                                                </span>

                                                @if ($connection->read_only)
                                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                                        Read-only
                                                    </span>
                                                @endif

                                                @if (
                                                    $connection->status === 'active'
                                                    && $isStale
                                                )
                                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
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

                                            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-slate-400">
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

                                    <div class="flex flex-wrap gap-3">
                                        <a
                                            href="{{ route('accounts.index') }}#connection-{{ $connection->id }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100"
                                        >
                                            View accounts
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
                                                            d="M16.023 9.348h4.992V4.356m-1.291 4.991a8.25 8.25 0 1 0 2.009 8.085"
                                                        />
                                                    </svg>

                                                    Sync now
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
                                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                                >
                                                    <svg
                                                        class="h-4 w-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M6 18 18 6M6 6l12 12"
                                                        />
                                                    </svg>

                                                    Disconnect
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if ($connection->last_error)
                                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <svg
                                                class="mt-0.5 h-5 w-5 shrink-0 text-red-700"
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

                                            <div>
                                                <p class="font-semibold text-red-900">
                                                    Synchronization issue
                                                </p>

                                                <p class="mt-1 text-sm leading-6 text-red-700">
                                                    {{ $connection->last_error }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($connection->investmentAccounts->isNotEmpty())
                                <div class="border-t border-slate-200 bg-slate-50/70">
                                    <div class="border-b border-slate-200 px-7 py-4">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Imported accounts
                                                </p>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    Accounts currently associated with this connection.
                                                </p>
                                            </div>

                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                                                {{ $connection->investmentAccounts->count() }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-slate-200">
                                        @foreach ($connection->investmentAccounts as $account)
                                            <div class="flex flex-wrap items-center justify-between gap-5 px-7 py-5">
                                                <div class="flex items-center gap-4">
                                                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-600 shadow-sm">
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

                                                    <div>
                                                        <a
                                                            href="{{ route(
                                                                'accounts.holdings.index',
                                                                $account
                                                            ) }}"
                                                            class="font-semibold text-slate-900 transition hover:text-blue-600"
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

                                                <div class="flex items-center gap-6">
                                                    <div class="text-right">
                                                        <p class="font-semibold text-slate-900">
                                                            ${{ number_format(
                                                                $account->current_value,
                                                                2
                                                            ) }}
                                                        </p>

                                                        <p class="mt-1 text-xs text-slate-400">
                                                            Cash:
                                                            ${{ number_format(
                                                                $account->cash_value,
                                                                2
                                                            ) }}
                                                        </p>
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        <a
                                                            href="{{ route(
                                                                'accounts.profile.edit',
                                                                $account
                                                            ) }}"
                                                            class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50"
                                                        >
                                                            Suitability
                                                        </a>

                                                        <a
                                                            href="{{ route(
                                                                'accounts.holdings.index',
                                                                $account
                                                            ) }}"
                                                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 text-slate-500 transition hover:bg-white hover:text-blue-600"
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
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="border-t border-slate-200 bg-white">
                                <div class="px-7 py-5">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">
                                                Recent sync history
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                Latest synchronization attempts for this connection.
                                            </p>
                                        </div>

                                        @if ($connection->syncRuns->isNotEmpty())
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                {{ $connection->syncRuns->count() }}
                                                recent
                                            </span>
                                        @endif
                                    </div>

                                    @if ($connection->syncRuns->isEmpty())
                                        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 p-6 text-center">
                                            <p class="font-medium text-slate-900">
                                                No sync history yet
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
                                                            'bg-emerald-100 text-emerald-800',

                                                        'failed' =>
                                                            'bg-red-100 text-red-800',

                                                        default =>
                                                            'bg-blue-100 text-blue-800',
                                                    };
                                                @endphp

                                                <div class="flex flex-wrap items-center justify-between gap-5 rounded-2xl border border-slate-200 px-5 py-4">
                                                    <div>
                                                        <div class="flex flex-wrap items-center gap-3">
                                                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $syncStatusClasses }}">
                                                                {{ str($run->status)->title() }}
                                                            </span>

                                                            <span class="text-sm font-medium text-slate-900">
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
                                                            <div class="mt-3 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                                                                {{ $run->error_message }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="text-right">
                                                        <p class="text-sm font-semibold text-slate-900">
                                                            @if ($run->duration_ms !== null)
                                                                {{ number_format(
                                                                    $run->duration_ms / 1000,
                                                                    2
                                                                ) }}s
                                                            @else
                                                                Running
                                                            @endif
                                                        </p>

                                                        <p class="mt-1 text-xs text-slate-400">
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

            <section class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">
                            About connection freshness
                        </p>

                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                            Helmio records the provider sync time separately from the
                            date of the underlying brokerage data. A successful sync
                            confirms that Helmio completed the import, but some providers
                            may return cached or delayed balances and transactions.
                        </p>
                    </div>

                    <a
                        href="{{ route('accounts.index') }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        View all accounts

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