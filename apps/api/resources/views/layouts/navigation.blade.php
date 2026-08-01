<nav x-data="{ open: false }" class="border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-18 justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <x-application-logo class="h-10 w-10" />
                    <span>
                        <strong class="block text-xl tracking-tight text-slate-950">HELMIO</strong>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-blue-600">Investment oversight</span>
                    </span>
                </a>

                <div class="hidden items-center gap-1 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                    <x-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">{{ __('Accounts') }}</x-nav-link>
                    
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
    :href="route('analytics.helm-score')"
    :active="request()->routeIs('analytics.helm-score')"
>
    {{ __('Helm Score') }}
</x-nav-link>
<span class="rounded-lg px-3 py-2 text-sm text-slate-400">Portfolio</span>
                    <span class="rounded-lg px-3 py-2 text-sm text-slate-400">Alerts</span>
                </div>
            </div>

            <div class="hidden items-center sm:flex">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 font-semibold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button @click="open = ! open" class="sm:hidden">Menu</button>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-slate-100 sm:hidden">
        <div class="space-y-1 px-4 py-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
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
    :href="route('analytics.helm-score')"
    :active="request()->routeIs('analytics.helm-score')"
>
    {{ __('Helm Score') }}
</x-responsive-nav-link>
        </div>
    </div>
</nav>
