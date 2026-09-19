<x-app-layout>
<div class="min-h-screen bg-slate-950 text-slate-100"><div class="mx-auto max-w-[1300px] px-4 py-8 sm:px-6 lg:px-8">
<header class="mb-6 flex flex-col gap-5 border-b border-slate-800 pb-6 lg:flex-row lg:items-end lg:justify-between">
<div><a href="{{ route('admin.customers.index') }}" class="text-sm font-semibold text-blue-400">← Customers</a><h1 class="mt-3 text-3xl font-semibold text-white">{{ $customer->name }}</h1><p class="mt-2 text-sm text-slate-400">{{ $customer->email }} · Customer since {{ $customer->created_at?->format('M j, Y') }}</p></div>
@if (auth()->user()->hasStaffPermission('customers.preview'))
<form method="POST" action="{{ route('admin.customers.preview.store', $customer) }}">@csrf<button class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-slate-950">View as Customer</button></form>
@endif
</header>
<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
@foreach ([['label' => 'Accounts', 'value' => $customer->investmentAccounts->count()], ['label' => 'Connections', 'value' => $customer->brokerageConnections->count()], ['label' => 'Ask Helmio conversations', 'value' => $customer->ask_helmio_conversations_count], ['label' => 'Ask Helmio messages', 'value' => $customer->ask_helmio_messages_count]] as $metric)
<article class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p><p class="mt-3 text-3xl font-semibold text-white">{{ number_format($metric['value']) }}</p></article>
@endforeach
</section>
<section class="mt-8 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900"><div class="border-b border-slate-800 px-5 py-4"><h2 class="font-semibold text-white">Investment accounts</h2></div><div class="divide-y divide-slate-800">
@forelse ($customer->investmentAccounts as $account)
<div class="grid gap-3 px-5 py-4 sm:grid-cols-4 sm:items-center"><div><p class="font-semibold text-white">{{ $account->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $account->institution?->name ?? $account->provider ?? 'Manual' }}</p></div><p class="text-sm">{{ $account->account_type ?? 'Account' }}</p><p class="text-sm">USD {{ number_format((float) $account->current_value, 2) }}</p><p class="text-sm text-slate-500 sm:text-right">Synced {{ $account->last_synced_at?->diffForHumans() ?? 'never' }}</p></div>
@empty
<p class="px-5 py-12 text-center text-slate-500">No investment accounts connected.</p>
@endforelse
</div></section>
<div class="mt-6 rounded-2xl border border-amber-500/20 bg-amber-500/10 p-5 text-sm text-amber-100">Customer preview is strictly read-only, expires after 20 minutes, displays a permanent banner, and records every viewed page.</div>
</div></div>
</x-app-layout>
