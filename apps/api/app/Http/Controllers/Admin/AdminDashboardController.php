<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiInsightRun;
use App\Models\AskHelmioMessage;
use App\Models\BrokerageConnection;
use App\Models\BrokerageSyncRun;
use App\Models\InvestmentAccount;
use App\Models\MarketingConversion;
use App\Models\MarketingVisit;
use App\Models\StaffAuditLog;
use App\Models\SupportConversation;
use App\Models\User;
use App\Services\Billing\StripeBusinessMetricsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(StripeBusinessMetricsService $stripeMetrics): View
    {
        $since = now()->subDays(30);
        $billing = $stripeMetrics->metrics();
        $activeSubscription = fn (Builder $query) => $query
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where(fn (Builder $query) => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>', now()));

        $activeSubscriptions = DB::table('subscriptions')
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where(fn ($query) => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>', now()));

        $customerQuery = fn () => User::query()->whereDoesntHave('staffRoles');
        $customers = $customerQuery()->count();
        $connectedCustomers = $customerQuery()->whereHas('investmentAccounts')->count();
        $profileCustomers = $customerQuery()->whereHas('investorProfile')->count();
        $onboardedCustomers = $customerQuery()
            ->whereHas('subscriptions', $activeSubscription)
            ->whereHas('investorProfile')
            ->whereHas('investmentAccounts')
            ->count();

        $staleCutoff = now()->subHours((int) config('brokerage.stale_after_hours', 24));
        $openStatuses = [
            SupportConversation::STATUS_OPEN,
            SupportConversation::STATUS_PENDING,
            SupportConversation::STATUS_WAITING_CUSTOMER,
        ];

        $system = [
            'failed_jobs' => Schema::hasTable('failed_jobs')
                ? DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count()
                : 0,
            'queued_jobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
            'connection_errors' => BrokerageConnection::query()
                ->where('status', BrokerageConnection::STATUS_ERROR)->count(),
            'stale_connections' => BrokerageConnection::query()
                ->where(fn (Builder $query) => $query
                    ->whereNull('last_successful_sync_at')
                    ->orWhere('last_successful_sync_at', '<', $staleCutoff))
                ->where('status', BrokerageConnection::STATUS_ACTIVE)
                ->count(),
            'failed_syncs' => BrokerageSyncRun::query()
                ->where('status', BrokerageSyncRun::STATUS_FAILED)
                ->where('started_at', '>=', now()->subDay())->count(),
            'ai_failures' => AskHelmioMessage::query()
                ->where('status', AskHelmioMessage::STATUS_FAILED)
                ->where('created_at', '>=', now()->subDay())->count()
                + AiInsightRun::query()
                    ->where('status', AiInsightRun::STATUS_FAILED)
                    ->where('created_at', '>=', now()->subDay())->count(),
            'reddit_failures' => MarketingConversion::query()
                ->where('reddit_status', 'failed')
                ->where('converted_at', '>=', now()->subDay())->count(),
        ];

        $support = [
            'open' => SupportConversation::query()->whereIn('status', $openStatuses)->count(),
            'unassigned' => SupportConversation::query()
                ->whereIn('status', $openStatuses)->whereNull('assigned_to_user_id')->count(),
            'urgent' => SupportConversation::query()
                ->whereIn('status', $openStatuses)
                ->whereIn('priority', ['urgent', 'high'])->count(),
            'oldest' => SupportConversation::query()
                ->whereIn('status', $openStatuses)->oldest('created_at')->first(),
        ];

        $attention = collect([
            ['label' => 'Brokerage connections need attention', 'count' => $system['connection_errors'], 'route' => 'admin.customers.index', 'severity' => 'critical'],
            ['label' => 'Connections have stale portfolio data', 'count' => $system['stale_connections'], 'route' => 'admin.customers.index', 'severity' => 'warning'],
            ['label' => 'Unassigned support conversations', 'count' => $support['unassigned'], 'route' => 'admin.support.index', 'severity' => 'warning'],
            ['label' => 'Stripe subscriptions are past due', 'count' => $billing['past_due'], 'route' => 'admin.operations.index', 'severity' => 'critical'],
            ['label' => 'Failed background jobs in 24 hours', 'count' => $system['failed_jobs'], 'route' => 'admin.operations.index', 'severity' => 'critical'],
            ['label' => 'AI requests failed in 24 hours', 'count' => $system['ai_failures'], 'route' => 'admin.operations.index', 'severity' => 'warning'],
            ['label' => 'Reddit conversions failed in 24 hours', 'count' => $system['reddit_failures'], 'route' => 'admin.marketing.reddit', 'severity' => 'warning'],
        ])->filter(fn (array $item): bool => $item['count'] > 0)->values();

        $atRiskCustomers = $customerQuery()
            ->whereHas('subscriptions', $activeSubscription)
            ->withCount('investmentAccounts')
            ->with(['brokerageConnections' => fn ($query) =>
                $query->where('status', BrokerageConnection::STATUS_ACTIVE)])
            ->get()
            ->map(function (User $customer) use ($staleCutoff): ?array {
                $reasons = [];

                if ($customer->investment_accounts_count === 0) {
                    $reasons[] = 'No connected account';
                }
                if ($customer->brokerageConnections->isNotEmpty()
                    && $customer->brokerageConnections->contains(fn (BrokerageConnection $connection) =>
                        $connection->last_successful_sync_at === null
                        || $connection->last_successful_sync_at->lt($staleCutoff))) {
                    $reasons[] = 'Stale data';
                }
                if ($customer->last_login_at && $customer->last_login_at->lt(now()->subDays(30))) {
                    $reasons[] = 'Inactive 30+ days';
                }

                return $reasons === [] ? null : ['user' => $customer, 'reasons' => $reasons];
            })
            ->filter()
            ->take(10);

        return view('admin.dashboard', [
            'metrics' => [
                'customers' => $customers,
                'new_customers' => $customerQuery()->where('created_at', '>=', $since)->count(),
                'active_subscriptions' => $billing['active_subscriptions'],
                'trials' => $billing['trials'],
                'trials_ending' => (clone $activeSubscriptions)
                    ->whereNotNull('trial_ends_at')
                    ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])->count(),
                'mrr' => $billing['mrr'],
                'arr' => $billing['arr'],
                'monthly_plans' => $billing['monthly_plans'],
                'annual_plans' => $billing['annual_plans'],
                'past_due' => $billing['past_due'],
                'new_subscriptions_30d' => $billing['new_subscriptions_30d'],
                'cancellations_30d' => $billing['cancellations_30d'],
                'refunds_30d' => $billing['refunds_30d'],
                'refund_amount_30d' => $billing['refund_amount_30d'],
                'accounts' => InvestmentAccount::query()->count(),
                'marketing_visitors' => MarketingVisit::query()
                    ->where('first_seen_at', '>=', $since)->distinct()->count('visitor_uuid'),
                'marketing_conversions' => MarketingConversion::query()
                    ->where('converted_at', '>=', $since)->count(),
                'staff' => User::query()->whereHas('staffRoles')->count(),
                'staff_activity' => StaffAuditLog::query()->where('created_at', '>=', $since)->count(),
            ],
            'customerHealth' => [
                'connected' => $connectedCustomers,
                'profiles' => $profileCustomers,
                'onboarded' => $onboardedCustomers,
                'without_accounts' => max(0, $customers - $connectedCustomers),
                'active_30_days' => $customerQuery()->where('last_login_at', '>=', $since)->count(),
            ],
            'system' => $system,
            'support' => $support,
            'attention' => $attention,
            'atRiskCustomers' => $atRiskCustomers,
            'billingMetrics' => $billing,
        ]);
    }
}
