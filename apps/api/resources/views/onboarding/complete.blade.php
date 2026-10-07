<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Your first review</h2></x-slot>
    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6">
            <p class="text-sm font-semibold text-blue-400">Step 3 of 3 · Your first review</p>
            @include('review.summary', ['review' => $dashboard, 'isOnboarding' => true])
            <form method="POST" action="{{ route('onboarding.finish') }}">@csrf
                <button class="w-full rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-500">Go to dashboard</button>
            </form>
        </div>
    </div>
</x-app-layout>
