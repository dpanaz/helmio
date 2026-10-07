<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold text-white">Connect your investments</h2></x-slot>
    <div class="bg-slate-950 py-8">
        <section class="mx-auto max-w-3xl rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-10">
            <p class="text-sm font-semibold text-blue-400">Step 2 of 3 · Connect</p>
            <h1 class="mt-2 text-3xl font-semibold text-white">Connect your first investment account.</h1>
            <p class="mt-4 leading-7 text-slate-400">Helmio reviews balances, holdings, and transactions to help you understand costs, risk, and portfolio changes.</p>
            <p class="mt-6 rounded-xl border border-blue-500/20 bg-blue-500/10 p-4 text-blue-200">Read-only access. Helmio cannot move money or trade.</p>
            @if (session('error')) <p role="alert" class="mt-4 text-red-300">{{ session('error') }}</p> @endif
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-between">
                <a href="{{ route('onboarding.profile') }}" class="rounded-xl border border-slate-700 px-5 py-3 text-center text-slate-300">Back</a>
                <a href="{{ route('brokerage-connections.create', ['onboarding' => 1]) }}" class="rounded-xl bg-blue-600 px-6 py-3 text-center font-semibold text-white hover:bg-blue-500">Connect account</a>
            </div>
        </section>
    </div>
</x-app-layout>
