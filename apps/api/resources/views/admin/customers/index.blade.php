<x-app-layout>
<div class="min-h-screen bg-slate-950 text-slate-100"><div class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6 lg:px-8">
<header class="mb-6 flex flex-col gap-4 border-b border-slate-800 pb-6 lg:flex-row lg:items-end lg:justify-between">
<div><a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-400">← Operations</a><h1 class="mt-3 text-3xl font-semibold text-white">Customers</h1><p class="mt-2 text-sm text-slate-400">Search customer records and open a read-only customer view.</p></div>
<form method="GET" class="flex w-full max-w-md gap-2"><input name="search" value="{{ $search }}" placeholder="Search name or email" class="min-w-0 flex-1 rounded-xl border-slate-700 bg-slate-900 text-slate-100"><button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white">Search</button></form>
</header>
<div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-800">
<thead><tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"><th class="px-5 py-4">Customer</th><th class="px-5 py-4">Accounts</th><th class="px-5 py-4">Connections</th><th class="px-5 py-4">Ask Helmio</th><th class="px-5 py-4">Joined</th><th></th></tr></thead>
<tbody class="divide-y divide-slate-800">
@forelse ($customers as $customer)
<tr class="hover:bg-slate-800/40"><td class="px-5 py-4"><p class="font-semibold text-white">{{ $customer->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $customer->email }}</p></td><td class="px-5 py-4 text-sm">{{ $customer->investment_accounts_count }}</td><td class="px-5 py-4 text-sm">{{ $customer->active_brokerage_connections_count }}</td><td class="px-5 py-4 text-sm">{{ $customer->ask_helmio_conversations_count }}</td><td class="px-5 py-4 text-sm text-slate-400">{{ $customer->created_at?->format('M j, Y') }}</td><td class="px-5 py-4 text-right"><a href="{{ route('admin.customers.show', $customer) }}" class="font-semibold text-blue-400">Open</a></td></tr>
@empty
<tr><td colspan="6" class="px-5 py-16 text-center text-slate-500">No customers matched your search.</td></tr>
@endforelse
</tbody></table></div></div>
<div class="mt-6">{{ $customers->links() }}</div>
</div></div>
</x-app-layout>
