<x-app-layout>
<div class="min-h-screen bg-slate-950 text-slate-100"><div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
<a href="{{ route('support.index') }}" class="text-sm font-semibold text-blue-400">← Support</a>
<h1 class="mt-4 text-3xl font-semibold">How can we help?</h1>
<p class="mt-2 text-sm text-slate-400">Choose a ticket for an asynchronous reply or live chat for a faster conversation when staff are available.</p>
<form method="POST" action="{{ route('support.store') }}" class="mt-8 space-y-6 rounded-2xl border border-slate-800 bg-slate-900 p-6">
@csrf
<div><label class="mb-2 block text-sm font-semibold">Conversation type</label><div class="grid gap-3 sm:grid-cols-2">
<label class="rounded-xl border border-slate-700 p-4"><input type="radio" name="channel" value="ticket" checked class="text-blue-600"><span class="ml-2 font-semibold">Support ticket</span><p class="ml-6 mt-1 text-xs text-slate-500">Best for questions that do not need an immediate response.</p></label>
<label class="rounded-xl border border-slate-700 p-4"><input type="radio" name="channel" value="live_chat" class="text-blue-600"><span class="ml-2 font-semibold">Live chat</span><p class="ml-6 mt-1 text-xs text-slate-500">The conversation stays open and checks for replies automatically.</p></label>
</div></div>
<div><label for="subject" class="mb-2 block text-sm font-semibold">Subject</label><input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="160" class="w-full rounded-xl border-slate-700 bg-slate-950 text-white"></div>
<div><label for="message" class="mb-2 block text-sm font-semibold">Question or problem</label><textarea id="message" name="message" required rows="8" maxlength="10000" class="w-full rounded-xl border-slate-700 bg-slate-950 text-white">{{ old('message') }}</textarea></div>
@if ($errors->any())<div class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>@endif
<button class="w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white">Send to Helmio Support</button>
</form>
</div></div>
</x-app-layout>
