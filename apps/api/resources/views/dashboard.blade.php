<x-app-layout>
    <x-slot name="header">
        <div class="hidden sm:block">
            <h2 class="text-2xl font-semibold tracking-tight text-slate-950">
                Dashboard
            </h2>

            <p class="mt-1.5 text-sm leading-6 text-slate-500">
                Your portfolio health, advisor audit, findings, and latest Helmio insights.
            </p>
        </div>
    </x-slot>

    @php
        $hasPremiumAccess = app(
            \App\Services\Billing\SubscriptionAccessService::class
        )->hasPremiumAccess(auth()->user());

        $audit = is_array($advisorAudit) ? $advisorAudit : [];
        $auditScore = $audit['overall_score'] ?? null;
        $auditLabel = $audit['overall_label'] ?? 'Building your score';
        $auditCompleteness = (float) ($audit['data_completeness'] ?? 0);

        $auditFindings = $audit['findings'] ?? [];
        $criticalFindings = $auditFindings['critical'] ?? [];
        $importantFindings = $auditFindings['important'] ?? [];
        $opportunityFindings = $auditFindings['opportunities'] ?? [];

        $topConcern = collect(
            array_merge(
                $criticalFindings,
                $importantFindings,
            )
        )
            ->sortByDesc('priority')
            ->first();

        $topOpportunity = collect($opportunityFindings)
            ->sortByDesc('priority')
            ->first();

        $auditCategories = $audit['categories'] ?? [];

        $suitabilityCategory = $auditCategories['suitability'] ?? [];
        $suitabilityScore = $suitabilityCategory['score'] ?? null;
        $suitabilityLabel = $suitabilityCategory['label']
            ?? 'Complete your investor profile';
        $suitabilityMetrics = $suitabilityCategory['metrics'] ?? [];
        $actualRiskLevel = $suitabilityMetrics['actual_risk_level'] ?? null;
        $expectedRiskTolerance = $suitabilityMetrics['expected_risk_tolerance'] ?? null;
        $riskGap = $suitabilityMetrics['risk_gap'] ?? null;
        $profileCompleteness = (float) (
            $suitabilityMetrics['profile_completeness'] ?? 0
        );
        $accountOverrideCount = (int) (
            $suitabilityMetrics['account_override_count'] ?? 0
        );

        $riskLabel = fn (?string $value): string =>
            $value
                ? str($value)->replace('_', ' ')->title()
                : 'Not available';

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

        $auditScoreClasses = match (true) {
            $auditScore === null => 'bg-gray-100 text-gray-700',
            $auditScore >= 90 => 'bg-green-100 text-green-800',
            $auditScore >= 80 => 'bg-emerald-100 text-emerald-800',
            $auditScore >= 70 => 'bg-blue-100 text-blue-800',
            $auditScore >= 60 => 'bg-amber-100 text-amber-800',
            $auditScore >= 40 => 'bg-orange-100 text-orange-800',
            default => 'bg-red-100 text-red-800',
        };

        $helmOverallScore = data_get($helmScore, 'overall_score');
        $helmOverallLabel = data_get(
            $helmScore,
            'overall_label',
            'Building your score'
        );

        $accountsCollection = collect($accounts);
        $findingsCollection = collect($openFindings);

        $criticalCount = $findingCounts['critical']
            ?? $findingCounts['critical_count']
            ?? collect($criticalFindings)->count();

        $importantCount = $findingCounts['important']
            ?? $findingCounts['important_count']
            ?? collect($importantFindings)->count();

        $opportunityCount = $findingCounts['opportunity']
            ?? $findingCounts['opportunity_count']
            ?? collect($opportunityFindings)->count();

        $totalAdvisorFindings = (int) $criticalCount
            + (int) $importantCount
            + (int) $opportunityCount;

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

        $latestAiInsightIsStale = (bool) data_get(
            $latestAiInsight,
            'is_stale',
            false
        );

        $latestAiInsightGeneratedAt = data_get(
            $latestAiInsight,
            'generated_at'
        );

        $latestAiInsightPortfolioValue = data_get(
            $latestAiInsight,
            'portfolio_value_at_generation'
        );

        $latestAiInsightAccountCount = data_get(
            $latestAiInsight,
            'account_count_at_generation'
        );
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if ($hasPremiumAccess)
                @include('dashboard.partials.hero')

                @include('dashboard.partials.priorities')

                @include('dashboard.partials.quick-actions')
            @else
                <section class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-xl">
                    <div class="relative overflow-hidden px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
                        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"></div>

                        <div class="relative grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                            <div class="min-w-0">
                                <span class="inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-xs font-semibold text-blue-200">
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

                                <h1 class="mt-5 text-3xl font-semibold tracking-tight sm:text-4xl">
                                    Subscribe before connecting your investment accounts.
                                </h1>

                                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                                    Helmio uses read-only brokerage access to monitor fees,
                                    risk, diversification, trading activity, tax efficiency,
                                    and advisor behavior. Start your trial to unlock secure
                                    account linking.
                                </p>

                                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                    <a
                                        href="{{ route('billing.pricing') }}"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
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
                                                d="M12 6v6l4 2"
                                            />
                                            <circle cx="12" cy="12" r="9" />
                                        </svg>

                                        Start Free Trial
                                    </a>

                                    <a
                                        href="{{ route('investor-profile.edit') }}"
                                        class="inline-flex w-full items-center justify-center rounded-xl border border-white/20 bg-white/10 px-5 py-3 font-semibold text-white transition hover:bg-white/15 sm:w-auto"
                                    >
                                        Complete Investor Profile
                                    </a>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                                <p class="text-sm font-semibold text-white">
                                    Premium unlocks
                                </p>

                                <div class="mt-4 space-y-3">
                                    @foreach ([
                                        'Secure brokerage connections',
                                        'Automatic holdings and transaction sync',
                                        'Advisor Audit and Action Center',
                                        'AI portfolio insights',
                                        'Performance, risk, cost, and tax analytics',
                                        'Monthly portfolio reviews and alerts',
                                    ] as $feature)
                                        <div class="flex items-start gap-3 text-sm text-slate-200">
                                            <svg
                                                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400"
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

                                            <span>{{ $feature }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-blue-200 bg-blue-50 p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">
                                Next step
                            </p>

                            <h2 class="mt-2 text-xl font-semibold text-blue-950">
                                Start monitoring your portfolio
                            </h2>

                            <p class="mt-1 text-sm leading-6 text-blue-800">
                                Once Stripe confirms your trial or subscription,
                                Helmio will unlock account linking automatically.
                            </p>
                        </div>

                        <a
                            href="{{ route('billing.pricing') }}"
                            class="inline-flex w-full shrink-0 items-center justify-center rounded-xl bg-blue-700 px-5 py-3 font-semibold text-white hover:bg-blue-800 sm:w-auto"
                        >
                            View Plans
                        </a>
                    </div>
                </section>
            @endif

            @include('dashboard.partials.suitability')

            <div class="min-w-0 w-full max-w-full overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:p-6">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-3">
                            <p class="text-sm font-medium text-gray-500">
                                Advisor Audit
                            </p>

                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $auditScoreClasses }}"
                            >
                                {{ $auditLabel }}
                            </span>

                            @if ($scoreChange !== null)
                                <span
                                    class="text-xs font-semibold
                                        {{ $scoreDirection === 'up'
                                            ? 'text-green-700'
                                            : ($scoreDirection === 'down'
                                                ? 'text-red-700'
                                                : 'text-gray-500') }}"
                                >
                                    @if ($scoreDirection === 'up')
                                        +{{ number_format((float) $scoreChange) }}
                                    @elseif ($scoreDirection === 'down')
                                        {{ number_format((float) $scoreChange) }}
                                    @else
                                        No change
                                    @endif
                                    from prior audit
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 flex items-end gap-3">
                            <p class="text-6xl font-bold tracking-tight text-gray-900">
                                {{ $auditScore ?? '—' }}
                            </p>

                            <p class="pb-2 text-lg font-medium text-gray-400">
                                / 100
                            </p>
                        </div>

                        <h3 class="mt-5 text-xl font-semibold text-gray-900">
                            {{ data_get(
                                $audit,
                                'executive_summary.headline',
                                'Building your advisor audit'
                            ) }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ data_get(
                                $audit,
                                'executive_summary.summary',
                                'Connect accounts and add complete portfolio history to generate a full advisor assessment.'
                            ) }}
                        </p>
                    </div>

                    <div class="w-full max-w-sm">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">
                                Data completeness
                            </span>

                            <span class="font-semibold text-gray-900">
                                {{ number_format($auditCompleteness * 100) }}%
                            </span>
                        </div>

                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-gray-900"
                                style="width: {{ min(
                                    100,
                                    max(0, $auditCompleteness * 100)
                                ) }}%"
                            ></div>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-2">
                            <div class="rounded-lg bg-red-50 p-3 text-center">
                                <p class="text-xl font-semibold text-red-900">
                                    {{ number_format((int) $criticalCount) }}
                                </p>

                                <p class="mt-1 text-xs text-red-700">
                                    Critical
                                </p>
                            </div>

                            <div class="rounded-lg bg-amber-50 p-3 text-center">
                                <p class="text-xl font-semibold text-amber-900">
                                    {{ number_format((int) $importantCount) }}
                                </p>

                                <p class="mt-1 text-xs text-amber-700">
                                    Important
                                </p>
                            </div>

                            <div class="rounded-lg bg-emerald-50 p-3 text-center">
                                <p class="text-xl font-semibold text-emerald-900">
                                    {{ number_format((int) $opportunityCount) }}
                                </p>

                                <p class="mt-1 text-xs text-emerald-700">
                                    Opportunities
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('advisor-audit.index') }}"
                            class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            View Full Advisor Audit
                        </a>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-700">
                            Top concern
                        </p>

                        @if ($topConcern)
                            <p class="mt-2 font-semibold text-red-900">
                                {{ $topConcern['title'] ?? 'Advisor audit finding' }}
                            </p>

                            <p class="mt-1 text-sm leading-6 text-red-800">
                                {{ $topConcern['message'] ?? '' }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-red-800">
                                No major concerns were detected in the available data.
                            </p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                            Top opportunity
                        </p>

                        @if ($topOpportunity)
                            <p class="mt-2 font-semibold text-emerald-900">
                                {{ $topOpportunity['title'] ?? 'Portfolio opportunity' }}
                            </p>

                            <p class="mt-1 text-sm leading-6 text-emerald-800">
                                {{ $topOpportunity['message'] ?? '' }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-emerald-800">
                                No major opportunities were identified yet.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-end justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Audit Categories
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Open any category for its detailed analysis.
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
                    @foreach ($categoryLabels as $key => $label)
                        @php
                            $category = $auditCategories[$key] ?? [];
                            $categoryScore = $category['score'] ?? null;
                            $categoryLabel = $category['label']
                                ?? 'Insufficient data';

                            $categoryScoreClasses = match (true) {
                                $categoryScore === null =>
                                    'bg-gray-100 text-gray-700',

                                $categoryScore >= 90 =>
                                    'bg-green-100 text-green-800',

                                $categoryScore >= 80 =>
                                    'bg-emerald-100 text-emerald-800',

                                $categoryScore >= 70 =>
                                    'bg-blue-100 text-blue-800',

                                $categoryScore >= 60 =>
                                    'bg-amber-100 text-amber-800',

                                $categoryScore >= 40 =>
                                    'bg-orange-100 text-orange-800',

                                default =>
                                    'bg-red-100 text-red-800',
                            };
                        @endphp

                        <a
                            href="{{ route($categoryRoutes[$key]) }}"
                            class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 transition hover:ring-gray-300"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-medium text-gray-500">
                                    {{ $label }}
                                </p>

                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $categoryScoreClasses }}"
                                >
                                    {{ $categoryScore ?? '—' }}
                                </span>
                            </div>

                            <p class="mt-4 truncate text-xs text-gray-500">
                                {{ $categoryLabel }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(0,0.65fr)]">
                <div class="min-w-0 overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:p-6">
                    <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Investment Accounts
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Accounts included in Helmio analytics.
                            </p>
                        </div>

                        <a
                            href="{{ $hasPremiumAccess
                                ? route('accounts.index')
                                : route('billing.pricing') }}"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 hover:text-gray-900"
                        >
                            @unless ($hasPremiumAccess)
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
                            @endunless

                            {{ $hasPremiumAccess ? 'View all' : 'Subscribe' }}
                        </a>
                    </div>

                    <div class="mt-5 min-w-0">
                        @if (! $hasPremiumAccess)
                            <div class="rounded-2xl border border-dashed border-blue-300 bg-blue-50 p-6 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
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
                                            d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5A1.5 1.5 0 0 1 18.75 12v7.5A1.5 1.5 0 0 1 17.25 21H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z"
                                        />
                                    </svg>
                                </div>

                                <p class="mt-4 font-semibold text-blue-950">
                                    Subscribe to connect investment accounts
                                </p>

                                <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-blue-800">
                                    Account linking, brokerage synchronization, holdings,
                                    and transaction monitoring are included with Helmio Premium.
                                </p>

                                <a
                                    href="{{ route('billing.pricing') }}"
                                    class="mt-5 inline-flex rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
                                >
                                    Start Free Trial
                                </a>
                            </div>
                        @elseif ($accountsCollection->isEmpty())
                            <div class="rounded-lg bg-gray-50 p-6 text-center">
                                <p class="text-sm font-medium text-gray-700">
                                    No investment accounts connected
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Add an account to begin building your portfolio audit.
                                </p>

                                <a
                                    href="{{ route('accounts.create') }}"
                                    class="mt-4 inline-flex rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                                >
                                    Add Account
                                </a>
                            </div>
                        @else
                            <div class="space-y-3 sm:hidden">
                                @foreach ($accountsCollection as $account)
                                    @php
                                        $accountValue =
                                            data_get($account, 'current_value')
                                            ?? data_get($account, 'market_value')
                                            ?? data_get($account, 'value')
                                            ?? 0;

                                        $holdingCount =
                                            data_get($account, 'holdings_count')
                                            ?? collect(
                                                data_get($account, 'holdings', [])
                                            )->count();

                                        $institutionName =
                                            data_get($account, 'institution.name')
                                            ?? data_get($account, 'institution_name')
                                            ?? data_get($account, 'institution')
                                            ?? '—';
                                    @endphp

                                    <article class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="min-w-0">
                                            <a
                                                href="{{ route(
                                                    'accounts.holdings.index',
                                                    $account
                                                ) }}"
                                                class="block truncate font-semibold text-slate-950"
                                            >
                                                {{ data_get(
                                                    $account,
                                                    'name',
                                                    'Investment Account'
                                                ) }}
                                            </a>

                                            <p class="mt-1 truncate text-sm text-slate-500">
                                                {{ $institutionName }}
                                            </p>
                                        </div>

                                        <div class="mt-4 flex min-w-0 items-end justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs text-slate-500">
                                                    Value
                                                </p>

                                                <p class="mt-1 break-words text-xl font-bold text-slate-950">
                                                    {{ money($accountValue) }}
                                                </p>
                                            </div>

                                            <div class="shrink-0 text-right">
                                                <p class="text-xs text-slate-500">
                                                    Holdings
                                                </p>

                                                <p class="mt-1 font-semibold text-slate-900">
                                                    {{ number_format((int) $holdingCount) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <a
                                                href="{{ route(
                                                    'accounts.holdings.index',
                                                    $account
                                                ) }}"
                                                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700"
                                            >
                                                Holdings
                                            </a>

                                            <a
                                                href="{{ route(
                                                    'accounts.profile.edit',
                                                    $account
                                                ) }}"
                                                class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                Suitability
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <div class="hidden w-full max-w-full overflow-x-auto sm:block">
                                <table class="w-full min-w-[42rem] divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500">
                                            Account
                                        </th>

                                        <th class="px-4 py-3 text-left font-medium text-gray-500">
                                            Institution
                                        </th>

                                        <th class="px-4 py-3 text-right font-medium text-gray-500">
                                            Value
                                        </th>

                                        <th class="px-4 py-3 text-right font-medium text-gray-500">
                                            Holdings
                                        </th>

                                        <th class="px-4 py-3 text-right font-medium text-gray-500">
                                            Profile
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($accountsCollection as $account)
                                        @php
                                            $accountValue =
                                                data_get($account, 'current_value')
                                                ?? data_get($account, 'market_value')
                                                ?? data_get($account, 'value')
                                                ?? 0;

                                            $holdingCount =
                                                data_get($account, 'holdings_count')
                                                ?? collect(
                                                    data_get($account, 'holdings', [])
                                                )->count();

                                            $institutionName =
                                                data_get($account, 'institution.name')
                                                ?? data_get($account, 'institution_name')
                                                ?? data_get($account, 'institution')
                                                ?? '—';
                                        @endphp

                                        <tr>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <a
                                                    href="{{ route(
                                                        'accounts.holdings.index',
                                                        $account
                                                    ) }}"
                                                    class="font-medium text-gray-900 hover:underline"
                                                >
                                                    {{ data_get($account, 'name', 'Investment Account') }}
                                                </a>
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                                {{ $institutionName }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-gray-900">
                                                {{ money($accountValue) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-right text-gray-600">
                                                {{ number_format((int) $holdingCount) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                                <a
                                                    href="{{ route(
                                                        'accounts.profile.edit',
                                                        $account
                                                    ) }}"
                                                    class="text-xs font-semibold text-blue-600 hover:text-blue-500"
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
                    </div>
                </div>

                <div class="min-w-0 space-y-6">
                    <div class="min-w-0 w-full max-w-full overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Largest Account
                        </h3>

                        @if ($largestAccount)
                            <p class="mt-4 break-words text-sm text-gray-500">
                                {{ data_get(
                                    $largestAccount,
                                    'institution.name',
                                    data_get(
                                        $largestAccount,
                                        'institution_name',
                                        'Investment account'
                                    )
                                ) }}
                            </p>

                            <p class="mt-1 break-words text-xl font-semibold text-gray-900">
                                {{ data_get($largestAccount, 'name', 'Account') }}
                            </p>

                            <p class="mt-3 break-words text-2xl font-bold text-gray-900">
                                {{ money(
                                    data_get($largestAccount, 'current_value')
                                    ?? data_get($largestAccount, 'market_value')
                                    ?? data_get($largestAccount, 'value')
                                    ?? 0
                                ) }}
                            </p>
                        @else
                            <p class="mt-4 text-sm text-gray-500">
                                No account data is available yet.
                            </p>
                        @endif
                    </div>

                    <div class="min-w-0 w-full max-w-full overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:p-6">
                        <div class="flex min-w-0 items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="break-words text-lg font-semibold text-gray-900">
                                    Latest AI Insight
                                </h3>

                                @if ($latestAiInsight)
                                    <p class="mt-1 break-words text-xs text-gray-400">
                                        @if ($latestAiInsightGeneratedAt)
                                            Generated
                                            {{ $latestAiInsightGeneratedAt->diffForHumans() }}
                                        @else
                                            Generation time unavailable
                                        @endif
                                    </p>
                                @endif
                            </div>

                            <a
                                href="{{ route('ai-insights.index') }}"
                                class="text-sm font-semibold text-gray-700 hover:text-gray-900"
                            >
                                View all
                            </a>
                        </div>

                        @if ($latestAiInsight)
                            <div class="mt-4 flex min-w-0 flex-wrap items-center gap-2">
                                @if ($latestAiInsightIsStale)
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                        Needs Refresh
                                    </span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                        Current
                                    </span>
                                @endif

                                @if ($latestAiInsightPortfolioValue !== null)
                                    <span class="max-w-full break-words rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        Based on
                                        {{ money($latestAiInsightPortfolioValue) }}
                                    </span>
                                @endif

                                @if ($latestAiInsightAccountCount !== null)
                                    <span class="max-w-full break-words rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        {{ number_format(
                                            (int) $latestAiInsightAccountCount
                                        ) }}
                                        {{ Str::plural(
                                            'account',
                                            (int) $latestAiInsightAccountCount
                                        ) }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-4 break-words text-sm font-semibold text-gray-900">
                                {{ data_get(
                                    $latestAiInsight,
                                    'headline',
                                    data_get(
                                        $latestAiInsight,
                                        'title',
                                        'Portfolio insight'
                                    )
                                ) }}
                            </p>

                            @if ($latestAiInsightIsStale)
                                <div class="mt-4 min-w-0 overflow-hidden rounded-xl border border-amber-200 bg-amber-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                                        Portfolio changed
                                    </p>

                                    <p class="mt-2 break-words text-sm leading-6 text-amber-800">
                                        {{ data_get(
                                            $latestAiInsight,
                                            'stale_reason',
                                            'Portfolio data changed after this insight was generated.'
                                        ) }}
                                    </p>

                                    <p class="mt-2 text-xs text-amber-700">
                                        The summary below reflects the portfolio snapshot captured when this insight was generated.
                                    </p>
                                </div>
                            @endif

                            <p class="mt-4 break-words text-sm leading-6 text-gray-600 sm:line-clamp-5">
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

                            <div class="mt-5 flex min-w-0 flex-wrap gap-3">
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
                                            class="inline-flex max-w-full items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-center text-sm font-semibold text-slate-950 hover:bg-amber-400"
                                        >
                                            Regenerate Insight
                                        </button>
                                    </form>
                                @endif

                                @if (data_get($latestAiInsight, 'id'))
                                    <a
                                        href="{{ route(
                                            'ai-insights.show',
                                            $latestAiInsight
                                        ) }}"
                                        class="inline-flex max-w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Read insight
                                    </a>
                                @endif
                            </div>
                        @else
                            <p class="mt-4 text-sm text-gray-500">
                                No AI insight has been generated yet.
                            </p>

                            <a
                                href="{{ route('ai-insights.index') }}"
                                class="mt-4 inline-flex rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                            >
                                Generate Insight
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Open Findings
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Findings requiring review or follow-up.
                            </p>
                        </div>

                        <a
                            href="{{ route('advisor-audit.index') }}"
                            class="text-sm font-semibold text-gray-700 hover:text-gray-900"
                        >
                            Full audit
                        </a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($findingsCollection->take(5) as $finding)
                            @php
                                $severity = data_get(
                                    $finding,
                                    'severity',
                                    'moderate'
                                );

                                $findingClasses = match ($severity) {
                                    'critical' =>
                                        'border-red-300 bg-red-50 text-red-900',

                                    'high' =>
                                        'border-red-200 bg-red-50 text-red-900',

                                    'moderate' =>
                                        'border-amber-200 bg-amber-50 text-amber-900',

                                    default =>
                                        'border-blue-200 bg-blue-50 text-blue-900',
                                };
                            @endphp

                            <div class="rounded-lg border p-4 {{ $findingClasses }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide opacity-70">
                                            {{ data_get(
                                                $finding,
                                                'category_label',
                                                data_get(
                                                    $finding,
                                                    'category',
                                                    'Finding'
                                                )
                                            ) }}
                                        </p>

                                        <p class="mt-1 font-semibold">
                                            {{ data_get(
                                                $finding,
                                                'title',
                                                'Advisor audit finding'
                                            ) }}
                                        </p>

                                        <p class="mt-1 text-sm leading-6 opacity-90">
                                            {{ data_get($finding, 'message', '') }}
                                        </p>
                                    </div>

                                    <span class="rounded-full bg-white/70 px-2 py-0.5 text-xs font-semibold capitalize">
                                        {{ $severity }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg bg-gray-50 p-5 text-sm text-gray-600">
                                No open findings require attention.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Audit Activity
                    </h3>

                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">
                                Current audit
                            </dt>

                            <dd class="text-right font-medium text-gray-900">
                                {{ data_get(
                                    $currentAuditRun,
                                    'calculated_at',
                                    data_get(
                                        $currentAuditRun,
                                        'created_at',
                                        'Not available'
                                    )
                                ) }}
                            </dd>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">
                                Previous audit
                            </dt>

                            <dd class="text-right font-medium text-gray-900">
                                {{ data_get(
                                    $previousAuditRun,
                                    'calculated_at',
                                    data_get(
                                        $previousAuditRun,
                                        'created_at',
                                        'Not available'
                                    )
                                ) }}
                            </dd>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">
                                Score change
                            </dt>

                            <dd
                                class="text-right font-medium
                                    {{ $scoreDirection === 'up'
                                        ? 'text-green-700'
                                        : ($scoreDirection === 'down'
                                            ? 'text-red-700'
                                            : 'text-gray-900') }}"
                            >
                                @if ($scoreChange === null)
                                    —
                                @elseif ($scoreChange > 0)
                                    +{{ number_format((float) $scoreChange) }}
                                @else
                                    {{ number_format((float) $scoreChange) }}
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <a
                            href="{{ route('advisor-audit.history') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Audit History
                        </a>

                        <a
                            href="{{ route('advisor-audit.report') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            Full Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>