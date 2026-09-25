<x-app-layout>
    @php
        $jobName = function ($job): string {
            $payload = json_decode($job->payload ?? '{}', true);
            return data_get($payload, 'displayName', 'Queued job');
        };
        $exceptionSummary = fn ($job): string => \App\Support\SafeFailureMessage::redact(trim(strtok((string) ($job->exception ?? ''), "\n"))) ?: 'No exception message recorded.';
    @endphp

    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-[1500px] px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-4 border-b border-slate-800 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-400">Helmio operations</p><h1 class="mt-2 text-3xl font-semibold text-white">Operations Health</h1><p class="mt-2 text-sm text-slate-400">Investigate queue, brokerage, AI, and marketing delivery failures.</p></div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-400 hover:text-blue-300">← Business dashboard</a>
            </header>

            @if (session('success'))<div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>@endif

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['Queued jobs', $queuedJobs],
                    ['Failed jobs', $failedJobCount],
                    ['Sync failures', $syncFailures->count()],
                    ['AI failures', $askFailures->count() + $insightFailures->count()],
                    ['Reddit failures', $redditFailures->count()],
                ] as [$label, $value])
                    <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-3 text-3xl font-semibold {{ $value > 0 ? 'text-amber-300' : 'text-emerald-300' }}">{{ $value }}</p></article>
                @endforeach
            </section>

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 px-6 py-5">
                    <div><h2 class="text-lg font-semibold text-white">Failed queue jobs</h2><p class="mt-1 text-sm text-slate-400">Showing the latest {{ $failedJobs->count() }} of {{ $failedJobCount }}. Retry only after reviewing the cause. Clear resolved historical failures without retrying them; both actions are audited.</p></div>
                    @if (auth()->user()->hasStaffPermission('staff.manage') && $failedJobs->isNotEmpty())
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" aria-label="Select all visible failed jobs" onchange="document.querySelectorAll('input[form=&quot;clear-failed-jobs&quot;]').forEach(box => box.checked = this.checked)" class="rounded border-slate-600 bg-slate-950 text-blue-500"> Select all shown</label>
                            <form id="clear-failed-jobs" method="POST" action="{{ route('admin.operations.jobs.clear') }}" onsubmit="return confirm('Clear the selected failed job records? This removes them from the failure count and they cannot be retried afterward.');">@csrf<button class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800">Clear selected</button></form>
                        </div>
                    @endif
                </div>
                <div class="divide-y divide-slate-800">
                    @forelse ($failedJobs as $job)
                        <div class="flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                @if (auth()->user()->hasStaffPermission('staff.manage'))
                                    <input type="checkbox" name="uuids[]" value="{{ $job->uuid }}" form="clear-failed-jobs" aria-label="Select failed job {{ $job->uuid }}" class="mt-1 rounded border-slate-600 bg-slate-950 text-blue-500">
                                @endif
                                <div class="min-w-0"><p class="font-semibold text-white">{{ $jobName($job) }}</p><p class="mt-1 break-words text-sm text-red-300">{{ Str::limit($exceptionSummary($job), 260) }}</p><p class="mt-2 text-xs text-slate-500">{{ $job->queue }} · Failed {{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }} · {{ $job->uuid }}</p>
                                    <details class="mt-3 max-w-4xl"><summary class="cursor-pointer text-sm font-semibold text-blue-300 hover:text-blue-200">View failure details</summary><p class="mt-2 break-all rounded-xl bg-slate-950 p-4 text-sm leading-6 text-slate-300">{{ $exceptionSummary($job) }}</p><p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Failing SQL</p><pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-all rounded-xl bg-slate-950 p-4 text-sm leading-6 text-slate-300">{{ \App\Support\SafeFailureMessage::sqlFromException($job->exception) ?? 'No SQL statement was recorded in this exception.' }}</pre><p class="mt-2 text-xs text-slate-500">Quoted SQL values, stack traces, and job payloads are hidden.</p></details>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.operations.jobs.retry', $job->uuid) }}">@csrf<button class="whitespace-nowrap rounded-xl border border-blue-500/30 bg-blue-500/10 px-4 py-2 text-sm font-semibold text-blue-300 hover:bg-blue-500/20">Retry job</button></form>
                        </div>
                    @empty
                        <p class="px-6 py-10 text-center text-sm text-slate-500">No failed queue jobs.</p>
                    @endforelse
                </div>
            </section>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    <div class="border-b border-slate-800 px-6 py-5"><h2 class="text-lg font-semibold text-white">Brokerage sync failures</h2></div>
                    <div class="divide-y divide-slate-800">
                        @forelse ($syncFailures as $run)
                            <div class="px-6 py-4"><div class="flex justify-between gap-4"><p class="font-semibold text-white">{{ $run->brokerageConnection?->brokerage_name ?? $run->provider }}</p><span class="text-xs text-slate-500">{{ $run->started_at?->diffForHumans() }}</span></div><p class="mt-1 text-sm text-slate-400">{{ $run->user?->name ?? 'Unknown customer' }} · {{ $run->user?->email }}</p><p class="mt-2 text-sm text-red-300">{{ Str::limit(\App\Support\SafeFailureMessage::redact($run->error_message ?? 'No error recorded.'), 240) }}</p></div>
                        @empty<p class="px-6 py-10 text-center text-sm text-slate-500">No brokerage sync failures.</p>@endforelse
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    <div class="border-b border-slate-800 px-6 py-5"><h2 class="text-lg font-semibold text-white">AI failures</h2></div>
                    <div class="max-h-[520px] divide-y divide-slate-800 overflow-y-auto">
                        @forelse ($askFailures->concat($insightFailures)->sortByDesc('created_at')->take(25) as $failure)
                            <div class="px-6 py-4"><div class="flex justify-between gap-4"><p class="font-semibold text-white">{{ class_basename($failure) === 'AskHelmioMessage' ? 'Ask Helmio' : 'AI Insight' }}</p><span class="text-xs text-slate-500">{{ $failure->created_at?->diffForHumans() }}</span></div><p class="mt-1 text-sm text-slate-400">{{ $failure->user?->email ?? 'Unknown customer' }} · {{ $failure->provider ?? 'provider unknown' }}</p><p class="mt-2 text-sm text-red-300">{{ Str::limit(\App\Support\SafeFailureMessage::redact($failure->error_message ?? 'No error recorded.'), 240) }}</p></div>
                        @empty<p class="px-6 py-10 text-center text-sm text-slate-500">No AI failures.</p>@endforelse
                    </div>
                </section>
            </div>

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="border-b border-slate-800 px-6 py-5"><h2 class="text-lg font-semibold text-white">Reddit conversion failures</h2></div>
                <div class="divide-y divide-slate-800">
                    @forelse ($redditFailures as $conversion)
                        <div class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-white">{{ $conversion->type }}</p><p class="mt-1 text-sm text-red-300">{{ Str::limit(\App\Support\SafeFailureMessage::redact($conversion->reddit_error ?? 'No error recorded.'), 240) }}</p></div><div class="text-left text-xs text-slate-500 sm:text-right"><p>{{ $conversion->user?->email ?? 'Anonymous visitor' }}</p><p class="mt-1">{{ $conversion->converted_at?->diffForHumans() }}</p></div></div>
                    @empty<p class="px-6 py-10 text-center text-sm text-slate-500">No Reddit conversion failures.</p>@endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
