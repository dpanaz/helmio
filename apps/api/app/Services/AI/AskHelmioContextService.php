<?php

namespace App\Services\AI;

use App\Models\AiInsightRun;
use App\Models\AuditFinding;
use App\Models\AuditRun;
use App\Models\BrokerageConnection;
use App\Models\InvestmentAccount;
use App\Models\MonthlyPortfolioReview;
use App\Models\PortfolioStateSnapshot;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Services\Analytics\HelmScoreService;
use App\Services\Analytics\Risk\SuitabilityRiskService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class AskHelmioContextService
{
    public const CONTEXT_VERSION =
        'ask-helmio-context-0.2.0';

    public function __construct(
        private readonly HelmScoreService $helmScoreService,
        private readonly SuitabilityRiskService $suitabilityRiskService,
    ) {
    }

    /**
     * Build comprehensive portfolio intelligence context
     * for Ask Helmio.
     *
     * @return array<string, mixed>
     */
    public function build(
        User $user,
        string $question,
    ): array {
        $startDate = now()
            ->subYear()
            ->startOfDay();

        $endDate = now()
            ->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Investment accounts
        |--------------------------------------------------------------------------
        |
        | Load enough relationships for the Helm Score analytics services.
        |
        */

        $accounts = InvestmentAccount::query()
            ->where('user_id', $user->id)
            ->with([
                'holdings.security',
                'transactions.security',
                'institution',
            ])
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Current portfolio snapshot
        |--------------------------------------------------------------------------
        */

        $latestSnapshot = PortfolioStateSnapshot::query()
            ->where('user_id', $user->id)
            ->with('holdings')
            ->orderByDesc('captured_at')
            ->orderByDesc('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Latest persisted Advisor Audit
        |--------------------------------------------------------------------------
        */

        $latestAuditQuery = AuditRun::query();

if (DB::connection()->getDriverName() === 'mysql') {
    $latestAuditQuery->from(
        DB::raw(
            'audit_runs FORCE INDEX (audit_runs_user_date_id_index)'
        )
    );
}

$latestAudit = $latestAuditQuery
    ->where('user_id', $user->id)
    ->orderByDesc('calculated_for_date')
    ->orderByDesc('id')
    ->first();

        /*
        |--------------------------------------------------------------------------
        | Monthly review
        |--------------------------------------------------------------------------
        */

        $latestReview = MonthlyPortfolioReview::query()
            ->where('user_id', $user->id)
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Latest AI insight
        |--------------------------------------------------------------------------
        */

        $latestInsight = AiInsightRun::query()
            ->where('user_id', $user->id)
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Timeline
        |--------------------------------------------------------------------------
        */

        $timelineEvents = TimelineEvent::query()
            ->where('user_id', $user->id)
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Advisor Audit findings
        |--------------------------------------------------------------------------
        */

        $findings = AuditFinding::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                AuditFinding::STATUS_OPEN,
                AuditFinding::STATUS_REVIEWED,
            ])
            ->orderByRaw(
                "CASE severity
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                    WHEN 'information' THEN 5
                    WHEN 'positive' THEN 6
                    ELSE 7
                END"
            )
            ->limit(20)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Brokerage freshness
        |--------------------------------------------------------------------------
        */

        $connections = BrokerageConnection::query()
            ->where('user_id', $user->id)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Helm Score + raw analytics
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | This becomes Ask Helmio's primary portfolio analytics source.
        | HelmScoreService already uses the same analytics engines used to
        | calculate the user's Helm Score.
        |
        | If one analytics calculation throws an exception, we do NOT want
        | Ask Helmio itself to fail.
        |
        */

        $helmScore = $this->safeAnalyticsCall(
            callback: fn (): array =>
                $accounts->isEmpty()
                    ? []
                    : $this->helmScoreService->calculate(
                        $accounts
                    ),
            category: 'helm_score',
        );

        /*
        |--------------------------------------------------------------------------
        | Suitability
        |--------------------------------------------------------------------------
        |
        | Suitability is not currently one of the seven Helm Score categories,
        | so calculate it separately.
        |
        */

        $suitability = $this->safeAnalyticsCall(
            callback: fn (): array =>
                $accounts->isEmpty()
                    ? []
                    : $this->suitabilityRiskService->analyze(
                        user: $user,
                        startDate: $startDate,
                        endDate: $endDate,
                    ),
            category: 'suitability',
        );

        /*
        |--------------------------------------------------------------------------
        | Holdings
        |--------------------------------------------------------------------------
        */

        $holdings = $latestSnapshot?->holdings
            ?->sortByDesc('market_value')
            ->take(25)
            ->map(
                fn ($holding): array => [
                    'symbol' =>
                        $holding->symbol,

                    'name' =>
                        $holding->name,

                    'market_value' =>
                        $holding->market_value !== null
                            ? (float) $holding->market_value
                            : null,

                    'portfolio_weight' =>
                        $holding->portfolio_weight !== null
                            ? (float) $holding->portfolio_weight
                            : null,

                    'asset_class' =>
                        $holding->asset_class,

                    'sector' =>
                        $holding->sector,
                ],
            )
            ->values()
            ->all() ?? [];

        /*
        |--------------------------------------------------------------------------
        | Recent transactions
        |--------------------------------------------------------------------------
        |
        | This allows questions such as:
        |
        | - What has my advisor been trading?
        | - Have I been trading too much?
        | - What changed recently?
        |
        */

        $recentTransactions = $accounts
            ->flatMap(
                fn (InvestmentAccount $account) =>
                    $account->transactions
                        ->map(
                            fn ($transaction): array => [
                                'account_id' =>
                                    $account->id,

                                'account_name' =>
                                    $account->name,

                                'date' =>
                                    $transaction
                                        ->transaction_date
                                        ?->toDateString(),

                                'type' =>
                                    $transaction
                                        ->transaction_type,

                                'symbol' =>
                                    $transaction
                                        ->security
                                        ?->symbol,

                                'description' =>
                                    $transaction
                                        ->description,

                                'quantity' =>
                                    $transaction->quantity !== null
                                        ? (float) $transaction->quantity
                                        : null,

                                'price' =>
                                    $transaction->price !== null
                                        ? (float) $transaction->price
                                        : null,

                                'gross_amount' =>
                                    $transaction->gross_amount !== null
                                        ? (float) $transaction->gross_amount
                                        : null,

                                'fees' =>
                                    $transaction->fees !== null
                                        ? (float) $transaction->fees
                                        : null,

                                'net_amount' =>
                                    $transaction->net_amount !== null
                                        ? (float) $transaction->net_amount
                                        : null,
                            ],
                        )
            )
            ->sortByDesc('date')
            ->take(40)
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Investor profile
        |--------------------------------------------------------------------------
        */

        $user->loadMissing('investorProfile');

        $investorProfile = $user->investorProfile;

        /*
        |--------------------------------------------------------------------------
        | Analytics availability
        |--------------------------------------------------------------------------
        */

        $dataAvailability =
            $this->buildDataAvailability(
                helmScore: $helmScore,
                suitability: $suitability,
                snapshotExists:
                    $latestSnapshot !== null,
                auditExists:
                    $latestAudit !== null,
            );

        /*
        |--------------------------------------------------------------------------
        | Build final context
        |--------------------------------------------------------------------------
        */

        return [
            'context_version' =>
                self::CONTEXT_VERSION,

            'generated_at' =>
                now()->toIso8601String(),

            'question' =>
                $question,

            'analysis_period' => [
                'start_date' =>
                    $startDate->toDateString(),

                'end_date' =>
                    $endDate->toDateString(),
            ],

            /*
            |--------------------------------------------------------------------------
            | Basic portfolio
            |--------------------------------------------------------------------------
            */

            'portfolio_snapshot' =>
                $latestSnapshot === null
                    ? null
                    : [
                        'id' =>
                            $latestSnapshot->id,

                        'captured_at' =>
                            $latestSnapshot
                                ->captured_at
                                ?->toIso8601String(),

                        'portfolio_value' =>
                            (float) $latestSnapshot
                                ->portfolio_value,

                        'cash_value' =>
                            (float) $latestSnapshot
                                ->cash_value,

                        'invested_value' =>
                            (float) $latestSnapshot
                                ->invested_value,

                        'account_count' =>
                            $latestSnapshot
                                ->account_count,

                        'holding_count' =>
                            $latestSnapshot
                                ->holding_count,

                        'top_holdings' =>
                            $holdings,
                    ],

            /*
            |--------------------------------------------------------------------------
            | Helm Score
            |--------------------------------------------------------------------------
            |
            | Keep the entire result.
            |
            | This is deliberate. HelmScoreService contains not only category
            | scores but also the analytics underlying those scores.
            |
            */

            'helm_score' =>
                $helmScore,

            /*
            |--------------------------------------------------------------------------
            | Suitability
            |--------------------------------------------------------------------------
            */

            'suitability' =>
                $suitability,

            /*
            |--------------------------------------------------------------------------
            | Persisted Advisor Audit
            |--------------------------------------------------------------------------
            */

            'latest_audit' =>
                $latestAudit === null
                    ? null
                    : [
                        'id' =>
                            $latestAudit->id,

                        'date' =>
                            $latestAudit
                                ->calculated_for_date
                                ?->toDateString(),

                        'score' =>
                            $latestAudit
                                ->audit_score,

                        'grade' =>
                            $latestAudit
                                ->audit_grade,

                        'annual_cost' =>
                            $latestAudit
                                    ->annual_cost !== null
                                ? (float) $latestAudit
                                    ->annual_cost
                                : null,

                        'potential_savings' =>
                            $latestAudit
                                    ->potential_savings !== null
                                ? (float) $latestAudit
                                    ->potential_savings
                                : null,

                        'issue_count' =>
                            $latestAudit
                                ->issue_count,

                        'category_scores' =>
                            $latestAudit
                                ->category_scores,
                    ],

            /*
            |--------------------------------------------------------------------------
            | Findings
            |--------------------------------------------------------------------------
            */

            'open_findings' =>
                $findings
                    ->map(
                        fn (
                            AuditFinding $finding
                        ): array => [
                            'id' =>
                                $finding->id,

                            'severity' =>
                                $finding->severity,

                            'category' =>
                                $finding->category,

                            'title' =>
                                $finding->title,

                            'description' =>
                                $finding->description,

                            'recommendation' =>
                                $finding->recommendation,

                            'route_name' =>
                                $finding->route_name,
                        ]
                    )
                    ->values()
                    ->all(),

            /*
            |--------------------------------------------------------------------------
            | Holdings
            |--------------------------------------------------------------------------
            */

            'holdings' =>
                $holdings,

            /*
            |--------------------------------------------------------------------------
            | Transactions
            |--------------------------------------------------------------------------
            */

            'recent_transactions' =>
                $recentTransactions,

            /*
            |--------------------------------------------------------------------------
            | Timeline
            |--------------------------------------------------------------------------
            */

            'recent_timeline_events' =>
                $timelineEvents
                    ->map(
                        fn (
                            TimelineEvent $event
                        ): array => [
                            'id' =>
                                $event->id,

                            'event_date' =>
                                $event
                                    ->event_date
                                    ?->toDateString(),

                            'type' =>
                                $event->type,

                            'severity' =>
                                $event->severity,

                            'category' =>
                                $event->category,

                            'headline' =>
                                $event->headline,

                            'summary' =>
                                $event->summary,

                            'metrics' =>
                                $event->metrics,

                            'route_name' =>
                                $event->route_name,

                            'source_id' =>
                                $event->source_id,
                        ]
                    )
                    ->values()
                    ->all(),

            /*
            |--------------------------------------------------------------------------
            | Monthly review
            |--------------------------------------------------------------------------
            */

            'latest_monthly_review' =>
                $latestReview === null
                    ? null
                    : [
                        'id' =>
                            $latestReview->id,

                        'period' =>
                            $latestReview
                                ->period_start
                                ?->format('F Y'),

                        'headline' =>
                            $latestReview->headline,

                        'summary' =>
                            $latestReview->summary,

                        'key_changes' =>
                            $latestReview->key_changes,

                        'positive_changes' =>
                            $latestReview
                                ->positive_changes,

                        'review_items' =>
                            $latestReview
                                ->review_items,

                        'limitations' =>
                            $latestReview
                                ->limitations,
                    ],

            /*
            |--------------------------------------------------------------------------
            | Latest AI insight
            |--------------------------------------------------------------------------
            */

            'latest_ai_insight' =>
                $latestInsight === null
                    ? null
                    : [
                        'id' =>
                            $latestInsight->id,

                        'headline' =>
                            $latestInsight->headline,

                        'summary' =>
                            $latestInsight->summary,

                        'priorities' =>
                            $latestInsight->priorities,

                        'positive_changes' =>
                            $latestInsight
                                ->positive_changes,

                        'limitations' =>
                            $latestInsight
                                ->limitations,
                    ],

            /*
            |--------------------------------------------------------------------------
            | Investor profile
            |--------------------------------------------------------------------------
            */

            'investor_profile' =>
                $investorProfile === null
                    ? null
                    : [
                        'planned_retirement_age' =>
                            $investorProfile
                                ->planned_retirement_age,

                        'employment_status' =>
                            $investorProfile
                                ->employment_status,

                        'annual_income' =>
                            $investorProfile
                                    ->annual_income !== null
                                ? (float) $investorProfile
                                    ->annual_income
                                : null,

                        'estimated_net_worth' =>
                            $investorProfile
                                    ->estimated_net_worth !== null
                                ? (float) $investorProfile
                                    ->estimated_net_worth
                                : null,

                        'tax_bracket' =>
                            $investorProfile
                                    ->tax_bracket !== null
                                ? (float) $investorProfile
                                    ->tax_bracket
                                : null,

                        'investment_experience' =>
                            $investorProfile
                                ->investment_experience,

                        'primary_objective' =>
                            $investorProfile
                                ->primary_objective,

                        'time_horizon_years' =>
                            $investorProfile
                                ->time_horizon_years,

                        'risk_tolerance' =>
                            $investorProfile
                                ->risk_tolerance,

                        'target_allocation' =>
                            $investorProfile
                                ->target_allocation,

                        'liquidity_needs' =>
                            $investorProfile
                                ->liquidity_needs,
                    ],

            /*
            |--------------------------------------------------------------------------
            | Data availability
            |--------------------------------------------------------------------------
            */

            'data_availability' =>
                $dataAvailability,

            /*
            |--------------------------------------------------------------------------
            | Brokerage freshness
            |--------------------------------------------------------------------------
            */

            'data_freshness' => [
                'connection_count' =>
                    $connections->count(),

                'active_connection_count' =>
                    $connections
                        ->where(
                            'status',
                            BrokerageConnection::STATUS_ACTIVE
                        )
                        ->count(),

                'stale_connection_count' =>
                    $connections
                        ->filter(
                            fn (
                                BrokerageConnection $connection
                            ): bool =>
                                $connection->isStale()
                        )
                        ->count(),

                'latest_successful_sync_at' =>
                    $connections
                        ->pluck(
                            'last_successful_sync_at'
                        )
                        ->filter()
                        ->sortDesc()
                        ->first()
                        ?->toIso8601String(),
            ],

            /*
            |--------------------------------------------------------------------------
            | Limitations
            |--------------------------------------------------------------------------
            */

            'limitations' =>
                $this->limitations(
                    snapshot:
                        $latestSnapshot,
                    audit:
                        $latestAudit,
                    connections:
                        $connections,
                    dataAvailability:
                        $dataAvailability,
                ),
        ];
    }

    /**
     * Execute analytics without allowing one broken or
     * unavailable category to break Ask Helmio.
     *
     * @param callable(): array $callback
     * @return array<string, mixed>
     */
    private function safeAnalyticsCall(
        callable $callback,
        string $category,
    ): array {
        try {
            $result = $callback();

            if (empty($result)) {
                return [
                    'status' =>
                        'insufficient_data',

                    'category' =>
                        $category,

                    'message' =>
                        'No analytics data is currently available.',
                ];
            }

            return $result;
        } catch (Throwable $exception) {
            report($exception);

            return [
                'status' =>
                    'unavailable',

                'category' =>
                    $category,

                'message' =>
                    'This analytics category is temporarily unavailable.',
            ];
        }
    }

    /**
     * @param array<string, mixed> $helmScore
     * @param array<string, mixed> $suitability
     * @return array<string, bool>
     */
    private function buildDataAvailability(
        array $helmScore,
        array $suitability,
        bool $snapshotExists,
        bool $auditExists,
    ): array {
        $categories =
            $helmScore['categories'] ?? [];

        return [
            'portfolio_snapshot' =>
                $snapshotExists,

            'helm_score' =>
                isset(
                    $helmScore['overall_score']
                ),

            'cost' =>
                $this->categoryAvailable(
                    $categories['cost'] ?? null
                ),

            'diversification' =>
                $this->categoryAvailable(
                    $categories['diversification'] ?? null
                ),

            'performance' =>
                $this->categoryAvailable(
                    $categories['performance'] ?? null
                ),

            'risk' =>
                $this->categoryAvailable(
                    $categories['risk'] ?? null
                ),

            'trading' =>
                $this->categoryAvailable(
                    $categories['trading'] ?? null
                ),

            'cash' =>
                $this->categoryAvailable(
                    $categories['cash'] ?? null
                ),

            'tax' =>
                $this->categoryAvailable(
                    $categories['tax'] ?? null
                ),

            'suitability' =>
                $this->analyticsAvailable(
                    $suitability
                ),

            'advisor_audit' =>
                $auditExists,
        ];
    }

    /**
     * @param mixed $category
     */
    private function categoryAvailable(
        mixed $category,
    ): bool {
        if (! is_array($category)) {
            return false;
        }

        if (
            array_key_exists(
                'score',
                $category
            )
        ) {
            return $category['score'] !== null;
        }

        return $this->analyticsAvailable(
            $category
        );
    }

    /**
     * @param array<string, mixed> $analytics
     */
    private function analyticsAvailable(
        array $analytics,
    ): bool {
        if ($analytics === []) {
            return false;
        }

        $status =
            strtolower(
                (string) (
                    $analytics['status'] ?? ''
                )
            );

        return ! in_array(
            $status,
            [
                'unavailable',
                'failed',
                'error',
                'insufficient_data',
            ],
            true
        );
    }

    /**
     * @param array<string, bool> $dataAvailability
     * @return array<int, string>
     */
    private function limitations(
        ?PortfolioStateSnapshot $snapshot,
        ?AuditRun $audit,
        $connections,
        array $dataAvailability,
    ): array {
        $limitations = collect();

        if ($snapshot === null) {
            $limitations->push(
                'No saved portfolio snapshot is available.'
            );
        }

        if ($audit === null) {
            $limitations->push(
                'No recorded Advisor Audit is available.'
            );
        }

        if ($connections->isEmpty()) {
            $limitations->push(
                'Portfolio data may rely on manual entry.'
            );
        }

        if (
            $connections->contains(
                fn (
                    BrokerageConnection $connection
                ): bool =>
                    $connection->isStale()
            )
        ) {
            $limitations->push(
                'One or more brokerage connections have stale data.'
            );
        }

        $missingCategories =
            collect($dataAvailability)
                ->only([
                    'cost',
                    'diversification',
                    'performance',
                    'risk',
                    'trading',
                    'cash',
                    'tax',
                    'suitability',
                ])
                ->filter(
                    fn (bool $available): bool =>
                        ! $available
                )
                ->keys()
                ->values();

        if ($missingCategories->isNotEmpty()) {
            $limitations->push(
                'Some analytics categories are unavailable: '
                . $missingCategories->implode(', ')
                . '.'
            );
        }

        return $limitations
            ->values()
            ->all();
    }
}