<div
    x-data="{
        moreOpen: false
    }"
    x-on:keydown.escape.window="moreOpen = false"
    class="w-full max-w-full"
>
    @php
        $analyticsItems = [
            ['route' => 'analytics.helm-score', 'label' => 'Helm Score'],
            ['route' => 'advisor-audit.index', 'label' => 'Advisor Audit'],
            ['route' => 'advisor-action-center.index', 'label' => 'Action Center'],
            ['route' => 'ai-insights.index', 'label' => 'AI Insights'],
            ['route' => 'ask-helmio.index', 'label' => 'Ask Helmio'],
            ['route' => 'what-if.index', 'label' => 'What If'],
            ['route' => 'portfolio-timeline.index', 'label' => 'Portfolio Timeline'],
            ['route' => 'monthly-reviews.index', 'label' => 'Monthly Reviews'],
            ['route' => 'analytics.costs', 'label' => 'Cost Analysis'],
            ['route' => 'analytics.fund-expenses', 'label' => 'Fund Costs'],
            ['route' => 'analytics.performance', 'label' => 'Performance'],
            ['route' => 'analytics.diversification', 'label' => 'Diversification'],
            ['route' => 'analytics.risk', 'label' => 'Risk'],
            ['route' => 'analytics.trading-discipline', 'label' => 'Trading'],
            ['route' => 'analytics.cash-drag', 'label' => 'Cash Drag'],
            ['route' => 'analytics.tax-efficiency', 'label' => 'Tax Efficiency'],
        ];

        $hasPremiumAccess = app(
            \App\Services\Billing\SubscriptionAccessService::class
        )->hasPremiumAccess(auth()->user());

        $unreadNotificationCount =
            auth()->user()->unreadNotifications()->count();

        $sidebarMain = [
            [
                'route' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'home',
                'active' => ['dashboard'],
                'premium' => false,
            ],
            [
                'route' => 'accounts.index',
                'label' => $hasPremiumAccess ? 'Accounts' : 'Upgrade',
                'icon' => $hasPremiumAccess ? 'accounts' : 'lock',
                'active' => ['accounts.*'],
                'premium' => true,
            ],
        ];

        $sidebarMonitor = [
            [
                'route' => 'analytics.helm-score',
                'label' => 'Helm Score',
                'icon' => 'score',
                'active' => ['analytics.helm-score'],
                'premium' => true,
            ],
            [
                'route' => 'advisor-action-center.index',
                'label' => 'Action Center',
                'icon' => 'alert',
                'active' => ['advisor-action-center.*'],
                'premium' => true,
            ],
            [
                'route' => 'advisor-audit.index',
                'label' => 'Advisor Audit',
                'icon' => 'shield',
                'active' => ['advisor-audit.*'],
                'premium' => true,
            ],
            [
                'route' => 'ai-insights.index',
                'label' => 'AI Insights',
                'icon' => 'sparkles',
                'active' => ['ai-insights.*'],
                'premium' => true,
            ],
            [
                'route' => 'ask-helmio.index',
                'label' => 'Ask Helmio',
                'icon' => 'chat',
                'active' => ['ask-helmio.*'],
                'premium' => true,
            ],
            [
                'route' => 'what-if.index',
                'label' => 'What If',
                'icon' => 'what-if',
                'active' => ['what-if.*'],
                'premium' => true,
            ],
        ];

        $sidebarAnalysis = [
            [
                'route' => 'analytics.costs',
                'label' => 'Cost Analysis',
                'icon' => 'dollar',
                'active' => ['analytics.costs'],
                'premium' => true,
            ],
            [
                'route' => 'analytics.fund-expenses',
                'label' => 'Fund Costs',
                'icon' => 'receipt',
                'active' => ['analytics.fund-expenses'],
                'premium' => true,
            ],
            [
                'route' => 'analytics.performance',
                'label' => 'Performance',
                'icon' => 'trend',
                'active' => ['analytics.performance*'],
                'premium' => true,
            ],
            [
                'route' => 'analytics.diversification',
                'label' => 'Diversification',
                'icon' => 'pie',
                'active' => ['analytics.diversification*'],
                'premium' => true,
            ],
            [
                'route' => 'analytics.risk',
                'label' => 'Risk',
                'icon' => 'risk',
                'active' => ['analytics.risk*'],
                'premium' => true,
            ],
            [
                'route' => 'analytics.trading-discipline',
                'label' => 'Trading',
                'icon' => 'trade',
                'active' => ['analytics.trading-discipline*'],
                'premium' => true,
            ],
            [
                'route' => 'analytics.cash-drag',
                'label' => 'Cash Drag',
                'icon' => 'cash',
                'active' => ['analytics.cash-drag*'],
                'premium' => true,
            ],
            [
                'route' => 'analytics.tax-efficiency',
                'label' => 'Tax Efficiency',
                'icon' => 'tax',
                'active' => ['analytics.tax-efficiency*'],
                'premium' => true,
            ],
        ];

        $sidebarHistory = [
            [
                'route' => 'portfolio-timeline.index',
                'label' => 'Portfolio Timeline',
                'icon' => 'clock',
                'active' => ['portfolio-timeline.*'],
                'premium' => true,
            ],
            [
                'route' => 'monthly-reviews.index',
                'label' => 'Monthly Reviews',
                'icon' => 'calendar',
                'active' => ['monthly-reviews.*'],
                'premium' => true,
            ],
        ];
    @endphp

    {{-- ============================================================= --}}
    {{-- DESKTOP SIDEBAR --}}
    {{-- ============================================================= --}}

    <aside
        class="fixed inset-y-0 left-0 z-50 hidden w-64 flex-col border-r border-slate-800/90 bg-slate-950 lg:flex"
        aria-label="Primary navigation"
    >
        {{-- Brand --}}
        <div class="flex h-20 shrink-0 items-center border-b border-slate-800/80 px-5">
            <a
                href="{{ route('dashboard') }}"
                class="flex min-w-0 items-center gap-3"
            >
                <img
                    src="{{ asset('icons/icon-192.png') }}"
                    alt="Helmio"
                    class="h-10 w-10 shrink-0 rounded-xl shadow-lg shadow-blue-950/30"
                >

                <div class="min-w-0">
                    <p class="truncate text-lg font-semibold tracking-tight text-white">
                        Helmio
                    </p>
                    <p class="mt-0.5 truncate text-[9px] font-semibold uppercase tracking-[0.2em] text-slate-600">
                        Investment oversight
                    </p>
                </div>
            </a>
        </div>

        {{-- Account / utility area --}}
        <div class="shrink-0 border-b border-slate-800/80 bg-slate-950 p-3">
            <div class="mb-2 grid grid-cols-2 gap-2">
                <a
                    href="{{ route('notifications.index') }}"
                    class="relative flex items-center justify-center gap-2 rounded-xl border border-slate-800 bg-slate-900/70 px-3 py-2.5 text-xs font-semibold text-slate-400 transition hover:border-slate-700 hover:bg-slate-900 hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082A3.001 3.001 0 0 1 9.143 17.082M18 8.25a6 6 0 1 0-12 0c0 7.5-3 7.5-3 7.5h18s-3 0-3-7.5Z"/></svg>
                    <span>Alerts</span>
                    @if ($unreadNotificationCount > 0)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-slate-950">
                            {{ min($unreadNotificationCount, 99) }}
                        </span>
                    @endif
                </a>

                <a
                    href="{{ route('profile.edit') }}"
                    class="flex items-center justify-center gap-2 rounded-xl border border-slate-800 bg-slate-900/70 px-3 py-2.5 text-xs font-semibold text-slate-400 transition hover:border-slate-700 hover:bg-slate-900 hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"/></svg>
                    <span>Profile</span>
                </a>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-2.5">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-sm font-bold text-blue-300 ring-1 ring-blue-500/20">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-200">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="truncate text-[10px] text-slate-500">
                            {{ Auth::user()->email }}
                        </p>
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-800 hover:text-white"
                                aria-label="Account menu"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('billing.index')">
                                Billing
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('investor-profile.edit')">
                                Investor Profile
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">
                                Profile
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link
                                    :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                >
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>

        {{-- Scrollable navigation --}}
        <div class="flex-1 overflow-y-auto px-3 py-5 [scrollbar-width:thin] [scrollbar-color:#334155_transparent]">
            @foreach ([
                ['label' => 'Main', 'items' => $sidebarMain],
                ['label' => 'Monitor', 'items' => $sidebarMonitor],
                ['label' => 'Portfolio Analysis', 'items' => $sidebarAnalysis],
                ['label' => 'History', 'items' => $sidebarHistory],
            ] as $section)
                <section class="mb-6 last:mb-0">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-600">
                        {{ $section['label'] }}
                    </p>

                    <div class="space-y-1">
                        @foreach ($section['items'] as $item)
                            @php
                                $itemActive = request()->routeIs(...$item['active']);
                                $itemLocked = $item['premium'] && ! $hasPremiumAccess;
                                $itemHref = $itemLocked
                                    ? route('billing.pricing')
                                    : route($item['route']);
                            @endphp

                            <a
                                href="{{ $itemHref }}"
                                @class([
                                    'group relative flex min-h-10 items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition duration-150',
                                    'bg-blue-500/10 text-blue-200 ring-1 ring-inset ring-blue-500/20' => $itemActive,
                                    'text-slate-400 hover:bg-slate-900 hover:text-slate-100' => ! $itemActive,
                                    'text-blue-400 hover:text-blue-300' => $itemLocked,
                                ])
                            >
                                @if ($itemActive)
                                    <span class="absolute inset-y-2 left-0 w-0.5 rounded-r-full bg-blue-400"></span>
                                @endif

                                <span
                                    @class([
                                        'flex h-7 w-7 shrink-0 items-center justify-center rounded-lg transition',
                                        'bg-blue-500/15 text-blue-300' => $itemActive,
                                        'bg-slate-900/80 text-slate-500 group-hover:text-slate-300' => ! $itemActive,
                                    ])
                                >
                                    @switch($item['icon'])
                                        @case('home')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="m3 11.25 9-7.5 9 7.5v8.25a.75.75 0 0 1-.75.75h-5.25v-6h-6v6H3.75A.75.75 0 0 1 3 19.5v-8.25Z"/></svg>
                                            @break
                                        @case('accounts')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5v10.5H3.75V6.75Zm3-3h10.5M7.5 10.5h4.5"/></svg>
                                            @break
                                        @case('lock')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v7.5A1.5 1.5 0 0 1 17.25 21H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
                                            @break
                                        @case('score')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 1 1 15 0M12 12l4-4M6.75 18h10.5"/></svg>
                                            @break
                                        @case('alert')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 4.5 2.6 18a1 1 0 0 0 .87 1.5h17.06a1 1 0 0 0 .87-1.5L13.7 4.5a1 1 0 0 0-1.74 0Z"/></svg>
                                            @break
                                        @case('shield')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5.25 5.25v5.625c0 4.065 2.73 7.83 6.75 9.375 4.02-1.545 6.75-5.31 6.75-9.375V5.25L12 3Zm-2.25 9 1.5 1.5 3-3"/></svg>
                                            @break
                                        @case('sparkles')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"/></svg>
                                            @break
                                        @case('chat')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75h6.75m-6.75 3h4.5M21 12a8.25 8.25 0 0 1-8.25 8.25 8.4 8.4 0 0 1-3.58-.8L3 21l1.55-6.17A8.25 8.25 0 1 1 21 12Z"/></svg>
                                            @break
                                        @case('what-if')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3m12.75-5.25L18 9m-9.75 6.75L6 18"/></svg>
                                            @break
                                        @case('dollar')
                                            <span class="text-sm font-semibold">$</span>
                                            @break
                                        @case('receipt')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.75h12v16.5l-3-1.5-3 1.5-3-1.5-3 1.5V3.75Zm3 4.5h6m-6 3h6m-6 3h3"/></svg>
                                            @break
                                        @case('trend')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="m4 16 5-5 4 3 7-8M15 6h5v5"/></svg>
                                            @break
                                        @case('pie')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9h9A9 9 0 1 1 12 3Z"/></svg>
                                            @break
                                        @case('risk')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5.25 5.25v5.625c0 4.065 2.73 7.83 6.75 9.375 4.02-1.545 6.75-5.31 6.75-9.375V5.25L12 3Z"/></svg>
                                            @break
                                        @case('trade')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h12m0 0-3-3m3 3-3 3m0 6H4.5m0 0 3 3m-3-3 3-3"/></svg>
                                            @break
                                        @case('cash')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5v10.5H3.75V6.75Zm4.5 5.25h.008v.008H8.25V12Zm7.5 0h.008v.008h-.008V12ZM12 14.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z"/></svg>
                                            @break
                                        @case('tax')
                                            <span class="text-xs font-bold">%</span>
                                            @break
                                        @case('clock')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            @break
                                        @case('calendar')
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/></svg>
                                            @break
                                    @endswitch
                                </span>

                                <span class="min-w-0 flex-1 truncate">
                                    {{ $item['label'] }}
                                </span>

                                @if ($itemLocked)
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3"/></svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

    </aside>

    {{-- Mobile/tablet navigation remains compact. --}}
    <nav
        class="relative z-40 w-full max-w-full overflow-x-clip border-b border-slate-800 bg-slate-950 lg:hidden"
    >
    {{-- ============================================================= --}}
    {{-- MOBILE HEADER --}}
    {{-- ============================================================= --}}

    <div
        class="w-full max-w-full overflow-hidden border-b border-slate-800 bg-slate-950 lg:hidden"
    >
        <div
            class="flex h-16 min-w-0 items-center justify-between gap-3 px-4"
        >
            <a
                href="{{ route('dashboard') }}"
                class="flex min-w-0 items-center gap-3 overflow-hidden"
            >
                <img
                    src="{{ asset('icons/icon-192.png') }}"
                    alt="Helmio"
                    class="h-10 w-10 rounded-xl"
                >

                <div class="min-w-0">
                    <p
                        class="truncate text-sm font-semibold text-white"
                    >
                        Helmio
                    </p>

                    <p
                        class="truncate text-[10px] uppercase tracking-[0.16em] text-slate-500"
                    >
                        Investment oversight
                    </p>
                </div>
            </a>

            <a
                href="{{ route('notifications.index') }}"
                class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-slate-400 transition hover:bg-slate-800 hover:text-white"
                aria-label="Notifications"
            >
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
                        d="M14.857 17.082A3.001 3.001 0 0 1 9.143 17.082M18 8.25a6 6 0 1 0-12 0c0 7.5-3 7.5-3 7.5h18s-3 0-3-7.5Z"
                    />
                </svg>

                @if ($unreadNotificationCount > 0)
                    <span
                        class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                    >
                        {{ min(
                            $unreadNotificationCount,
                            99
                        ) }}
                    </span>
                @endif
            </a>
        </div>
    </div>

</nav>

    {{-- ============================================================= --}}
    {{-- MOBILE MORE SHEET --}}
    {{-- Kept outside the main nav so iOS fixes overlays to viewport. --}}
    {{-- ============================================================= --}}

    <div
        x-cloak
        x-show="moreOpen"
        x-transition.opacity
        class="fixed inset-0 z-[80] max-w-full overflow-x-hidden bg-black/60 lg:hidden"
        x-on:click.self="moreOpen = false"
    >
        <div
            class="absolute inset-x-0 bottom-0 max-h-[82vh] w-full max-w-full overflow-x-hidden overflow-y-auto rounded-t-3xl border-t border-slate-700 bg-slate-950 shadow-2xl"
        >
            <div
                class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-800 bg-slate-950 px-5 py-4"
            >
                <div>
                    <p
                        class="font-semibold text-white"
                    >
                        More
                    </p>

                    <p
                        class="mt-1 text-xs text-slate-500"
                    >
                        Analytics, settings, and account tools
                    </p>
                </div>

                <button
                    type="button"
                    x-on:click="moreOpen = false"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-white"
                    aria-label="Close menu"
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
                            d="M6 18 18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div
                class="space-y-7 px-5 py-5 pb-28"
            >
                {{-- Portfolio --}}
                <section>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-600"
                    >
                        Portfolio
                    </p>

                    <div class="mt-3 grid gap-2">
                        <a
                            href="{{ $hasPremiumAccess
                                ? route('brokerage-connections.index')
                                : route('billing.pricing') }}"
                            class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-300"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 6H18a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3h-4.5M10.5 18H6a3 3 0 0 1-3-3V9a3 3 0 0 1 3-3h4.5m-3 6h9"
                                    />
                                </svg>
                            </div>

                            <span class="min-w-0 flex-1">
                                {{ $hasPremiumAccess
                                    ? 'Brokerage Connections'
                                    : 'Subscribe to Connect' }}
                            </span>

                            @unless ($hasPremiumAccess)
                                <svg
                                    class="h-4 w-4 shrink-0 text-slate-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3"
                                    />
                                </svg>
                            @endunless
                        </a>

                        <a
                            href="{{ route('investor-profile.edit') }}"
                            class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-slate-400"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"
                                    />
                                </svg>
                            </div>

                            <span>Investor Profile</span>
                        </a>

                        <a
                            href="{{ $hasPremiumAccess
                                ? route('portfolio-timeline.index')
                                : route('billing.pricing') }}"
                            class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-slate-400"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                    />
                                </svg>
                            </div>

                            <span class="min-w-0 flex-1">
                                Portfolio Timeline
                            </span>

                            @unless ($hasPremiumAccess)
                                <svg
                                    class="h-4 w-4 shrink-0 text-slate-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3"
                                    />
                                </svg>
                            @endunless
                        </a>
                    </div>
                </section>

                {{-- Analytics --}}
                <section>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-600"
                    >
                        Analytics
                    </p>

                    <div class="mt-3 grid gap-2">
                        @foreach (
                            $analyticsItems
                            as $item
                        )
                            <a
                                href="{{ $hasPremiumAccess
                                    ? route($item['route'])
                                    : route('billing.pricing') }}"
                                @class([
                                    'flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold transition',

                                    'border-blue-500/30 bg-blue-500/10 text-blue-300' =>
                                        request()->routeIs(
                                            $item['route']
                                        ),

                                    'border-slate-800 bg-slate-900 text-slate-300 hover:border-blue-500/40 hover:text-white' =>
                                        ! request()->routeIs(
                                            $item['route']
                                        ),
                                ])
                            >
                                <div
                                    @class([
                                        'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',

                                        'bg-blue-500/10 text-blue-300' =>
                                            request()->routeIs(
                                                $item['route']
                                            ),

                                        'bg-slate-800 text-slate-400' =>
                                            ! request()->routeIs(
                                                $item['route']
                                            ),
                                    ])
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.9"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4.5 19.5v-6m5.25 6V9m5.25 10.5V5.25m5.25 14.25H3.75"
                                        />
                                    </svg>
                                </div>

                                <span class="min-w-0 flex-1">
                                    {{ $item['label'] }}
                                </span>

                                @unless ($hasPremiumAccess)
                                    <svg
                                        class="h-4 w-4 shrink-0 text-slate-500"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3"
                                        />
                                    </svg>
                                @endunless
                            </a>
                        @endforeach
                    </div>
                </section>

                {{-- Account --}}
                <section>
                    <p
                        class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-600"
                    >
                        Account
                    </p>

                    <div class="mt-3 grid gap-2">
                        <a
                            href="{{ route('billing.index') }}"
                            class="flex items-center gap-3 rounded-xl border border-blue-500/30 bg-blue-500/10 px-4 py-3 text-sm font-semibold text-blue-300"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-300"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.75 7.5h16.5v9h-16.5v-9Zm0 3h16.5"
                                    />
                                </svg>
                            </div>

                            <span>
                                {{ $hasPremiumAccess
                                    ? 'Billing & Subscription'
                                    : 'Upgrade to Premium' }}
                            </span>
                        </a>

                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-slate-400"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.9"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"
                                    />
                                </svg>
                            </div>

                            <span>Profile</span>
                        </a>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-left text-sm font-semibold text-red-300 transition hover:bg-red-500/15"
                            >
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-500/10 text-red-300"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.9"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M10.5 6H6.75A2.25 2.25 0 0 0 4.5 8.25v7.5A2.25 2.25 0 0 0 6.75 18h3.75m3-3 3-3m0 0-3-3m3 3H9"
                                        />
                                    </svg>
                                </div>

                                <span>Log Out</span>
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- MOBILE BOTTOM NAVIGATION --}}
    {{-- ============================================================= --}}

    <div
        class="fixed inset-x-0 bottom-0 z-[100] w-full max-w-full border-t border-slate-800 bg-slate-950/95 pb-[env(safe-area-inset-bottom)] shadow-2xl backdrop-blur lg:hidden"
        style="transform: translateZ(0);"
    >
        <div
            class="grid w-full min-w-0 grid-cols-5"
        >
            {{-- Home --}}
            <a
                href="{{ route('dashboard') }}"
                @class([
                    'flex min-h-16 min-w-0 flex-col items-center justify-center gap-1 overflow-hidden px-0.5 text-[10px] font-semibold',

                    'text-blue-400' =>
                        request()->routeIs('dashboard'),

                    'text-slate-500' =>
                        ! request()->routeIs('dashboard'),
                ])
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
                        d="m3 11.25 9-7.5 9 7.5v8.25a.75.75 0 0 1-.75.75h-5.25v-6h-6v6H3.75A.75.75 0 0 1 3 19.5v-8.25Z"
                    />
                </svg>

                <span
                    class="block max-w-full truncate"
                >
                    Home
                </span>
            </a>

            {{-- Accounts --}}
            <a
                href="{{ $hasPremiumAccess
                    ? route('accounts.index')
                    : route('billing.pricing') }}"
                @class([
                    'flex min-h-16 min-w-0 flex-col items-center justify-center gap-1 overflow-hidden px-0.5 text-[10px] font-semibold',

                    'text-blue-400' =>
                        $hasPremiumAccess
                            ? request()->routeIs('accounts.*')
                            : request()->routeIs(
                                'billing.pricing'
                            ),

                    'text-slate-500' =>
                        $hasPremiumAccess
                            ? ! request()->routeIs(
                                'accounts.*'
                            )
                            : ! request()->routeIs(
                                'billing.pricing'
                            ),
                ])
            >
                @if ($hasPremiumAccess)
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
                            d="M3.75 6.75h16.5v10.5H3.75V6.75Zm3-3h10.5M7.5 10.5h4.5"
                        />
                    </svg>

                    <span
                        class="block max-w-full truncate"
                    >
                        Accounts
                    </span>
                @else
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
                            d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3"
                        />
                    </svg>

                    <span
                        class="block max-w-full truncate"
                    >
                        Upgrade
                    </span>
                @endif
            </a>

            {{-- Audit --}}
            <a
                href="{{ $hasPremiumAccess
                    ? route('advisor-audit.index')
                    : route('billing.pricing') }}"
                @class([
                    'flex min-h-16 min-w-0 flex-col items-center justify-center gap-1 overflow-hidden px-0.5 text-[10px] font-semibold',

                    'text-blue-400' =>
                        request()->routeIs(
                            'advisor-audit.*'
                        )
                        || request()->routeIs(
                            'advisor-action-center.*'
                        ),

                    'text-slate-500' =>
                        ! (
                            request()->routeIs(
                                'advisor-audit.*'
                            )
                            || request()->routeIs(
                                'advisor-action-center.*'
                            )
                        ),
                ])
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
                        d="M12 3 5.25 5.25v5.625c0 4.065 2.73 7.83 6.75 9.375 4.02-1.545 6.75-5.31 6.75-9.375V5.25L12 3Zm-2.25 9 1.5 1.5 3-3"
                    />
                </svg>

                <span
                    class="block max-w-full truncate"
                >
                    Audit
                </span>
            </a>

            {{-- AI --}}
            <a
                href="{{ $hasPremiumAccess
                    ? route('ai-insights.index')
                    : route('billing.pricing') }}"
                @class([
                    'flex min-h-16 min-w-0 flex-col items-center justify-center gap-1 overflow-hidden px-0.5 text-[10px] font-semibold',

                    'text-blue-400' =>
                        request()->routeIs(
                            'ai-insights.*'
                        )
                        || request()->routeIs(
                            'ask-helmio.*'
                        )
                        || request()->routeIs(
                            'what-if.*'
                        ),

                    'text-slate-500' =>
                        ! (
                            request()->routeIs(
                                'ai-insights.*'
                            )
                            || request()->routeIs(
                                'ask-helmio.*'
                            )
                            || request()->routeIs(
                                'what-if.*'
                            )
                        ),
                ])
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
                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                    />
                </svg>

                <span
                    class="block max-w-full truncate"
                >
                    AI
                </span>
            </a>

            {{-- More --}}
            <button
                type="button"
                x-on:click="moreOpen = true"
                class="flex min-h-16 min-w-0 flex-col items-center justify-center gap-1 overflow-hidden px-0.5 text-[10px] font-semibold text-slate-500 transition hover:text-white"
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
                        d="M4.5 6.75h15m-15 5.25h15m-15 5.25h15"
                    />
                </svg>

                <span
                    class="block max-w-full truncate"
                >
                    More
                </span>
            </button>
        </div>
    </div>
</div>