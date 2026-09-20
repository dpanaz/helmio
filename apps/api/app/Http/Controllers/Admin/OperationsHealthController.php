<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiInsightRun;
use App\Models\AskHelmioMessage;
use App\Models\BrokerageSyncRun;
use App\Models\MarketingConversion;
use App\Models\StaffAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OperationsHealthController extends Controller
{
    public function index(): View
    {
        $failedJobs = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->latest('failed_at')->limit(25)->get()
            : collect();

        return view('admin.operations.index', [
            'failedJobs' => $failedJobs,
            'queuedJobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
            'syncFailures' => BrokerageSyncRun::query()
                ->where('status', BrokerageSyncRun::STATUS_FAILED)
                ->with(['user', 'brokerageConnection'])
                ->latest('started_at')->limit(25)->get(),
            'askFailures' => AskHelmioMessage::query()
                ->where('status', AskHelmioMessage::STATUS_FAILED)
                ->with('user')->latest()->limit(25)->get(),
            'insightFailures' => AiInsightRun::query()
                ->where('status', AiInsightRun::STATUS_FAILED)
                ->with('user')->latest()->limit(25)->get(),
            'redditFailures' => MarketingConversion::query()
                ->where('reddit_status', 'failed')
                ->with('user')->latest('converted_at')->limit(25)->get(),
        ]);
    }

    public function retryJob(Request $request, string $uuid): RedirectResponse
    {
        abort_unless(Schema::hasTable('failed_jobs'), 404);
        abort_unless(DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        StaffAuditLog::query()->create([
            'actor_user_id' => $request->user()->id,
            'event' => 'operations.failed_job.retried',
            'route_name' => $request->route()?->getName(),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['failed_job_uuid' => $uuid],
        ]);

        return back()->with('success', 'The failed job was returned to its queue.');
    }
}
