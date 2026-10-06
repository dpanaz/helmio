<?php

namespace App\Services\Operations;

use App\Models\BrokerageSyncRun;
use App\Models\PortfolioAnalysisRun;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperationsActivityService
{
    public function snapshot(): array
    {
        $checkedAt = CarbonImmutable::now();
        $since = $checkedAt->subDay();
        $hasJobs = Schema::hasTable('jobs');
        $hasFailures = Schema::hasTable('failed_jobs');

        $successfulSyncs = BrokerageSyncRun::query()
            ->where('status', BrokerageSyncRun::STATUS_SUCCESS)
            ->whereBetween('finished_at', [$since, $checkedAt]);
        $successfulAnalyses = PortfolioAnalysisRun::query()
            ->where('status', PortfolioAnalysisRun::STATUS_READY)
            ->whereBetween('completed_at', [$since, $checkedAt]);

        $customerIds = DB::table('brokerage_connections')->select('user_id')
            ->union(DB::table('portfolio_analysis_runs')->select('user_id'));
        $latestSyncs = DB::table('brokerage_sync_runs')
            ->select('user_id')->selectRaw('MAX(finished_at) as last_sync_at')
            ->where('status', BrokerageSyncRun::STATUS_SUCCESS)->groupBy('user_id');
        $latestAnalyses = DB::table('portfolio_analysis_runs')
            ->select('user_id')->selectRaw('MAX(completed_at) as last_analysis_at')
            ->where('status', PortfolioAnalysisRun::STATUS_READY)->groupBy('user_id');

        return [
            'checkedAt' => $checkedAt,
            'activitySince' => $since,
            'successfulSyncCount' => (clone $successfulSyncs)->count(),
            'successfulAnalysisCount' => (clone $successfulAnalyses)->count(),
            'recentSyncs' => (clone $successfulSyncs)
                ->with(['user:id,name,email', 'brokerageConnection:id,brokerage_name'])
                ->latest('finished_at')->orderByDesc('id')->limit(20)
                ->get(['id', 'user_id', 'brokerage_connection_id', 'provider', 'finished_at', 'accounts_imported', 'positions_imported', 'transactions_imported']),
            'recentAnalyses' => (clone $successfulAnalyses)
                ->with('user:id,name,email')->latest('completed_at')->orderByDesc('id')->limit(20)
                ->get(['id', 'user_id', 'completed_at', 'trigger']),
            'recentFailedJobCount' => $hasFailures
                ? DB::table('failed_jobs')->whereBetween('failed_at', [$since, $checkedAt])->count()
                : 0,
            'historicalFailedJobCount' => $hasFailures
                ? DB::table('failed_jobs')->where('failed_at', '<', $since)->count()
                : 0,
            'customerActivity' => DB::table('users')
                ->whereIn('users.id', $customerIds)
                ->leftJoinSub($latestSyncs, 'sync_activity', 'users.id', '=', 'sync_activity.user_id')
                ->leftJoinSub($latestAnalyses, 'analysis_activity', 'users.id', '=', 'analysis_activity.user_id')
                ->select('users.id', 'users.name', 'users.email', 'sync_activity.last_sync_at', 'analysis_activity.last_analysis_at')
                ->orderBy('users.name')->orderBy('users.id')
                ->paginate(25, ['*'], 'activity_page')->withQueryString(),
            'queueReadyCount' => $hasJobs ? DB::table('jobs')->whereNull('reserved_at')->where('available_at', '<=', $checkedAt->timestamp)->count() : 0,
            'queueDelayedCount' => $hasJobs ? DB::table('jobs')->whereNull('reserved_at')->where('available_at', '>', $checkedAt->timestamp)->count() : 0,
            'queueReservedCount' => $hasJobs ? DB::table('jobs')->whereNotNull('reserved_at')->count() : 0,
            'pendingJobs' => $hasJobs
                ? DB::table('jobs')->orderBy('created_at')->orderBy('id')->limit(25)
                    ->get(['id', 'queue', 'attempts', 'created_at', 'available_at', 'reserved_at'])
                : collect(),
        ];
    }
}
