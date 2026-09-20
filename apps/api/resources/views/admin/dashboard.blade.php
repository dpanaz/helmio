<x-app-layout>
    @php
        $cards = [
            ['label' => 'MRR', 'value' => '$'.number_format($metrics['mrr'], 2), 'detail' => 'Estimated from active plan IDs'],
            ['label' => 'ARR', 'value' => '$'.number_format($metrics['arr'], 2), 'detail' => 'Current MRR annualized'],
            ['label' => 'Active subscriptions', 'value' => number_format($metrics['active_subscriptions']), 'detail' => $metrics['monthly_plans'].' monthly · '.$metrics['annual_plans'].' annual'],
            ['label' => 'Active trials', 'value' => number_format($metrics['trials']), 'detail' => $metrics['trials_ending'].' ending within 7 days'],
            ['label' => 'Customers', 'value' => number_format($metrics['customers']), 'detail' => $metrics['new_customers'].' new in 30 days'],
            ['label' => 'Connected accounts', 'value' => number_format($metrics['accounts']), 'detail' => $customerHealth['connected'].' customers connected'],
        ];

        $healthItems = [
            ['label' => 'Onboarding complete', 'value' => $customerHealth['onboarded'], 'total' => $metrics['customers'], 'color' => 'emerald'],
            ['label' => 'Investor profile complete', 'value' => $customerHealth['profiles'], 'total' => $metrics['customers'], 'color' => 'blue'],
            ['label' => 'Account connected', 'value' => $customerHealth['connected'], 'total' => $metrics['customers'], 'color' => 'cyan'],
            ['label' => 'Active in last 30 days', 'value' => $customerHealth['active_30_days'], 'total' => $metrics['customers'], 'color' => 'violet'],
        ];

        $systemItems = [
            ['label' => 'Connection errors', 'value' => $system['connection_errors']],
            ['label' => 'Stale connections', 'value' => $system['stale_connections']],
            ['label' => 'Failed syncs (24h)', 'value' => $system['failed_syncs']],
            ['label' => 'Failed jobs (24h)', 'value' => $system['failed_jobs']],
            ['label' => 'AI failures (24h)', 'value' => $system['ai_failures']],
            ['label' => 'Queued jobs', 'value' => $system['queued_jobs']],
        ];

        $links = [
            ['label' => 'Customers', 'description' => 'Search customers and inspect account health.', 'permission' => 'customers.view', 'route' => 'admin.customers.index'],
            ['label' => 'Support inbox', 'description' => 'Tickets and live customer conversations.', 'permission' => 'support.view', 'route' => 'admin.support.index'],
            ['label' => 'Reddit marketing', 'description' => 'Attribution, conversion, and revenue reporting.', 'permission' => 'marketing.view', 'route' => 'admin.marketing.reddit'],
            ['label' => 'Employees', 'description' => 'Create employees and assign staff roles.', 'permission' => 'staff.manage', 'route' => 'admin.staff.index'],
            ['label' => 'Subscription pricing', 'description' => 'Manage monthly and annual customer pricing.', 'permission' => 'billing.manage', 'route' => 'admin.pricing.edit'],
        ];
    @endphp

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-5 border-b border-slate-800 pb-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-400">Helmio operations</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">Business Dashboard</h1>
                    <p class="mt-2 text-sm text-slate-400">Revenue, customers, support, and platform health in one place.</p>
                </div>
                <div class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm">
                    <span class="text-slate-500">Signed in as</span>
                    <strong class="ml-2 text-white">{{ auth()->user()->name }}</strong>
                </div>
            </header>

            <section class="mb-8">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-white">Needs attention</h2>
                        <p class="mt-1 text-sm text-slate-400">Issues that may affect customers or business operations.</p>
                    </div>
                    <span @class([
                        'rounded-full px-3 py-1 text-xs font-bold',
                        'bg-emerald-500/10 text-emerald-300' => $attention->isEmpty(),
                        'bg-red-500/10 text-red-300' => $attention->isNotEmpty(),
                    ])>{{ $attention->isEmpty() ? 'All clear' : $attention->count().' issue types' }}</span>
                </div>

                @if ($attention->isEmpty())
                    <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.06] px-5 py-5 text-sm text-emerald-200">No operational issues currently require attention.</div>
                @else
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($attention as $item)
                            @if ($item['route'])
                                <a href="{{ route($item['route']) }}" class="flex items-center justify-between rounded-2xl border border-red-500/20 bg-red-500/[0.06] p-5 transition hover:border-red-400/40">
                            @else
                                <div class="flex items-center justify-between rounded-2xl border border-red-500/20 bg-red-500/[0.06] p-5">
                            @endif
                                <div class="pr-4"><p class="text-sm font-semibold text-white">{{ $item['label'] }}</p><p class="mt-1 text-xs text-slate-400">Review and resolve promptly</p></div>
                                <span class="text-3xl font-semibold text-red-300">{{ number_format($item['count']) }}</span>
                            @if ($item['route']) </a> @else </div> @endif
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <div class="mb-4"><h2 class="text-xl font-semibold text-white">Business snapshot</h2><p class="mt-1 text-sm text-slate-400">Current subscription values based on Helmio's synchronized Stripe records.</p></div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($cards as $card)
                        <article class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 shadow-xl shadow-black/10">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-semibold text-white">{{ $card['value'] }}</p>
                            <p class="mt-2 text-sm text-slate-400">{{ $card['detail'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                    <div class="flex items-start justify-between"><div><h2 class="text-lg font-semibold text-white">Customer health</h2><p class="mt-1 text-sm text-slate-400">Progress through activation and continued use.</p></div><span class="text-sm text-slate-500">{{ $metrics['customers'] }} total</span></div>
                    <div class="mt-6 space-y-5">
                        @foreach ($healthItems as $item)
                            @php $percentage = $item['total'] > 0 ? round(($item['value'] / $item['total']) * 100) : 0; @endphp
                            <div>
                                <div class="mb-2 flex justify-between text-sm"><span class="text-slate-300">{{ $item['label'] }}</span><span class="font-semibold text-white">{{ $item['value'] }} <span class="font-normal text-slate-500">({{ $percentage }}%)</span></span></div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-800"><div class="h-full rounded-full bg-blue-500" style="width: {{ min(100, $percentage) }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                    @if ($customerHealth['without_accounts'] > 0)
                        <a href="{{ route('admin.customers.index') }}" class="mt-6 flex items-center justify-between rounded-xl border border-amber-500/20 bg-amber-500/[0.06] px-4 py-3 text-sm text-amber-200"><span>Customers without an account</span><strong>{{ $customerHealth['without_accounts'] }}</strong></a>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                    <div><h2 class="text-lg font-semibold text-white">System health</h2><p class="mt-1 text-sm text-slate-400">Queues, integrations, AI, and account synchronization.</p></div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($systemItems as $item)
                            <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                                <div class="flex items-center justify-between gap-3"><span class="text-sm text-slate-400">{{ $item['label'] }}</span><span @class(['text-xl font-semibold', 'text-emerald-300' => $item['value'] === 0, 'text-amber-300' => $item['value'] > 0])>{{ number_format($item['value']) }}</span></div>
                                <p class="mt-2 text-[11px] font-bold uppercase tracking-wider {{ $item['value'] === 0 ? 'text-emerald-500' : 'text-amber-500' }}">{{ $item['value'] === 0 ? 'Healthy' : 'Review' }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-[1.4fr_0.8fr]">
                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-800 px-6 py-5"><div><h2 class="text-lg font-semibold text-white">Customers at risk</h2><p class="mt-1 text-sm text-slate-400">Paying or trial customers with engagement or connection issues.</p></div><a href="{{ route('admin.customers.index') }}" class="text-sm font-semibold text-blue-400 hover:text-blue-300">View all</a></div>
                    <div class="divide-y divide-slate-800">
                        @forelse ($atRiskCustomers as $item)
                            <a href="{{ route('admin.customers.show', $item['user']) }}" class="flex flex-col gap-3 px-6 py-4 transition hover:bg-slate-800/40 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0"><p class="truncate font-semibold text-white">{{ $item['user']->name }}</p><p class="truncate text-sm text-slate-500">{{ $item['user']->email }}</p></div>
                                <div class="flex flex-wrap gap-2">@foreach ($item['reasons'] as $reason)<span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-300">{{ $reason }}</span>@endforeach</div>
                            </a>
                        @empty
                            <p class="px-6 py-10 text-center text-sm text-slate-500">No active subscribers currently match the risk rules.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                    <h2 class="text-lg font-semibold text-white">Support</h2><p class="mt-1 text-sm text-slate-400">Current customer-service workload.</p>
                    <div class="mt-5 space-y-3">
                        @foreach ([['Open conversations', $support['open']], ['Unassigned', $support['unassigned']], ['High priority', $support['urgent']]] as [$label, $value])
                            <div class="flex items-center justify-between rounded-xl bg-slate-950/60 px-4 py-3"><span class="text-sm text-slate-400">{{ $label }}</span><strong class="text-xl text-white">{{ $value }}</strong></div>
                        @endforeach
                    </div>
                    @if ($support['oldest'])<p class="mt-4 text-xs text-slate-500">Oldest unresolved: {{ $support['oldest']->created_at->diffForHumans() }}</p>@endif
                    <a href="{{ route('admin.support.index') }}" class="mt-5 inline-flex w-full justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500">Open support inbox</a>
                </section>
            </div>

            <section class="mt-8">
                <div class="mb-4"><h2 class="text-xl font-semibold text-white">Operations</h2><p class="mt-1 text-sm text-slate-400">Tools available for your assigned staff role.</p></div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($links as $link)
                        @if (auth()->user()->hasStaffPermission($link['permission']))
                            <a href="{{ route($link['route']) }}" class="group rounded-2xl border border-slate-800 bg-slate-900 p-5 transition hover:border-blue-500/60"><h3 class="font-semibold text-white group-hover:text-blue-300">{{ $link['label'] }}</h3><p class="mt-2 text-sm leading-6 text-slate-400">{{ $link['description'] }}</p></a>
                        @endif
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
