<x-app-layout>
    @php
        $cards = [
            ['label' => 'Customers', 'value' => $metrics['customers'], 'detail' => $metrics['new_customers'].' new in 30 days', 'color' => 'blue'],
            ['label' => 'Active subscriptions', 'value' => $metrics['active_subscriptions'], 'detail' => 'Stripe active or trialing', 'color' => 'emerald'],
            ['label' => 'Connected accounts', 'value' => $metrics['accounts'], 'detail' => 'Investment accounts monitored', 'color' => 'cyan'],
            ['label' => 'Marketing visitors', 'value' => $metrics['marketing_visitors'], 'detail' => 'Attributed in 30 days', 'color' => 'violet'],
            ['label' => 'Conversions', 'value' => $metrics['marketing_conversions'], 'detail' => 'Recorded in 30 days', 'color' => 'amber'],
            ['label' => 'Staff members', 'value' => $metrics['staff'], 'detail' => $metrics['staff_activity'].' audited actions in 30 days', 'color' => 'rose'],
        ];

        $links = [
            ['label' => 'Customers', 'description' => 'Search customers and inspect account health.', 'permission' => 'customers.view', 'route' => 'admin.customers.index'],
            ['label' => 'Support inbox', 'description' => 'Tickets and live customer conversations.', 'permission' => 'support.view', 'route' => 'admin.support.index'],
            ['label' => 'Reddit marketing', 'description' => 'Attribution, conversion, and revenue reporting.', 'permission' => 'marketing.view', 'route' => 'admin.marketing.reddit'],
            ['label' => 'Operations', 'description' => 'Sync, analytics, AI, and queue health.', 'permission' => 'operations.view', 'route' => null],
            ['label' => 'Team access', 'description' => 'Create employees and assign staff roles.', 'permission' => 'staff.manage', 'route' => 'admin.staff.index'],
            ['label' => 'Subscription pricing', 'description' => 'Manage monthly and annual customer pricing.', 'permission' => 'billing.manage', 'route' => 'admin.pricing.edit'],
            ['label' => 'Audit log', 'description' => 'Review sensitive staff activity.', 'permission' => 'audit.view', 'route' => null],
        ];
    @endphp

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-5 border-b border-slate-800 pb-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-blue-400">Helmio operations</p>
                    <h1 class="text-3xl font-semibold tracking-tight text-white">Business Dashboard</h1>
                    <p class="mt-2 text-sm text-slate-400">Customers, revenue operations, support, marketing, and system health.</p>
                </div>

                <div class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm">
                    <span class="text-slate-500">Signed in as</span>
                    <strong class="ml-2 text-white">{{ auth()->user()->name }}</strong>
                </div>
            </header>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($cards as $card)
                    <article class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl shadow-black/10">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-sm text-slate-400">{{ $card['detail'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="mt-8">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-white">Operations</h2>
                    <p class="mt-1 text-sm text-slate-400">Your access is controlled by your assigned staff role.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($links as $link)
                        @if (auth()->user()->hasStaffPermission($link['permission']))
                            @if ($link['route'])
                                <a href="{{ route($link['route']) }}" class="group rounded-2xl border border-slate-800 bg-slate-900 p-5 transition hover:border-blue-500/60 hover:bg-slate-900/90">
                                    <h3 class="font-semibold text-white group-hover:text-blue-300">{{ $link['label'] }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-400">{{ $link['description'] }}</p>
                                </a>
                            @else
                                <article class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5 opacity-75">
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="font-semibold text-white">{{ $link['label'] }}</h3>
                                        <span class="rounded-full bg-slate-800 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Next phase</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-400">{{ $link['description'] }}</p>
                                </article>
                            @endif
                        @endif
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
