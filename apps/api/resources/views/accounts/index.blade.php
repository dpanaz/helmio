<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">Portfolio connections</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Investment accounts</h2>
            </div>

            <a
                href="{{ route('accounts.create') }}"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
            >
                Connect account
            </a>
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
                    <p class="text-sm font-medium text-slate-500">Total value</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($totalValue, 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Cash</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($totalCash, 2) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Connected accounts</p>
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
                        Connect your first investment account
                    </h3>

                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-600">
                        Start with a manual account while we build the secure brokerage-linking integration.
                        Helmio will ultimately connect using read-only access and will never move money or place trades.
                    </p>

                    <a
                        href="{{ route('accounts.create') }}"
                        class="mt-7 inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500"
                    >
                        Add an account
                    </a>
                </section>
            @else
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="font-semibold text-slate-900">Your accounts</h3>
                    </div>

                    <div class="divide-y divide-slate-200">
                        @foreach ($accounts as $account)
                            <article class="flex flex-wrap items-center justify-between gap-5 px-6 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 font-semibold text-slate-700">
                                        {{ strtoupper(substr($account->institution?->name ?? 'H', 0, 1)) }}
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-slate-900">{{ $account->name }}</h4>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $account->institution?->name ?? 'Manual account' }}
                                            · {{ str($account->account_type)->replace('_', ' ')->title() }}

                                            @if ($account->account_number_mask)
                                                · •••• {{ $account->account_number_mask }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-lg font-semibold text-slate-900">
                                        ${{ number_format($account->current_value, 2) }}
                                    </p>

                                    <p class="mt-1 text-sm text-emerald-600">
                                        Active
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
