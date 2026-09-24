<x-app-layout>
<div class="min-h-screen bg-slate-950 text-slate-100"><main class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
<a href="{{ route('admin.support.index') }}" class="text-sm font-semibold text-blue-400">← Support Inbox</a>
<h1 class="mt-4 text-3xl font-semibold">Message a customer</h1>
<p class="mt-2 text-sm text-slate-400">Start a conversation the customer can read and reply to in Support.</p>
<form method="POST" action="{{ route('admin.support.store') }}" class="mt-6 space-y-5 rounded-2xl border border-slate-800 bg-slate-900 p-6">
@csrf
<div><label for="user_id" class="block text-sm font-semibold">Customer</label><select id="user_id" name="user_id" required class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white"><option value="">Select a customer</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected(old('user_id') == $customer->id)>{{ $customer->name }} · {{ $customer->email }}</option>@endforeach</select>@error('user_id')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror</div>
<div><label for="subject" class="block text-sm font-semibold">Subject</label><input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="160" class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white">@error('subject')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror</div>
<div><label for="message" class="block text-sm font-semibold">Message</label><textarea id="message" name="message" required maxlength="10000" rows="7" class="mt-2 w-full rounded-xl border-slate-700 bg-slate-950 text-white">{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror</div>
<button type="submit" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">Send message</button>
</form></main></div>
</x-app-layout>
