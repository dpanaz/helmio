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
            </div>

            <a
                href="{{ route('brokerage-connections.create') }}"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
            >
                Connect brokerage
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <section class="rounded-3xl border border-blue-200 bg-blue-50 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
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
                                d="M12 3 4.5 6v5.25c0 4.64 3.125 8.872 7.5 9.75 4.375-.878 7.5-5.11 7.5-9.75V6L12 3Z"
                            />
                        </svg>
                    </div>

                    <div>
                        <p class="font-semibold text-blue-950">
                            Helmio connections are read-only
                        </p>

                        <p class="mt-2 text-sm leading-6 text-blue-800">
                            Helmio imports balances, holdings and transactions.
                            It does not request authority to place trades,
                            transfer money or change your brokerage account.
                        </p>
                    </div>
                </div>
            </section>

            @if ($connections->isEmpty())
                <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
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
                                d="M3.75 9h16.5M5.25 9V6.75A2.25 2.25 0 0 1 7.5 4.5h9a2.25 2.25 0 0 1 2.25 2.25V9M5.25 9v10.5h13.5V9M9 13.5h6"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-semibold text-slate-900">
                        No brokerage connected
                    </h3>

                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                        Connect the development brokerage to test account,
                        holding and transaction synchronization.
                    </p>

                    <a
                        href="{{ route('brokerage-connections.create') }}"
                        class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
                    >
                        Connect your first brokerage
                    </a>
                </section>
            @else
                <div class="space-y-6">
                    @foreach ($connections as $connection)
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
                        @endphp

                        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-6 p-7">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-xl font-semibold text-slate-900">
                                            {{ $connection->brokerage_name
                                                ?: str($connection->provider)->title() }}
                                        </h3>

                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                            {{ str($connection->status)->title() }}
                                        </span>

                                        @if ($connection->read_only)
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                                Read-only
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
                                            Last synchronized:
                                            {{ $connection->last_successful_sync_at
                                                ? $connection->last_successful_sync_at->diffForHumans()
                                                : 'Never' }}
                                        </span>
                                    </div>

                                    @if ($connection->last_error)
                                        <div class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                                            {{ $connection->last_error }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    @if (! in_array(
                                        $connection->status,
                                        ['disconnected', 'disabled'],
                                        true
                                    ))
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'brokerage-connections.sync',
                                                $connection
                                            ) }}"
                                        >
                                            @csrf

                                            <button
                                                class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                                            >
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
                                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                                            >
                                                Disconnect
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if ($connection->investmentAccounts->isNotEmpty())
                                <div class="border-t border-slate-200 bg-slate-50">
                                    @foreach ($connection->investmentAccounts as $account)
                                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-7 py-5 last:border-b-0">
                                            <div>
                                                <a
                                                    href="{{ route(
                                                        'accounts.holdings.index',
                                                        $account
                                                    ) }}"
                                                    class="font-semibold text-slate-900 hover:text-blue-600"
                                                >
                                                    {{ $account->name }}
                                                </a>

                                                <p class="mt-1 text-sm text-slate-500">
                                                    {{ $account->institution?->name
                                                        ?: 'Imported brokerage account' }}
                                                    ·••••
                                                    {{ $account->account_number_mask
                                                        ?: '----' }}
                                                </p>
                                            </div>

                                            <div class="text-right">
                                                <p class="font-semibold text-slate-900">
                                                    ${{ number_format(
                                                        $account->current_value,
                                                        2
                                                    ) }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-400">
                                                    Synced
                                                    {{ $account->provider_synced_at
                                                        ? $account->provider_synced_at->diffForHumans()
                                                        : 'never' }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>