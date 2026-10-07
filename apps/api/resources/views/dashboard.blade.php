<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Your review</h2></x-slot>
    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6">
            @if (($unreadSupportCount ?? 0) > 0)
                <a href="{{ route('support.index') }}" class="block rounded-xl border border-blue-500/30 p-4 text-blue-200">You have a new message from Helmio Support.</a>
            @endif
            @include('review.summary', ['review' => $dashboard, 'isOnboarding' => false])
        </div>
    </div>
</x-app-layout>
