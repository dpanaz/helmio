@php
    $helm = $review['helm'] ?? [];
    $score = data_get($helm, 'overall_score');
    $scoreAvailable = is_numeric($score);
    $findings = collect($review['openFindings'] ?? []);
    $insight = $review['latestAiInsight'] ?? null;
    $summaryAvailable = data_get($insight, 'status') === 'completed' && ! data_get($insight, 'is_stale', false);
    $comparison = $review['auditComparison'] ?? [];
    $audit = $review['advisorAudit'] ?? [];
    $analysis = $review['analysisRun'] ?? null;
    $summaryAvailable = $summaryAvailable && (data_get($analysis, 'completed_at') === null || data_get($insight, 'generated_at')?->gte($analysis->completed_at));
    $updating = $analysis && ! in_array($analysis->status, ['ready', 'failed'], true);
    $syncDate = collect($review['accounts'] ?? [])->filter(fn ($a) => $a->last_synced_at)->min('last_synced_at');
@endphp
<section class="rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8" aria-labelledby="review-health">
    <h1 id="review-health" class="text-2xl font-semibold text-white">How are my investments doing?</h1>
    @if ($updating)
        <p role="status" class="mt-4 text-blue-300">Your review is updating. Results below are from the last completed review.</p>
    @elseif (data_get($analysis, 'status') === 'failed')
        <p role="alert" class="mt-4 text-amber-300">The latest review could not finish. Any results below are from an earlier review.</p>
    @endif
    <div class="mt-6 flex flex-wrap items-end gap-4">
        <div><p class="text-sm text-slate-400">Helm Score</p><p class="mt-1 text-5xl font-semibold text-white">{{ $scoreAvailable ? $score : '—' }}@if ($scoreAvailable)<span class="text-xl text-slate-400"> / 100</span>@endif</p></div>
        <p class="text-lg text-blue-300">{{ $scoreAvailable ? data_get($helm, 'overall_label', 'Based on available data') : 'Not enough data to assess' }}</p>
    </div>
    @if ($summaryAvailable && ! $updating)
        <p class="mt-6 whitespace-pre-line leading-7 text-slate-300">{{ data_get($insight, 'summary') }}</p>
        @foreach (collect(data_get($insight, 'limitations', []))->filter(fn ($v) => is_string($v)) as $limitation)
            <p class="mt-2 text-sm text-amber-300">{{ $limitation }}</p>
        @endforeach
    @else
        <p class="mt-6 leading-7 text-slate-400">{{ $scoreAvailable ? 'This review summarizes the investment data Helmio could assess. The plain-English summary is unavailable or being updated.' : 'Helmio needs more investment data before it can calculate your score.' }}</p>
    @endif
    <p class="mt-4 text-sm text-slate-400">Review date: {{ data_get($helm, 'calculated_for_date') ?? 'Not available' }} · Account data through: {{ $syncDate?->format('M j, Y') ?? 'Not available' }}</p>
    @if ((float) data_get($helm, 'data_completeness', 0) < 1)
        <p class="mt-2 text-sm text-amber-300">Some data is missing. Findings and scores reflect available information; unassessed areas are not a clean bill of health.</p>
    @endif
</section>
<section class="rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8" aria-labelledby="review-attention">
    <h2 id="review-attention" class="text-xl font-semibold text-white">What needs my attention?</h2>
    <div class="mt-5 space-y-4">
        @forelse ($findings->take(3) as $finding)
            <article class="rounded-2xl border border-slate-700 bg-slate-950 p-5">
                <p class="text-xs font-semibold uppercase text-amber-300">{{ str($finding->severity)->replace('_', ' ')->title() }}</p>
                <h3 class="mt-2 font-semibold text-white">{{ $finding->title }}</h3>
                <p class="mt-2 leading-6 text-slate-300">{{ $finding->description }}</p>
                @if (is_numeric(data_get($finding, 'metadata.financial_impact')))
                    <p class="mt-3 text-sm text-slate-300">Estimated financial impact: {{ money(data_get($finding, 'metadata.financial_impact')) }}</p>
                @endif
                @if ($finding->recommendation)
                    <p class="mt-3 text-sm text-blue-200">Discussion point for your advisor: {{ $finding->recommendation }}</p>
                @endif
                <details class="mt-3 text-sm text-slate-400">
                    <summary class="cursor-pointer text-blue-300">View evidence</summary>
                    <p class="mt-2">Category: {{ str($finding->category)->replace('_', ' ')->title() }} · Last detected: {{ $finding->last_detected_at?->format('M j, Y') ?? 'Not available' }}</p>
                    @foreach (collect(data_get($finding, 'metadata.evidence', []))->filter(fn ($v) => is_scalar($v)) as $key => $value)
                        <p class="mt-1">{{ is_string($key) ? str($key)->replace('_', ' ')->title().': ' : '' }}{{ $value }}</p>
                    @endforeach
                </details>
            </article>
        @empty
            <p class="text-slate-400">{{ $scoreAvailable && data_get($audit, 'status') === 'complete' ? 'No open findings were recorded in the latest review. This applies only to the data assessed.' : 'Findings are not available yet. Missing results do not mean there are no concerns.' }}</p>
        @endforelse
    </div>
    @if (! $isOnboarding)
        <a class="mt-5 inline-block text-blue-300 underline" href="{{ route('advisor-action-center.index') }}">View all findings and next steps</a>
    @endif
</section>
<section class="rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8" aria-labelledby="review-changes">
    <h2 id="review-changes" class="text-xl font-semibold text-white">What changed?</h2>
    @if (data_get($comparison, 'has_previous', false))
        <p class="mt-4 text-slate-300">Since your previous advisor review: {{ collect(data_get($comparison, 'new_findings', []))->count() }} new findings and {{ collect(data_get($comparison, 'resolved_findings', []))->count() }} resolved findings.</p>
        @foreach (['new_findings' => 'New', 'resolved_findings' => 'Resolved'] as $key => $label)
            @foreach (collect(data_get($comparison, $key, []))->take(3) as $change)
                <p class="mt-2 text-sm text-slate-400">{{ $label }}: {{ data_get($change, 'title') }}</p>
            @endforeach
        @endforeach
    @else
        <p class="mt-4 text-slate-400">This is your starting point. Your next review will show what changed.</p>
    @endif
</section>
<details class="rounded-3xl border border-slate-800 bg-slate-900 p-6 sm:p-8">
    <summary class="cursor-pointer font-semibold text-blue-300">View details</summary>
    <p class="mt-4 text-slate-300">Portfolio value: {{ money($review['portfolioValue'] ?? 0) }} · {{ $review['accountCount'] ?? 0 }} connected accounts</p>
    <p class="mt-2 text-sm text-slate-400">Formula version: {{ data_get($helm, 'formula_version') ?? 'Not available' }}</p>
    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        @foreach (data_get($helm, 'categories', []) as $key => $category)
            <div class="rounded-xl border border-slate-700 p-4">
                <p class="font-medium text-white">{{ str($key)->replace('_', ' ')->title() }}</p>
                <p class="mt-1 text-slate-400">{{ is_numeric(data_get($category, 'score')) ? data_get($category, 'score').'/100 · '.data_get($category, 'label', '') : 'Not enough data to assess' }}</p>
                @foreach (collect(data_get($category, 'warnings', []))->filter(fn ($v) => is_string($v)) as $warning)
                    <p class="mt-2 text-sm text-amber-300">{{ $warning }}</p>
                @endforeach
            </div>
        @endforeach
    </div>
    @if (! $isOnboarding)
        <div class="mt-6 flex flex-wrap gap-4 text-blue-300">
            <a class="underline" href="{{ route('advisor-audit.index') }}">Full review and evidence</a>
            <a class="underline" href="{{ route('accounts.index') }}">Accounts and holdings</a>
            <a class="underline" href="{{ route('monthly-reviews.index') }}">Monthly reports</a>
            <a class="underline" href="{{ route('ask-helmio.index') }}">Ask Helmio</a>
        </div>
    @endif
</details>
