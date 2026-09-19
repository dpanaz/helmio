<x-app-layout>
<div class="min-h-screen bg-slate-950 text-slate-100"><div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
<a href="{{ route('support.index') }}" class="text-sm font-semibold text-blue-400">← Support</a>
<header class="mt-4 mb-6 border-b border-slate-800 pb-6"><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-slate-800 px-2 py-1 text-[10px] font-bold uppercase text-slate-400">{{ str_replace('_', ' ', $conversation->channel) }}</span><span class="rounded-full bg-blue-500/10 px-2 py-1 text-[10px] font-bold uppercase text-blue-300" id="conversation-status">{{ str_replace('_', ' ', $conversation->status) }}</span></div><h1 class="mt-3 text-2xl font-semibold">{{ $conversation->subject }}</h1></header>
@if (session('status'))<div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-sm text-emerald-200">{{ session('status') }}</div>@endif
<div id="support-messages" class="space-y-4">
@foreach ($conversation->messages as $message)
<div data-message-id="{{ $message->id }}" class="flex {{ $message->sender_type === 'customer' ? 'justify-end' : 'justify-start' }}"><article class="max-w-[85%] rounded-2xl {{ $message->sender_type === 'customer' ? 'bg-blue-600 text-white' : 'border border-slate-800 bg-slate-900 text-slate-200' }} px-5 py-4"><p class="mb-2 text-xs font-bold opacity-70">{{ $message->sender_type === 'customer' ? 'You' : ($message->sender?->name ?? 'Helmio Support') }}</p><p class="whitespace-pre-wrap text-sm leading-6">{{ $message->body }}</p><time class="mt-2 block text-[10px] opacity-60">{{ $message->created_at?->format('M j, g:i A') }}</time></article></div>
@endforeach
</div>
@if ($conversation->status !== 'closed')
<form method="POST" action="{{ route('support.reply', $conversation) }}" class="mt-6 rounded-2xl border border-slate-800 bg-slate-900 p-4">
@csrf
<textarea name="message" required rows="4" maxlength="10000" placeholder="Write a reply..." class="w-full rounded-xl border-slate-700 bg-slate-950 text-white"></textarea>
<div class="mt-3 flex justify-end"><button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold">Send reply</button></div>
</form>
@endif
</div></div>
@if ($conversation->channel === 'live_chat')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('support-messages');
    let lastId = Number(container.lastElementChild?.dataset.messageId || 0);
    const endpoint = @json(route('support.messages', $conversation));

    window.setInterval(async function () {
        try {
            const response = await fetch(endpoint + '?after=' + lastId, {headers: {'Accept': 'application/json'}});
            if (!response.ok) return;
            const data = await response.json();
            data.messages.forEach(function (message) {
                lastId = Math.max(lastId, message.id);
                const row = document.createElement('div');
                row.dataset.messageId = message.id;
                row.className = 'flex ' + (message.sender_type === 'customer' ? 'justify-end' : 'justify-start');
                const article = document.createElement('article');
                article.className = 'max-w-[85%] rounded-2xl px-5 py-4 ' + (message.sender_type === 'customer' ? 'bg-blue-600 text-white' : 'border border-slate-800 bg-slate-900 text-slate-200');
                const name = document.createElement('p');
                name.className = 'mb-2 text-xs font-bold opacity-70';
                name.textContent = message.sender_type === 'customer' ? 'You' : message.sender_name;
                const body = document.createElement('p');
                body.className = 'whitespace-pre-wrap text-sm leading-6';
                body.textContent = message.body;
                article.append(name, body);
                row.append(article);
                container.append(row);
            });
            document.getElementById('conversation-status').textContent = data.status.replaceAll('_', ' ');
        } catch (error) {}
    }, 5000);
});
</script>
@endif
</x-app-layout>
