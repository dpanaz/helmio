<x-app-layout>
<div class="min-h-screen bg-slate-950 text-slate-100"><div class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6 lg:px-8">
<header class="mb-6 flex flex-wrap items-end justify-between gap-4 border-b border-slate-800 pb-6"><div><a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-400">← Operations</a><h1 class="mt-3 text-3xl font-semibold">Support Inbox</h1><p class="mt-2 text-sm text-slate-400">Tickets and live customer conversations in one queue.</p></div>@if (auth()->user()->hasStaffPermission('support.manage'))<a href="{{ route('admin.support.create') }}" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Message a customer</a>@endif</header>
<div class="mb-5 flex flex-wrap gap-2">
@foreach (['' => 'All', 'open' => 'Open', 'pending' => 'Pending', 'waiting_customer' => 'Waiting', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
<a href="{{ route('admin.support.index', array_filter(['status' => $value, 'channel' => $channel])) }}" class="rounded-full px-3 py-2 text-xs font-bold {{ $status === $value ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400' }}">{{ $label }}@if ($value !== '') · {{ $counts[$value] ?? 0 }}@endif</a>
@endforeach
<span class="mx-2 h-8 w-px bg-slate-800"></span>
@foreach (['' => 'All channels', 'ticket' => 'Tickets', 'live_chat' => 'Live chat'] as $value => $label)
<a href="{{ route('admin.support.index', array_filter(['status' => $status, 'channel' => $value])) }}" class="rounded-full px-3 py-2 text-xs font-bold {{ $channel === $value ? 'bg-violet-600 text-white' : 'bg-slate-900 text-slate-400' }}">{{ $label }}</a>
@endforeach
</div>
<div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900"><div class="divide-y divide-slate-800">
@forelse ($conversations as $conversation)
<a href="{{ route('admin.support.show', $conversation) }}" class="grid gap-3 p-5 hover:bg-slate-800/40 md:grid-cols-[1fr_160px_140px_110px] md:items-center">
<div><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-slate-800 px-2 py-1 text-[10px] font-bold uppercase text-slate-400">{{ str_replace('_', ' ', $conversation->channel) }}</span>@if (in_array($conversation->priority, ['high', 'urgent']))<span class="rounded-full bg-red-500/10 px-2 py-1 text-[10px] font-bold uppercase text-red-300">{{ $conversation->priority }}</span>@endif</div><p class="mt-2 font-semibold text-white">{{ $conversation->subject }}</p><p class="mt-1 text-sm text-slate-500">{{ $conversation->customer->name }} · {{ $conversation->customer->email }} · {{ $conversation->messages_count }} messages</p></div>
<p class="text-sm text-slate-400">{{ $conversation->assignee?->name ?? 'Unassigned' }}</p><p class="text-xs font-bold uppercase text-blue-300">{{ str_replace('_', ' ', $conversation->status) }}</p><time class="text-xs text-slate-500 md:text-right">{{ $conversation->last_message_at?->diffForHumans() }}</time>
</a>
@empty
<p class="p-16 text-center text-slate-500">No support conversations match this filter.</p>
@endforelse
</div></div>
<div class="mt-6">{{ $conversations->links() }}</div>
</div></div>
</x-app-layout>
