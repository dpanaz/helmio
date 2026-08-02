<nav
    x-data="{
        mobileOpen: false,
        analyticsOpen: false,
        mobileAnalyticsOpen: false
    }"
    class="border-b border-slate-200 bg-white"
>
    @php
        $analyticsItems = [
            [
                'route' => 'analytics.helm-score',
                'label' => 'Helm Score',
                'description' => 'Overall portfolio health and category scores.',
                'icon' => 'score',
                'iconClass' => 'bg-blue-100 text-blue-700',
            ],
            [
                'route' => 'advisor-audit.index',
                'label' => 'Advisor Audit',
                'description' => 'Prioritized findings, costs and portfolio review items.',
                'icon' => 'audit',
                'iconClass' => 'bg-indigo-100 text-indigo-700',
            ],
            [
                'route' => 'analytics.costs',
                'label' => 'Cost Analysis',
                'description' => 'Advisory fees, account costs and annual expenses.',
                'icon' => 'cost',
                'iconClass' => 'bg-emerald-100 text-emerald-700',
            ],
            [
                'route' => 'analytics.fund-expenses',
                'label' => 'Fund Costs',
                'description' => 'Expense ratios and lower-cost comparisons.',
                'icon' => 'fund',
                'iconClass' => 'bg-violet-100 text-violet-700',
            ],
            [
                'route' => 'analytics.performance',
                'label' => 'Performance',
                'description' => 'Returns and benchmark-relative results.',
                'icon' => 'performance',
                'iconClass' => 'bg-sky-100 text-sky-700',
            ],
            [
                'route' => 'analytics.diversification',
                'label' => 'Diversification',
                'description' => 'Security, sector and asset-class exposure.',
                'icon' => 'diversification',
                'iconClass' => 'bg-amber-100 text-amber-700',
            ],
            [
                'route' => 'analytics.risk',
                'label' => 'Risk',
                'description' => 'Volatility, drawdowns and portfolio exposure.',
                'icon' => 'risk',
                'iconClass' => 'bg-rose-100 text-rose-700',
            ],
            [
                'route' => 'analytics.trading-discipline',
                'label' => 'Trading',
                'description' => 'Turnover, fees and trading-pattern indicators.',
                'icon' => 'trading',
                'iconClass' => 'bg-orange-100 text-orange-700',
            ],
            [
                'route' => 'analytics.tax-efficiency',
                'label' => 'Tax Efficiency',
                'description' => 'Gains, taxable income and wash-sale indicators.',
                'icon' => 'tax',
                'iconClass' => 'bg-teal-100 text-teal-700',
            ],
        ];

        $analyticsActive =
            request()->routeIs('analytics.*')
            || request()->routeIs('advisor-audit.*');
    @endphp

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center">
            <a
                href="{{ route('dashboard') }}"
                class="flex shrink-0 items-center gap-3"
            >
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-lg font-bold text-white">
                    H
                </div>

                <div class="hidden sm:block">
                    <p class="text-lg font-semibold tracking-tight text-slate-950">
                        Helmio
                    </p>

                    <p class="text-[10px] uppercase tracking-[0.18em] text-slate-400">
                        Investment oversight
                    </p>
                </div>
            </a>

            <div class="relative ml-auto hidden items-center sm:flex">
                <div class="flex items-center gap-1">
                    <x-nav-link
                        :href="route('dashboard')"
                        :active="request()->routeIs('dashboard')"
                    >
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link
                        :href="route('accounts.index')"
                        :active="request()->routeIs('accounts.*')"
                    >
                        {{ __('Accounts') }}
                    </x-nav-link>

                    <button
                        type="button"
                        @click="analyticsOpen = ! analyticsOpen"
                        @keydown.escape.window="analyticsOpen = false"
                        @class([
                            'inline-flex h-20 items-center gap-1.5 border-b-2 px-3 text-sm font-medium transition',
                            'border-blue-500 text-slate-900' => $analyticsActive,
                            'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' => ! $analyticsActive,
                        ])
                    >
                        Analytics

                        <svg
                            class="h-4 w-4 transition duration-200"
                            :class="{ 'rotate-180': analyticsOpen }"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
                <a
                    href="{{ route('notifications.index') }}"
                    class="relative ml-4 inline-flex h-11 w-11 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                    aria-label="Notifications"
                >
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
                            d="M14.857 17.082A3.001 3.001 0 0 1 9.143 17.082M18 8.25a6 6 0 1 0-12 0c0 7.5-3 7.5-3 7.5h18s-3 0-3-7.5Z"
                        />
                    </svg>

                    @if (auth()->user()->unreadNotifications()->count() > 0)
                        <span class="absolute right-1 top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                            {{ min(
                                auth()->user()->unreadNotifications()->count(),
                                99
                            ) }}
                        </span>
                    @endif
                </a>
                <div class="ml-5 border-l border-slate-200 pl-5">
                    <x-dropdown
                        align="right"
                        width="48"
                    >
                        <x-slot name="trigger">
                            <button
                                type="button"
                                class="inline-flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                            >
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 font-semibold text-slate-700">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>

                                <div class="hidden text-left lg:block">
                                    <p class="max-w-40 truncate font-medium text-slate-900">
                                        {{ Auth::user()->name }}
                                    </p>

                                    <p class="max-w-40 truncate text-xs text-slate-400">
                                        {{ Auth::user()->email }}
                                    </p>
                                </div>

                                <svg
                                    class="h-4 w-4 fill-current"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form
                                method="POST"
                                action="{{ route('logout') }}"
                            >
                                @csrf

                                <x-dropdown-link
                                    :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                >
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <div
                    x-cloak
                    x-show="analyticsOpen"
                    @click.outside="analyticsOpen = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                    class="absolute right-0 top-full z-50 mt-2 w-[42rem] max-w-[calc(100vw-3rem)] origin-top-right overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
                >
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <p class="text-sm font-semibold text-slate-900">
                            Portfolio analytics
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Review portfolio health, adviser findings, costs,
                            performance, risk, trading activity and tax efficiency.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-3">
                        @foreach ($analyticsItems as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'rounded-2xl p-4 transition',
                                    'bg-blue-50' => request()->routeIs($item['route']),
                                    'hover:bg-slate-50' => ! request()->routeIs($item['route']),
                                ])
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $item['iconClass'] }}">
                                        @switch($item['icon'])
                                            @case('score')
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
                                                        d="M4 18V9m5 9V5m5 13v-7m5 7V3"
                                                    />
                                                </svg>
                                                @break

                                            @case('audit')
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
                                                        d="M9 3h6l1 2h3v16H5V5h3l1-2Zm0 8 2 2 4-4m-6 8h6"
                                                    />
                                                </svg>
                                                @break

                                            @case('cost')
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
                                                        d="M12 6v12m3-9.75C15 7.007 13.657 6 12 6s-3 1.007-3 2.25 1.343 2.25 3 2.25 3 1.007 3 2.25S13.657 15 12 15s-3-1.007-3-2.25"
                                                    />
                                                </svg>
                                                @break

                                            @case('fund')
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
                                                        d="M4 20h16M6 17V8m4 9V4m4 13v-6m4 6V7"
                                                    />
                                                </svg>
                                                @break

                                            @case('performance')
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
                                                        d="m3 17 5-5 4 4 9-10m-5 0h5v5"
                                                    />
                                                </svg>
                                                @break

                                            @case('diversification')
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
                                                        d="M12 3v9h9A9 9 0 1 1 12 3Zm3 0a6 6 0 0 1 6 6h-6V3Z"
                                                    />
                                                </svg>
                                                @break

                                            @case('risk')
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
                                                        d="M12 3 4.5 6v5.25c0 4.64 3.125 8.872 7.5 9.75 4.375-.878 7.5-5.11 7.5-9.75V6L12 3Z"
                                                    />
                                                </svg>
                                                @break

                                            @case('trading')
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
                                                        d="M7 7h10m0 0-3-3m3 3-3 3m3 7H7m0 0 3 3m-3-3 3-3"
                                                    />
                                                </svg>
                                                @break

                                            @case('tax')
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
                                                        d="M7 3h8l3 3v15H7V3Zm8 0v4h4M10 11h5m-5 4h5"
                                                    />
                                                </svg>
                                                @break
                                        @endswitch
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $item['label'] }}
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-slate-500">
                                            {{ $item['description'] }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4">
                        <p class="text-xs text-slate-500">
                            Versioned, reproducible calculations.
                        </p>

                        <a
                            href="{{ route('advisor-audit.index') }}"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-500"
                        >
                            Open Advisor Audit →
                        </a>
                    </div>
                </div>
            </div>

            <button
                type="button"
                @click="mobileOpen = ! mobileOpen"
                class="ml-auto inline-flex items-center justify-center rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 sm:hidden"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        x-show="! mobileOpen"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />

                    <path
                        x-show="mobileOpen"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18 18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>
    </div>

    <div
        x-cloak
        x-show="mobileOpen"
        class="border-t border-slate-200 sm:hidden"
    >
        <div class="space-y-1 px-4 py-4">
            <x-responsive-nav-link
                :href="route('dashboard')"
                :active="request()->routeIs('dashboard')"
            >
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('accounts.index')"
                :active="request()->routeIs('accounts.*')"
            >
                {{ __('Accounts') }}
            </x-responsive-nav-link>

            <button
                type="button"
                @click="mobileAnalyticsOpen = ! mobileAnalyticsOpen"
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900"
            >
                <span>Analytics</span>

                <svg
                    class="h-4 w-4 transition"
                    :class="{ 'rotate-180': mobileAnalyticsOpen }"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>

            <div
                x-show="mobileAnalyticsOpen"
                class="space-y-1 border-l border-slate-200 pl-3"
            >
                @foreach ($analyticsItems as $item)
                    <x-responsive-nav-link
                        :href="route($item['route'])"
                        :active="request()->routeIs($item['route'])"
                    >
                        {{ __($item['label']) }}
                    </x-responsive-nav-link>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-200 px-4 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 font-semibold text-slate-700">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

                <div>
                    <p class="font-medium text-slate-900">
                        {{ Auth::user()->name }}
                    </p>

                    <p class="text-sm text-slate-500">
                        {{ Auth::user()->email }}
                    </p>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link
                    :href="route('notifications.index')"
                    :active="request()->routeIs('notifications.*')"
                >
                    {{ __('Notifications') }}

                    @if (auth()->user()->unreadNotifications()->count() > 0)
                        <span class="ml-2 rounded-full bg-red-600 px-2 py-0.5 text-xs font-semibold text-white">
                            {{ auth()->user()->unreadNotifications()->count() }}
                        </span>
                    @endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                    >
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>