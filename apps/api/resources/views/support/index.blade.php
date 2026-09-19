<x-app-layout>
<div class="min-h-screen bg-slate-950 text-slate-100"><div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
<header class="mb-6 flex items-end justify-between gap-4 border-b border-slate-800 pb-6">
<div><p class="text-xs font-bold uppercase tracking-[.18em] text-blue-400">Help center</p><h1 class="mt-2 text-3xl font-semibold">Helmio Support</h1><p class="mt-2 text-sm text-slate-400">Ask a question, report a problem, or start a live conversation.</p></div>
<a href="{{ route('support.create') }}" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Ask Support</a>
</header>
<div class="space-y-3">
@forelse ($conversations as $conversation)
<a href="{{ route('support.show', $conversation) }}" class="block rounded-2xl border border-slate-800 bg-slate-900 p-5 hover:border-blue-500/50">
<div class="flex flex-wrap items-start justify-between gap-3"><div><div class="flex items-center gap-2"><span class="rounded-full bg-slate-800 px-2 py-1 text-[10px] font-bold uppercase text-slate-400">{{ str_replace('_', ' ', $conversation->channel) }}</span><span class="rounded-full bg-blue-500/10 px-2 py-1 text-[10px] font-bold uppercase text-blue-300">{{ str_replace('_', ' ', $conversation->status) }}</span></div><h2 class="mt-3 font-semibold text-white">{{ $conversation->subject }}</h2><p class="mt-1 text-sm text-slate-500">{{ $conversation->customer_visible_messages_count }} messages</p></div><time class="text-xs text-slate-500">{{ $conversation->last_message_at?->diffForHumans() }}</time></div>
</a>
@empty
<div class="rounded-2xl border border-dashed border-slate-700 p-12 text-center"><h2 class="font-semibold text-white">No support conversations</h2><p class="mt-2 text-sm text-slate-500">When you contact Helmio, your conversation history will appear here.</p></div>
@endforelse
</div>
<div class="mt-6">{{ $conversations->links() }}</div>
</div></div>
</x-app-layout>
