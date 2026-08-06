@php
    $userName = trim((string) auth()->user()?->name);

    $firstName = $userName !== ''
        ? str($userName)->before(' ')->toString()
        : 'there';

    $currentHour = now()->hour;

    $greeting = match (true) {
        $currentHour < 12 => 'Good morning',
        $currentHour < 17 => 'Good afternoon',
        default => 'Good evening',
    };

    $portfolioValueNumber = (float) ($portfolioValue ?? 0);
    $cashValueNumber = (float) ($cashValue ?? 0);

    $cashPercentage = $portfolioValueNumber > 0
        ? ($cashValueNumber / $portfolioValueNumber) * 100
        : 0;

    $heroHelmScore = data_get(
        $helmScore,
        'overall_score'
    );

    $heroHelmLabel = data_get(
        $helmScore,
        'overall_label',
        'Building your score'
    );

    $heroAudit = is_array($advisorAudit ?? null)
        ? $advisorAudit
        : [];

    $heroAuditScore = data_get(
        $heroAudit,
        'overall_score'
    );

    $heroAuditLabel = data_get(
        $heroAudit,
        'overall_label',
        'Building your audit'
    );

    $scoreTone = function (?int $score): string {
        return match (true) {
            $score === null =>
                'border-slate-200 bg-slate-50 text-slate-700',

            $score >= 80 =>
                'border-emerald-200 bg-emerald-50 text-emerald-800',

            $score >= 65 =>
                'border-blue-200 bg-blue-50 text-blue-800',

            $score >= 50 =>
                'border-amber-200 bg-amber-50 text-amber-800',

            default =>
                'border-red-200 bg-red-50 text-red-800',
        };
    };
@endphp

<section
    class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-xl"
>
    <div
        class="relative overflow-hidden px-5 py-6 sm:px-8 sm:py-8 lg:px-10 lg:py-10"
    >
        <div
            class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"
        ></div>

        <div
            class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"
        ></div>

        <div class="relative">
            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="text-sm font-medium text-blue-300">
                        {{ $greeting }}, {{ $firstName }}
                    </p>

                    <h1
                        class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl"
                    >
                        Your portfolio at a glance
                    </h1>

                    <p
                        class="mt-2 max-w-2xl text-sm leading-6 text-slate-300"
                    >
                        Monitor your investments, review advisor performance,
                        and act on the findings that matter most.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <a
                        href="{{ route('accounts.create') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-slate-100"
                    >
                        Add Account
                    </a>

                    <a
                        href="{{ route('advisor-audit.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15"
                    >
                        View Audit
                    </a>
                </div>
            </div>

            <div class="mt-8">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                >
                    Total portfolio value
                </p>

                <p
                    class="mt-2 break-words text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl"
                >
                    {{ money($portfolioValueNumber) }}
                </p>

                <div
                    class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-300"
                >
                    <span>
                        Across
                        <strong class="font-semibold text-white">
                            {{ number_format((int) ($accountCount ?? 0)) }}
                        </strong>
                        {{ Str::plural(
                            'account',
                            (int) ($accountCount ?? 0)
                        ) }}
                    </span>

                    <span>
                        Cash
                        <strong class="font-semibold text-white">
                            {{ money($cashValueNumber) }}
                        </strong>
                    </span>

                    <span>
                        <strong class="font-semibold text-white">
                            {{ number_format($cashPercentage, 1) }}%
                        </strong>
                        held in cash
                    </span>
                </div>
            </div>

            <div
                class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
            >
                <a
                    href="{{ route('analytics.helm-score') }}"
                    class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur transition hover:bg-white/15"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-blue-200"
                            >
                                Helm Score
                            </p>

                            <p class="mt-2 text-3xl font-bold">
                                {{ $heroHelmScore ?? '—' }}
                            </p>
                        </div>

                        @if ($heroHelmScore !== null)
                            <span
                                class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $scoreTone((int) $heroHelmScore) }}"
                            >
                                / 100
                            </span>
                        @endif
                    </div>

                    <p class="mt-3 truncate text-xs text-slate-300">
                        {{ $heroHelmLabel }}
                    </p>
                </a>

                <a
                    href="{{ route('advisor-audit.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur transition hover:bg-white/15"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-indigo-200"
                            >
                                Advisor Audit
                            </p>

                            <p class="mt-2 text-3xl font-bold">
                                {{ $heroAuditScore ?? '—' }}
                            </p>
                        </div>

                        @if ($heroAuditScore !== null)
                            <span
                                class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $scoreTone((int) $heroAuditScore) }}"
                            >
                                / 100
                            </span>
                        @endif
                    </div>

                    <p class="mt-3 truncate text-xs text-slate-300">
                        {{ $heroAuditLabel }}
                    </p>
                </a>

                <a
                    href="{{ route('advisor-action-center.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur transition hover:bg-white/15"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-rose-200"
                    >
                        Open Findings
                    </p>

                    <p class="mt-2 text-3xl font-bold">
                        {{ number_format(
                            (int) ($totalAdvisorFindings ?? 0)
                        ) }}
                    </p>

                    <p class="mt-3 truncate text-xs text-slate-300">
                        Review prioritized actions
                    </p>
                </a>

                <a
                    href="{{ route('ai-insights.index') }}"
                    class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur transition hover:bg-white/15"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-fuchsia-200"
                    >
                        AI Insight
                    </p>

                    <p class="mt-2 text-lg font-semibold">
                        @if ($latestAiInsight ?? null)
                            {{ data_get(
                                $latestAiInsight,
                                'is_stale',
                                false
                            )
                                ? 'Updating'
                                : 'Current' }}
                        @else
                            Not generated
                        @endif
                    </p>

                    <p class="mt-3 truncate text-xs text-slate-300">
                        @if ($latestAiInsight ?? null)
                            {{ data_get(
                                $latestAiInsight,
                                'headline',
                                'View your latest portfolio insight'
                            ) }}
                        @else
                            Generate your first insight
                        @endif
                    </p>
                </a>
            </div>
        </div>
    </div>
</section>