<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Your portfolio overview</h2></x-slot>
    <div class="bg-[#080d18] min-h-screen py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6">
            @if (($unreadSupportCount ?? 0) > 0)
                <a href="{{ route('support.index') }}" class="block rounded-xl border border-blue-500/30 p-4 text-blue-200">You have a new message from Helmio Support.</a>
            @endif
            <div class="flex flex-wrap items-end justify-between gap-4 pb-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-400">Helmio dashboard</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">Your portfolio at a glance</h1>
                    <p class="mt-2 text-sm text-slate-400">A clear view of your investments, priorities, and progress.</p>
                </div>
                <a href="{{ route('accounts.index') }}" class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm font-medium text-blue-200 hover:border-blue-500">View accounts →</a>
            </div>
            @include('review.summary', ['review' => $dashboard, 'isOnboarding' => false])
        </div>
    </div>
</x-app-layout>
