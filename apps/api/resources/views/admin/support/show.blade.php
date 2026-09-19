<x-app-layout>
<div class="min-h-screen bg-slate-950 text-slate-100"><div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 lg:px-8">
<a href="{{ route('admin.support.index') }}" class="text-sm font-semibold text-blue-400">← Support Inbox</a>
<div class="mt-4 grid gap-6 xl:grid-cols-[1fr_340px]">
<main>
<header class="mb-5"><div class="flex flex-wrap gap-2"><span class="rounded-full bg-slate-800 px-2 py-1 text-[10px] font-bold uppercase text-slate-400">{{ str_replace('_', ' ', $conversation->channel) }}</span><span class="rounded-full bg-blue-500/10 px-2 py-1 text-[10px] font-bold uppercase text-blue-300">{{ str_replace('_', ' ', $conversation->status) }}</span></div><h1 class="mt-3 text-2xl font-semibold">{{ $conversation->subject }}</h1><p class="mt-2 text-sm text-slate-500">{{ $conversation->customer->name }} · {{ $conversation->customer->email }}</p></header>
@if (session('status'))<div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-sm text-emerald-200">{{ session('status') }}</div>@endif
<div class="space-y-4">
@foreach ($conversation->messages as $message)
<article class="rounded-2xl border {{ $message->is_internal ? 'border-amber-500/30 bg-amber-500/10' : 'border-slate-800 bg-slate-900' }} p-5">
<div class="flex items-center justify-between gap-3"><p class="text-xs font-bold uppercase tracking-wider {{ $message->is_internal ? 'text-amber-300' : 'text-slate-400' }}">{{ $message->is_internal ? 'Internal note · ' : '' }}{{ $message->sender?->name ?? ucfirst($message->sender_type) }}</p><time class="text-xs text-slate-600">{{ $message->created_at?->format('M j, g:i A') }}</time></div><p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-200">{{ $message->body }}</p>
</article>
@endforeach
</div>
<form method="POST" action="{{ route('admin.support.reply', $conversation) }}" class="mt-6 rounded-2xl border border-slate-800 bg-slate-900 p-5">
@csrf
<textarea name="message" required rows="5" maxlength="10000" placeholder="Reply to the customer or add an internal note..." class="w-full rounded-xl border-slate-700 bg-slate-950 text-white"></textarea>
<div class="mt-3 flex flex-wrap items-center justify-between gap-3"><label class="text-sm text-amber-300"><input type="checkbox" name="is_internal" value="1" class="rounded border-slate-600 bg-slate-950 text-amber-500"> Internal note only</label><button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold">Send</button></div>
</form>
</main>
<aside class="space-y-4">
<form method="POST" action="{{ route('admin.support.update', $conversation) }}" class="rounded-2xl border border-slate-800 bg-slate-900 p-5">@csrf @method('PATCH')
<h2 class="font-semibold">Conversation controls</h2>
<label class="mt-5 block text-xs font-bold uppercase text-slate-500">Assigned to</label><select name="assigned_to_user_id" class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white"><option value="">Unassigned</option>@foreach ($staff as $member)<option value="{{ $member->id }}" @selected($conversation->assigned_to_user_id === $member->id)>{{ $member->name }}</option>@endforeach</select>
<label class="mt-4 block text-xs font-bold uppercase text-slate-500">Status</label><select name="status" class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white">@foreach (['open', 'pending', 'waiting_customer', 'resolved', 'closed'] as $value)<option value="{{ $value }}" @selected($conversation->status === $value)>{{ ucwords(str_replace('_', ' ', $value)) }}</option>@endforeach</select>
<label class="mt-4 block text-xs font-bold uppercase text-slate-500">Priority</label><select name="priority" class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white">@foreach (['low', 'normal', 'high', 'urgent'] as $value)<option value="{{ $value }}" @selected($conversation->priority === $value)>{{ ucfirst($value) }}</option>@endforeach</select>
<button class="mt-5 w-full rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-semibold">Save changes</button>
</form>
<a href="{{ route('admin.customers.show', $conversation->customer) }}" class="block rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs font-bold uppercase text-slate-500">Customer record</p><p class="mt-2 font-semibold text-blue-300">Open {{ $conversation->customer->name }} →</p></a>
</aside>
</div></div></div>
</x-app-layout>
