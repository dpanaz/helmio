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

        $greeting = 'Hello';

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
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
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
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-6"
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
                class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-9 lg:px-8"
            >

                {{-- ===================================================== --}}
                {{-- GREETING --}}
                {{-- ===================================================== --}}

                <div
                    class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Portfolio overview
                        </p>

                        <h1
                            class="mt-2 text-3xl font-semibold tracking-tight text-white"
                        >
                            {{ $greeting }}, {{ $firstName }}.
                        </h1>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-400"
                        >
                            Here’s what Helmio sees across your investments.
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-xs font-medium text-slate-400"
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

                <div
                    class="grid gap-5 xl:grid-cols-[1.65fr_1fr]"
                >

                    {{-- Helm Score / Analysis Progress --}}
                    <section
                        data-analysis-container
                        data-analysis-running="{{ $analysisIsRunning ? '1' : '0' }}"
                        class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                    >
                        @if ($analysisIsRunning)

                            <div
                                class="grid gap-8 p-6 sm:p-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-center"
                            >
                                <div>
                                    <div
                                        class="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-3 py-1.5"
                                    >
                                        <span class="relative flex h-2 w-2">
                                            <span
                                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"
                                            ></span>

                                            <span
                                                class="relative inline-flex h-2 w-2 rounded-full bg-blue-400"
                                            ></span>
                                        </span>

                                        <span
                                            class="text-xs font-semibold text-blue-300"
                                        >
                                            Portfolio analysis in progress
                                        </span>
                                    </div>

                                    <h2
                                        data-analysis-headline
                                        class="mt-5 text-2xl font-semibold tracking-tight text-white sm:text-3xl"
                                    >
                                        {{ $analysisHeadline }}
                                    </h2>

                                    <p
                                        data-analysis-message
                                        class="mt-3 max-w-xl text-sm leading-7 text-slate-300"
                                    >
                                        {{ $analysisMessage }}
                                    </p>

                                    <div class="mt-7">
                                        <div
                                            class="flex items-center justify-between text-xs"
                                        >
                                            <span
                                                class="font-medium text-slate-400"
                                            >
                                                Analysis progress
                                            </span>

                                            <span
                                                data-analysis-progress-label
                                                class="font-semibold text-blue-300"
                                            >
                                                {{ $analysisProgress }}%
                                            </span>
                                        </div>

                                        <div
                                            class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-800"
                                        >
                                            <div
                                                data-analysis-progress
                                                class="h-full rounded-full bg-blue-500 transition-all duration-700"
                                                style="width: {{ $analysisProgress }}%"
                                            ></div>
                                        </div>
                                    </div>

                                    <p
                                        class="mt-4 text-xs text-slate-500"
                                    >
                                        You can leave this page. Helmio will continue
                                        working in the background.
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-800 bg-slate-950 p-5 sm:p-6"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500"
                                    >
                                        What Helmio is doing
                                    </p>

                                    <div
                                        data-analysis-steps
                                        class="mt-5 space-y-4"
                                    >
                                        @foreach ($analysisSteps as $step)
                                            @php
                                                $stepStatus =
                                                    $step['status']
                                                    ?? 'pending';
                                            @endphp

                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                @if ($stepStatus === 'complete')

                                                    <div
                                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-300"
                                                    >
                                                        <svg
                                                            class="h-4 w-4"
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

                                                    <div
                                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-500/10"
                                                    >
                                                        <div
                                                            class="h-2.5 w-2.5 animate-pulse rounded-full bg-blue-400"
                                                        ></div>
                                                    </div>

                                                @else

                                                    <div
                                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-slate-700"
                                                    >
                                                        <div
                                                            class="h-2 w-2 rounded-full bg-slate-700"
                                                        ></div>
                                                    </div>

                                                @endif

                                                <span
                                                    class="text-sm font-medium
                                                        {{ $stepStatus === 'complete'
                                                            ? 'text-slate-300'
                                                            : (
                                                                $stepStatus === 'active'
                                                                    ? 'text-white'
                                                                    : 'text-slate-600'
                                                            )
                                                        }}"
                                                >
                                                    {{ $step['label'] }}
                                                </span>

                                                @if ($stepStatus === 'active')
                                                    <span
                                                        class="ml-auto text-xs font-semibold text-blue-400"
                                                    >
                                                        Working…
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        @elseif ($analysisHasFailed)

                            <div class="p-6 sm:p-8">
                                <div
                                    class="flex flex-col gap-5 sm:flex-row sm:items-start"
                                >
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-300"
                                    >
                                        <svg
                                            class="h-6 w-6"
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
                                        <p
                                            class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-400"
                                        >
                                            Analysis delayed
                                        </p>

                                        <h2
                                            class="mt-2 text-2xl font-semibold text-white"
                                        >
                                            {{ $analysisHeadline }}
                                        </h2>

                                        <p
                                            class="mt-3 max-w-2xl text-sm leading-7 text-slate-300"
                                        >
                                            {{ $analysisMessage }}
                                        </p>

                                        <p
                                            class="mt-3 text-xs text-slate-500"
                                        >
                                            Your brokerage connection has not been removed.
                                        </p>
                                    </div>
                                </div>
                            </div>

                        @else

                            <div
                                class="grid gap-8 p-6 sm:p-8 md:grid-cols-[auto_1fr] md:items-center"
                            >
                                <div class="flex justify-center">
                                    <div
                                        data-helm-score-dial
                                        data-score="{{ $helmOverallScore }}"
                                        class="relative flex h-56 w-56 items-center justify-center"
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
                                            class="relative flex h-40 w-40 flex-col items-center justify-center rounded-full border border-slate-800 bg-slate-950"
                                        >
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500"
                                            >
                                                Helm Score
                                            </p>

                                            <div class="mt-1 flex items-baseline">
                                                <span
                                                    data-helm-score-number
                                                    class="text-6xl font-semibold tracking-tight text-white"
                                                >
                                                    0
                                                </span>

                                                <span
                                                    class="ml-1 text-sm font-medium text-slate-500"
                                                >
                                                    /100
                                                </span>
                                            </div>

                                            <p
                                                class="mt-2 text-sm font-semibold"
                                                style="color: {{ $helmScoreColor }}"
                                            >
                                                {{ $helmOverallLabel }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                                    >
                                        Portfolio health
                                    </p>

                                    <h2
                                        class="mt-2 text-2xl font-semibold tracking-tight text-white"
                                    >
                                        {{ $helmOverallLabel }}
                                    </h2>

                                    @if ($needsAttention)
                                        <p
                                            class="mt-3 text-base leading-7 text-slate-300"
                                        >
                                            Your portfolio is being monitored and
                                            Helmio found
                                            <span class="font-semibold text-amber-300">
                                                {{ $totalAdvisorFindings }}
                                                {{ Str::plural(
                                                    'item',
                                                    $totalAdvisorFindings
                                                ) }}
                                            </span>
                                            that may deserve your attention.
                                        </p>
                                    @else
                                        <p
                                            class="mt-3 text-base leading-7 text-slate-300"
                                        >
                                            Your portfolio is being monitored and
                                            Helmio has not identified any major
                                            open concerns.
                                        </p>
                                    @endif

                                    <div
                                        class="mt-6 flex flex-wrap gap-3"
                                    >
                                        <a
                                            href="{{ route('analytics.helm-score') }}"
                                            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                                        >
                                            View Helm Score
                                        </a>

                                        @if ($needsAttention)
                                            <a
                                                href="{{ route('advisor-action-center.index') }}"
                                                class="inline-flex items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-600 hover:text-white"
                                            >
                                                Review Findings
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        @endif
                    </section>


                    {{-- Key money metrics --}}
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-1">

                        <section
                            class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                            >
                                Portfolio Value
                            </p>

                            <p
                                class="mt-3 text-4xl font-semibold tracking-tight text-white"
                            >
                                {{ money($portfolioValue) }}
                            </p>

                            <p
                                class="mt-2 text-sm text-slate-400"
                            >
                                Across
                                {{ number_format($connectedAccountCount) }}
                                {{ Str::plural(
                                    'connected account',
                                    $connectedAccountCount
                                ) }}
                            </p>

                            <a
                                href="{{ route('accounts.index') }}"
                                class="mt-5 inline-flex text-sm font-semibold text-blue-400 hover:text-blue-300"
                            >
                                View Accounts →
                            </a>
                        </section>

                        <section
                            class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                            >
                                Estimated Annual Cost
                            </p>

                            <p
                                class="mt-3 text-3xl font-semibold tracking-tight text-white"
                            >
                                @if ($allInCostDollars !== null)
                                    {{ money($allInCostDollars) }}
                                @else
                                    —
                                @endif
                            </p>

                            <p
                                class="mt-2 text-sm text-slate-400"
                            >
                                @if ($allInCostRate !== null)
                                    {{ number_format(
                                        (float) $allInCostRate,
                                        2
                                    ) }}%
                                    of assets annually
                                @else
                                    Cost data still being calculated
                                @endif
                            </p>

                            <a
                                href="{{ route('analytics.costs') }}"
                                class="mt-5 inline-flex text-sm font-semibold text-blue-400 hover:text-blue-300"
                            >
                                View Cost Analysis →
                            </a>
                        </section>

                    </div>
                </div>


                {{-- ===================================================== --}}
                {{-- NEEDS ATTENTION --}}
                {{-- ===================================================== --}}

                <section class="mt-7">

                    <div
                        class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-400"
                            >
                                Needs your attention
                            </p>

                            <h2
                                class="mt-1 text-xl font-semibold text-white"
                            >
                                What matters most right now
                            </h2>
                        </div>

                        <a
                            href="{{ route('advisor-action-center.index') }}"
                            class="text-sm font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View Action Center
                        </a>
                    </div>

                    @if ($topConcern)

                        <article
                            class="rounded-3xl border {{ $topConcernClasses['border'] }} {{ $topConcernClasses['background'] }} p-6 shadow-xl sm:p-7"
                        >
                            <div
                                class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div class="flex max-w-4xl gap-4">

                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $topConcernClasses['icon'] }}"
                                    >
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
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="rounded-full border px-3 py-1 text-xs font-semibold {{ $topConcernClasses['badge'] }}"
                                            >
                                                {{ str($topConcernSeverity)
                                                    ->replace('_', ' ')
                                                    ->title() }}
                                            </span>

                                            <span
                                                class="text-xs text-slate-500"
                                            >
                                                Highest-priority finding
                                            </span>
                                        </div>

                                        <h3
                                            class="mt-3 text-xl font-semibold text-white sm:text-2xl"
                                        >
                                            {{ $topConcern['title']
                                                ?? 'Advisor audit finding' }}
                                        </h3>

                                        <p
                                            class="mt-3 max-w-3xl text-sm leading-7 text-slate-300"
                                        >
                                            {{ $topConcern['message']
                                                ?? 'Helmio identified a portfolio issue that may deserve review.' }}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('advisor-action-center.index') }}"
                                    class="inline-flex shrink-0 items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500"
                                >
                                    Review Finding
                                </a>
                            </div>
                        </article>

                    @else

                        <article
                            class="rounded-3xl border border-emerald-500/20 bg-emerald-500/[0.05] p-6"
                        >
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-300"
                                >
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
                                            d="m5 12 4 4L19 6"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h3
                                        class="font-semibold text-white"
                                    >
                                        Nothing urgent needs your attention.
                                    </h3>

                                    <p
                                        class="mt-2 text-sm leading-6 text-slate-400"
                                    >
                                        Helmio has not identified a major
                                        concern in the portfolio data currently available.
                                    </p>
                                </div>
                            </div>
                        </article>

                    @endif
                </section>


                {{-- ===================================================== --}}
                {{-- PORTFOLIO HEALTH --}}
                {{-- ===================================================== --}}

                <section class="mt-8">

                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                            >
                                Portfolio health
                            </p>

                            <h2
                                class="mt-1 text-xl font-semibold text-white"
                            >
                                Score Breakdown
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-400"
                            >
                                A quick view of how each area is performing.
                            </p>
                        </div>

                        <a
                            href="{{ route('analytics.helm-score') }}"
                            class="text-sm font-semibold text-blue-400 hover:text-blue-300"
                        >
                            Full Score Report
                        </a>
                    </div>

                    <div
                        class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        @foreach ($scoreBreakdown as $item)

                            @php
                                $categoryScore =
                                    data_get(
                                        $auditCategories,
                                        $item['key'] . '.score'
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
                                    $analysisIsRunning =>
                                        'Analyzing...',

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
                            @endphp

                            <a
                                href="{{ route(
                                    $categoryRoutes[$item['key']]
                                ) }}"
                                class="group rounded-2xl border border-slate-800 bg-slate-900 p-5 transition hover:border-blue-500/50 hover:bg-slate-800/50"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="font-semibold text-white"
                                        >
                                            {{ $item['label'] }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{ $categoryStatus }}
                                        </p>
                                    </div>

                                    <span
                                        class="text-2xl font-semibold {{ $categoryScoreClass }}"
                                    >
                                        @if ($analysisIsRunning)
                                            <span class="animate-pulse text-blue-300">
                                                ···
                                            </span>
                                        @else
                                            {{ $categoryScore ?? '—' }}
                                        @endif
                                    </span>
                                </div>

                                <div
                                    class="mt-5 h-2 overflow-hidden rounded-full bg-slate-800"
                                >
                                    @if ($categoryScore !== null)
                                        <div
                                            class="h-full rounded-full bg-blue-500"
                                            style="width: {{ $categoryScore }}%"
                                        ></div>
                                    @endif
                                </div>

                                <p
                                    class="mt-4 text-xs font-semibold text-blue-400 opacity-0 transition group-hover:opacity-100"
                                >
                                    View analysis →
                                </p>
                            </a>

                        @endforeach
                    </div>
                </section>


                {{-- ===================================================== --}}
                {{-- ACTIVITY + ADVISOR AUDIT --}}
                {{-- ===================================================== --}}

                <div
                    class="mt-8 grid gap-5 xl:grid-cols-[1.6fr_1fr]"
                >

                    {{-- Recent Activity --}}
                    <section
                        class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-800 px-6 py-5"
                        >
                            <div>
                                <h2
                                    class="text-lg font-semibold text-white"
                                >
                                    Recent Activity
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-400"
                                >
                                    Portfolio findings and monitoring events.
                                </p>
                            </div>

                            <a
                                href="{{ route('advisor-audit.index') }}"
                                class="text-sm font-semibold text-blue-400 hover:text-blue-300"
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

                                <div class="flex gap-4 px-6 py-5">

                                    <div
                                        class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $alertColor }}"
                                    >
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
                                                d="M12 9v4m0 4h.01M10.3 4.5 2.6 18a1 1 0 0 0 .87 1.5h17.06a1 1 0 0 0 .87-1.5L13.7 4.5a1 1 0 0 0-1.74 0Z"
                                            />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <div
                                            class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <p
                                                class="font-medium text-white"
                                            >
                                                {{ data_get(
                                                    $finding,
                                                    'title',
                                                    'Portfolio finding'
                                                ) }}
                                            </p>

                                            <span
                                                class="text-xs capitalize text-slate-500"
                                            >
                                                {{ $severity }}
                                            </span>
                                        </div>

                                        <p
                                            class="mt-2 line-clamp-2 text-sm leading-6 text-slate-400"
                                        >
                                            {{ data_get(
                                                $finding,
                                                'message',
                                                ''
                                            ) }}
                                        </p>
                                    </div>
                                </div>

                            @empty

                                <div
                                    class="px-6 py-10 text-center"
                                >
                                    <div
                                        class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-300"
                                    >
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
                                                d="m5 12 4 4L19 6"
                                            />
                                        </svg>
                                    </div>

                                    <p
                                        class="mt-3 text-sm font-medium text-white"
                                    >
                                        No open alerts
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        Helmio hasn't identified anything
                                        requiring immediate attention.
                                    </p>
                                </div>

                            @endforelse
                        </div>
                    </section>


                    {{-- Advisor Audit --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400"
                            >
                                Advisor oversight
                            </p>

                            <h2
                                class="mt-1 text-lg font-semibold text-white"
                            >
                                Advisor Audit
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-400"
                            >
                                Independent review of advisor activity.
                            </p>
                        </div>

                        <div
                            class="mt-6 rounded-2xl border border-slate-800 bg-slate-950 p-5"
                        >
                            <div
                                class="flex items-end justify-between"
                            >
                                <div>
                                    <p
                                        class="text-xs uppercase tracking-wide text-slate-500"
                                    >
                                        Audit score
                                    </p>

                                    <p
                                        class="mt-2 text-4xl font-semibold text-white"
                                    >
                                        {{ $auditScore ?? '—' }}
                                    </p>
                                </div>

                                @if ($scoreChange !== null)
                                    <div class="text-right">
                                        <p
                                            class="text-xs text-slate-500"
                                        >
                                            Change
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-semibold
                                            {{ $scoreDirection === 'up'
                                                ? 'text-emerald-300'
                                                : (
                                                    $scoreDirection === 'down'
                                                        ? 'text-red-300'
                                                        : 'text-slate-300'
                                                )
                                            }}"
                                        >
                                            @if ($scoreChange > 0)
                                                +{{ number_format(
                                                    (float) $scoreChange
                                                ) }}
                                            @else
                                                {{ number_format(
                                                    (float) $scoreChange
                                                ) }}
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5">
                                <div
                                    class="flex items-center justify-between text-xs"
                                >
                                    <span class="text-slate-500">
                                        Data completeness
                                    </span>

                                    <span class="font-semibold text-slate-300">
                                        {{ number_format(
                                            $auditCompleteness * 100
                                        ) }}%
                                    </span>
                                </div>

                                <div
                                    class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full bg-blue-500"
                                        style="width: {{
                                            min(
                                                100,
                                                max(
                                                    0,
                                                    $auditCompleteness * 100
                                                )
                                            )
                                        }}%"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <a
                            href="{{ route('advisor-audit.report') }}"
                            class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500 hover:text-white"
                        >
                            Full Advisor Audit
                        </a>
                    </section>
                </div>


                {{-- ===================================================== --}}
                {{-- AI INSIGHT --}}
                {{-- ===================================================== --}}

                <section
                    class="mt-8 overflow-hidden rounded-3xl border border-violet-500/20 bg-slate-900 shadow-xl"
                >
                    <div
                        class="grid gap-6 p-6 lg:grid-cols-[0.7fr_1.3fr] lg:p-8"
                    >
                        <div>
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl border border-violet-500/30 bg-violet-500/10 text-violet-300"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.847-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09L9 18.75Z"
                                    />
                                </svg>
                            </div>

                            <p
                                class="mt-4 text-xs font-semibold uppercase tracking-[0.16em] text-violet-400"
                            >
                                AI Portfolio Insight
                            </p>

                            <h2
                                class="mt-2 text-xl font-semibold text-white"
                            >
                                Your portfolio, explained.
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-400"
                            >
                                Plain-English context based on your
                                underlying Helmio analytics.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-6"
                        >
                            @if ($latestAiInsight)

                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    @if ($latestAiInsightIsStale)
                                        <span
                                            class="rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300"
                                        >
                                            Needs refresh
                                        </span>
                                    @else
                                        <span
                                            class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                                        >
                                            Current
                                        </span>
                                    @endif

                                    @if ($latestAiInsightGeneratedAt)
                                        <span
                                            class="text-xs text-slate-500"
                                        >
                                            Generated
                                            {{ $latestAiInsightGeneratedAt->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>

                                <h3
                                    class="mt-4 text-lg font-semibold text-white"
                                >
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

                                <p
                                    class="mt-3 text-sm leading-7 text-slate-300"
                                >
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

                                <div
                                    class="mt-5 flex flex-wrap gap-3"
                                >
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
                                                class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-500"
                                            >
                                                Regenerate
                                            </button>
                                        </form>
                                    @endif

                                    @if (
                                        data_get(
                                            $latestAiInsight,
                                            'id'
                                        )
                                    )
                                        <a
                                            href="{{ route(
                                                'ai-insights.show',
                                                $latestAiInsight
                                            ) }}"
                                            class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-violet-500 hover:text-white"
                                        >
                                            Read Insight
                                        </a>
                                    @endif
                                </div>

                            @else

                                <p
                                    class="text-sm leading-6 text-slate-400"
                                >
                                    No AI insight has been generated yet.
                                </p>

                                <a
                                    href="{{ route('ai-insights.index') }}"
                                    class="mt-5 inline-flex rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-500"
                                >
                                    Generate Insight
                                </a>

                            @endif
                        </div>
                    </div>
                </section>


                {{-- ===================================================== --}}
                {{-- SUITABILITY --}}
                {{-- ===================================================== --}}

                <div class="mt-8">
                    @include(
                        'dashboard.partials.suitability'
                    )
                </div>


                {{-- ===================================================== --}}
                {{-- ACCOUNTS --}}
                {{-- ===================================================== --}}

                <section
                    class="mt-8 overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl"
                >
                    <div
                        class="flex flex-col gap-3 border-b border-slate-800 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
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
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Account
                                        </th>

                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Institution
                                        </th>

                                        <th
                                            class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Value
                                        </th>

                                        <th
                                            class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"
                                        >
                                            Holdings
                                        </th>

                                        <th
                                            class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"
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
                                                class="whitespace-nowrap px-6 py-4"
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
                                                class="whitespace-nowrap px-6 py-4 text-slate-400"
                                            >
                                                {{ $institutionName }}
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-right font-semibold text-white"
                                            >
                                                {{ money(
                                                    $accountValue
                                                ) }}
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-right text-slate-300"
                                            >
                                                {{ number_format(
                                                    $holdingCount
                                                ) }}
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-right"
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