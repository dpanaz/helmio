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
            'cost' =>
                'Cost',

            'diversification' =>
                'Diversification',

            'performance' =>
                'Performance',

            'risk' =>
                'Risk',

            'suitability' =>
                'Suitability',

            'trading' =>
                'Trading',

            'cash' =>
                'Cash',

            'tax' =>
                'Tax',
        ];

        $categoryRoutes = [
            'cost' =>
                'analytics.costs',

            'diversification' =>
                'analytics.diversification',

            'performance' =>
                'analytics.performance',

            'risk' =>
                'analytics.risk',

            'suitability' =>
                'investor-profile.edit',

            'trading' =>
                'analytics.trading-discipline',

            'cash' =>
                'analytics.cash-drag',

            'tax' =>
                'analytics.tax-efficiency',
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
                'key' => 'trading',
                'label' => 'Trading Discipline',
            ],
            [
                'key' => 'tax',
                'label' => 'Tax Efficiency',
            ],
            [
                'key' => 'diversification',
                'label' => 'Diversification',
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
            $scoreChange === null =>
                null,

            $scoreChange > 0 =>
                'up',

            $scoreChange < 0 =>
                'down',

            default =>
                'flat',
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

        $latestAiInsightPortfolioValue =
            data_get(
                $latestAiInsight,
                'portfolio_value_at_generation'
            );

        $latestAiInsightAccountCount =
            data_get(
                $latestAiInsight,
                'account_count_at_generation'
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
            $hour < 12 =>
                'Good morning',

            $hour < 17 =>
                'Good afternoon',

            default =>
                'Good evening',
        };

        $firstName =
            auth()->user()->name
                ? str(
                    auth()->user()->name
                )->before(' ')
                : 'there';
    @endphp

    <div class="min-h-screen bg-slate-950">

        @if (! $hasPremiumAccess)

            {{-- ========================================================= --}}
            {{-- NON-PREMIUM STATE --}}
            {{-- ========================================================= --}}

            <div
                class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
            >
                <section
                    class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl"
                >
                    <div
                        class="grid gap-10 p-6 sm:p-8 lg:grid-cols-2 lg:p-12"
                    >
                        <div>
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-300"
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
                                        d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v7.5A1.5 1.5 0 0 1 17.25 21H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z"
                                    />
                                </svg>

                                Subscription required
                            </span>

                            <h1
                                class="mt-6 text-3xl font-semibold tracking-tight text-white sm:text-4xl"
                            >
                                Unlock your Helmio dashboard.
                            </h1>

                            <p
                                class="mt-4 max-w-xl text-base leading-8 text-slate-400"
                            >
                                Start your Helmio trial to securely connect
                                your investment accounts and begin monitoring
                                fees, performance, risk, diversification,
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
                            class="rounded-2xl border border-slate-700 bg-slate-950 p-6"
                        >
                            <p
                                class="text-sm font-semibold text-white"
                            >
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
                                    <div
                                        class="flex items-start gap-3"
                                    >
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
            {{-- PREMIUM DASHBOARD --}}
            {{-- ========================================================= --}}

            <div
                class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
            >

                {{-- Greeting --}}
                <div
                    class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-medium text-slate-500"
                        >
                            Portfolio overview
                        </p>

                        <h1
                            class="mt-1 text-3xl font-semibold tracking-tight text-white"
                        >
                            {{ $greeting }}, {{ $firstName }}.
                        </h1>

                        <p
                            class="mt-2 text-sm text-slate-400"
                        >
                            Here's how your portfolio is looking today.
                        </p>
                    </div>

                    <div
                        class="inline-flex items-center rounded-xl border border-slate-800 bg-slate-900 px-4 py-3 text-sm text-slate-300"
                    >
                        <svg
                            class="mr-2 h-4 w-4 text-slate-500"
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
                {{-- TOP GRID --}}
                {{-- ===================================================== --}}

                <div
                    class="grid gap-5 xl:grid-cols-3"
                >

                    {{-- Helm Score --}}
                    <section
                        class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900 shadow-xl xl:col-span-2"
                    >
                        <div
                            class="border-b border-slate-800 px-6 py-5"
                        >
                            <div
                                class="flex items-center justify-between"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-white"
                                    >
                                        Helm Score
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        Overall portfolio health
                                    </p>
                                </div>

                                <a
                                    href="{{ route('analytics.helm-score') }}"
                                    class="text-xs font-semibold text-blue-400 hover:text-blue-300"
                                >
                                    View report
                                </a>
                            </div>
                        </div>

                        <div
                            class="grid gap-8 p-6 lg:grid-cols-2 lg:items-center"
                        >

                            {{-- Dial --}}
                            <div
                                class="flex justify-center"
                            >
                                <div
                                    data-helm-score-dial
                                    data-score="{{ $helmOverallScore }}"
                                    class="relative flex h-64 w-64 items-center justify-center"
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
                                        class="relative flex h-44 w-44 flex-col items-center justify-center rounded-full border border-slate-800 bg-slate-950 shadow-2xl"
                                    >
                                        <p
                                            class="text-xs font-semibold uppercase tracking-widest text-slate-500"
                                        >
                                            Helm Score
                                        </p>

                                        <div
                                            class="mt-2 flex items-baseline"
                                        >
                                            <span
                                                data-helm-score-number
                                                class="text-6xl font-semibold tracking-tight text-white"
                                            >
                                                0
                                            </span>

                                            <span
                                                class="ml-1 text-sm font-medium text-slate-600"
                                            >
                                                /100
                                            </span>
                                        </div>

                                        <p
                                            class="mt-3 text-sm font-semibold"
                                            style="color: {{ $helmScoreColor }}"
                                        >
                                            {{ $helmOverallLabel }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Breakdown --}}
                            <div>
                                <div
                                    class="flex items-center justify-between"
                                >
                                    <p
                                        class="text-xs font-semibold uppercase tracking-widest text-slate-500"
                                    >
                                        Score Breakdown
                                    </p>

                                    <span
                                        class="text-xs text-slate-600"
                                    >
                                        /100
                                    </span>
                                </div>

                                <div class="mt-5 space-y-4">
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
                                        @endphp

                                        <div>
                                            <div
                                                class="flex items-center justify-between gap-4"
                                            >
                                                <span
                                                    class="text-xs font-medium text-slate-300"
                                                >
                                                    {{ $item['label'] }}
                                                </span>

                                                <span
                                                    class="text-xs font-semibold text-slate-400"
                                                >
                                                    {{ $categoryScore ?? '—' }}
                                                </span>
                                            </div>

                                            <div
                                                class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800"
                                            >
                                                @if ($categoryScore !== null)
                                                    <div
                                                        class="h-full rounded-full bg-blue-500"
                                                        style="width: {{ $categoryScore }}%"
                                                    ></div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <a
                                    href="{{ route('advisor-audit.index') }}"
                                    class="mt-6 inline-flex items-center text-xs font-semibold text-blue-400 hover:text-blue-300"
                                >
                                    Scoring methodology

                                    <svg
                                        class="ml-1 h-3.5 w-3.5"
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
                        </div>
                    </section>

                    {{-- Portfolio Value --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div
                            class="flex items-start justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-semibold text-white"
                                >
                                    Portfolio Value
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Across connected accounts
                                </p>
                            </div>

                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-400"
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
                                        d="M4 18 9 13l3 3 8-9"
                                    />
                                </svg>
                            </div>
                        </div>

                        <p
                            class="mt-8 text-4xl font-semibold tracking-tight text-white"
                        >
                            {{ money($portfolioValue) }}
                        </p>

                        <p
                            class="mt-2 text-sm text-slate-500"
                        >
                            {{ number_format($connectedAccountCount) }}
                            {{ Str::plural(
                                'connected account',
                                $connectedAccountCount
                            ) }}
                        </p>

                        <div
                            class="mt-8 flex h-28 items-end gap-1"
                            aria-hidden="true"
                        >
                            @foreach ([
                                24, 28, 25, 33, 38, 35, 44, 47,
                                42, 50, 56, 61, 58, 66, 72, 68,
                                75, 79, 74, 83, 88, 84, 91, 96,
                            ] as $height)
                                <div
                                    class="flex-1 rounded-t bg-blue-500/50"
                                    style="height: {{ $height }}%"
                                ></div>
                            @endforeach
                        </div>

                        <a
                            href="{{ route('accounts.index') }}"
                            class="mt-5 inline-flex text-xs font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View accounts →
                        </a>
                    </section>
                </div>

                {{-- ===================================================== --}}
                {{-- SECOND ROW --}}
                {{-- ===================================================== --}}

                <div
                    class="mt-5 grid gap-5 lg:grid-cols-3"
                >

                    {{-- Cost --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-semibold text-white"
                                >
                                    All-In Cost Rate
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Portfolio-wide investment costs
                                </p>
                            </div>

                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400"
                            >
                                $
                            </div>
                        </div>

                        <div
                            class="mt-7 flex items-end justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="text-3xl font-semibold text-white"
                                >
                                    @if ($allInCostRate !== null)
                                        {{ number_format(
                                            (float) $allInCostRate,
                                            2
                                        ) }}%
                                    @else
                                        —
                                    @endif
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    of assets annually
                                </p>
                            </div>

                            @if ($allInCostDollars !== null)
                                <div class="text-right">
                                    <p
                                        class="text-xs text-slate-500"
                                    >
                                        Estimated annual cost
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-amber-300"
                                    >
                                        {{ money($allInCostDollars) }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div
                            class="mt-6 h-px bg-slate-800"
                        ></div>

                        <a
                            href="{{ route('analytics.costs') }}"
                            class="mt-5 inline-flex text-xs font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View cost analysis →
                        </a>
                    </section>

                    {{-- Top Finding --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-semibold text-white"
                                >
                                    Top Finding
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Highest-priority issue
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300"
                            >
                                {{ number_format($totalAdvisorFindings) }}
                                open
                            </span>
                        </div>

                        @if ($topConcern)
                            <h3
                                class="mt-6 text-xl font-semibold text-white"
                            >
                                {{ $topConcern['title']
                                    ?? 'Advisor audit finding' }}
                            </h3>

                            <p
                                class="mt-3 line-clamp-3 text-sm leading-6 text-slate-400"
                            >
                                {{ $topConcern['message']
                                    ?? '' }}
                            </p>
                        @else
                            <h3
                                class="mt-6 text-xl font-semibold text-white"
                            >
                                No major concerns detected
                            </h3>

                            <p
                                class="mt-3 text-sm leading-6 text-slate-400"
                            >
                                Helmio has not identified a major concern
                                in the available portfolio data.
                            </p>
                        @endif

                        <a
                            href="{{ route('advisor-audit.index') }}"
                            class="mt-6 inline-flex rounded-lg border border-slate-700 bg-slate-950 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:border-blue-500 hover:text-white"
                        >
                            See recommendation
                        </a>
                    </section>

                    {{-- Accounts --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-semibold text-white"
                                >
                                    Accounts
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Connected to Helmio
                                </p>
                            </div>

                            <span
                                class="text-3xl font-semibold text-white"
                            >
                                {{ number_format(
                                    $connectedAccountCount
                                ) }}
                            </span>
                        </div>

                        <div class="mt-6 space-y-3">
                            @forelse (
                                $accountsCollection->take(3)
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

                                <div
                                    class="flex items-center justify-between gap-4 rounded-xl border border-slate-800 bg-slate-950 px-4 py-3"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-medium text-slate-200"
                                        >
                                            {{ data_get(
                                                $account,
                                                'name',
                                                'Investment Account'
                                            ) }}
                                        </p>

                                        <p
                                            class="mt-1 truncate text-xs text-slate-600"
                                        >
                                            {{ $institutionName }}
                                        </p>
                                    </div>

                                    <p
                                        class="shrink-0 text-sm font-semibold text-white"
                                    >
                                        {{ money(
                                            $accountValue
                                        ) }}
                                    </p>
                                </div>
                            @empty
                                <p
                                    class="text-sm text-slate-500"
                                >
                                    No accounts connected.
                                </p>
                            @endforelse
                        </div>

                        <a
                            href="{{ route('accounts.index') }}"
                            class="mt-5 inline-flex text-xs font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View accounts →
                        </a>
                    </section>
                </div>

                {{-- ===================================================== --}}
                {{-- THIRD ROW --}}
                {{-- ===================================================== --}}

                <div
                    class="mt-5 grid gap-5 xl:grid-cols-3"
                >

                    {{-- Recent Alerts --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 shadow-xl xl:col-span-2"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-800 px-6 py-5"
                        >
                            <div>
                                <p
                                    class="text-sm font-semibold text-white"
                                >
                                    Recent Alerts
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Findings that may need your attention
                                </p>
                            </div>

                            <a
                                href="{{ route('advisor-audit.index') }}"
                                class="text-xs font-semibold text-blue-400 hover:text-blue-300"
                            >
                                View all
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

                                    $alertColor = match (
                                        $severity
                                    ) {
                                        'critical',
                                        'high' =>
                                            'text-red-400 bg-red-500/10',

                                        'moderate' =>
                                            'text-amber-400 bg-amber-500/10',

                                        default =>
                                            'text-blue-400 bg-blue-500/10',
                                    };
                                @endphp

                                <div
                                    class="flex gap-4 px-6 py-5"
                                >
                                    <div
                                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $alertColor }}"
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
                                                class="font-medium text-slate-200"
                                            >
                                                {{ data_get(
                                                    $finding,
                                                    'title',
                                                    'Portfolio finding'
                                                ) }}
                                            </p>

                                            <span
                                                class="text-xs capitalize text-slate-600"
                                            >
                                                {{ $severity }}
                                            </span>
                                        </div>

                                        <p
                                            class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500"
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
                                        class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400"
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
                                        class="mt-3 text-sm font-medium text-slate-300"
                                    >
                                        No open alerts
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-600"
                                    >
                                        Helmio hasn't identified anything
                                        requiring immediate attention.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    {{-- Advisor Activity --}}
                    <section
                        class="rounded-3xl border border-slate-800 bg-slate-900 p-6 shadow-xl"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-semibold text-white"
                                >
                                    Advisor Activity
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Audit changes and status
                                </p>
                            </div>

                            <a
                                href="{{ route('advisor-audit.history') }}"
                                class="text-xs font-semibold text-blue-400 hover:text-blue-300"
                            >
                                History
                            </a>
                        </div>

                        <dl class="mt-7 space-y-5">
                            <div
                                class="flex items-center justify-between border-b border-slate-800 pb-4"
                            >
                                <dt
                                    class="text-sm text-slate-500"
                                >
                                    Advisor Audit
                                </dt>

                                <dd
                                    class="text-sm font-semibold text-white"
                                >
                                    {{ $auditScore ?? '—' }}
                                    /100
                                </dd>
                            </div>

                            <div
                                class="flex items-center justify-between border-b border-slate-800 pb-4"
                            >
                                <dt
                                    class="text-sm text-slate-500"
                                >
                                    Score change
                                </dt>

                                <dd
                                    class="text-sm font-semibold
                                    {{ $scoreDirection === 'up'
                                        ? 'text-emerald-400'
                                        : (
                                            $scoreDirection === 'down'
                                                ? 'text-red-400'
                                                : 'text-slate-300'
                                        )
                                    }}"
                                >
                                    @if ($scoreChange === null)
                                        —
                                    @elseif ($scoreChange > 0)
                                        +{{ number_format(
                                            (float) $scoreChange
                                        ) }}
                                    @else
                                        {{ number_format(
                                            (float) $scoreChange
                                        ) }}
                                    @endif
                                </dd>
                            </div>

                            <div
                                class="flex items-center justify-between"
                            >
                                <dt
                                    class="text-sm text-slate-500"
                                >
                                    Data completeness
                                </dt>

                                <dd
                                    class="text-sm font-semibold text-white"
                                >
                                    {{ number_format(
                                        $auditCompleteness * 100
                                    ) }}%
                                </dd>
                            </div>
                        </dl>

                        <div
                            class="mt-5 h-2 overflow-hidden rounded-full bg-slate-800"
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

                        <a
                            href="{{ route('advisor-audit.report') }}"
                            class="mt-6 inline-flex w-full items-center justify-center rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:border-blue-500 hover:text-white"
                        >
                            Full Advisor Audit
                        </a>
                    </section>
                </div>

                {{-- ===================================================== --}}
                {{-- AI INSIGHT --}}
                {{-- ===================================================== --}}

                <section
                    class="mt-5 overflow-hidden rounded-3xl border border-violet-500/20 bg-slate-900 shadow-xl"
                >
                    <div
                        class="grid gap-8 p-6 lg:grid-cols-3 lg:p-8"
                    >
                        <div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl border border-violet-500/30 bg-violet-500/10 text-violet-300"
                            >
                                <svg
                                    class="h-6 w-6"
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
                                class="mt-5 text-xs font-semibold uppercase tracking-widest text-violet-400"
                            >
                                AI Portfolio Insight
                            </p>

                            <h2
                                class="mt-3 text-2xl font-semibold text-white"
                            >
                                Your portfolio, explained.
                            </h2>

                            <p
                                class="mt-3 text-sm leading-6 text-slate-500"
                            >
                                Helmio turns your underlying analytics
                                into plain-English explanations.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-800 bg-slate-950 p-6 lg:col-span-2"
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
                                            class="text-xs text-slate-600"
                                        >
                                            Generated
                                            {{ $latestAiInsightGeneratedAt->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>

                                <h3
                                    class="mt-5 text-xl font-semibold text-white"
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
                                    class="mt-3 text-sm leading-7 text-slate-400"
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
                                    class="mt-6 flex flex-wrap gap-3"
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
                                            class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-violet-500"
                                        >
                                            Read insight
                                        </a>
                                    @endif
                                </div>
                            @else
                                <p
                                    class="text-sm text-slate-500"
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
                {{-- CATEGORY CARDS --}}
                {{-- ===================================================== --}}

                <section class="mt-8">
                    <div
                        class="flex items-end justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-widest text-blue-400"
                            >
                                Analytics
                            </p>

                            <h2
                                class="mt-2 text-xl font-semibold text-white"
                            >
                                Portfolio Health Categories
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Open a category for detailed analysis.
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        @foreach (
                            $categoryLabels
                            as $key => $label
                        )
                            @php
                                $category =
                                    $auditCategories[$key]
                                    ?? [];

                                $categoryScore =
                                    $category['score']
                                    ?? null;

                                $categoryLabel =
                                    $category['label']
                                    ?? 'Insufficient data';
                            @endphp

                            <a
                                href="{{ route(
                                    $categoryRoutes[$key]
                                ) }}"
                                class="group rounded-2xl border border-slate-800 bg-slate-900 p-5 transition hover:-translate-y-1 hover:border-blue-500"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <p
                                        class="text-sm font-semibold text-slate-200"
                                    >
                                        {{ $label }}
                                    </p>

                                    <span
                                        class="text-xl font-semibold text-white"
                                    >
                                        {{ $categoryScore
                                            ?? '—' }}
                                    </span>
                                </div>

                                <div
                                    class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-800"
                                >
                                    @if (
                                        $categoryScore !== null
                                    )
                                        <div
                                            class="h-full rounded-full bg-blue-500"
                                            style="width: {{
                                                min(
                                                    100,
                                                    max(
                                                        0,
                                                        $categoryScore
                                                    )
                                                )
                                            }}%"
                                        ></div>
                                    @endif
                                </div>

                                <p
                                    class="mt-4 truncate text-xs text-slate-600"
                                >
                                    {{ $categoryLabel }}
                                </p>
                            </a>
                        @endforeach
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
                {{-- ACCOUNT TABLE --}}
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
                                class="mt-1 text-sm text-slate-500"
                            >
                                Accounts included in Helmio analytics.
                            </p>
                        </div>

                        <a
                            href="{{ route(
                                'accounts.index'
                            ) }}"
                            class="text-sm font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View all
                        </a>
                    </div>

                    @if ($accountsCollection->isEmpty())
                        <div
                            class="px-6 py-12 text-center"
                        >
                            <p
                                class="text-sm font-medium text-slate-300"
                            >
                                No investment accounts connected
                            </p>

                            <a
                                href="{{ route(
                                    'accounts.create'
                                ) }}"
                                class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                Add Account
                            </a>
                        </div>
                    @else
                        <div
                            class="overflow-x-auto"
                        >
                            <table
                                class="w-full min-w-full text-sm"
                            >
                                <thead
                                    class="bg-slate-950"
                                >
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                                        >
                                            Account
                                        </th>

                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-600"
                                        >
                                            Institution
                                        </th>

                                        <th
                                            class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600"
                                        >
                                            Value
                                        </th>

                                        <th
                                            class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600"
                                        >
                                            Holdings
                                        </th>

                                        <th
                                            class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-600"
                                        >
                                            Profile
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    class="divide-y divide-slate-800"
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
                                                ?? '—';
                                        @endphp

                                        <tr
                                            class="transition hover:bg-slate-800/50"
                                        >
                                            <td
                                                class="whitespace-nowrap px-6 py-4"
                                            >
                                                <a
                                                    href="{{ route(
                                                        'accounts.holdings.index',
                                                        $account
                                                    ) }}"
                                                    class="font-medium text-slate-200 hover:text-blue-400"
                                                >
                                                    {{ data_get(
                                                        $account,
                                                        'name',
                                                        'Investment Account'
                                                    ) }}
                                                </a>
                                            </td>

                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-slate-500"
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
                                                class="whitespace-nowrap px-6 py-4 text-right text-slate-400"
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
                    @endif
                </section>
            </div>
        @endif
    </div>

    {{-- ============================================================= --}}
    {{-- HELM SCORE ANIMATION --}}
    {{-- ============================================================= --}}

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
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
                        target
                        * eased;

                    scoreElement.textContent =
                        Math.round(
                            current
                        );

                    ring.style.strokeDashoffset =
                        String(
                            100 - current
                        );

                    if (
                        progress < 1
                    ) {
                        requestAnimationFrame(
                            animate
                        );
                    } else {
                        scoreElement.textContent =
                            Math.round(
                                target
                            );

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
        );
    </script>
</x-app-layout>