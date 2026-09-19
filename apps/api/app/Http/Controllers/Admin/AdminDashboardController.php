<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentAccount;
use App\Models\MarketingConversion;
use App\Models\MarketingVisit;
use App\Models\StaffAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $since = now()->subDays(30);

        return view('admin.dashboard', [
            'metrics' => [
                'customers' => User::query()
                    ->whereDoesntHave('staffRoles')
                    ->count(),
                'new_customers' => User::query()
                    ->whereDoesntHave('staffRoles')
                    ->where('created_at', '>=', $since)
                    ->count(),
                'staff' => User::query()
                    ->whereHas('staffRoles')
                    ->count(),
                'accounts' => InvestmentAccount::query()->count(),
                'marketing_visitors' => MarketingVisit::query()
                    ->where('first_seen_at', '>=', $since)
                    ->distinct()
                    ->count('visitor_uuid'),
                'marketing_conversions' => MarketingConversion::query()
                    ->where('converted_at', '>=', $since)
                    ->count(),
                'active_subscriptions' => DB::table('subscriptions')
                    ->whereIn(
                        'stripe_status',
                        ['active', 'trialing'],
                    )
                    ->where(function ($query): void {
                        $query
                            ->whereNull('ends_at')
                            ->orWhere('ends_at', '>', now());
                    })
                    ->count(),
                'staff_activity' => StaffAuditLog::query()
                    ->where('created_at', '>=', $since)
                    ->count(),
            ],
        ]);
    }
}
