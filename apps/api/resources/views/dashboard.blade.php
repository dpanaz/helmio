<x-app-layout>
    <x-slot name="header">
        <div class="hidden"></div>
    </x-slot>
    @php
        /*
        |--------------------------------------------------------------------------
        | Access
        |--------------------------------------------------------------------------
        */

        $hasPremiumAccess = app(
            \App\Services\Billing\SubscriptionAccessService::class
        )->hasPremiumAccess(auth()->user());


        /*
        |--------------------------------------------------------------------------
        | Portfolio Analysis State
        |--------------------------------------------------------------------------
        */

        $analysis =
            is_array($analysisState ?? null)
                ? $analysisState
                : [];

        $analysisIsRunning =
            (bool) (
                $analysis['is_running']
                ?? false
            );

        $analysisIsReady =
            (bool) (
                $analysis['is_ready']
                ?? false
            );

        $analysisHasFailed =
            (bool) (
                $analysis['has_failed']
                ?? false
            );

        $analysisProgress =
            (int) (
                $analysis['progress']
                ?? 0
            );

        $analysisHeadline =
            $analysis['headline']
            ?? 'Building your Helm Score';

        $analysisMessage =
            $analysis['message']
            ?? 'Helmio is analyzing your portfolio.';

        $analysisSteps =
            $analysis['steps']
            ?? [];

        /*
        |--------------------------------------------------------------------------
        | Advisor Audit
        |--------------------------------------------------------------------------
        */

        $audit = is_array($advisorAudit)
            ? $advisorAudit
            : [];

        $auditScore =
            $audit['overall_score']
            ?? null;

        $auditLabel =
            $audit['overall_label']
            ?? 'Building your score';

        $auditCompleteness = (float) (
            $audit['data_completeness']
            ?? 0
        );

        $auditFindings =
            $audit['findings']
            ?? [];

        $criticalFindings =
            $auditFindings['critical']
            ?? [];

        $importantFindings =
            $auditFindings['important']
            ?? [];

        $opportunityFindings =
            $auditFindings['opportunities']
            ?? [];

        $topConcern = collect(
            array_merge(
                $criticalFindings,
                $importantFindings,
            )
        )
            ->sortByDesc('priority')
            ->first();

        $topOpportunity = collect(
            $opportunityFindings
        )
            ->sortByDesc('priority')
            ->first();

        $auditCategories =
            $audit['categories']
            ?? [];

        /*
        |--------------------------------------------------------------------------
        | Suitability
        |--------------------------------------------------------------------------
        */

        $suitabilityCategory =
            $auditCategories['suitability']
            ?? [];

        $suitabilityScore =
            $suitabilityCategory['score']
            ?? null;

        $suitabilityLabel =
            $suitabilityCategory['label']
            ?? 'Complete your investor profile';

        $suitabilityMetrics =
            $suitabilityCategory['metrics']
            ?? [];

        $actualRiskLevel =
            $suitabilityMetrics['actual_risk_level']
            ?? null;

        $expectedRiskTolerance =
            $suitabilityMetrics['expected_risk_tolerance']
            ?? null;

        $riskGap =
            $suitabilityMetrics['risk_gap']
            ?? null;

        $profileCompleteness = (float) (
            $suitabilityMetrics['profile_completeness']
            ?? 0
        );

        $accountOverrideCount = (int) (
            $suitabilityMetrics['account_override_count']
            ?? 0
        );

        $riskLabel = fn (?string $value): string =>
            $value
                ? str($value)
                    ->replace('_', ' ')
                    ->title()
                : 'Not available';


        /*
        |--------------------------------------------------------------------------
        | Helm Score
        |--------------------------------------------------------------------------
        */

        $helmOverallScore = (int) (
            data_get(
                $helmScore,
                'overall_score',
                0
            )
            ?? 0
        );

        $helmOverallScore = min(
            100,
            max(
                0,
                $helmOverallScore
            )
        );

        $helmOverallLabel = data_get(
            $helmScore,
            'overall_label',
            'Building your score'
        );

        $helmScoreColor = match (true) {
            $helmOverallScore >= 90 =>
                '#22c55e',

            $helmOverallScore >= 80 =>
                '#10b981',

            $helmOverallScore >= 70 =>
                '#3b82f6',

            $helmOverallScore >= 60 =>
                '#f59e0b',

            $helmOverallScore >= 40 =>
                '#f97316',

            default =>
                '#ef4444',
        };

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categoryLabels = [
            'cost' => 'Cost',
            'diversification' => 'Diversification',
            'performance' => 'Performance',
            'risk' => 'Risk',
            'suitability' => 'Suitability',
            'trading' => 'Trading',
            'cash' => 'Cash',
            'tax' => 'Tax',
        ];

        $categoryRoutes = [
            'cost' => 'analytics.costs',
            'diversification' => 'analytics.diversification',
            'performance' => 'analytics.performance',
            'risk' => 'analytics.risk',
            'suitability' => 'investor-profile.edit',
            'trading' => 'analytics.trading-discipline',
            'cash' => 'analytics.cash-drag',
            'tax' => 'analytics.tax-efficiency',
        ];

        $scoreBreakdown = [
            [
                'key' => 'cost',
                'label' => 'Costs',
            ],
            [
                'key' => 'performance',
                'label' => 'Performance',
            ],
            [
                'key' => 'risk',
                'label' => 'Risk',
            ],
            [
                'key' => 'diversification',
                'label' => 'Diversification',
            ],
            [
                'key' => 'trading',
                'label' => 'Trading',
            ],
            [
                'key' => 'tax',
                'label' => 'Tax',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Findings
        |--------------------------------------------------------------------------
        */

        $accountsCollection =
            collect($accounts);

        $findingsCollection =
            collect($openFindings);

        $criticalCount =
            $findingCounts['critical']
            ?? $findingCounts['critical_count']
            ?? collect(
                $criticalFindings
            )->count();

        $importantCount =
            $findingCounts['important']
            ?? $findingCounts['important_count']
            ?? collect(
                $importantFindings
            )->count();

        $opportunityCount =
            $findingCounts['opportunity']
            ?? $findingCounts['opportunity_count']
            ?? collect(
                $opportunityFindings
            )->count();

        $totalAdvisorFindings =
            (int) $criticalCount
            + (int) $importantCount
            + (int) $opportunityCount;

        /*
        |--------------------------------------------------------------------------
        | Audit comparison
        |--------------------------------------------------------------------------
        */

        $scoreChange = data_get(
            $auditComparison,
            'score_change'
        );

        $scoreDirection = match (true) {
            $scoreChange === null => null,
            $scoreChange > 0 => 'up',
            $scoreChange < 0 => 'down',
            default => 'flat',
        };

        /*
        |--------------------------------------------------------------------------
        | AI Insight
        |--------------------------------------------------------------------------
        */

        $latestAiInsightIsStale =
            (bool) data_get(
                $latestAiInsight,
                'is_stale',
                false
            );

        $latestAiInsightGeneratedAt =
            data_get(
                $latestAiInsight,
                'generated_at'
            );

        /*
        |--------------------------------------------------------------------------
        | Portfolio summary
        |--------------------------------------------------------------------------
        */

        $portfolioValue = $accountsCollection
            ->sum(
                function ($account) {
                    return (float) (
                        data_get(
                            $account,
                            'current_value'
                        )
                        ?? data_get(
                            $account,
                            'market_value'
                        )
                        ?? data_get(
                            $account,
                            'value'
                        )
                        ?? 0
                    );
                }
            );

        $connectedAccountCount =
            $accountsCollection->count();

        /*
        |--------------------------------------------------------------------------
        | Cost data
        |--------------------------------------------------------------------------
        */

        $allInCostRate =
            data_get(
                $auditCategories,
                'cost.metrics.all_in_cost_rate'
            )
            ?? data_get(
                $auditCategories,
                'cost.metrics.total_cost_rate'
            )
            ?? null;

        $allInCostDollars =
            data_get(
                $auditCategories,
                'cost.metrics.total_annual_cost'
            )
            ?? data_get(
                $auditCategories,
                'cost.metrics.annual_cost'
            )
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Greeting
        |--------------------------------------------------------------------------
        */

        $hour = now()->hour;

        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $firstName =
            auth()->user()->name
                ? str(
                    auth()->user()->name
                )->before(' ')
                : 'there';

        /*
        |--------------------------------------------------------------------------
        | Main attention state
        |--------------------------------------------------------------------------
        */

        $needsAttention =
            $totalAdvisorFindings > 0;

        $topConcernSeverity =
            data_get(
                $topConcern,
                'severity',
                'moderate'
            );

        $topConcernClasses = match ($topConcernSeverity) {
            'critical',
            'high' =>
                [
                    'border' => 'border-red-500/30',
                    'background' => 'bg-red-500/[0.06]',
                    'badge' => 'border-red-500/30 bg-red-500/10 text-red-300',
                    'icon' => 'bg-red-500/10 text-red-300',
                ],

            'important',
            'moderate',
            'medium' =>
                [
                    'border' => 'border-amber-500/30',
                    'background' => 'bg-amber-500/[0.06]',
                    'badge' => 'border-amber-500/30 bg-amber-500/10 text-amber-300',
                    'icon' => 'bg-amber-500/10 text-amber-300',
                ],

            default =>
                [
                    'border' => 'border-blue-500/30',
                    'background' => 'bg-blue-500/[0.06]',
                    'badge' => 'border-blue-500/30 bg-blue-500/10 text-blue-300',
                    'icon' => 'bg-blue-500/10 text-blue-300',
                ],
        };
    @endphp

    <div class="min-h-screen bg-slate-950">

        @if (! $hasPremiumAccess)

            {{-- ========================================================= --}}
            {{-- NON-PREMIUM --}}
            {{-- ========================================================= --}}

            <div
                class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
            >
                <section
                    class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
                >
                    <div
                        class="grid gap-10 p-6 sm:p-8 lg:grid-cols-2 lg:p-12"
                    >
                        <div>
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
                            >
                                Subscription required
                            </span>

                            <h1
                                class="mt-6 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Unlock your Helmio dashboard.
                            </h1>

                            <p
                                class="mt-4 max-w-xl text-base leading-8 text-slate-300"
                            >
                                Connect your investment accounts and let Helmio
                                monitor fees, performance, risk, diversification,
                                trading activity, taxes, and advisor behavior.
                            </p>

                            <div
                                class="mt-7 flex flex-col gap-3 sm:flex-row"
                            >
                                <a
                                    href="{{ route('billing.pricing') }}"
                                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500"
                                >
                                    Start Free Trial
                                </a>

                                <a
                                    href="{{ route('investor-profile.edit') }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-5 py-3 font-semibold text-slate-200 transition hover:border-slate-600"
                                >
                                    Complete Investor Profile
                                </a>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-slate-800 bg-slate-950 p-5"
                        >
                            <p class="text-sm font-semibold text-white">
                                Premium includes
                            </p>

                            <div class="mt-5 space-y-4">
                                @foreach ([
                                    'Secure read-only brokerage connections',
                                    'Automatic holdings and transaction sync',
                                    'Helm Score and portfolio analytics',
                                    'Advisor Audit and Action Center',
                                    'AI portfolio insights',
                                    'Ongoing monitoring and alerts',
                                ] as $feature)
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5 text-emerald-400"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2.5"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m5 12 4 4L19 6"
                                                />
                                            </svg>
                                        </div>

                                        <span
                                            class="text-sm leading-6 text-slate-300"
                                        >
                                            {{ $feature }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            </div>

        @else

            {{-- ========================================================= --}}
            {{-- PREMIUM --}}
            {{-- ========================================================= --}}

            <div
                class="mx-auto max-w-[1400px] px-4 py-5 sm:px-6 sm:py-6 lg:px-7"
            >

                {{-- ===================================================== --}}
                {{-- GREETING --}}
                {{-- ===================================================== --}}

                <div
                    class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Portfolio overview
                        </p>

                        <h1
                            class="mt-1.5 text-2xl font-semibold tracking-tight text-white sm:text-3xl"
                        >
                            {{ $greeting }}, {{ $firstName }}.
                        </h1>

                        <p
                            class="mt-1.5 text-sm leading-5 text-slate-400"
                        >
                            Here’s what Helmio sees across your investments.
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-xs font-medium text-slate-400"
                    >
                        <svg
                            class="h-4 w-4 text-slate-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 3v3m8-3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"
                            />
                        </svg>

                        Updated {{ now()->format('M j, Y') }}
                    </div>
                </div>


                {{-- ===================================================== --}}
                {{-- EXECUTIVE SUMMARY --}}
                {{-- ===================================================== --}}

                @if ($analysisIsRunning)

                    {{-- Analysis progress spans the full row while rebuilding --}}
                    <section
                        data-analysis-container
                        data-analysis-running="1"
                        class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
                    >
                        <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-2 lg:items-center">
                            <div>
                                <div
                                    class="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-3 py-1.5"
                                >
                                    <span class="relative flex h-2 w-2">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                                        <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-400"></span>
                                    </span>

                                    <span class="text-xs font-semibold text-blue-300">
                                        Portfolio analysis in progress
                                    </span>
                                </div>

                                <h2
                                    data-analysis-headline
                                    class="mt-4 text-xl font-semibold tracking-tight text-white sm:text-2xl"
                                >
                                    {{ $analysisHeadline }}
                                </h2>

                                <p
                                    data-analysis-message
                                    class="mt-2 text-sm leading-6 text-slate-400"
                                >
                                    {{ $analysisMessage }}
                                </p>

                                <div class="mt-5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500">
                                            Analysis progress
                                        </span>

                                        <span
                                            data-analysis-progress-label
                                            class="font-semibold text-blue-300"
                                        >
                                            {{ $analysisProgress }}%
                                        </span>
                                    </div>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                                        <div
                                            data-analysis-progress
                                            class="h-full rounded-full bg-blue-500 transition-all duration-700"
                                            style="width: {{ $analysisProgress }}%"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                    What Helmio is doing
                                </p>

                                <div
                                    data-analysis-steps
                                    class="mt-4 grid gap-3 sm:grid-cols-2"
                                >
                                    @foreach ($analysisSteps as $step)
                                        @php
                                            $stepStatus =
                                                $step['status']
                                                ?? 'pending';
                                        @endphp

                                        <div class="flex items-center gap-2.5">
                                            @if ($stepStatus === 'complete')
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-300">
                                                    <svg
                                                        class="h-3.5 w-3.5"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="2.5"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="m5 12 4 4L19 6"
                                                        />
                                                    </svg>
                                                </div>
                                            @elseif ($stepStatus === 'active')
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                                    <div class="h-2 w-2 animate-pulse rounded-full bg-blue-400"></div>
                                                </div>
                                            @else
                                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-700">
                                                    <div class="h-1.5 w-1.5 rounded-full bg-slate-700"></div>
                                                </div>
                                            @endif

                                            <span
                                                class="text-xs font-medium {{ $stepStatus === 'active'
                                                    ? 'text-white'
                                                    : (
                                                        $stepStatus === 'complete'
                                                            ? 'text-slate-300'
                                                            : 'text-slate-600'
                                                    )
                                                }}"
                                            >
                                                {{ $step['label'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>

                @elseif ($analysisHasFailed)

                    <section
                        data-analysis-container
                        data-analysis-running="0"
                        class="overflow-hidden rounded-2xl border border-amber-500/20 bg-slate-900 shadow-lg"
                    >
                        <div class="flex items-start gap-4 p-5 sm:p-6">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-300">
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M10.3 4.5 2.6 18a1 1 0 0 0 .87 1.5h17.06a1 1 0 0 0 .87-1.5L13.7 4.5a1 1 0 0 0-1.74 0Z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-amber-400">
                                    Analysis delayed
                                </p>

                                <h2 class="mt-1 text-xl font-semibold text-white">
                                    {{ $analysisHeadline }}
                                </h2>

                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                    {{ $analysisMessage }}
                                </p>
                            </div>
                        </div>
                    </section>

                @else

                    {{-- ================================================= --}}
                    {{-- TOP ROW: HELM SCORE + SCORE BREAKDOWN              --}}
                    {{-- ================================================= --}}

                    <div
                        data-analysis-container
                        data-analysis-running="0"
                        class="flex flex-col gap-4 lg:flex-row"
                    >

                        {{-- ================================================= --}}
                        {{-- LEFT CARD: HELM SCORE                             --}}
                        {{-- ================================================= --}}
                        <section
                            class="flex min-w-0 flex-col rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg sm:p-6 lg:w-1/3"
                        >
                            <div class="flex items-center gap-2">
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300"
                                >
                                    Helm Score
                                </p>

                                <span
                                    class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 text-[9px] text-slate-500"
                                    title="A 0–100 summary of portfolio health."
                                >
                                    ?
                                </span>
                            </div>

                            <div
                                class="flex flex-1 flex-col items-center justify-center py-4"
                            >
                                <div
                                    data-helm-score-dial
                                    data-score="{{ $helmOverallScore }}"
                                    class="relative flex h-52 w-52 items-center justify-center"
                                >
                                    <svg
                                        class="absolute inset-0 h-full w-full -rotate-90"
                                        viewBox="0 0 240 240"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="120"
                                            cy="120"
                                            r="98"
                                            fill="none"
                                            stroke="#1e293b"
                                            stroke-width="16"
                                        />

                                        <circle
                                            data-helm-score-ring
                                            cx="120"
                                            cy="120"
                                            r="98"
                                            fill="none"
                                            stroke="{{ $helmScoreColor }}"
                                            stroke-width="16"
                                            stroke-linecap="round"
                                            pathLength="100"
                                            stroke-dasharray="100"
                                            stroke-dashoffset="100"
                                        />
                                    </svg>

                                    <div
                                        class="relative flex h-36 w-36 flex-col items-center justify-center rounded-full border border-slate-800 bg-slate-950"
                                    >
                                        <div class="flex items-baseline">
                                            <span
                                                data-helm-score-number
                                                class="text-5xl font-semibold tracking-tight text-white"
                                            >
                                                0
                                            </span>

                                            <span
                                                class="ml-1 text-xs font-medium text-slate-500"
                                            >
                                                /100
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 text-base font-semibold"
                                            style="color: {{ $helmScoreColor }}"
                                        >
                                            {{ $helmOverallLabel }}
                                        </p>
                                    </div>
                                </div>

                                @if ($needsAttention)
                                    <p
                                        class="mt-4 max-w-xs text-center text-sm leading-5 text-slate-300"
                                    >
                                        Your portfolio is being monitored and
                                        <span class="font-semibold text-white">
                                            {{ $totalAdvisorFindings }}
                                            {{ Str::plural(
                                                'item',
                                                $totalAdvisorFindings
                                            ) }}
                                        </span>
                                        may deserve your attention.
                                    </p>
                                @else
                                    <p
                                        class="mt-4 max-w-xs text-center text-sm leading-5 text-slate-300"
                                    >
                                        Helmio has not identified any major
                                        open concerns.
                                    </p>
                                @endif

                                <a
                                    href="{{ route('analytics.helm-score') }}"
                                    class="mt-4 inline-flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:border-blue-500 hover:bg-slate-900"
                                >
                                    View Full Report

                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m9 18 6-6-6-6"
                                        />
                                    </svg>
                                </a>
                            </div>
                        </section>


                        {{-- ================================================= --}}
                        {{-- RIGHT CARD: SCORE BREAKDOWN                       --}}
                        {{-- ================================================= --}}
                        <section
                            class="min-w-0 rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg sm:p-6 lg:w-2/3"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div class="flex items-center gap-2">
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300"
                                    >
                                        Score Breakdown
                                    </p>

                                    <span
                                        class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-slate-700 text-[9px] text-slate-500"
                                        title="The six categories used to calculate the Helm Score."
                                    >
                                        ?
                                    </span>
                                </div>

                                <span class="text-xs text-slate-500">
                                    0–100
                                </span>
                            </div>

                            <div class="mt-3 divide-y divide-slate-800">
                                @foreach ($scoreBreakdown as $item)
                                    @php
                                        $categoryScore =
                                            data_get(
                                                $helmScore,
                                                'categories.'
                                                . $item['key']
                                                . '.score'
                                            )
                                            ?? data_get(
                                                $auditCategories,
                                                $item['key']
                                                . '.score'
                                            );

                                        $categoryScore =
                                            $categoryScore !== null
                                                ? min(
                                                    100,
                                                    max(
                                                        0,
                                                        (int) $categoryScore
                                                    )
                                                )
                                                : null;

                                        $categoryStatus = match (true) {
                                            $categoryScore === null =>
                                                'More data needed',

                                            $categoryScore >= 90 =>
                                                'Excellent',

                                            $categoryScore >= 80 =>
                                                'Strong',

                                            $categoryScore >= 70 =>
                                                'Good',

                                            $categoryScore >= 60 =>
                                                'Needs review',

                                            default =>
                                                'Attention needed',
                                        };

                                        $categoryScoreClass = match (true) {
                                            $categoryScore === null =>
                                                'text-slate-500',

                                            $categoryScore >= 80 =>
                                                'text-emerald-300',

                                            $categoryScore >= 70 =>
                                                'text-blue-300',

                                            $categoryScore >= 60 =>
                                                'text-amber-300',

                                            default =>
                                                'text-red-300',
                                        };

                                        $categoryBarClass = match (true) {
                                            $categoryScore === null =>
                                                'bg-slate-700',

                                            $categoryScore >= 80 =>
                                                'bg-emerald-400',

                                            $categoryScore >= 70 =>
                                                'bg-blue-500',

                                            $categoryScore >= 60 =>
                                                'bg-amber-400',

                                            default =>
                                                'bg-red-500',
                                        };
                                    @endphp

                                    <a
                                        href="{{ route(
                                            $categoryRoutes[$item['key']]
                                        ) }}"
                                        class="group block py-3.5"
                                    >
                                        <div
                                            class="flex flex-col gap-2 sm:flex-row sm:items-center"
                                        >
                                            {{-- Icon --}}
                                            <div
                                                class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400 sm:flex"
                                            >
                                                @switch($item['key'])
                                                    @case('cost')
                                                        <span class="text-lg font-semibold">
                                                            $
                                                        </span>
                                                        @break

                                                    @case('performance')
                                                        <svg
                                                            class="h-5 w-5"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="1.9"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="m4 16 5-5 4 3 7-8"
                                                            />
                                                        </svg>
                                                        @break

                                                    @case('risk')
                                                        <svg
                                                            class="h-5 w-5"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="1.9"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M12 3 5.25 5.25v5.625c0 4.065 2.73 7.83 6.75 9.375 4.02-1.545 6.75-5.31 6.75-9.375V5.25L12 3Z"
                                                            />
                                                        </svg>
                                                        @break

                                                    @case('diversification')
                                                        <svg
                                                            class="h-5 w-5"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="1.9"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M12 3v9h9A9 9 0 1 1 12 3Z"
                                                            />
                                                        </svg>
                                                        @break

                                                    @case('trading')
                                                        <svg
                                                            class="h-5 w-5"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="1.9"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M4 7h13m0 0-3-3m3 3-3 3M20 17H7m0 0 3 3m-3-3 3-3"
                                                            />
                                                        </svg>
                                                        @break

                                                    @case('tax')
                                                        <svg
                                                            class="h-5 w-5"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="1.9"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M6 3.75h12v16.5H6V3.75Zm3 3h6M9 10h1.5m3 0H15M9 13.25h1.5m3 0H15M9 16.5h1.5m3 0H15"
                                                            />
                                                        </svg>
                                                        @break
                                                @endswitch
                                            </div>

                                            {{-- Label / Status --}}
                                            <div
                                                class="min-w-0 shrink-0 sm:w-36 lg:w-40"
                                            >
                                                <p
                                                    class="truncate text-sm font-semibold text-white transition group-hover:text-blue-300"
                                                >
                                                    {{ $item['label'] }}
                                                </p>

                                                <p
                                                    class="mt-0.5 truncate text-xs text-slate-500"
                                                >
                                                    {{ $categoryStatus }}
                                                </p>
                                            </div>

                                            {{-- Bar --}}
                                            <div
                                                class="min-w-0 flex-1"
                                            >
                                                <div
                                                    class="h-2 overflow-hidden rounded-full bg-slate-800"
                                                >
                                                    @if ($categoryScore !== null)
                                                        <div
                                                            class="h-full rounded-full {{ $categoryBarClass }} transition-all duration-500"
                                                            style="width: {{ $categoryScore }}%"
                                                        ></div>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Score --}}
                                            <span
                                                class="w-10 shrink-0 text-right text-base font-semibold {{ $categoryScoreClass }}"
                                            >
                                                {{ $categoryScore ?? '—' }}
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    </div>

                @endif


                {{-- ===================================================== --}}
                {{-- SECONDARY KPI ROW --}}
                {{-- ===================================================== --}}

                <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

                    {{-- Estimated annual cost --}}
                    <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Estimated Annual Cost
                        </p>

                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-300">
                                <span class="text-xl">$</span>
                            </div>

                            <div>
                                <p class="text-2xl font-semibold tracking-tight text-white">
                                    @if ($allInCostDollars !== null)
                                        {{ money($allInCostDollars) }}
                                    @else
                                        —
                                    @endif
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    @if ($allInCostRate !== null)
                                        {{ number_format((float) $allInCostRate, 2) }}% of assets annually
                                    @else
                                        Cost data is still being calculated
                                    @endif
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('analytics.costs') }}"
                            class="mt-5 inline-flex text-xs font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View Cost Analysis →
                        </a>
                    </section>

                    {{-- Top finding --}}
                    <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Top Finding
                        </p>

                        @if ($topConcern)
                            <h3 class="mt-4 line-clamp-2 text-base font-semibold leading-6 text-white">
                                {{ $topConcern['title'] ?? 'Portfolio finding' }}
                            </h3>

                            <p class="mt-2 line-clamp-2 text-xs leading-5 text-slate-500">
                                {{ $topConcern['message'] ?? 'Review this portfolio finding.' }}
                            </p>

                            <a
                                href="{{ route('advisor-action-center.index') }}"
                                class="mt-5 inline-flex rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:border-blue-500 hover:text-white"
                            >
                                See Recommendation
                            </a>
                        @else
                            <div class="mt-4">
                                <p class="text-base font-semibold text-emerald-300">
                                    No urgent finding
                                </p>

                                <p class="mt-2 text-xs leading-5 text-slate-500">
                                    Helmio has not identified a major open concern.
                                </p>
                            </div>
                        @endif
                    </section>

                    {{-- Accounts --}}
                    <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Accounts
                        </p>

                        <div class="mt-4 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-3xl font-semibold text-white">
                                    {{ number_format($connectedAccountCount) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Connected
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-200">
                                    {{ money($portfolioValue) }}
                                </p>

                                <p class="mt-1 text-[10px] uppercase tracking-wide text-slate-600">
                                    Total value
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full w-full rounded-full bg-blue-500"></div>
                        </div>

                        <a
                            href="{{ route('accounts.index') }}"
                            class="mt-5 inline-flex text-xs font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View Accounts →
                        </a>
                    </section>

                    {{-- Advisor audit --}}
                    <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-lg">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                            Advisor Audit
                        </p>

                        <div class="mt-4 flex items-baseline gap-1">
                            <p class="text-3xl font-semibold text-white">
                                {{ $auditScore ?? '—' }}
                            </p>

                            @if ($auditScore !== null)
                                <span class="text-xs text-slate-500">/100</span>
                            @endif
                        </div>

                        <p class="mt-1 text-sm font-semibold text-emerald-300">
                            {{ $auditLabel }}
                        </p>

                        <p class="mt-3 text-xs text-slate-500">
                            Data completeness:
                            {{ number_format($auditCompleteness * 100) }}%
                        </p>

                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                            <div
                                class="h-full rounded-full bg-emerald-400"
                                style="width: {{ min(100, max(0, $auditCompleteness * 100)) }}%"
                            ></div>
                        </div>

                        <a
                            href="{{ route('advisor-audit.report') }}"
                            class="mt-5 inline-flex text-xs font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View Audit →
                        </a>
                    </section>
                </div>


                {{-- ===================================================== --}}
                {{-- NEEDS YOUR ATTENTION --}}
                {{-- ===================================================== --}}

                <section
                    class="mt-4 rounded-2xl border {{ $needsAttention ? 'border-red-500/30 bg-red-500/[0.05]' : 'border-emerald-500/20 bg-emerald-500/[0.04]' }} px-5 py-4 shadow-lg"
                >
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $needsAttention ? 'bg-red-500/10 text-red-300' : 'bg-emerald-500/10 text-emerald-300' }}"
                            >
                                @if ($needsAttention)
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 4.5 2.6 18a1 1 0 0 0 .87 1.5h17.06a1 1 0 0 0 .87-1.5L13.7 4.5a1 1 0 0 0-1.74 0Z" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                    </svg>
                                @endif
                            </div>

                            <div>
                                <h2 class="text-base font-semibold text-white">
                                    @if ($needsAttention)
                                        {{ number_format($totalAdvisorFindings) }}
                                        {{ Str::plural('item', $totalAdvisorFindings) }}
                                        need your attention
                                    @else
                                        Nothing urgent needs your attention
                                    @endif
                                </h2>

                                <p class="mt-1 text-xs text-slate-500">
                                    @if ($needsAttention)
                                        Review these findings to strengthen your portfolio.
                                    @else
                                        Helmio has not identified a major open concern.
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($needsAttention)
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="min-w-[5.5rem] rounded-lg border border-red-500/20 bg-red-500/[0.05] px-3 py-2 text-center">
                                    <p class="text-base font-semibold text-red-300">
                                        {{ number_format((int) $criticalCount) }}
                                    </p>
                                    <p class="text-[10px] uppercase tracking-wide text-red-400/80">
                                        Critical
                                    </p>
                                </div>

                                <div class="min-w-[5.5rem] rounded-lg border border-orange-500/20 bg-orange-500/[0.05] px-3 py-2 text-center">
                                    <p class="text-base font-semibold text-orange-300">
                                        {{ number_format((int) $importantCount) }}
                                    </p>
                                    <p class="text-[10px] uppercase tracking-wide text-orange-400/80">
                                        Important
                                    </p>
                                </div>

                                <div class="min-w-[5.5rem] rounded-lg border border-amber-500/20 bg-amber-500/[0.05] px-3 py-2 text-center">
                                    <p class="text-base font-semibold text-amber-300">
                                        {{ number_format((int) $opportunityCount) }}
                                    </p>
                                    <p class="text-[10px] uppercase tracking-wide text-amber-400/80">
                                        Opportunities
                                    </p>
                                </div>

                                <a
                                    href="{{ route('advisor-action-center.index') }}"
                                    class="ml-0 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-500 lg:ml-2"
                                >
                                    Review Action Center
                                </a>
                            </div>
                        @endif
                    </div>
                </section>


                {{-- ===================================================== --}}
                {{-- RECENT ACTIVITY + AI INSIGHT --}}
                {{-- ===================================================== --}}

                <div class="mt-4 grid gap-4 xl:grid-cols-[1.25fr_0.75fr]">

                    {{-- Recent Activity --}}
                    <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg">
                        <div class="flex items-center justify-between border-b border-slate-800 px-5 py-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                                    Recent Activity
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Portfolio findings and monitoring events.
                                </p>
                            </div>

                            <a
                                href="{{ route('advisor-audit.index') }}"
                                class="text-xs font-semibold text-blue-400 hover:text-blue-300"
                            >
                                View All
                            </a>
                        </div>

                        <div class="divide-y divide-slate-800">
                            @forelse (
                                $findingsCollection->take(5)
                                as $finding
                            )
                                @php
                                    $severity = data_get(
                                        $finding,
                                        'severity',
                                        'moderate'
                                    );

                                    $alertColor = match ($severity) {
                                        'critical',
                                        'high' =>
                                            'text-red-300 bg-red-500/10',

                                        'moderate',
                                        'medium' =>
                                            'text-amber-300 bg-amber-500/10',

                                        'positive' =>
                                            'text-emerald-300 bg-emerald-500/10',

                                        default =>
                                            'text-blue-300 bg-blue-500/10',
                                    };
                                @endphp

                                <div class="flex gap-3 px-5 py-3.5">
                                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $alertColor }}">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 4.5 2.6 18a1 1 0 0 0 .87 1.5h17.06a1 1 0 0 0 .87-1.5L13.7 4.5a1 1 0 0 0-1.74 0Z" />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="truncate text-sm font-medium text-white">
                                                {{ data_get($finding, 'title', 'Portfolio finding') }}
                                            </p>

                                            <span class="shrink-0 text-[10px] capitalize text-slate-600">
                                                {{ $severity }}
                                            </span>
                                        </div>

                                        <p class="mt-1 line-clamp-1 text-xs leading-5 text-slate-500">
                                            {{ data_get($finding, 'message', '') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-8 text-center">
                                    <p class="text-sm font-medium text-emerald-300">
                                        No open alerts
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Nothing currently requires immediate attention.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    {{-- AI Portfolio Insight --}}
                    <section class="rounded-2xl border border-violet-500/20 bg-slate-900 p-5 shadow-lg">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-violet-500/30 bg-violet-500/10 text-violet-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-violet-400">
                                    AI Portfolio Insight
                                </p>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    Plain-English context from Helmio analytics.
                                </p>
                            </div>
                        </div>

                        @if ($latestAiInsight)
                            <div class="mt-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($latestAiInsightIsStale)
                                        <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-[10px] font-semibold text-amber-300">
                                            Needs refresh
                                        </span>
                                    @else
                                        <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold text-emerald-300">
                                            Current
                                        </span>
                                    @endif

                                    @if ($latestAiInsightGeneratedAt)
                                        <span class="text-[10px] text-slate-600">
                                            Generated {{ $latestAiInsightGeneratedAt->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="mt-3 line-clamp-2 text-base font-semibold leading-6 text-white">
                                    {{ data_get(
                                        $latestAiInsight,
                                        'headline',
                                        data_get(
                                            $latestAiInsight,
                                            'title',
                                            'Portfolio insight'
                                        )
                                    ) }}
                                </h3>

                                <p class="mt-2 line-clamp-4 text-xs leading-5 text-slate-400">
                                    {{ data_get(
                                        $latestAiInsight,
                                        'summary',
                                        data_get(
                                            $latestAiInsight,
                                            'content',
                                            'Your latest Helmio insight is ready.'
                                        )
                                    ) }}
                                </p>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($latestAiInsightIsStale)
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'ai-insights.regenerate',
                                                $latestAiInsight
                                            ) }}"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-violet-500"
                                            >
                                                Regenerate
                                            </button>
                                        </form>
                                    @endif

                                    @if (data_get($latestAiInsight, 'id'))
                                        <a
                                            href="{{ route(
                                                'ai-insights.show',
                                                $latestAiInsight
                                            ) }}"
                                            class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-violet-500 hover:text-white"
                                        >
                                            Read Insight
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="mt-4">
                                <p class="text-sm font-medium text-white">
                                    No AI insight yet
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Generate a plain-English explanation of your latest portfolio analytics.
                                </p>

                                <a
                                    href="{{ route('ai-insights.index') }}"
                                    class="mt-4 inline-flex rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-violet-500"
                                >
                                    Generate Insight
                                </a>
                            </div>
                        @endif
                    </section>
                </div>


                {{-- ===================================================== --}}
                {{-- SUITABILITY --}}
                {{-- ===================================================== --}}

                <div class="mt-4">
                    @include(
                        'dashboard.partials.suitability'
                    )
                </div>


                {{-- ===================================================== --}}
                {{-- ACCOUNTS --}}
                {{-- ===================================================== --}}

                <section
                    class="mt-4 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
                >
                    <div
                        class="flex flex-col gap-2 border-b border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-white"
                            >
                                Investment Accounts
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-400"
                            >
                                Accounts included in Helmio analytics.
                            </p>
                        </div>

                        <a
                            href="{{ route('accounts.index') }}"
                            class="text-sm font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View All
                        </a>
                    </div>

                    @if ($accountsCollection->isEmpty())

                        <div class="px-6 py-12 text-center">

                            <p
                                class="text-sm font-medium text-slate-300"
                            >
                                No investment accounts connected
                            </p>

                            <a
                                href="{{ route('accounts.create') }}"
                                class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                Add Account
                            </a>
                        </div>

                    @else

                        {{-- Desktop --}}
                        <div class="hidden overflow-x-auto md:block">
                            <table class="w-full min-w-full text-sm">

                                <thead class="bg-slate-950">
                                    <tr>
                                        <th
                                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Account
                                        </th>

                                        <th
                                            class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Institution
                                        </th>

                                        <th
                                            class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Value
                                        </th>

                                        <th
                                            class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Holdings
                                        </th>

                                        <th
                                            class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Profile
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-800">

                                    @foreach (
                                        $accountsCollection
                                        as $account
                                    )

                                        @php
                                            $accountValue =
                                                data_get(
                                                    $account,
                                                    'current_value'
                                                )
                                                ?? data_get(
                                                    $account,
                                                    'market_value'
                                                )
                                                ?? data_get(
                                                    $account,
                                                    'value'
                                                )
                                                ?? 0;

                                            $holdingCount =
                                                data_get(
                                                    $account,
                                                    'holdings_count'
                                                )
                                                ?? collect(
                                                    data_get(
                                                        $account,
                                                        'holdings',
                                                        []
                                                    )
                                                )->count();

                                            $institutionName =
                                                data_get(
                                                    $account,
                                                    'institution.name'
                                                )
                                                ?? data_get(
                                                    $account,
                                                    'institution_name'
                                                )
                                                ?? data_get(
                                                    $account,
                                                    'institution'
                                                )
                                                ?? '—';
                                        @endphp

                                        <tr
                                            class="transition hover:bg-slate-800/40"
                                        >
                                            <td
                                                class="whitespace-nowrap px-5 py-3"
                                            >
                                                <a
                                                    href="{{ route(
                                                        'accounts.holdings.index',
                                                        $account
                                                    ) }}"
                                                    class="font-medium text-white hover:text-blue-400"
                                                >
                                                    {{ data_get(
                                                        $account,
                                                        'name',
                                                        'Investment Account'
                                                    ) }}
                                                </a>
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-5 py-3 text-slate-400"
                                            >
                                                {{ $institutionName }}
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-5 py-3 text-right font-semibold text-white"
                                            >
                                                {{ money(
                                                    $accountValue
                                                ) }}
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-5 py-3 text-right text-slate-300"
                                            >
                                                {{ number_format(
                                                    $holdingCount
                                                ) }}
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-5 py-3 text-right"
                                            >
                                                <a
                                                    href="{{ route(
                                                        'accounts.profile.edit',
                                                        $account
                                                    ) }}"
                                                    class="text-xs font-semibold text-blue-400 hover:text-blue-300"
                                                >
                                                    Suitability
                                                </a>
                                            </td>
                                        </tr>

                                    @endforeach

                                </tbody>
                            </table>
                        </div>


                        {{-- Mobile --}}
                        <div
                            class="divide-y divide-slate-800 md:hidden"
                        >
                            @foreach (
                                $accountsCollection
                                as $account
                            )

                                @php
                                    $accountValue =
                                        data_get(
                                            $account,
                                            'current_value'
                                        )
                                        ?? data_get(
                                            $account,
                                            'market_value'
                                        )
                                        ?? data_get(
                                            $account,
                                            'value'
                                        )
                                        ?? 0;

                                    $holdingCount =
                                        data_get(
                                            $account,
                                            'holdings_count'
                                        )
                                        ?? collect(
                                            data_get(
                                                $account,
                                                'holdings',
                                                []
                                            )
                                        )->count();

                                    $institutionName =
                                        data_get(
                                            $account,
                                            'institution.name'
                                        )
                                        ?? data_get(
                                            $account,
                                            'institution_name'
                                        )
                                        ?? data_get(
                                            $account,
                                            'institution'
                                        )
                                        ?? 'Investment account';
                                @endphp

                                <article class="p-5">

                                    <div
                                        class="flex items-start justify-between gap-4"
                                    >
                                        <div class="min-w-0">
                                            <a
                                                href="{{ route(
                                                    'accounts.holdings.index',
                                                    $account
                                                ) }}"
                                                class="truncate font-semibold text-white"
                                            >
                                                {{ data_get(
                                                    $account,
                                                    'name',
                                                    'Investment Account'
                                                ) }}
                                            </a>

                                            <p
                                                class="mt-1 truncate text-xs text-slate-500"
                                            >
                                                {{ $institutionName }}
                                            </p>
                                        </div>

                                        <p
                                            class="shrink-0 font-semibold text-white"
                                        >
                                            {{ money(
                                                $accountValue
                                            ) }}
                                        </p>
                                    </div>

                                    <div
                                        class="mt-4 flex items-center justify-between"
                                    >
                                        <p
                                            class="text-xs text-slate-500"
                                        >
                                            {{ number_format(
                                                $holdingCount
                                            ) }}
                                            {{ Str::plural(
                                                'holding',
                                                $holdingCount
                                            ) }}
                                        </p>

                                        <a
                                            href="{{ route(
                                                'accounts.profile.edit',
                                                $account
                                            ) }}"
                                            class="text-xs font-semibold text-blue-400"
                                        >
                                            Suitability
                                        </a>
                                    </div>
                                </article>

                            @endforeach
                        </div>

                    @endif
                </section>

            </div>

        @endif
    </div>


    {{-- ============================================================= --}}
    {{-- HELM SCORE + LIVE ANALYSIS --}}
    {{-- ============================================================= --}}

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                function animateHelmScore() {
                    const dial =
                        document.querySelector(
                            '[data-helm-score-dial]'
                        );

                    if (!dial) {
                        return;
                    }

                    const scoreElement =
                        dial.querySelector(
                            '[data-helm-score-number]'
                        );

                    const ring =
                        dial.querySelector(
                            '[data-helm-score-ring]'
                        );

                    if (
                        !scoreElement
                        || !ring
                    ) {
                        return;
                    }

                    const target =
                        Math.max(
                            0,
                            Math.min(
                                100,
                                Number(
                                    dial.dataset.score
                                    || 0
                                )
                            )
                        );

                    const reducedMotion =
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches;

                    if (reducedMotion) {
                        scoreElement.textContent =
                            Math.round(target);

                        ring.style.strokeDashoffset =
                            String(
                                100 - target
                            );

                        return;
                    }

                    const duration = 1650;

                    const startTime =
                        performance.now();

                    function easeOutCubic(
                        progress
                    ) {
                        return 1 - Math.pow(
                            1 - progress,
                            3
                        );
                    }

                    function animate(
                        currentTime
                    ) {
                        const elapsed =
                            currentTime
                            - startTime;

                        const progress =
                            Math.min(
                                elapsed / duration,
                                1
                            );

                        const eased =
                            easeOutCubic(
                                progress
                            );

                        const current =
                            target * eased;

                        scoreElement.textContent =
                            Math.round(current);

                        ring.style.strokeDashoffset =
                            String(
                                100 - current
                            );

                        if (progress < 1) {
                            requestAnimationFrame(
                                animate
                            );
                        } else {
                            scoreElement.textContent =
                                Math.round(target);

                            ring.style.strokeDashoffset =
                                String(
                                    100 - target
                                );
                        }
                    }

                    requestAnimationFrame(
                        animate
                    );
                }

                animateHelmScore();

                const analysisContainer =
                    document.querySelector(
                        '[data-analysis-container]'
                    );

                if (
                    !analysisContainer
                    || analysisContainer.dataset.analysisRunning !== '1'
                ) {
                    return;
                }

                const statusUrl =
                    @json(
                        route(
                            'dashboard.analysis-status'
                        )
                    );

                let requestInFlight = false;
                let pollCount = 0;
                const maximumPolls = 120;

                function renderSteps(steps) {
                    const container =
                        document.querySelector(
                            '[data-analysis-steps]'
                        );

                    if (
                        !container
                        || !Array.isArray(steps)
                    ) {
                        return;
                    }

                    container.innerHTML =
                        steps.map(
                            function (step) {
                                const status =
                                    step.status
                                    || 'pending';

                                let icon = '';
                                let textClass =
                                    'text-slate-600';
                                let working = '';

                                if (status === 'complete') {
                                    icon = `
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-300">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"></path>
                                            </svg>
                                        </div>
                                    `;

                                    textClass =
                                        'text-slate-300';
                                } else if (status === 'active') {
                                    icon = `
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                            <div class="h-2.5 w-2.5 animate-pulse rounded-full bg-blue-400"></div>
                                        </div>
                                    `;

                                    textClass =
                                        'text-white';

                                    working = `
                                        <span class="ml-auto text-xs font-semibold text-blue-400">
                                            Working…
                                        </span>
                                    `;
                                } else {
                                    icon = `
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-slate-700">
                                            <div class="h-2 w-2 rounded-full bg-slate-700"></div>
                                        </div>
                                    `;
                                }

                                return `
                                    <div class="flex items-center gap-3">
                                        ${icon}
                                        <span class="text-sm font-medium ${textClass}">
                                            ${step.label}
                                        </span>
                                        ${working}
                                    </div>
                                `;
                            }
                        ).join('');
                }

                async function pollAnalysis() {
                    if (requestInFlight) {
                        return;
                    }

                    requestInFlight = true;

                    try {
                        const response =
                            await fetch(
                                statusUrl,
                                {
                                    method: 'GET',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },

                                    credentials:
                                        'same-origin',

                                    cache:
                                        'no-store',
                                }
                            );

                        if (!response.ok) {
                            throw new Error(
                                'Analysis status request failed.'
                            );
                        }

                        const state =
                            await response.json();

                        const headline =
                            document.querySelector(
                                '[data-analysis-headline]'
                            );

                        const message =
                            document.querySelector(
                                '[data-analysis-message]'
                            );

                        const progress =
                            document.querySelector(
                                '[data-analysis-progress]'
                            );

                        const progressLabel =
                            document.querySelector(
                                '[data-analysis-progress-label]'
                            );

                        if (headline) {
                            headline.textContent =
                                state.headline
                                || 'Analyzing your portfolio';
                        }

                        if (message) {
                            message.textContent =
                                state.message
                                || '';
                        }

                        const numericProgress =
                            Math.max(
                                0,
                                Math.min(
                                    100,
                                    Number(
                                        state.progress
                                        || 0
                                    )
                                )
                            );

                        if (progress) {
                            progress.style.width =
                                numericProgress + '%';
                        }

                        if (progressLabel) {
                            progressLabel.textContent =
                                Math.round(
                                    numericProgress
                                ) + '%';
                        }

                        renderSteps(
                            state.steps
                            || []
                        );

                        if (
                            state.is_ready
                            || state.has_failed
                        ) {
                            window.setTimeout(
                                function () {
                                    window.location.reload();
                                },
                                650
                            );

                            return;
                        }

                        pollCount++;

                        if (
                            pollCount
                            < maximumPolls
                        ) {
                            window.setTimeout(
                                pollAnalysis,
                                2500
                            );
                        }
                    } catch (error) {
                        pollCount++;

                        if (
                            pollCount
                            < maximumPolls
                        ) {
                            window.setTimeout(
                                pollAnalysis,
                                5000
                            );
                        }
                    } finally {
                        requestInFlight = false;
                    }
                }

                window.setTimeout(
                    pollAnalysis,
                    1500
                );
            }
        );
    </script>

</x-app-layout>