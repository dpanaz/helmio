<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-4 border-b border-slate-800 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-400">Security and accountability</p><h1 class="mt-2 text-3xl font-semibold text-white">Staff Audit Log</h1><p class="mt-2 text-sm text-slate-400">Search staff activity, customer access, administrative changes, and operational actions.</p></div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-400 hover:text-blue-300">← Business dashboard</a>
            </header>

            @if ($errors->any())<div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ $errors->first() }}</div>@endif

            <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-800 bg-slate-900 p-5 md:grid-cols-2 xl:grid-cols-6">
                <select name="actor" class="rounded-xl border-slate-700 bg-slate-950 text-sm text-white"><option value="">All employees</option>@foreach ($staff as $member)<option value="{{ $member->id }}" @selected((string) ($filters['actor'] ?? '') === (string) $member->id)>{{ $member->name }}</option>@endforeach</select>
                <input name="customer" value="{{ $filters['customer'] ?? '' }}" placeholder="Customer name or email" class="rounded-xl border-slate-700 bg-slate-950 text-sm text-white">
                <input name="event" value="{{ $filters['event'] ?? '' }}" list="audit-events" placeholder="Event type" class="rounded-xl border-slate-700 bg-slate-950 text-sm text-white"><datalist id="audit-events">@foreach ($events as $event)<option value="{{ $event }}">@endforeach</datalist>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-xl border-slate-700 bg-slate-950 text-sm text-white">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-xl border-slate-700 bg-slate-950 text-sm text-white">
                <div class="flex gap-2"><button class="flex-1 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Filter</button><a href="{{ route('admin.audit.index') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300">Clear</a></div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-800">
                    <thead><tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-500"><th class="px-5 py-4">Time</th><th class="px-5 py-4">Employee</th><th class="px-5 py-4">Event</th><th class="px-5 py-4">Subject</th><th class="px-5 py-4">Request</th><th class="px-5 py-4">Details</th></tr></thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($logs as $log)
                            <tr class="align-top hover:bg-slate-800/30"><td class="whitespace-nowrap px-5 py-4 text-sm text-slate-400"><p>{{ $log->created_at->format('M j, Y') }}</p><p class="mt-1 text-xs text-slate-600">{{ $log->created_at->format('g:i:s A') }}</p></td><td class="px-5 py-4 text-sm"><p class="font-semibold text-white">{{ $log->actor?->name ?? 'System' }}</p><p class="mt-1 text-xs text-slate-500">{{ $log->actor?->email }}</p></td><td class="px-5 py-4"><span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-medium text-blue-300">{{ $log->event }}</span></td><td class="px-5 py-4 text-sm text-slate-300">{{ $log->customer?->email ?? data_get($log->metadata, 'staff_email', '—') }}</td><td class="px-5 py-4 text-xs text-slate-500"><p>{{ $log->request_method }} {{ $log->request_path }}</p><p class="mt-1">{{ $log->ip_address }}</p></td><td class="max-w-xs px-5 py-4 text-xs text-slate-400"><pre class="whitespace-pre-wrap font-sans">{{ $log->metadata ? json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre></td></tr>
                        @empty<tr><td colspan="6" class="px-5 py-16 text-center text-sm text-slate-500">No audit records matched these filters.</td></tr>@endforelse
                    </tbody>
                </table></div>
            </div>
            <div class="mt-6">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
