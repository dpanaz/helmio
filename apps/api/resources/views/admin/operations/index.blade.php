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
                <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-400">Helmio operations</p><h1 class="mt-2 text-3xl font-semibold text-white">Operations Health</h1><p class="mt-2 text-sm text-slate-400">Check completed work, queue progress, and delivery failures.</p></div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-blue-400 hover:text-blue-300">← Business dashboard</a>
            </header>

            @if (session('success'))<div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ $errors->first() }}</div>@endif

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['Queued jobs', $queuedJobs],
                    ['Retained failed jobs', $failedJobCount],
                    ['Sync failures', $syncFailures->count()],
                    ['AI failures', $askFailures->count() + $insightFailures->count()],
                    ['Reddit failures', $redditFailures->count()],
                ] as [$label, $value])
                    <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-3 text-3xl font-semibold {{ $value > 0 ? 'text-amber-300' : 'text-emerald-300' }}">{{ $value }}</p></article>
                @endforeach
            </section>

            <section class="mt-8">
                <div class="mb-4"><h2 class="text-xl font-semibold text-white">Activity in the last 24 hours</h2><p class="mt-1 text-sm text-slate-400">Completed runs prove work is finishing. Zero failures alone does not confirm healthy processing.</p><p class="mt-1 text-xs text-slate-500">{{ $activitySince->utc()->format('M j, H:i') }}–{{ $checkedAt->utc()->format('M j, H:i') }} UTC · Refresh this page to update.</p></div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['Successful syncs', $successfulSyncCount, 'text-emerald-300'],
                        ['Completed analyses', $successfulAnalysisCount, 'text-emerald-300'],
                        ['New failed queue jobs', $recentFailedJobCount, $recentFailedJobCount > 0 ? 'text-red-300' : 'text-slate-100'],
                        ['Older failed queue jobs', $historicalFailedJobCount, 'text-slate-300'],
                    ] as [$label, $value, $color])
                        <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-3 text-3xl font-semibold {{ $color }}">{{ $value }}</p></article>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-400">Failure counts are retained queue records. Clearing a record removes it from these counts. Successes are brokerage syncs and portfolio analyses, not every queue job.</p>
                <div class="mt-4 grid gap-4 xl:grid-cols-2">
                    <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                        <div class="border-b border-slate-800 px-5 py-4"><h3 class="font-semibold text-white">Recent successful syncs</h3><p class="mt-1 text-xs text-slate-400">Latest {{ $recentSyncs->count() }} of {{ $successfulSyncCount }} in this period.</p></div>
                        <div class="divide-y divide-slate-800">
                            @forelse ($recentSyncs as $run)
                                <div class="px-5 py-4"><p class="break-words font-semibold text-white">{{ $run->user?->name ?? 'Unknown customer' }} · {{ $run->brokerageConnection?->brokerage_name ?? $run->provider }}</p><p class="mt-1 break-all text-xs text-slate-400">{{ $run->user?->email }}</p><p class="mt-2 text-sm text-emerald-300">Completed {{ $run->finished_at->utc()->format('M j, H:i:s') }} UTC</p><p class="mt-1 text-xs text-slate-400">{{ $run->accounts_imported }} accounts · {{ $run->positions_imported }} positions · {{ $run->transactions_imported }} transactions</p></div>
                            @empty
                                <p class="px-5 py-6 text-sm text-slate-400">No successful syncs recorded in the last 24 hours.</p>
                            @endforelse
                        </div>
                    </section>
                    <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                        <div class="border-b border-slate-800 px-5 py-4"><h3 class="font-semibold text-white">Recent completed analyses</h3><p class="mt-1 text-xs text-slate-400">Latest {{ $recentAnalyses->count() }} of {{ $successfulAnalysisCount }} in this period.</p></div>
                        <div class="divide-y divide-slate-800">
                            @forelse ($recentAnalyses as $run)
                                <div class="px-5 py-4"><p class="break-words font-semibold text-white">{{ $run->user?->name ?? 'Unknown customer' }}</p><p class="mt-1 break-all text-xs text-slate-400">{{ $run->user?->email }}</p><p class="mt-2 text-sm text-emerald-300">Completed {{ $run->completed_at->utc()->format('M j, H:i:s') }} UTC · Run #{{ $run->id }}</p></div>
                            @empty
                                <p class="px-5 py-6 text-sm text-slate-400">No completed analyses recorded in the last 24 hours.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </section>

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="border-b border-slate-800 px-5 py-4"><h2 class="text-lg font-semibold text-white">Latest completion by customer</h2><p class="mt-1 text-sm text-slate-400">Customers with a brokerage connection or analysis run. Dates show their latest recorded success across all history; a newer failure may still need attention.</p></div>
                <div class="divide-y divide-slate-800">
                    @forelse ($customerActivity as $customer)
                        <div class="grid gap-3 px-5 py-4 md:grid-cols-3">
                            <div><p class="break-words font-semibold text-white">{{ $customer->name }}</p><p class="mt-1 break-all text-xs text-slate-400">{{ $customer->email }}</p></div>
                            @foreach (['Last successful sync' => $customer->last_sync_at, 'Last completed analysis' => $customer->last_analysis_at] as $label => $completedAt)
                                <div><p class="text-xs text-slate-400">{{ $label }}</p><p class="mt-1 text-sm {{ $completedAt ? 'text-slate-100' : 'text-amber-300' }}">{{ $completedAt ? \Carbon\CarbonImmutable::parse($completedAt)->utc()->format('M j, Y H:i:s').' UTC' : 'No successful run recorded' }}</p></div>
                            @endforeach
                        </div>
                    @empty
                        <p class="px-5 py-6 text-sm text-slate-400">No customers with recorded brokerage or analysis activity.</p>
                    @endforelse
                </div>
                @if ($customerActivity->hasPages())<div class="border-t border-slate-800 px-5 py-4">{{ $customerActivity->links() }}</div>@endif
            </section>

            <section class="mt-8 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                <div class="border-b border-slate-800 px-5 py-4"><h2 class="text-lg font-semibold text-white">Queue activity</h2><p class="mt-1 text-sm text-slate-400">{{ $queueReadyCount }} ready · {{ $queueDelayedCount }} delayed · {{ $queueReservedCount }} reserved by workers</p><p class="mt-1 text-xs text-slate-500">Oldest {{ $pendingJobs->count() }} of {{ $queuedJobs }} queued jobs. Delayed jobs may be waiting for a retry or scheduled time. A reservation does not prove a worker is still running.</p></div>
                <div class="divide-y divide-slate-800">
                    @forelse ($pendingJobs as $job)
                        @php
                            $createdAt = \Carbon\CarbonImmutable::createFromTimestampUTC($job->created_at);
                            $availableAt = \Carbon\CarbonImmutable::createFromTimestampUTC($job->available_at);
                            $queueState = $job->reserved_at !== null ? 'Reserved by worker' : ($job->available_at > $checkedAt->timestamp ? 'Delayed' : 'Ready');
                        @endphp
                        <div class="grid gap-3 px-5 py-4 md:grid-cols-3">
                            <div><p class="break-words font-semibold text-white">{{ $job->queue }} · Job #{{ $job->id }}</p><p class="mt-1 text-xs text-slate-400">Attempts: {{ $job->attempts }}</p></div>
                            <div><p class="text-sm text-slate-100">{{ $queueState }}</p><p class="mt-1 text-xs text-slate-400">Queued {{ $createdAt->diffForHumans($checkedAt, true) }} ago</p></div>
                            <div class="text-xs text-slate-400">@if ($job->reserved_at !== null)Reserved {{ \Carbon\CarbonImmutable::createFromTimestampUTC($job->reserved_at)->format('M j, H:i:s') }} UTC@else Available {{ $availableAt->format('M j, H:i:s') }} UTC@endif</div>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-sm text-slate-400">No queued jobs.</p>
                    @endforelse
                </div>
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
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 px-6 py-5"><div><h2 class="text-lg font-semibold text-white">Brokerage sync failures</h2><p class="mt-1 text-xs text-slate-400">Acknowledge resolved history without deleting or retrying sync runs.</p></div>
                        @if (auth()->user()->hasStaffPermission('staff.manage') && $syncFailures->isNotEmpty())
                            <div class="flex items-center gap-3 text-sm"><label class="flex items-center gap-2 text-slate-300"><input type="checkbox" aria-label="Select all shown sync failures" onchange="document.querySelectorAll('input[form=&quot;ack-sync-failures&quot;]').forEach(box => box.checked = this.checked)" class="rounded border-slate-600 bg-slate-950 text-blue-500"> Select all shown</label><form id="ack-sync-failures" method="POST" action="{{ route('admin.operations.failures.acknowledge') }}" onsubmit="return confirm('Acknowledge selected sync failures? Their original records will be preserved.');">@csrf<button class="rounded-xl border border-slate-600 px-3 py-2 font-semibold text-slate-200 hover:bg-slate-800">Acknowledge selected</button></form></div>
                        @endif
                    </div>
                    <div class="divide-y divide-slate-800">
                        @forelse ($syncFailures as $run)
                            <div class="flex gap-3 px-6 py-4">@if (auth()->user()->hasStaffPermission('staff.manage'))<input type="checkbox" name="sync_ids[]" value="{{ $run->id }}" form="ack-sync-failures" aria-label="Select sync failure {{ $run->id }}" class="mt-1 rounded border-slate-600 bg-slate-950 text-blue-500">@endif<div class="min-w-0 flex-1"><div class="flex justify-between gap-4"><p class="font-semibold text-white">{{ $run->brokerageConnection?->brokerage_name ?? $run->provider }}</p><span class="text-xs text-slate-500">{{ $run->started_at?->diffForHumans() }}</span></div><p class="mt-1 text-sm text-slate-400">{{ $run->user?->name ?? 'Unknown customer' }} · {{ $run->user?->email }}</p><p class="mt-2 text-sm text-red-300">{{ Str::limit(\App\Support\SafeFailureMessage::redact($run->error_message ?? 'No error recorded.'), 240) }}</p></div></div>
                        @empty<p class="px-6 py-10 text-center text-sm text-slate-500">No brokerage sync failures.</p>@endforelse
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 px-6 py-5"><div><h2 class="text-lg font-semibold text-white">AI failures</h2><p class="mt-1 text-xs text-slate-400">Acknowledge resolved history without deleting or retrying messages or insights.</p></div>
                        @if (auth()->user()->hasStaffPermission('staff.manage') && ($askFailures->isNotEmpty() || $insightFailures->isNotEmpty()))
                            <div class="flex items-center gap-3 text-sm"><label class="flex items-center gap-2 text-slate-300"><input type="checkbox" aria-label="Select all shown AI failures" onchange="document.querySelectorAll('input[form=&quot;ack-ai-failures&quot;]').forEach(box => box.checked = this.checked)" class="rounded border-slate-600 bg-slate-950 text-blue-500"> Select all shown</label><form id="ack-ai-failures" method="POST" action="{{ route('admin.operations.failures.acknowledge') }}" onsubmit="return confirm('Acknowledge selected AI failures? Their original records will be preserved.');">@csrf<button class="rounded-xl border border-slate-600 px-3 py-2 font-semibold text-slate-200 hover:bg-slate-800">Acknowledge selected</button></form></div>
                        @endif
                    </div>
                    <div class="max-h-[520px] divide-y divide-slate-800 overflow-y-auto">
                        @forelse ($askFailures->concat($insightFailures)->sortByDesc('created_at')->take(25) as $failure)
                            <div class="flex gap-3 px-6 py-4">@if (auth()->user()->hasStaffPermission('staff.manage'))<input type="checkbox" name="{{ $failure instanceof \App\Models\AskHelmioMessage ? 'ask_ids[]' : 'insight_ids[]' }}" value="{{ $failure->id }}" form="ack-ai-failures" aria-label="Select {{ $failure instanceof \App\Models\AskHelmioMessage ? 'Ask Helmio' : 'AI Insight' }} failure {{ $failure->id }}" class="mt-1 rounded border-slate-600 bg-slate-950 text-blue-500">@endif<div class="min-w-0 flex-1"><div class="flex justify-between gap-4"><p class="font-semibold text-white">{{ class_basename($failure) === 'AskHelmioMessage' ? 'Ask Helmio' : 'AI Insight' }}</p><span class="text-xs text-slate-500">{{ $failure->created_at?->diffForHumans() }}</span></div><p class="mt-1 text-sm text-slate-400">{{ $failure->user?->email ?? 'Unknown customer' }} · {{ $failure->provider ?? 'provider unknown' }}</p><p class="mt-2 text-sm text-red-300">{{ Str::limit(\App\Support\SafeFailureMessage::redact($failure->error_message ?? 'No error recorded.'), 240) }}</p></div></div>
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
