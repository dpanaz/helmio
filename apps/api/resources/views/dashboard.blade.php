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

        /*
         * The dashboard's attention state should match the Action Center.
         * $openFindings is already returned in priority order by
         * DashboardService, so use it as the source of truth.
         */
        $topConcern =
            collect($openFindings)
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

        /*
         * Continuous Helmio blue score scale.
         *
         * The hue stays blue while lightness decreases as the score rises:
         * low score = lighter blue, high score = deeper blue.
         *
         * Red/orange remain reserved for urgency badges and findings.
         */
        $scoreBlue = function (?int $score): string {
            if ($score === null) {
                return '#64748b';
            }

            $score = min(100, max(0, $score));

            // HSL lightness moves smoothly from 82% at 0
            // to 28% at 100 while keeping Helmio's blue hue.
            $lightness =
                82 - (54 * ($score / 100));

            return sprintf(
                'hsl(217 91%% %.1f%%)',
                $lightness
            );
        };

        $helmScoreColor =
            $scoreBlue($helmOverallScore);

        $helmScoreLabelColor =
            $helmScoreColor;

        $helmScoreBadgeClasses = match (true) {
            $helmOverallScore >= 80 =>
                'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
            $helmOverallScore >= 70 =>
                'border-blue-500/30 bg-blue-500/10 text-blue-300',
            $helmOverallScore >= 60 =>
                'border-amber-500/30 bg-amber-500/10 text-amber-300',
            $helmOverallScore >= 40 =>
                'border-orange-500/30 bg-orange-500/10 text-orange-300',
            default =>
                'border-red-500/25 bg-red-500/[0.08] text-red-400',
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
            (int) (
                $findingCounts['critical']
                ?? $findingCounts['critical_count']
                ?? collect(
                    $criticalFindings
                )->count()
            );

        $importantCount =
            (int) (
                $findingCounts['important']
                ?? $findingCounts['important_count']
                ?? collect(
                    $importantFindings
                )->count()
            );

        $opportunityCount =
            (int) (
                $findingCounts['opportunity']
                ?? $findingCounts['opportunity_count']
                ?? collect(
                    $opportunityFindings
                )->count()
            );

        $totalAdvisorFindings =
            $criticalCount
            + $importantCount
            + $opportunityCount;


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

        $hasCriticalFindings =
            $criticalCount > 0;

        $auditScoreColor =
            $scoreBlue(
                $auditScore !== null
                    ? (int) $auditScore
                    : null
            );

        $auditCardClasses = match (true) {
            $auditScore === null =>
                'border-slate-800 bg-slate-900',
            $auditScore >= 80 =>
                'border-emerald-500/20 bg-emerald-500/[0.035]',
            $auditScore >= 70 =>
                'border-blue-500/20 bg-blue-500/[0.035]',
            $auditScore >= 60 =>
                'border-amber-500/20 bg-amber-500/[0.035]',
            $auditScore >= 40 =>
                'border-orange-500/25 bg-orange-500/[0.04]',
            default =>
                'border-red-500/30 bg-red-500/[0.05]',
        };

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
                    'badge' => 'border-red-500/25 bg-red-500/[0.08] text-red-400',
                    'icon' => 'bg-red-500/10 text-red-400',
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

    <style>
        /*
         * Score Breakdown geometry.
         * Raw CSS is intentional here so the four-column layout does not
         * depend on Tailwind arbitrary-value class generation.
         */
        .helm-score-breakdown-row {
            display: grid;
            grid-template-columns: 2rem 9.5rem minmax(0, 1fr) 3rem;
            align-items: center;
            column-gap: 0.75rem;
            min-height: 48px;
        }

        .helm-score-breakdown-score {
            justify-self: end;
        }

        @media (max-width: 639px) {
            .helm-score-breakdown-row {
                grid-template-columns: 1fr auto;
                grid-template-areas:
                    "label score"
                    "bar bar";
                row-gap: 0.5rem;
            }

            .helm-score-breakdown-icon {
                display: none;
            }

            .helm-score-breakdown-label {
                grid-area: label;
            }

            .helm-score-breakdown-bar {
                grid-area: bar;
            }

            .helm-score-breakdown-score {
                grid-area: score;
            }
        }
    </style>


    <div class="min-h-screen bg-slate-950">

        @if (! $hasPremiumAccess)

            {{-- ========================================================= --}}
            {{-- NON-PREMIUM --}}
            {{-- ========================================================= --}}

            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

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
                class="mx-auto max-w-[1700px] px-4 py-4 sm:px-5 sm:py-5 lg:px-6 xl:px-7"
            >

                {{-- ===================================================== --}}
                {{-- GREETING --}}
                {{-- ===================================================== --}}

                <div
                    class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-400"
                        >
                            Portfolio overview
                        </p>

                        <h1
                            class="mt-1 text-2xl font-semibold tracking-tight text-white"
                        >
                            {{ $greeting }}, {{ $firstName }}.
                        </h1>

                        <p
                            class="mt-1 text-sm leading-5 text-slate-400"
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

                    <section
                        data-analysis-container
                        data-analysis-running="1"
                        class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg"
                    >
                        <div
                            class="grid gap-5 p-5 sm:p-6 lg:grid-cols-2 lg:items-center"
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
                                    <div
                                        class="flex items-center justify-between text-xs"
                                    >
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

                                    <div
                                        class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800"
                                    >
                                        <div
                                            data-analysis-progress
                                            class="h-full rounded-full bg-blue-500 transition-all duration-700"
                                            style="width: {{ $analysisProgress }}%"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-xl border border-slate-800 bg-slate-950/70 p-4"
                            >
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500"
                                >
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

                                        <div
                                            class="flex items-center gap-2.5"
                                        >
                                            @if ($stepStatus === 'complete')

                                                <div
                                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-300"
                                                >
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

                                                <div
                                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-500/10"
                                                >
                                                    <div
                                                        class="h-2 w-2 animate-pulse rounded-full bg-blue-400"
                                                    ></div>
                                                </div>

                                            @else

                                                <div
                                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-700"
                                                >
                                                    <div
                                                        class="h-1.5 w-1.5 rounded-full bg-slate-700"
                                                    ></div>
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
                        <div
                            class="flex items-start gap-4 p-5 sm:p-6"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-300"
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
                                <p
                                    class="text-[11px] font-semibold uppercase tracking-[0.16em] text-amber-400"
                                >
                                    Analysis delayed
                                </p>

                                <h2
                                    class="mt-1 text-xl font-semibold text-white"
                                >
                                    {{ $analysisHeadline }}
                                </h2>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-400"
                                >
                                    {{ $analysisMessage }}
                                </p>
                            </div>
                        </div>
                    </section>

                @else

                    {{-- ================================================= --}}
                    {{-- TOP ROW --}}
                    {{-- ================================================= --}}

                    <div
                        data-analysis-container
                        data-analysis-running="0"
                        class="flex flex-col gap-3 lg:flex-row"
                    >

                        {{-- ================================================= --}}
                        {{-- HELM SCORE --}}
                        {{-- ================================================= --}}

                        <section
                            class="flex min-w-0 flex-col rounded-2xl border border-slate-800/90 bg-slate-900 p-4 shadow-lg sm:p-5 lg:w-[27%] xl:w-[25%]"
                        >
                            <div class="flex items-center justify-between gap-3">

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

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border border-red-500/20 bg-red-500/[0.06] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-red-400"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                    {{ $helmOverallLabel }}
                                </span>

                            </div>

                            <div
                                class="flex flex-1 flex-col items-center justify-center py-2"
                            >

                                {{-- ================================================= --}}
                                {{-- HELM SCORE GAUGE                                  --}}
                                {{-- Keep all gauge typography inside the SVG so the  --}}
                                {{-- score cannot drift or overlap badges responsively. --}}
                                {{-- ================================================= --}}

                                <div
                                    data-helm-score-dial
                                    data-score="{{ $helmOverallScore }}"
                                    class="relative mt-2 w-full max-w-[320px]"
                                >
                                    <svg
                                        class="block h-auto w-full overflow-visible"
                                        viewBox="0 0 320 205"
                                        role="img"
                                        aria-label="Helm Score {{ $helmOverallScore }} out of 100"
                                    >
                                        <defs>
                                            <linearGradient
                                                id="helmScoreGradient"
                                                gradientUnits="userSpaceOnUse"
                                                x1="42"
                                                y1="0"
                                                x2="278"
                                                y2="0"
                                            >
                                                <stop offset="0%" stop-color="#dbeafe" />
                                                <stop offset="24%" stop-color="#93c5fd" />
                                                <stop offset="52%" stop-color="#60a5fa" />
                                                <stop offset="78%" stop-color="#2563eb" />
                                                <stop offset="100%" stop-color="#1e3a8a" />
                                            </linearGradient>

                                            <filter
                                                id="helmGaugeGlow"
                                                x="-20%"
                                                y="-20%"
                                                width="140%"
                                                height="140%"
                                            >
                                                <feGaussianBlur stdDeviation="2.5" result="blur" />
                                                <feMerge>
                                                    <feMergeNode in="blur" />
                                                    <feMergeNode in="SourceGraphic" />
                                                </feMerge>
                                            </filter>
                                        </defs>

                                        {{-- Gauge track --}}
                                        <path
                                            d="M 42 150 A 118 118 0 0 1 278 150"
                                            fill="none"
                                            stroke="#1e293b"
                                            stroke-width="18"
                                            stroke-linecap="round"
                                            pathLength="100"
                                        />

                                        {{-- Subtle inner guide --}}
                                        <path
                                            d="M 56 150 A 104 104 0 0 1 264 150"
                                            fill="none"
                                            stroke="#334155"
                                            stroke-width="1"
                                            stroke-dasharray="2 5"
                                            opacity="0.55"
                                        />

                                        {{-- Animated score arc --}}
                                        <path
                                            data-helm-score-ring
                                            d="M 42 150 A 118 118 0 0 1 278 150"
                                            fill="none"
                                            stroke="url(#helmScoreGradient)"
                                            stroke-width="18"
                                            stroke-linecap="round"
                                            pathLength="100"
                                            stroke-dasharray="100"
                                            stroke-dashoffset="100"
                                            filter="url(#helmGaugeGlow)"
                                        />

                                        {{-- Score-position marker --}}
                                        <circle
                                            data-helm-score-marker
                                            cx="42"
                                            cy="150"
                                            r="5.5"
                                            fill="#f8fafc"
                                            stroke="#2563eb"
                                            stroke-width="3"
                                            opacity="0"
                                        />

                                        {{-- Scale labels --}}
                                        <text
                                            x="37"
                                            y="179"
                                            fill="#64748b"
                                            font-size="10"
                                            font-weight="600"
                                            text-anchor="middle"
                                        >0</text>

                                        <text
                                            x="160"
                                            y="25"
                                            fill="#64748b"
                                            font-size="10"
                                            font-weight="600"
                                            text-anchor="middle"
                                        >50</text>

                                        <text
                                            x="283"
                                            y="179"
                                            fill="#64748b"
                                            font-size="10"
                                            font-weight="600"
                                            text-anchor="middle"
                                        >100</text>

                                        {{-- Main score --}}
                                        <text
                                            data-helm-score-number
                                            x="153"
                                            y="126"
                                            fill="#f8fafc"
                                            font-size="58"
                                            font-weight="650"
                                            letter-spacing="-3"
                                            text-anchor="middle"
                                        >0</text>

                                        <text
                                            x="203"
                                            y="126"
                                            fill="#64748b"
                                            font-size="12"
                                            font-weight="600"
                                        >/100</text>

                                        <text
                                            x="160"
                                            y="147"
                                            fill="#64748b"
                                            font-size="9"
                                            font-weight="700"
                                            letter-spacing="2.2"
                                            text-anchor="middle"
                                        >PORTFOLIO HEALTH</text>
                                    </svg>
                                </div>

                                @if ($needsAttention)
                                    <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                        @if ($criticalCount > 0)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-500/[0.05] px-2.5 py-1.5 text-xs font-semibold text-red-400"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                                {{ number_format($criticalCount) }} critical
                                            </span>
                                        @endif

                                        @if ($importantCount > 0)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-orange-500/20 bg-orange-500/[0.05] px-2.5 py-1.5 text-xs font-semibold text-orange-300"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>
                                                {{ number_format($importantCount) }} important
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1.5 max-w-xs text-center text-[11px] leading-4 text-slate-500">
                                        Review the highest-priority findings first.
                                    </p>
                                @else
                                    <p
                                        class="mt-3 max-w-xs text-center text-xs leading-5 text-slate-400"
                                    >
                                        No major open concerns are currently identified.
                                    </p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                    <a
                                        href="{{ route('analytics.helm-score') }}"
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-500"
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

                                    @if ($needsAttention)
                                        <a
                                            href="{{ route('advisor-action-center.index') }}"
                                            class="inline-flex items-center rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:border-red-500/40 hover:text-white"
                                        >
                                            Review Findings
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </section>


                        {{-- SCORE BREAKDOWN --}}
                        {{-- ================================================= --}}

                        <section
                            class="min-w-0 flex-1 rounded-2xl border border-slate-800/90 bg-slate-900 p-4 shadow-lg sm:p-5"
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

                                <span
                                    class="text-xs text-slate-500"
                                >
                                    0–100
                                </span>
                            </div>


                            <div
                                class="mt-2 divide-y divide-slate-800"
                            >

                                @foreach ($scoreBreakdown as $item)

                                    @php
                                       $categoryScore =
                                            data_get(
                                                $auditCategories,
                                                $item['key']
                                                . '.score'
                                            )
                                            ?? data_get(
                                                $helmScore,
                                                'categories.'
                                                . $item['key']
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

                                            $categoryScore >= 40 =>
                                                'Needs attention',

                                            default =>
                                                'Action recommended',
                                        };

                                        /*
                                         * Category score presentation.
                                         *
                                         * Bars and score numbers always use Helmio's
                                         * continuous blue visual language.
                                         *
                                         * Status text carries the health/urgency signal:
                                         *
                                         * 90–100  Excellent           Blue
                                         * 80–89   Strong              Blue
                                         * 70–79   Good                Blue
                                         * 60–69   Needs review        Amber
                                         * 40–59   Needs attention     Orange
                                         * 0–39    Action recommended  Red
                                         */
                                        $categoryStatusColor = match (true) {
                                            $categoryScore === null =>
                                                '#94a3b8',

                                            $categoryScore >= 70 =>
                                                '#93c5fd',

                                            $categoryScore >= 60 =>
                                                '#fcd34d',

                                            $categoryScore >= 40 =>
                                                '#fdba74',

                                            default =>
                                                '#ef4444',
                                        };

                                        /*
                                         * Continuous Helmio blue score color.
                                         * Lower scores are lighter blue and higher
                                         * scores become progressively deeper blue.
                                         */
                                        $categoryScoreColor =
                                            $scoreBlue(
                                                $categoryScore
                                            );

                                        /*
                                         * Category icons remain consistently Helmio blue.
                                         */
                                        $categoryIconColor =
                                            '#60a5fa';

                                        $categoryIconBackground =
                                            'rgba(37, 99, 235, 0.12)';

                                        $categoryScoreCapped =
                                            $categoryScore !== null
                                                ? max(
                                                    0,
                                                    min(
                                                        100,
                                                        $categoryScore
                                                    )
                                                )
                                                : null;
                                    @endphp


                                    <a
                                        href="{{ route(
                                            $categoryRoutes[$item['key']]
                                        ) }}"
                                        class="group block rounded-lg px-2 py-2.5 transition hover:bg-slate-800/30"
                                    >

                                        <div class="helm-score-breakdown-row">

                                            {{-- Icon --}}

                                            <div
                                                class="helm-score-breakdown-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                                                style="
                                                            color: {{ $categoryIconColor }};
                                                            background-color: {{ $categoryIconBackground }};
                                                        "
                                            >

                                                @switch($item['key'])

                                                    @case('cost')

                                                        <span
                                                            class="text-lg font-semibold"
                                                        >
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
                                                class="helm-score-breakdown-label min-w-0"
                                            >
                                                <p
                                                    class="truncate text-[13px] font-semibold text-white transition group-hover:text-blue-300"
                                                >
                                                    {{ $item['label'] }}
                                                </p>

                                                <p
                                                    class="mt-0.5 truncate text-[11px] font-medium"
                                                    style="color: {{ $categoryStatusColor }}"
                                                >
                                                    {{ $categoryStatus }}
                                                </p>
                                            </div>


                                            {{-- ================================= --}}
                                            {{-- GRADIENT CATEGORY BAR             --}}
                                            {{-- ================================= --}}

                                            <div
                                                class="helm-score-breakdown-bar min-w-0 w-full"
                                            >
                                                <div
                                                    class="h-1.5 overflow-hidden rounded-full bg-slate-800/90"
                                                >
                                                    @if ($categoryScore !== null)

                                                        <div
                                                            class="h-full rounded-full transition-all duration-500"
                                                            style="
                                                                width: {{ $categoryScoreCapped }}%;
                                                                background:
                                                                    linear-gradient(
                                                                        90deg,
                                                                        #dbeafe 0%,
                                                                        #bfdbfe 18%,
                                                                        #93c5fd 38%,
                                                                        #60a5fa 58%,
                                                                        #3b82f6 76%,
                                                                        #2563eb 90%,
                                                                        #1e3a8a 100%
                                                                    );
                                                                box-shadow:
                                                                    0 0 10px
                                                                    rgba(59, 130, 246, 0.20);
                                                                background-repeat: no-repeat;
                                                            "
                                                        ></div>

                                                    @endif
                                                </div>
                                            </div>


                                            {{-- Score --}}

                                            <span
                                                class="helm-score-breakdown-score w-full text-right text-sm font-semibold tabular-nums"
                                                style="color: {{ $categoryScoreColor }}"
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
                {{-- AI INSIGHT --}}
                {{-- ===================================================== --}}

                <section
                    class="mt-4 overflow-hidden rounded-2xl border border-violet-500/20 bg-slate-900 shadow-lg"
                >
                    <div class="border-b border-slate-800/80 px-5 py-4 sm:px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-violet-500/25 bg-violet-500/10 text-violet-300"
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

                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-violet-400">
                                        AI Portfolio Insight
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        The executive summary of what matters most across your portfolio right now.
                                    </p>
                                </div>
                            </div>

                            <a
                                href="{{ route('ai-insights.index') }}"
                                class="inline-flex w-fit items-center gap-2 text-xs font-semibold text-violet-300 transition hover:text-violet-200"
                            >
                                All AI Insights

                                <svg
                                    class="h-3.5 w-3.5"
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

                    <div class="px-5 py-5 sm:px-6">
                        @if ($latestAiInsight)
                            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($latestAiInsightIsStale)
                                            <span
                                                class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-[10px] font-semibold text-amber-300"
                                            >
                                                Needs refresh
                                            </span>
                                        @else
                                            <span
                                                class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-semibold text-emerald-300"
                                            >
                                                Current
                                            </span>
                                        @endif

                                        @if ($latestAiInsightGeneratedAt)
                                            <span class="text-[10px] text-slate-600">
                                                Generated {{ $latestAiInsightGeneratedAt->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>

                                    <h2
                                        class="mt-3 max-w-5xl text-xl font-semibold tracking-tight text-white sm:text-2xl"
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
                                    </h2>

                                    <p
                                        class="mt-3 line-clamp-3 max-w-6xl text-sm leading-6 text-slate-400"
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

                                    <div class="mt-5 flex flex-wrap gap-2">
                                        @if (data_get($latestAiInsight, 'id'))
                                            <a
                                                href="{{ route('ai-insights.show', $latestAiInsight) }}"
                                                class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-3.5 py-2.5 text-xs font-semibold text-white transition hover:bg-violet-500"
                                            >
                                                Read Full Insight

                                                <svg
                                                    class="h-3.5 w-3.5"
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
                                        @endif

                                        @if ($latestAiInsightIsStale)
                                            <form
                                                method="POST"
                                                action="{{ route('ai-insights.regenerate', $latestAiInsight) }}"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="rounded-lg border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs font-semibold text-slate-300 transition hover:border-violet-500/50 hover:text-white"
                                                >
                                                    Refresh Insight
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                <div
                                    class="rounded-xl border {{ $topConcern
                                        ? $topConcernClasses['border']
                                        : 'border-slate-800'
                                    }} {{ $topConcern
                                        ? $topConcernClasses['background']
                                        : 'bg-slate-950/60'
                                    }} p-4"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-[0.14em] {{ $topConcern
                                                ? (
                                                    in_array(
                                                        $topConcernSeverity,
                                                        ['critical', 'high'],
                                                        true
                                                    )
                                                        ? 'text-red-400'
                                                        : 'text-orange-400'
                                                )
                                                : 'text-slate-500'
                                            }}"
                                        >
                                            {{ $topConcern ? 'Top concern' : 'Portfolio status' }}
                                        </p>

                                        @if ($topConcern)
                                            <span
                                                class="rounded-full border px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.1em] {{ $topConcernClasses['badge'] }}"
                                            >
                                                {{ str($topConcernSeverity)
                                                    ->replace('_', ' ')
                                                    ->title() }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($topConcern)
                                        <h3 class="mt-2 text-sm font-semibold leading-5 text-white">
                                            {{ data_get(
                                                $topConcern,
                                                'title',
                                                'Portfolio issue worth reviewing'
                                            ) }}
                                        </h3>

                                        <p class="mt-1.5 line-clamp-3 text-xs leading-5 text-slate-400">
                                            {{ data_get(
                                                $topConcern,
                                                'message',
                                                data_get(
                                                    $topConcern,
                                                    'description',
                                                    'Review the highest-priority finding in your Action Center.'
                                                )
                                            ) }}
                                        </p>

                                        <a
                                            href="{{ route('advisor-action-center.index') }}"
                                            class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-400 transition hover:text-blue-300"
                                        >
                                            Review top concern

                                            <svg
                                                class="h-3.5 w-3.5"
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
                                    @else
                                        <h3 class="mt-2 text-sm font-semibold text-white">
                                            No major open concern
                                        </h3>

                                        <p class="mt-1.5 text-xs leading-5 text-slate-500">
                                            Helmio has not identified a high-priority open finding right now.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-white">
                                        See what your portfolio data is telling you.
                                    </h2>

                                    <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                                        Generate an AI explanation based on Helmio's deterministic scores, findings, and portfolio analytics.
                                    </p>
                                </div>

                                <a
                                    href="{{ route('ai-insights.index') }}"
                                    class="inline-flex w-fit shrink-0 rounded-lg bg-violet-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-violet-500"
                                >
                                    Generate Insight
                                </a>
                            </div>
                        @endif
                    </div>
                </section>


                {{-- ===================================================== --}}
                {{-- PORTFOLIO HEALTH BY CATEGORY --}}
                {{-- ===================================================== --}}

                @php
                    $dashboardCategoryAliases = [
                        'cost' => ['cost', 'costs', 'fees'],
                        'performance' => ['performance'],
                        'risk' => ['risk'],
                        'diversification' => ['diversification', 'concentration'],
                        'trading' => [
                            'trading',
                            'trading_discipline',
                            'trading-discipline',
                        ],
                        'tax' => [
                            'tax',
                            'tax_efficiency',
                            'tax-efficiency',
                        ],
                    ];

                    $formatDashboardPercent = function ($value, int $decimals = 1): ?string {
                        if (! is_numeric($value)) {
                            return null;
                        }

                        $numeric = (float) $value;

                        /*
                         * Most Helmio analytics ratios are stored as decimals
                         * (0.18 = 18%). Values already above 2 are assumed to
                         * be percentage points.
                         */
                        if (abs($numeric) <= 2) {
                            $numeric *= 100;
                        }

                        return number_format($numeric, $decimals) . '%';
                    };

                    $dashboardHealthCards = collect($scoreBreakdown)
                        ->map(function ($item) use (
                            $auditCategories,
                            $helmScore,
                            $findingsCollection,
                            $dashboardCategoryAliases,
                            $scoreBlue,
                            $allInCostDollars,
                            $formatDashboardPercent
                        ) {
                            $categoryKey = $item['key'];

                            $categoryScore = data_get(
                                $auditCategories,
                                $categoryKey . '.score'
                            ) ?? data_get(
                                $helmScore,
                                'categories.' . $categoryKey . '.score'
                            );

                            $categoryScore = $categoryScore !== null
                                ? min(100, max(0, (int) $categoryScore))
                                : null;

                            $categoryStatus = match (true) {
                                $categoryScore === null => 'More data needed',
                                $categoryScore >= 90 => 'Excellent',
                                $categoryScore >= 80 => 'Strong',
                                $categoryScore >= 70 => 'Good',
                                $categoryScore >= 60 => 'Needs review',
                                $categoryScore >= 40 => 'Needs attention',
                                default => 'Action recommended',
                            };

                            $categoryStatusClasses = match (true) {
                                $categoryScore === null =>
                                    'border-slate-700 bg-slate-800/50 text-slate-400',
                                $categoryScore >= 70 =>
                                    'border-blue-500/20 bg-blue-500/[0.07] text-blue-300',
                                $categoryScore >= 60 =>
                                    'border-amber-500/20 bg-amber-500/[0.07] text-amber-300',
                                $categoryScore >= 40 =>
                                    'border-orange-500/20 bg-orange-500/[0.07] text-orange-300',
                                default =>
                                    'border-red-500/25 bg-red-500/[0.08] text-red-400',
                            };

                            $aliases =
                                $dashboardCategoryAliases[$categoryKey]
                                ?? [$categoryKey];

                            $categoryFinding = $findingsCollection->first(
                                function ($finding) use ($aliases) {
                                    $findingCategory = strtolower((string) (
                                        data_get($finding, 'category')
                                        ?? data_get(
                                            $finding,
                                            'analytics_category'
                                        )
                                        ?? data_get($finding, 'type')
                                        ?? ''
                                    ));

                                    return collect($aliases)->contains(
                                        fn ($alias) =>
                                            $findingCategory
                                                === strtolower($alias)
                                            || str_contains(
                                                $findingCategory,
                                                strtolower($alias)
                                            )
                                    );
                                }
                            );

                            $categorySeverity = strtolower((string) data_get(
                                $categoryFinding,
                                'severity',
                                ''
                            ));

                            $severityRank = match ($categorySeverity) {
                                'critical' => 0,
                                'high' => 1,
                                'important', 'moderate', 'medium' => 2,
                                'low' => 3,
                                default => 4,
                            };

                            $categoryAccent = match (true) {
                                in_array(
                                    $categorySeverity,
                                    ['critical', 'high'],
                                    true
                                ) =>
                                    'bg-red-500',

                                in_array(
                                    $categorySeverity,
                                    ['important', 'moderate', 'medium'],
                                    true
                                ) =>
                                    'bg-orange-500',

                                $categoryScore !== null
                                    && $categoryScore < 40 =>
                                    'bg-red-500',

                                $categoryScore !== null
                                    && $categoryScore < 60 =>
                                    'bg-orange-500',

                                $categoryScore !== null
                                    && $categoryScore < 70 =>
                                    'bg-amber-500',

                                default =>
                                    'bg-blue-500',
                            };

                            $categoryCardClasses = match (true) {
                                in_array(
                                    $categorySeverity,
                                    ['critical', 'high'],
                                    true
                                ) =>
                                    'border-red-500/25 bg-red-500/[0.025]',

                                $categoryScore !== null
                                    && $categoryScore < 40 =>
                                    'border-red-500/20 bg-red-500/[0.018]',

                                in_array(
                                    $categorySeverity,
                                    ['important', 'moderate', 'medium'],
                                    true
                                ) =>
                                    'border-orange-500/20 bg-orange-500/[0.018]',

                                $categoryScore !== null
                                    && $categoryScore < 60 =>
                                    'border-orange-500/15 bg-slate-900',

                                default =>
                                    'border-slate-800/90 bg-slate-900',
                            };

                            $categoryRiskTitle =
                                data_get($categoryFinding, 'title');

                            $categoryRiskDetail = data_get(
                                $categoryFinding,
                                'message',
                                data_get(
                                    $categoryFinding,
                                    'description'
                                )
                            );

                            if (! $categoryRiskDetail) {
                                $categoryRiskDetail = data_get(
                                    $auditCategories,
                                    $categoryKey . '.reasons.0'
                                ) ?? data_get(
                                    $auditCategories,
                                    $categoryKey . '.recommendations.0'
                                );
                            }

                            if (! $categoryRiskTitle) {
                                $categoryRiskTitle = match (true) {
                                    $categoryScore === null =>
                                        'Still gathering data',

                                    $categoryScore < 40 =>
                                        'This category needs action',

                                    $categoryScore < 60 =>
                                        'This category needs attention',

                                    $categoryScore < 70 =>
                                        'Worth reviewing',

                                    default =>
                                        'No urgent risk identified',
                                };
                            }

                            if (! $categoryRiskDetail) {
                                $categoryRiskDetail = match (true) {
                                    $categoryScore === null =>
                                        'Helmio needs more portfolio history or account data before identifying a reliable risk here.',

                                    $categoryScore < 70 =>
                                        'Open the category analysis to review the factors currently affecting this score.',

                                    default =>
                                        'Helmio has not identified a high-priority issue in this category right now.',
                                };
                            }

                            $metrics = data_get(
                                $auditCategories,
                                $categoryKey . '.metrics',
                                []
                            );

                            $metricLabel = null;
                            $metricValue = null;

                            switch ($categoryKey) {
                                case 'cost':
                                    $annualCost = $allInCostDollars
                                        ?? data_get(
                                            $metrics,
                                            'total_annual_cost'
                                        )
                                        ?? data_get(
                                            $metrics,
                                            'annual_cost'
                                        );

                                    if (is_numeric($annualCost)) {
                                        $metricLabel = 'Estimated annual cost';
                                        $metricValue = money(
                                            (float) $annualCost
                                        );
                                    }
                                    break;

                                case 'performance':
                                    $return = data_get(
                                        $metrics,
                                        'portfolio_return'
                                    ) ?? data_get(
                                        $metrics,
                                        'annualized_return'
                                    ) ?? data_get(
                                        $metrics,
                                        'twr'
                                    );

                                    if (is_numeric($return)) {
                                        $metricLabel = 'Portfolio return';
                                        $metricValue =
                                            $formatDashboardPercent(
                                                $return
                                            );
                                    }
                                    break;

                                case 'risk':
                                    $volatility = data_get(
                                        $metrics,
                                        'annualized_volatility'
                                    ) ?? data_get(
                                        $metrics,
                                        'volatility'
                                    );

                                    if (is_numeric($volatility)) {
                                        $metricLabel = 'Annualized volatility';
                                        $metricValue =
                                            $formatDashboardPercent(
                                                $volatility
                                            );
                                    }
                                    break;

                                case 'diversification':
                                    $largestWeight = data_get(
                                        $metrics,
                                        'largest_security_weight'
                                    ) ?? data_get(
                                        $metrics,
                                        'largest_holding_weight'
                                    );

                                    if (is_numeric($largestWeight)) {
                                        $metricLabel = 'Largest position';
                                        $metricValue =
                                            $formatDashboardPercent(
                                                $largestWeight
                                            );
                                    }
                                    break;

                                case 'trading':
                                    $turnoverRate = data_get(
                                        $metrics,
                                        'turnover_rate'
                                    );

                                    if (is_numeric($turnoverRate)) {
                                        $metricLabel = 'Portfolio turnover';
                                        $metricValue =
                                            $formatDashboardPercent(
                                                $turnoverRate
                                            );
                                    }
                                    break;

                                case 'tax':
                                    $taxDrag = data_get(
                                        $metrics,
                                        'tax_drag_rate'
                                    ) ?? data_get(
                                        $metrics,
                                        'tax_cost_rate'
                                    );

                                    $taxCost = data_get(
                                        $metrics,
                                        'estimated_tax_cost'
                                    ) ?? data_get(
                                        $metrics,
                                        'tax_cost'
                                    );

                                    if (is_numeric($taxDrag)) {
                                        $metricLabel = 'Estimated tax drag';
                                        $metricValue =
                                            $formatDashboardPercent(
                                                $taxDrag
                                            );
                                    } elseif (is_numeric($taxCost)) {
                                        $metricLabel = 'Estimated tax cost';
                                        $metricValue = money(
                                            (float) $taxCost
                                        );
                                    }
                                    break;
                            }

                            $scoreSort =
                                $categoryScore === null
                                    ? 999
                                    : $categoryScore;

                            return [
                                'key' => $categoryKey,
                                'label' => $item['label'],
                                'score' => $categoryScore,
                                'score_color' => $scoreBlue(
                                    $categoryScore
                                ),
                                'status' => $categoryStatus,
                                'status_classes' =>
                                    $categoryStatusClasses,
                                'finding' => $categoryFinding,
                                'severity' => $categorySeverity,
                                'accent' => $categoryAccent,
                                'card_classes' => $categoryCardClasses,
                                'risk_title' => $categoryRiskTitle,
                                'risk_detail' => $categoryRiskDetail,
                                'metric_label' => $metricLabel,
                                'metric_value' => $metricValue,
                                'sort_key' =>
                                    ($severityRank * 1000)
                                    + $scoreSort,
                            ];
                        })
                        ->sortBy('sort_key')
                        ->values();
                @endphp

                <section class="mt-4">
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                Portfolio health
                            </p>

                            <h2 class="mt-1 text-lg font-semibold text-white">
                                Portfolio health by category
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Ranked by urgency so the areas that deserve attention appear first.
                            </p>
                        </div>

                        @if ($needsAttention)
                            <a
                                href="{{ route('advisor-action-center.index') }}"
                                class="inline-flex w-fit items-center gap-2 text-xs font-semibold text-blue-400 transition hover:text-blue-300"
                            >
                                Review Action Center

                                <svg
                                    class="h-3.5 w-3.5"
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
                        @endif
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($dashboardHealthCards as $rank => $card)
                            <a
                                href="{{ route($categoryRoutes[$card['key']]) }}"
                                class="group relative overflow-hidden rounded-2xl border {{ $card['card_classes'] }} p-5 shadow-lg transition duration-200 hover:-translate-y-0.5 hover:border-slate-700"
                            >
                                <div
                                    class="absolute inset-y-0 left-0 w-1 {{ $card['accent'] }}"
                                ></div>

                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-300"
                                        >
                                            @switch($card['key'])
                                                @case('cost')
                                                    <span class="text-lg font-semibold">$</span>
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

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-600"
                                                >
                                                    #{{ $rank + 1 }}
                                                </span>

                                                <p
                                                    class="truncate text-sm font-semibold text-white transition group-hover:text-blue-300"
                                                >
                                                    {{ $card['label'] }}
                                                </p>
                                            </div>

                                            <p
                                                class="mt-0.5 text-[10px] uppercase tracking-[0.12em] text-slate-600"
                                            >
                                                Helm Score category
                                            </p>
                                        </div>
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <div class="flex items-baseline justify-end gap-1">
                                            <span
                                                class="text-xl font-semibold tabular-nums"
                                                style="color: {{ $card['score_color'] }}"
                                            >
                                                {{ $card['score'] ?? '—' }}
                                            </span>

                                            @if ($card['score'] !== null)
                                                <span class="text-[10px] text-slate-600">
                                                    /100
                                                </span>
                                            @endif
                                        </div>

                                        <span
                                            class="mt-1 inline-flex rounded-full border px-2 py-0.5 text-[9px] font-semibold {{ $card['status_classes'] }}"
                                        >
                                            {{ $card['status'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-800/90">
                                    @if ($card['score'] !== null)
                                        <div
                                            class="h-full rounded-full"
                                            style="
                                                width: {{ $card['score'] }}%;
                                                background: linear-gradient(
                                                    90deg,
                                                    #dbeafe 0%,
                                                    #93c5fd 38%,
                                                    #60a5fa 58%,
                                                    #2563eb 90%,
                                                    #1e3a8a 100%
                                                );
                                            "
                                        ></div>
                                    @endif
                                </div>

                                @if ($card['metric_value'])
                                    <div
                                        class="mt-4 flex items-center justify-between rounded-xl border border-slate-800/80 bg-slate-950/55 px-3 py-2.5"
                                    >
                                        <span class="text-[10px] font-medium text-slate-500">
                                            {{ $card['metric_label'] }}
                                        </span>

                                        <span class="text-sm font-semibold tabular-nums text-slate-200">
                                            {{ $card['metric_value'] }}
                                        </span>
                                    </div>
                                @endif

                                <div class="mt-4 border-t border-slate-800/80 pt-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="h-1.5 w-1.5 rounded-full {{ $card['accent'] }}"
                                        ></span>

                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500"
                                        >
                                            {{ $card['finding']
                                                ? 'Most urgent risk'
                                                : 'Current assessment' }}
                                        </p>
                                    </div>

                                    <h3
                                        class="mt-2 line-clamp-1 text-sm font-semibold text-slate-200"
                                    >
                                        {{ $card['risk_title'] }}
                                    </h3>

                                    <p
                                        class="mt-1.5 line-clamp-2 min-h-[2.5rem] text-xs leading-5 text-slate-500"
                                    >
                                        {{ $card['risk_detail'] }}
                                    </p>

                                    <div class="mt-4 flex items-center justify-between">
                                        <span class="text-[10px] font-medium text-slate-600">
                                            View {{ strtolower($card['label']) }} analysis
                                        </span>

                                        <svg
                                            class="h-4 w-4 text-slate-600 transition group-hover:translate-x-0.5 group-hover:text-blue-400"
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
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>


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

                        <div
                            class="px-6 py-12 text-center"
                        >
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

                        <div
                            class="hidden overflow-x-auto md:block"
                        >
                            <table
                                class="w-full min-w-full text-sm"
                            >

                                <thead
                                    class="bg-slate-950"
                                >
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

                /*
                |--------------------------------------------------------------------------
                | Animate Helm Score
                |--------------------------------------------------------------------------
                */

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

                    const marker =
                        dial.querySelector(
                            '[data-helm-score-marker]'
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

                    function positionMarker(value) {
                        if (!marker) {
                            return;
                        }

                        const clamped = Math.max(0, Math.min(100, value));
                        const angle = Math.PI * (1 - (clamped / 100));
                        const radius = 118;
                        const centerX = 160;
                        const centerY = 150;
                        const x = centerX + (radius * Math.cos(angle));
                        const y = centerY - (radius * Math.sin(angle));

                        marker.setAttribute('cx', x.toFixed(2));
                        marker.setAttribute('cy', y.toFixed(2));
                        marker.style.opacity = clamped > 0 ? '1' : '0';
                    }


                    if (reducedMotion) {

                        scoreElement.textContent =
                            Math.round(target);

                        ring.style.strokeDashoffset =
                            String(
                                100 - target
                            );

                        positionMarker(target);

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

                        positionMarker(current);


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


                /*
                |--------------------------------------------------------------------------
                | Live Analysis Polling
                |--------------------------------------------------------------------------
                */

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
                                                ></path>
                                            </svg>
                                        </div>
                                    `;

                                    textClass =
                                        'text-slate-300';

                                } else if (status === 'active') {

                                    icon = `
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-500/10">
                                            <div
                                                class="h-2.5 w-2.5 animate-pulse rounded-full bg-blue-400"
                                            ></div>
                                        </div>
                                    `;

                                    textClass =
                                        'text-white';

                                    working = `
                                        <span
                                            class="ml-auto text-xs font-semibold text-blue-400"
                                        >
                                            Working…
                                        </span>
                                    `;

                                } else {

                                    icon = `
                                        <div
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-slate-700"
                                        >
                                            <div
                                                class="h-2 w-2 rounded-full bg-slate-700"
                                            ></div>
                                        </div>
                                    `;

                                }


                                return `
                                    <div
                                        class="flex items-center gap-3"
                                    >
                                        ${icon}

                                        <span
                                            class="text-sm font-medium ${textClass}"
                                        >
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