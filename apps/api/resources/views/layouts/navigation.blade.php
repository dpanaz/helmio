<nav
    x-data="{
        moreOpen: false,
        analyticsOpen: false
    }"
    x-on:keydown.escape.window="
        moreOpen = false;
        analyticsOpen = false;
    "
    class="relative z-40 w-full max-w-full overflow-x-clip border-b border-slate-800 bg-slate-950"
>
    @php
        $analyticsItems = [
            ['route' => 'analytics.helm-score', 'label' => 'Helm Score'],
            ['route' => 'advisor-audit.index', 'label' => 'Advisor Audit'],
            ['route' => 'advisor-action-center.index', 'label' => 'Action Center'],
            ['route' => 'ai-insights.index', 'label' => 'AI Insights'],
            ['route' => 'ask-helmio.index', 'label' => 'Ask Helmio'],
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

        $analyticsActive =
            request()->routeIs('analytics.*')
            || request()->routeIs('advisor-audit.*')
            || request()->routeIs('advisor-action-center.*')
            || request()->routeIs('ai-insights.*')
            || request()->routeIs('ask-helmio.*')
            || request()->routeIs('portfolio-timeline.*')
            || request()->routeIs('monthly-reviews.*');

        $hasPremiumAccess = app(
            \App\Services\Billing\SubscriptionAccessService::class
        )->hasPremiumAccess(auth()->user());

        $unreadNotificationCount =
            auth()->user()->unreadNotifications()->count();

        $desktopBase =
            'inline-flex h-20 items-center gap-2 border-b-2 px-3 text-sm font-medium transition';

        $desktopActive =
            'border-blue-500 text-white';

        $desktopInactive =
            'border-transparent text-slate-400 hover:border-slate-700 hover:text-white';
    @endphp

    {{-- Desktop navigation --}}
    <div class="hidden sm:block">
        <div
            class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
        >
            <div
                class="flex h-20 items-center justify-between gap-6"
            >
                {{-- Brand --}}
                <a
                    href="{{ route('dashboard') }}"
                    class="flex shrink-0 items-center gap-3"
                >
                    <img
                        src="{{ asset('icons/icon-192.png') }}"
                        alt="Helmio"
                        class="h-11 w-11 rounded-xl shadow-lg"
                    >

                    <div>
                        <p
                            class="text-lg font-semibold leading-5 tracking-tight text-white"
                        >
                            Helmio
                        </p>

                        <p
                            class="mt-1 text-[10px] uppercase leading-none tracking-[0.18em] text-slate-500"
                        >
                            Investment oversight
                        </p>
                    </div>
                </a>

                {{-- Main links --}}
                <div
                    class="flex min-w-0 flex-1 items-center justify-end"
                >
                    <div
                        class="flex h-20 items-center"
                    >
                        {{-- Dashboard --}}
                        <a
                            href="{{ route('dashboard') }}"
                            @class([
                                $desktopBase,
                                $desktopActive =>
                                    request()->routeIs('dashboard'),
                                $desktopInactive =>
                                    ! request()->routeIs('dashboard'),
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
                                    d="m3 11.25 9-7.5 9 7.5v8.25a.75.75 0 0 1-.75.75h-5.25v-6h-6v6H3.75A.75.75 0 0 1 3 19.5v-8.25Z"
                                />
                            </svg>

                            <span>
                                Dashboard
                            </span>
                        </a>

                        {{-- Accounts / Upgrade --}}
                        <a
                            href="{{ $hasPremiumAccess
                                ? route('accounts.index')
                                : route('billing.pricing') }}"
                            @class([
                                $desktopBase,
                                $desktopActive =>
                                    $hasPremiumAccess
                                    && request()->routeIs('accounts.*'),
                                $desktopInactive =>
                                    ! (
                                        $hasPremiumAccess
                                        && request()->routeIs('accounts.*')
                                    ),
                                'text-blue-400 hover:text-blue-300' =>
                                    ! $hasPremiumAccess,
                            ])
                        >
                            @if ($hasPremiumAccess)
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
                                        d="M3.75 6.75h16.5v10.5H3.75V6.75Zm3-3h10.5M7.5 10.5h4.5"
                                    />
                                </svg>

                                <span>
                                    Accounts
                                </span>
                            @else
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
                                        d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v7.5A1.5 1.5 0 0 1 17.25 21H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z"
                                    />
                                </svg>

                                <span>
                                    Upgrade
                                </span>
                            @endif
                        </a>

                        {{-- Analytics --}}
                        <div
                            class="relative flex h-20 items-center"
                        >
                            <button
                                type="button"
                                x-on:click="analyticsOpen = ! analyticsOpen"
                                @class([
                                    $desktopBase,
                                    'gap-1.5',
                                    $desktopActive => $analyticsActive,
                                    $desktopInactive => ! $analyticsActive,
                                ])
                            >
                                @if ($hasPremiumAccess)
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
                                @else
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
                                            d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v7.5A1.5 1.5 0 0 1 17.25 21H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z"
                                        />
                                    </svg>
                                @endif

                                <span>
                                    Analytics
                                </span>

                                <svg
                                    class="h-4 w-4 transition"
                                    x-bind:class="{
                                        'rotate-180': analyticsOpen
                                    }"
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

                            {{-- Analytics dropdown --}}
                            <div
                                x-cloak
                                x-show="analyticsOpen"
                                x-transition
                                x-on:click.outside="
                                    analyticsOpen = false
                                "
                                class="absolute right-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl"
                            >
                                <div
                                    class="border-b border-slate-800 px-4 py-3"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-widest text-slate-500"
                                    >
                                        Analytics & Oversight
                                    </p>
                                </div>

                                <div
                                    class="max-h-[70vh] space-y-1 overflow-y-auto p-3"
                                >
                                    @foreach (
                                        $analyticsItems
                                        as $item
                                    )
                                        <a
                                            href="{{ $hasPremiumAccess
                                                ? route($item['route'])
                                                : route('billing.pricing') }}"
                                            x-on:click="
                                                analyticsOpen = false
                                            "
                                            @class([
                                                'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',

                                                'bg-blue-500/10 text-blue-300 ring-1 ring-blue-500/20' =>
                                                    request()->routeIs(
                                                        $item['route']
                                                    ),

                                                'text-slate-300 hover:bg-slate-800 hover:text-white' =>
                                                    ! request()->routeIs(
                                                        $item['route']
                                                    ),
                                            ])
                                        >
                                            @if ($hasPremiumAccess)
                                                <div
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-slate-400"
                                                >
                                                    <svg
                                                        class="h-4 w-4"
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
                                            @else
                                                <div
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-slate-500"
                                                >
                                                    <svg
                                                        class="h-4 w-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.9"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v7.5A1.5 1.5 0 0 1 17.25 21H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z"
                                                        />
                                                    </svg>
                                                </div>
                                            @endif

                                            <span
                                                class="min-w-0 flex-1 truncate"
                                            >
                                                {{ $item['label'] }}
                                            </span>

                                            @unless ($hasPremiumAccess)
                                                <svg
                                                    class="h-3.5 w-3.5 shrink-0 text-slate-600"
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
                            </div>
                        </div>
                    </div>

                    {{-- Right side --}}
                    <div
                        class="ml-5 flex h-20 items-center gap-4 border-l border-slate-800 pl-5"
                    >
                        {{-- Notifications --}}
                        <a
                            href="{{ route('notifications.index') }}"
                            class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-slate-400 transition hover:border-slate-700 hover:bg-slate-800 hover:text-white"
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

                        {{-- User dropdown --}}
                        <x-dropdown
                            align="right"
                            width="56"
                        >
                            <x-slot name="trigger">
                                <button
                                    type="button"
                                    class="inline-flex h-12 items-center gap-3 rounded-xl border border-slate-800 bg-slate-900 px-2.5 text-sm font-medium text-slate-300 transition hover:border-slate-700 hover:bg-slate-800 hover:text-white"
                                >
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-500/10 font-semibold text-blue-300 ring-1 ring-blue-500/20"
                                    >
                                        {{ strtoupper(
                                            substr(
                                                Auth::user()->name,
                                                0,
                                                1
                                            )
                                        ) }}
                                    </div>

                                    <span
                                        class="hidden lg:inline"
                                    >
                                        {{ Auth::user()->name }}
                                    </span>

                                    <svg
                                        class="hidden h-4 w-4 text-slate-500 lg:block"
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
                            </x-slot>

                            <x-slot name="content">
                                <div
                                    class="border-b border-slate-200 px-4 py-3"
                                >
                                    <p
                                        class="truncate text-sm font-semibold text-slate-900"
                                    >
                                        {{ Auth::user()->name }}
                                    </p>

                                    <p
                                        class="mt-1 truncate text-xs text-slate-500"
                                    >
                                        {{ Auth::user()->email }}
                                    </p>
                                </div>

                                <x-dropdown-link
                                    :href="route('billing.index')"
                                >
                                    Billing
                                </x-dropdown-link>

                                <x-dropdown-link
                                    :href="route('profile.edit')"
                                >
                                    Profile
                                </x-dropdown-link>

                                <form
                                    method="POST"
                                    action="{{ route('logout') }}"
                                >
                                    @csrf

                                    <x-dropdown-link
                                        :href="route('logout')"
                                        onclick="
                                            event.preventDefault();
                                            this.closest('form').submit();
                                        "
                                    >
                                        Log Out
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- MOBILE HEADER --}}
    {{-- ============================================================= --}}

    <div
        class="w-full max-w-full overflow-hidden border-b border-slate-800 bg-slate-950 sm:hidden"
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

    {{-- ============================================================= --}}
    {{-- MOBILE MORE SHEET --}}
    {{-- ============================================================= --}}

    <div
        x-cloak
        x-show="moreOpen"
        x-transition.opacity
        class="fixed inset-0 z-[80] max-w-full overflow-x-hidden bg-black/60 sm:hidden"
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

                    <div
                        class="mt-3 grid gap-2"
                    >
                        <a
                            href="{{ $hasPremiumAccess
                                ? route('brokerage-connections.index')
                                : route('billing.pricing') }}"
                            class="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                        >
                            <span>
                                {{ $hasPremiumAccess
                                    ? 'Brokerage Connections'
                                    : 'Subscribe to Connect' }}
                            </span>

                            @unless ($hasPremiumAccess)
                                <svg
                                    class="h-4 w-4 text-slate-500"
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
                            class="rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                        >
                            Investor Profile
                        </a>

                        <a
                            href="{{ $hasPremiumAccess
                                ? route('portfolio-timeline.index')
                                : route('billing.pricing') }}"
                            class="flex items-center justify-between rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500/40 hover:text-white"
                        >
                            <span>
                                Portfolio Timeline
                            </span>

                            @unless ($hasPremiumAccess)
                                <svg
                                    class="h-4 w-4 text-slate-500"
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

                    <div
                        class="mt-3 grid gap-2"
                    >
                        @foreach (
                            $analyticsItems
                            as $item
                        )
                            <a
                                href="{{ $hasPremiumAccess
                                    ? route($item['route'])
                                    : route('billing.pricing') }}"
                                @class([
                                    'flex items-center justify-between rounded-xl border px-4 py-3 text-sm font-semibold transition',

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
                                <span>
                                    {{ $item['label'] }}
                                </span>

                                @unless ($hasPremiumAccess)
                                    <svg
                                        class="h-4 w-4 text-slate-500"
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

                    <div
                        class="mt-3 grid gap-2"
                    >
                        <a
                            href="{{ route('billing.index') }}"
                            class="rounded-xl border border-blue-500/30 bg-blue-500/10 px-4 py-3 text-sm font-semibold text-blue-300"
                        >
                            {{ $hasPremiumAccess
                                ? 'Billing & Subscription'
                                : 'Upgrade to Premium' }}
                        </a>

                        <a
                            href="{{ route('profile.edit') }}"
                            class="rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-300"
                        >
                            Profile
                        </a>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="w-full rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-left text-sm font-semibold text-red-300"
                            >
                                Log Out
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
        class="fixed inset-x-0 bottom-0 z-50 w-full max-w-full overflow-x-hidden border-t border-slate-800 bg-slate-950/95 pb-[env(safe-area-inset-bottom)] shadow-2xl backdrop-blur sm:hidden"
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
                        ),

                    'text-slate-500' =>
                        ! (
                            request()->routeIs(
                                'ai-insights.*'
                            )
                            || request()->routeIs(
                                'ask-helmio.*'
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
</nav>