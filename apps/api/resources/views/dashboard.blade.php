
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600">
                    Independent investment oversight
                </p>

                <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    Welcome back, {{ auth()->user()->name }}
                </h2>
            </div>

            <a
                href="{{ route('accounts.create') }}"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
            >
                Connect account
            </a>
        </div>
    </x-slot>

    @php
        $overallScore = $helmScore['overall_score'] ?? null;
        $completeness = ($helmScore['data_completeness'] ?? 0) * 100;
        $categories = $helmScore['categories'] ?? [];

        $costAnalytics = $helmScore['cost_analytics'] ?? [];
        $fundAnalytics = $helmScore['fund_analytics'] ?? [];
        $diversificationAnalytics =
            $helmScore['diversification_analytics'] ?? [];
        $tradingAnalytics =
            $helmScore['trading_analytics'] ?? [];
        $performanceAnalytics =
            $helmScore['performance_analytics'] ?? [];
        $riskAnalytics =
            $helmScore['risk_analytics'] ?? [];

        $annualCost =
            $costAnalytics['total_annual_cost'] ?? 0;

        $potentialSavings =
            $fundAnalytics['estimated_savings'] ?? 0;

        $largestHolding =
            $diversificationAnalytics['securities'][0] ?? null;

        $largestSector =
            $diversificationAnalytics['sectors'][0] ?? null;

        $largestAssetClass =
            $diversificationAnalytics['asset_classes'][0] ?? null;

        $portfolioReturn =
            $performanceAnalytics['metrics']['portfolio_return']
            ?? null;

        $maximumDrawdown =
            $riskAnalytics['metrics']['maximum_drawdown']
            ?? null;

        $recommendations = collect($categories)
            ->flatMap(
                fn ($category) =>
                    $category['recommendations'] ?? []
            )
            ->filter()
            ->unique()
            ->take(5)
            ->values();

        $scoreRoutes = [
            'cost' => 'analytics.costs',
            'diversification' => 'analytics.diversification',
            'trading' => 'analytics.trading-discipline',
            'performance' => 'analytics.performance',
            'risk' => 'analytics.risk',
        ];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-6 xl:grid-cols-3">
                <article class="overflow-hidden rounded-3xl bg-slate-950 p-8 text-white shadow-xl xl:col-span-2">
                    <div class="flex flex-wrap items-start justify-between gap-8">
                        <div>
                            <p class="text-sm font-medium text-blue-300">
                                Overall Helm Score
                            </p>

                            @if ($overallScore !== null)
                                <div class="mt-4 flex flex-wrap items-end gap-4">
                                    <span class="text-7xl font-semibold tracking-tight">
                                        {{ $overallScore }}
                                    </span>

                                    <span class="pb-2 text-xl text-slate-300">
                                        {{ $helmScore['overall_label'] }}
                                    </span>
                                </div>

                                <p class="mt-4 max-w-xl text-sm leading-6 text-slate-400">
                                    Your score reflects the completed cost,
                                    diversification, trading, performance and
                                    risk analyses.
                                </p>
                            @else
                                <p class="mt-4 text-3xl font-semibold">
                                    {{ $helmScore['overall_label'] ?? 'Building your score' }}
                                </p>

                                <p class="mt-4 max-w-xl text-sm leading-6 text-slate-400">
                                    Helmio publishes an overall score after at
                                    least four categories have sufficient data.
                                </p>
                            @endif
                        </div>

                        <div class="min-w-56 rounded-2xl border border-white/10 bg-white/5 p-5">
                            <p class="text-sm text-slate-400">
                                Score completeness
                            </p>

                            <p class="mt-2 text-3xl font-semibold">
                                {{ number_format($completeness, 0) }}%
                            </p>

                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                                <div
                                    class="h-full rounded-full bg-blue-500"
                                    style="width: {{ min(100, $completeness) }}%"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a
                            href="{{ route('analytics.helm-score') }}"
                            class="rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-slate-100"
                        >
                            View full Helm Score
                        </a>

                        <a
                            href="{{ route('analytics.performance') }}"
                            class="rounded-xl border border-white/15 px-5 py-3 text-sm font-semibold text-white hover:bg-white/5"
                        >
                            Review performance
                        </a>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Portfolio value
                    </p>

                    <p class="mt-3 text-4xl font-semibold text-slate-900">
                        ${{ number_format($portfolioValue, 2) }}
                    </p>

                    <div class="mt-6 space-y-4 border-t border-slate-200 pt-6">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-slate-500">
                                Cash
                            </span>

                            <span class="font-semibold text-slate-900">
                                ${{ number_format($cashValue, 2) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-slate-500">
                                Accounts
                            </span>

                            <span class="font-semibold text-slate-900">
                                {{ $accountCount }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-slate-500">
                                Largest account
                            </span>

                            <span class="max-w-40 truncate font-semibold text-slate-900">
                                {{ $largestAccount?->name ?? '—' }}
                            </span>
                        </div>
                    </div>
                </article>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Estimated annual cost
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        ${{ number_format($annualCost, 2) }}
                    </p>

                    <a
                        href="{{ route('analytics.costs') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Review fees →
                    </a>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Potential savings
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-emerald-700">
                        ${{ number_format($potentialSavings, 2) }}
                    </p>

                    <a
                        href="{{ route('analytics.fund-expenses') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Review fund costs →
                    </a>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Largest holding
                    </p>

                    <p class="mt-3 text-2xl font-semibold text-slate-900">
                        {{ $largestHolding['symbol']
                            ?? $largestHolding['name']
                            ?? '—' }}
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        @if ($largestHolding)
                            {{ number_format(
                                $largestHolding['weight'] * 100,
                                1
                            ) }}% of portfolio
                        @else
                            Add holdings to calculate
                        @endif
                    </p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Portfolio return
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if ($portfolioReturn !== null)
                            {{ number_format(
                                $portfolioReturn * 100,
                                2
                            ) }}%
                        @else
                            —
                        @endif
                    </p>

                    <a
                        href="{{ route('analytics.performance') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Performance →
                    </a>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Maximum drawdown
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        @if ($maximumDrawdown !== null)
                            {{ number_format(
                                $maximumDrawdown * 100,
                                1
                            ) }}%
                        @else
                            —
                        @endif
                    </p>

                    <a
                        href="{{ route('analytics.risk') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        Risk analysis →
                    </a>
                </article>
            </section>

            <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    'cost' => 'Cost',
                    'diversification' => 'Diversification',
                    'trading' => 'Trading',
                    'performance' => 'Performance',
                    'risk' => 'Risk',
                ] as $key => $label)
                    @php
                        $category = $categories[$key] ?? [
                            'score' => null,
                            'label' => 'Not calculated',
                        ];

                        $score = $category['score'];
                    @endphp

                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    {{ $label }}
                                </p>

                                <p class="mt-2 text-lg font-semibold text-slate-900">
                                    {{ $category['label'] }}
                                </p>
                            </div>

                            <div
                                @class([
                                    'flex h-12 w-12 items-center justify-center rounded-2xl text-lg font-semibold',
                                    'bg-emerald-100 text-emerald-800' =>
                                        $score !== null && $score >= 80,
                                    'bg-blue-100 text-blue-800' =>
                                        $score !== null
                                        && $score >= 60
                                        && $score < 80,
                                    'bg-amber-100 text-amber-800' =>
                                        $score !== null
                                        && $score >= 40
                                        && $score < 60,
                                    'bg-red-100 text-red-800' =>
                                        $score !== null && $score < 40,
                                    'bg-slate-100 text-slate-500' =>
                                        $score === null,
                                ])
                            >
                                {{ $score ?? '—' }}
                            </div>
                        </div>

                        @if (isset($scoreRoutes[$key]))
                            <a
                                href="{{ route($scoreRoutes[$key]) }}"
                                class="mt-6 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                            >
                                Open analysis →
                            </a>
                        @endif
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm lg:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Portfolio review
                            </p>

                            <h3 class="mt-1 text-xl font-semibold text-slate-900">
                                Current findings
                            </h3>
                        </div>

                        <a
                            href="{{ route('analytics.helm-score') }}"
                            class="text-sm font-semibold text-blue-600 hover:text-blue-500"
                        >
                            View all findings →
                        </a>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($recommendations as $recommendation)
                            <div class="flex gap-4 rounded-2xl border border-slate-200 p-5">
                                <div class="mt-1 h-3 w-3 shrink-0 rounded-full bg-amber-500"></div>

                                <p class="text-sm leading-6 text-slate-700">
                                    {{ $recommendation }}
                                </p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center">
                                <p class="font-medium text-slate-900">
                                    No recommendations yet
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    Add account, holding, transaction and
                                    snapshot data to generate findings.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Quick actions
                    </p>

                    <div class="mt-6 space-y-3">
                        <a
                            href="{{ route('accounts.create') }}"
                            class="block rounded-xl bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-blue-500"
                        >
                            Connect account
                        </a>

                        <a
                            href="{{ route('accounts.index') }}"
                            class="block rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Manage accounts
                        </a>

                        <a
                            href="{{ route('analytics.helm-score') }}"
                            class="block rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Open Helm Score
                        </a>

                        <a
                            href="{{ route('analytics.risk') }}"
                            class="block rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Review risk
                        </a>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="font-semibold text-slate-900">
                            Investment accounts
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Current balances and account access
                        </p>
                    </div>

                    @if ($accounts->isEmpty())
                        <div class="p-12 text-center">
                            <p class="font-semibold text-slate-900">
                                No accounts connected
                            </p>

                            <p class="mt-2 text-sm text-slate-500">
                                Add your first investment account to begin.
                            </p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-200">
                            @foreach ($accounts->take(5) as $account)
                                <article class="flex flex-wrap items-center justify-between gap-5 px-6 py-5">
                                    <div>
                                        <a
                                            href="{{ route('accounts.holdings.index', $account) }}"
                                            class="font-semibold text-slate-900 hover:text-blue-600"
                                        >
                                            {{ $account->name }}
                                        </a>

                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $account->institution?->name
                                                ?? 'Manual account' }}
                                            ·
                                            {{ str($account->account_type)
                                                ->replace('_', ' ')
                                                ->title() }}
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <p class="font-semibold text-slate-900">
                                            ${{ number_format(
                                                $account->current_value,
                                                2
                                            ) }}
                                        </p>

                                        <div class="mt-2 flex flex-wrap justify-end gap-3 text-sm">
                                            <a
                                                href="{{ route('accounts.holdings.index', $account) }}"
                                                class="font-medium text-blue-600 hover:text-blue-500"
                                            >
                                                Holdings
                                            </a>

                                            <a
                                                href="{{ route('accounts.transactions.index', $account) }}"
                                                class="font-medium text-blue-600 hover:text-blue-500"
                                            >
                                                Transactions
                                            </a>

                                            <a
                                                href="{{ route('accounts.performance-data.index', $account) }}"
                                                class="font-medium text-blue-600 hover:text-blue-500"
                                            >
                                                Performance
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Portfolio concentration
                    </p>

                    <div class="mt-6 space-y-6">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                Largest holding
                            </p>

                            <p class="mt-2 font-semibold text-slate-900">
                                {{ $largestHolding['symbol']
                                    ?? $largestHolding['name']
                                    ?? '—' }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                @if ($largestHolding)
                                    {{ number_format(
                                        $largestHolding['weight'] * 100,
                                        1
                                    ) }}%
                                @else
                                    No data
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                Largest sector
                            </p>

                            <p class="mt-2 font-semibold text-slate-900">
                                {{ $largestSector['name'] ?? '—' }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                @if ($largestSector)
                                    {{ number_format(
                                        $largestSector['weight'] * 100,
                                        1
                                    ) }}%
                                @else
                                    No data
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                Largest asset class
                            </p>

                            <p class="mt-2 font-semibold text-slate-900">
                                {{ $largestAssetClass['name'] ?? '—' }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                @if ($largestAssetClass)
                                    {{ number_format(
                                        $largestAssetClass['weight'] * 100,
                                        1
                                    ) }}%
                                @else
                                    No data
                                @endif
                            </p>
                        </div>
                    </div>

                    <a
                        href="{{ route('analytics.diversification') }}"
                        class="mt-7 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-500"
                    >
                        View diversification →
                    </a>
                </article>
            </section>

            <section class="rounded-3xl bg-slate-950 p-8 text-white">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <p class="text-sm font-medium text-blue-300">
                            Helmio methodology
                        </p>

                        <h3 class="mt-2 text-xl font-semibold">
                            Scores are evidence-based and reproducible
                        </h3>

                        <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300">
                            Helmio calculates analytics using versioned formulas.
                            The dashboard summarizes those calculations but does
                            not determine whether an adviser, transaction or
                            investment is legally improper.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">
                            Helm Score version
                        </p>

                        <p class="mt-2 text-sm font-semibold text-white">
                            {{ $helmScore['formula_version'] ?? '—' }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
```
