<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Preparing your review</h2></x-slot>
    <div class="bg-slate-950 py-8">
        <section x-data="reviewPreparation(@js($preparation), @js(route('onboarding.status')), @js(route('onboarding.complete')))" class="mx-auto max-w-3xl rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-10">
            <p class="text-sm font-semibold text-blue-400">Step 3 of 3 · Your first review</p>
            <h1 class="mt-2 text-3xl font-semibold text-white">Preparing your review.</h1>
            <p class="mt-4 text-slate-400" role="status" aria-live="polite" x-text="state.message">{{ $preparation['message'] }}</p>
            <ol class="mt-8 space-y-3">
                @foreach (['Connecting accounts', 'Reviewing investments', 'Preparing your summary'] as $label)
                    <li class="rounded-xl border border-slate-700 p-4 text-slate-300">
                        <span>{{ $loop->iteration }}. {{ $label }}</span>
                        <span class="ml-2 text-sm text-blue-300" x-text="state.failed ? 'Paused' : ({{ $loop->index }} < state.stage ? 'Complete' : ({{ $loop->index }} === state.stage ? 'In progress' : 'Waiting'))"></span>
                    </li>
                @endforeach
            </ol>
            <p x-show="connectionError" x-cloak role="alert" class="mt-4 text-amber-300">We could not check progress. We will retry automatically; you can also refresh this page.</p>
            <div x-show="state.failed" @if (! $preparation['failed']) x-cloak @endif class="mt-6">
                <form method="POST" action="{{ route('onboarding.retry') }}">@csrf
                    <button class="rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white">Try again</button>
                </form>
            </div>
            <a href="{{ route('brokerage-connections.index') }}" class="mt-6 inline-block text-sm text-blue-300 underline">Manage connection</a>
            <noscript><p class="mt-6 text-slate-400">Refresh this page to check progress.</p></noscript>
        </section>
    </div>
</x-app-layout>
