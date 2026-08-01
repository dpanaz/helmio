<nav
    x-data="{ open: false }"
    class="border-b border-slate-200 bg-white"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex min-w-0">
                <div class="flex shrink-0 items-center">
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex items-center gap-3"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg font-bold text-white">
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
                </div>

                <div class="hidden min-w-0 space-x-1 sm:-my-px sm:ms-8 sm:flex">
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

                    <x-nav-link
                        :href="route('analytics.helm-score')"
                        :active="request()->routeIs('analytics.helm-score')"
                    >
                        {{ __('Helm Score') }}
                    </x-nav-link>

                    <x-nav-link
                        :href="route('analytics.costs')"
                        :active="request()->routeIs('analytics.costs')"
                    >
                        {{ __('Fees') }}
                    </x-nav-link>

                    <x-nav-link
                        :href="route('analytics.fund-expenses')"
                        :active="request()->routeIs('analytics.fund-expenses')"
                    >
                        {{ __('Fund Costs') }}
                    </x-nav-link>

                    <x-nav-link
                        :href="route('analytics.diversification')"
                        :active="request()->routeIs('analytics.diversification')"
                    >
                        {{ __('Diversification') }}
                    </x-nav-link>

                    <x-nav-link
                        :href="route('analytics.trading-discipline')"
                        :active="request()->routeIs('analytics.trading-discipline')"
                    >
                        {{ __('Trading') }}
                    </x-nav-link>

                    <x-nav-link
                        :href="route('analytics.performance')"
                        :active="request()->routeIs('analytics.performance')"
                    >
                        {{ __('Performance') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                <x-dropdown
                    align="right"
                    width="48"
                >
                    <x-slot name="trigger">
                        <button
                            type="button"
                            class="inline-flex items-center gap-3 rounded-xl border border-transparent bg-white px-3 py-2 text-sm font-medium text-slate-600 transition duration-150 ease-in-out hover:bg-slate-50 hover:text-slate-900 focus:outline-none"
                        >
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 font-semibold text-slate-700">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>

                            <div class="hidden text-left lg:block">
                                <p class="max-w-36 truncate font-medium text-slate-900">
                                    {{ Auth::user()->name }}
                                </p>

                                <p class="max-w-36 truncate text-xs text-slate-400">
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
                                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
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

            <div class="-me-2 flex items-center sm:hidden">
                <button
                    type="button"
                    @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-xl p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-600 focus:bg-slate-100 focus:text-slate-600 focus:outline-none"
                >
                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            :class="{ 'hidden': open, 'inline-flex': ! open }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                        <path
                            :class="{ 'hidden': ! open, 'inline-flex': open }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div
        :class="{ 'block': open, 'hidden': ! open }"
        class="hidden border-t border-slate-200 sm:hidden"
    >
        <div class="space-y-1 px-4 pb-3 pt-3">
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

            <x-responsive-nav-link
                :href="route('analytics.helm-score')"
                :active="request()->routeIs('analytics.helm-score')"
            >
                {{ __('Helm Score') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('analytics.costs')"
                :active="request()->routeIs('analytics.costs')"
            >
                {{ __('Fees') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('analytics.fund-expenses')"
                :active="request()->routeIs('analytics.fund-expenses')"
            >
                {{ __('Fund Costs') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('analytics.diversification')"
                :active="request()->routeIs('analytics.diversification')"
            >
                {{ __('Diversification') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('analytics.trading-discipline')"
                :active="request()->routeIs('analytics.trading-discipline')"
            >
                {{ __('Trading') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link
                :href="route('analytics.performance')"
                :active="request()->routeIs('analytics.performance')"
            >
                {{ __('Performance') }}
            </x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 px-4 pb-4 pt-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 font-semibold text-slate-700">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

                <div>
                    <div class="font-medium text-slate-900">
                        {{ Auth::user()->name }}
                    </div>

                    <div class="text-sm text-slate-500">
                        {{ Auth::user()->email }}
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
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