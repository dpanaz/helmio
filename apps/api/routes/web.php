<?php

use App\Http\Controllers\AdvisorActionCenterController;
use App\Http\Controllers\AdvisorAuditController;
use App\Http\Controllers\AdvisorAuditHistoryController;
use App\Http\Controllers\AdvisorAuditReportController;
use App\Http\Controllers\AiPortfolioInsightController;
use App\Http\Controllers\AskHelmioController;
use App\Http\Controllers\AuditFindingController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BrokerageConnectionController;
use App\Http\Controllers\CashDragAnalyticsController;
use App\Http\Controllers\CostAnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiversificationAnalyticsController;
use App\Http\Controllers\FundExpenseAnalyticsController;
use App\Http\Controllers\HelmScoreController;
use App\Http\Controllers\HoldingController;
use App\Http\Controllers\InvestmentAccountController;
use App\Http\Controllers\InvestmentAccountProfileController;
use App\Http\Controllers\InvestmentTransactionController;
use App\Http\Controllers\InvestorProfileController;
use App\Http\Controllers\MonthlyAuditSettingsController;
use App\Http\Controllers\MonthlyPortfolioReviewController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\PerformanceAnalyticsController;
use App\Http\Controllers\PerformanceDataController;
use App\Http\Controllers\PortfolioTimelineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\RiskAnalyticsController;
use App\Http\Controllers\TaxEfficiencyAnalyticsController;
use App\Http\Controllers\TradingDisciplineAnalyticsController;
use App\Http\Controllers\Webhooks\SnapTradeWebhookController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Onboarding\HelmScoreRevealController;
use App\Http\Controllers\Onboarding\PortfolioRevealController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Onboarding\TopFindingsRevealController;
use App\Http\Controllers\Onboarding\ExecutiveSummaryRevealController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::view(
    '/contact',
    'legal.contact',
)->name('contact');

Route::view(
    '/terms',
    'legal.terms',
)->name('terms');

Route::view(
    '/privacy',
    'legal.privacy',
)->name('privacy');

Route::post(
    '/webhooks/snaptrade',
    SnapTradeWebhookController::class,
)->name('webhooks.snaptrade');

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authenticated onboarding and billing
|--------------------------------------------------------------------------
|
| These routes must remain available before onboarding is complete. A customer
| needs access to pricing, billing, the investor profile, and every onboarding
| step before Helmio can allow them into the main dashboard.
|
*/

Route::middleware([
    'auth',
    'verified',
])->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Billing
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/pricing',
        [
            BillingController::class,
            'pricing',
        ],
    )->name('billing.pricing');

    Route::post(
        '/billing/checkout',
        [
            BillingController::class,
            'checkout',
        ],
    )->name('billing.checkout');

    Route::get(
        '/billing/success',
        [
            BillingController::class,
            'success',
        ],
    )->name('billing.success');

    Route::get(
        '/billing',
        [
            BillingController::class,
            'index',
        ],
    )->name('billing.index');

    Route::post(
        '/billing/portal',
        [
            BillingController::class,
            'portal',
        ],
    )->name('billing.portal');

    Route::get(
        '/billing/status',
        [
            BillingController::class,
            'status',
        ],
    )->name('billing.status');

    Route::get(
        '/billing/invoices/{invoice}',
        [
            BillingController::class,
            'downloadInvoice',
        ],
    )->name('billing.invoices.download');

    /*
    |--------------------------------------------------------------------------
    | Onboarding
    |--------------------------------------------------------------------------
    */

    Route::prefix('onboarding')
        ->name('onboarding.')
        ->group(function (): void {
            Route::get(
                '/',
                [
                    OnboardingController::class,
                    'index',
                ],
            )->name('index');

            Route::get(
                '/welcome',
                [
                    OnboardingController::class,
                    'welcome',
                ],
            )->name('welcome');

            Route::get(
                '/profile',
                [
                    OnboardingController::class,
                    'profile',
                ],
            )->name('profile');

            Route::get(
                '/connect',
                [
                    OnboardingController::class,
                    'connect',
                ],
            )->name('connect');

            Route::get(
                '/syncing',
                [
                    OnboardingController::class,
                    'syncing',
                ],
            )->name('syncing');

            Route::get(
                '/reveal',
                [
                    PortfolioRevealController::class,
                    'index',
                ],
            )->name('reveal');

            Route::get(
                '/score',
                [
                    HelmScoreRevealController::class,
                    'index',
                ],
            )->name('score');

            Route::get(
                '/findings',
                [
                    TopFindingsRevealController::class,
                    'index',
                ],
            )->name('findings');
            
            Route::get(
                '/executive-summary',
                [
                    ExecutiveSummaryRevealController::class,
                    'index',
                ],
            )->name('executive-summary');

            Route::get(
                '/complete',
                [
                    OnboardingController::class,
                    'complete',
                ],
            )->name('complete');

            Route::post(
                '/finish',
                [
                    OnboardingController::class,
                    'finish',
                ],
            )->name('finish');
        });

    /*
    |--------------------------------------------------------------------------
    | Investor profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/investor-profile',
        [
            InvestorProfileController::class,
            'edit',
        ],
    )->name('investor-profile.edit');

    Route::put(
        '/investor-profile',
        [
            InvestorProfileController::class,
            'update',
        ],
    )->name('investor-profile.update');

    /*
    |--------------------------------------------------------------------------
    | User profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [
            ProfileController::class,
            'edit',
        ],
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [
            ProfileController::class,
            'update',
        ],
    )->name('profile.update');

    Route::delete(
        '/profile',
        [
            ProfileController::class,
            'destroy',
        ],
    )->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Push notifications
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/push-subscriptions/vapid-public-key',
        [
            PushSubscriptionController::class,
            'publicKey',
        ],
    )->name(
        'push-subscriptions.vapid-public-key',
    );

    Route::post(
        '/push-subscriptions',
        [
            PushSubscriptionController::class,
            'store',
        ],
    )->name('push-subscriptions.store');

    Route::delete(
        '/push-subscriptions',
        [
            PushSubscriptionController::class,
            'destroy',
        ],
    )->name('push-subscriptions.destroy');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notifications/state',
        [
            NotificationCenterController::class,
            'state',
        ],
    )->name('notifications.state');

    Route::get(
        '/notifications/unread-count',
        [
            NotificationCenterController::class,
            'unreadCount',
        ],
    )->name('notifications.unread-count');

    Route::get(
        '/notifications',
        [
            NotificationCenterController::class,
            'index',
        ],
    )->name('notifications.index');

    Route::patch(
        '/notifications/read-all',
        [
            NotificationCenterController::class,
            'markAllRead',
        ],
    )->name('notifications.read-all');

    Route::patch(
        '/notifications/{notification}/read',
        [
            NotificationCenterController::class,
            'read',
        ],
    )->name('notifications.read');

    Route::delete(
        '/notifications/{notification}',
        [
            NotificationCenterController::class,
            'destroy',
        ],
    )->name('notifications.destroy');
});

/*
|--------------------------------------------------------------------------
| Completed-onboarding dashboard
|--------------------------------------------------------------------------
|
| Only customers who have subscribed, completed their investor profile, and
| connected an account may enter the main dashboard.
|
*/

Route::get(
    '/dashboard',
    [
        DashboardController::class,
        'index',
    ],
)
    ->middleware([
        'auth',
        'verified',
        'onboarding.complete',
    ])
    ->name('dashboard');

Route::get(
    '/dashboard/analysis-status',
    [
        DashboardController::class,
        'analysisStatus',
    ],
)
    ->middleware([
        'auth',
        'verified',
        'onboarding.complete',
    ])
    ->name('dashboard.analysis-status');

/*
|--------------------------------------------------------------------------
| Premium-only application routes
|--------------------------------------------------------------------------
|
| Customers must have an active Stripe subscription, valid trial, or grace
| period before they can connect accounts or use Helmio's premium features.
|
*/

Route::middleware([
    'auth',
    'verified',
    'subscribed',
])->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Investment accounts
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/accounts',
        [
            InvestmentAccountController::class,
            'index',
        ],
    )->name('accounts.index');

    Route::get(
        '/accounts/connect',
        [
            InvestmentAccountController::class,
            'create',
        ],
    )->name('accounts.create');

    Route::post(
        '/accounts',
        [
            InvestmentAccountController::class,
            'store',
        ],
    )->name('accounts.store');

    Route::get(
        '/accounts/{investmentAccount}/profile',
        [
            InvestmentAccountProfileController::class,
            'edit',
        ],
    )->name('accounts.profile.edit');

    Route::put(
        '/accounts/{investmentAccount}/profile',
        [
            InvestmentAccountProfileController::class,
            'update',
        ],
    )->name('accounts.profile.update');

    /*
    |--------------------------------------------------------------------------
    | Holdings
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/accounts/{investmentAccount}/holdings',
        [
            HoldingController::class,
            'index',
        ],
    )->name('accounts.holdings.index');

    Route::get(
        '/accounts/{investmentAccount}/holdings/create',
        [
            HoldingController::class,
            'create',
        ],
    )->name('accounts.holdings.create');

    Route::post(
        '/accounts/{investmentAccount}/holdings',
        [
            HoldingController::class,
            'store',
        ],
    )->name('accounts.holdings.store');

    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/accounts/{investmentAccount}/transactions',
        [
            InvestmentTransactionController::class,
            'index',
        ],
    )->name('accounts.transactions.index');

    Route::get(
        '/accounts/{investmentAccount}/transactions/create',
        [
            InvestmentTransactionController::class,
            'create',
        ],
    )->name('accounts.transactions.create');

    Route::post(
        '/accounts/{investmentAccount}/transactions',
        [
            InvestmentTransactionController::class,
            'store',
        ],
    )->name('accounts.transactions.store');

    /*
    |--------------------------------------------------------------------------
    | Performance data management
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/accounts/{investmentAccount}/performance-data',
        [
            PerformanceDataController::class,
            'index',
        ],
    )->name('accounts.performance-data.index');

    Route::post(
        '/accounts/{investmentAccount}/portfolio-snapshots',
        [
            PerformanceDataController::class,
            'storeSnapshot',
        ],
    )->name('accounts.portfolio-snapshots.store');

    Route::put(
        '/accounts/{investmentAccount}/benchmark',
        [
            PerformanceDataController::class,
            'assignBenchmark',
        ],
    )->name('accounts.benchmark.update');

    Route::post(
        '/benchmarks',
        [
            PerformanceDataController::class,
            'storeBenchmark',
        ],
    )->name('benchmarks.store');

    Route::post(
        '/benchmarks/{benchmark}/returns',
        [
            PerformanceDataController::class,
            'storeBenchmarkReturn',
        ],
    )->name('benchmarks.returns.store');

    /*
    |--------------------------------------------------------------------------
    | Brokerage connections
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/brokerage-connections',
        [
            BrokerageConnectionController::class,
            'index',
        ],
    )->name('brokerage-connections.index');

    Route::get(
        '/brokerage-connections/create',
        [
            BrokerageConnectionController::class,
            'create',
        ],
    )->name('brokerage-connections.create');

    Route::post(
        '/brokerage-connections',
        [
            BrokerageConnectionController::class,
            'connect',
        ],
    )->name('brokerage-connections.connect');

    Route::get(
        '/brokerage-connections/{brokerageConnection}/callback',
        [
            BrokerageConnectionController::class,
            'callback',
        ],
    )->name('brokerage-connections.callback');

    Route::get(
        '/brokerage-connections/{brokerageConnection}/fake-complete',
        [
            BrokerageConnectionController::class,
            'fakeComplete',
        ],
    )->name('brokerage-connections.fake-complete');

    Route::post(
        '/brokerage-connections/{brokerageConnection}/sync',
        [
            BrokerageConnectionController::class,
            'sync',
        ],
    )->name('brokerage-connections.sync');

    Route::post(
        '/brokerage-connections/{brokerageConnection}/refresh',
        [
            BrokerageConnectionController::class,
            'refresh',
        ],
    )->name('brokerage-connections.refresh');

    Route::delete(
        '/brokerage-connections/{brokerageConnection}',
        [
            BrokerageConnectionController::class,
            'disconnect',
        ],
    )->name('brokerage-connections.disconnect');

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/analytics/costs',
        [
            CostAnalyticsController::class,
            'index',
        ],
    )->name('analytics.costs');

    Route::get(
        '/analytics/fund-expenses',
        [
            FundExpenseAnalyticsController::class,
            'index',
        ],
    )->name('analytics.fund-expenses');

    Route::get(
        '/analytics/helm-score',
        [
            HelmScoreController::class,
            'index',
        ],
    )->name('analytics.helm-score');

    Route::get(
        '/analytics/diversification',
        [
            DiversificationAnalyticsController::class,
            'index',
        ],
    )->name('analytics.diversification');

    Route::get(
        '/analytics/performance',
        [
            PerformanceAnalyticsController::class,
            'index',
        ],
    )->name('analytics.performance');

    Route::get(
        '/analytics/performance/data',
        [
            PerformanceAnalyticsController::class,
            'data',
        ],
    )->name('analytics.performance.data');

    Route::get(
        '/analytics/risk',
        [
            RiskAnalyticsController::class,
            'index',
        ],
    )->name('analytics.risk');

    Route::get(
        '/analytics/risk/data',
        [
            RiskAnalyticsController::class,
            'data',
        ],
    )->name('analytics.risk.data');

    Route::get(
        '/analytics/trading-discipline',
        [
            TradingDisciplineAnalyticsController::class,
            'index',
        ],
    )->name('analytics.trading-discipline');

    Route::get(
        '/analytics/trading-discipline/data',
        [
            TradingDisciplineAnalyticsController::class,
            'data',
        ],
    )->name('analytics.trading-discipline.data');

    Route::get(
        '/analytics/cash-drag',
        [
            CashDragAnalyticsController::class,
            'index',
        ],
    )->name('analytics.cash-drag');

    Route::get(
        '/analytics/cash-drag/data',
        [
            CashDragAnalyticsController::class,
            'data',
        ],
    )->name('analytics.cash-drag.data');

    Route::get(
        '/analytics/tax-efficiency',
        [
            TaxEfficiencyAnalyticsController::class,
            'index',
        ],
    )->name('analytics.tax-efficiency');

    Route::get(
        '/analytics/tax-efficiency/data',
        [
            TaxEfficiencyAnalyticsController::class,
            'data',
        ],
    )->name('analytics.tax-efficiency.data');

    /*
    |--------------------------------------------------------------------------
    | Advisor audit
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/advisor-audit',
        [
            AdvisorAuditController::class,
            'index',
        ],
    )->name('advisor-audit.index');

    Route::get(
        '/advisor-audit/data',
        [
            AdvisorAuditController::class,
            'data',
        ],
    )->name('advisor-audit.data');

    Route::post(
        '/advisor-audit/run',
        [
            AdvisorAuditController::class,
            'run',
        ],
    )->name('advisor-audit.run');

    Route::get(
        '/advisor-action-center',
        [
            AdvisorActionCenterController::class,
            'index',
        ],
    )->name('advisor-action-center.index');

    Route::patch(
        '/audit-findings/{auditFinding}',
        [
            AuditFindingController::class,
            'update',
        ],
    )->name('audit-findings.update');

    Route::get(
        '/advisor-audit/report',
        [
            AdvisorAuditReportController::class,
            'show',
        ],
    )->name('advisor-audit.report');

    Route::get(
        '/advisor-audit/report/pdf',
        [
            AdvisorAuditReportController::class,
            'download',
        ],
    )->name('advisor-audit.report.pdf');

    Route::get(
        '/advisor-audit/history',
        [
            AdvisorAuditHistoryController::class,
            'index',
        ],
    )->name('advisor-audit.history');

    Route::get(
        '/advisor-audit/history/{auditRun}',
        [
            AdvisorAuditHistoryController::class,
            'show',
        ],
    )->name('advisor-audit.history.show');

    Route::get(
        '/advisor-audit/monthly-report',
        [
            MonthlyAuditSettingsController::class,
            'edit',
        ],
    )->name('advisor-audit.monthly-settings');

    Route::put(
        '/advisor-audit/monthly-report',
        [
            MonthlyAuditSettingsController::class,
            'update',
        ],
    )->name('advisor-audit.monthly-settings.update');

    /*
    |--------------------------------------------------------------------------
    | AI insights
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/ai-insights',
        [
            AiPortfolioInsightController::class,
            'index',
        ],
    )->name('ai-insights.index');

    Route::post(
        '/ai-insights',
        [
            AiPortfolioInsightController::class,
            'generate',
        ],
    )->name('ai-insights.generate');

    Route::get(
        '/ai-insights/{aiInsightRun}',
        [
            AiPortfolioInsightController::class,
            'show',
        ],
    )->name('ai-insights.show');

    Route::post(
        '/ai-insights/{aiInsightRun}/regenerate',
        [
            AiPortfolioInsightController::class,
            'regenerate',
        ],
    )->name('ai-insights.regenerate');

    /*
    |--------------------------------------------------------------------------
    | Portfolio timeline
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/portfolio-timeline',
        [
            PortfolioTimelineController::class,
            'index',
        ],
    )->name('portfolio-timeline.index');

    /*
    |--------------------------------------------------------------------------
    | Monthly portfolio reviews
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/monthly-reviews',
        [
            MonthlyPortfolioReviewController::class,
            'index',
        ],
    )->name('monthly-reviews.index');

    Route::post(
        '/monthly-reviews',
        [
            MonthlyPortfolioReviewController::class,
            'generate',
        ],
    )->name('monthly-reviews.generate');

    Route::get(
        '/monthly-reviews/{monthlyPortfolioReview}/pdf',
        [
            MonthlyPortfolioReviewController::class,
            'pdf',
        ],
    )->name('monthly-reviews.pdf');

    Route::get(
        '/monthly-reviews/{monthlyPortfolioReview}',
        [
            MonthlyPortfolioReviewController::class,
            'show',
        ],
    )->name('monthly-reviews.show');

    /*
    |--------------------------------------------------------------------------
    | Ask Helmio
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/ask-helmio',
        [
            AskHelmioController::class,
            'index',
        ],
    )->name('ask-helmio.index');

    Route::get(
        '/ask-helmio/new',
        [
            AskHelmioController::class,
            'create',
        ],
    )->name('ask-helmio.create');

    Route::post(
        '/ask-helmio',
        [
            AskHelmioController::class,
            'store',
        ],
    )->name('ask-helmio.store');

    Route::get(
        '/ask-helmio/{askHelmioConversation}',
        [
            AskHelmioController::class,
            'show',
        ],
    )->name('ask-helmio.show');

    Route::patch(
        '/ask-helmio/{askHelmioConversation}/archive',
        [
            AskHelmioController::class,
            'archive',
        ],
    )->name('ask-helmio.archive');
});

require __DIR__.'/auth.php';