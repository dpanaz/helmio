<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiInsightRun;
use App\Models\AskHelmioMessage;
use App\Models\BrokerageSyncRun;
use App\Models\MarketingConversion;
use App\Models\StaffAuditLog;
use App\Services\Operations\OperationsActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OperationsHealthController extends Controller
{
    public function index(OperationsActivityService $activity): View
    {
        $failedJobs = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->latest('failed_at')->limit(25)->get()
            : collect();

        return view('admin.operations.index', [
            ...$activity->snapshot(),
            'failedJobs' => $failedJobs,
            'failedJobCount' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
            'queuedJobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
            'syncFailures' => BrokerageSyncRun::query()
                ->where('status', BrokerageSyncRun::STATUS_FAILED)
                ->whereNotIn('id', $this->acknowledgedIds('sync'))
                ->with(['user', 'brokerageConnection'])
                ->latest('started_at')->limit(25)->get(),
            'askFailures' => AskHelmioMessage::query()
                ->where('status', AskHelmioMessage::STATUS_FAILED)
                ->whereNotIn('id', $this->acknowledgedIds('ask'))
                ->with('user')->latest()->limit(25)->get(),
            'insightFailures' => AiInsightRun::query()
                ->where('status', AiInsightRun::STATUS_FAILED)
                ->whereNotIn('id', $this->acknowledgedIds('insight'))
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

    public function clearJobs(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('failed_jobs'), 404);

        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1', 'max:25'],
            'uuids.*' => ['required', 'uuid', 'distinct'],
        ]);

        $uuids = $validated['uuids'];

        $count = DB::transaction(function () use ($request, $uuids): int {
            $jobs = DB::table('failed_jobs')
                ->whereIn('uuid', $uuids)
                ->lockForUpdate()
                ->get(['uuid', 'queue', 'failed_at']);

            if ($jobs->count() !== count($uuids)) {
                abort(409, 'Some selected failed jobs have already changed. Refresh the page.');
            }

            StaffAuditLog::query()->create([
                'actor_user_id' => $request->user()->id,
                'event' => 'operations.failed_jobs.cleared',
                'route_name' => $request->route()?->getName(),
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'jobs' => $jobs->map(fn ($job) => [
                        'uuid' => $job->uuid,
                        'queue' => $job->queue,
                        'failed_at' => $job->failed_at,
                    ])->all(),
                ],
            ]);

            return DB::table('failed_jobs')->whereIn('uuid', $uuids)->delete();
        });

        return back()->with('success', "Cleared {$count} failed job records. No jobs were retried.");
    }

    public function acknowledgeFailures(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sync_ids' => ['sometimes', 'array', 'max:25'],
            'sync_ids.*' => ['required', 'integer', 'min:1', 'distinct'],
            'ask_ids' => ['sometimes', 'array', 'max:25'],
            'ask_ids.*' => ['required', 'integer', 'min:1', 'distinct'],
            'insight_ids' => ['sometimes', 'array', 'max:25'],
            'insight_ids.*' => ['required', 'integer', 'min:1', 'distinct'],
        ]);

        $selected = [
            'sync' => array_map('intval', $validated['sync_ids'] ?? []),
            'ask' => array_map('intval', $validated['ask_ids'] ?? []),
            'insight' => array_map('intval', $validated['insight_ids'] ?? []),
        ];
        $count = array_sum(array_map('count', $selected));

        if ($count < 1 || $count > 25) {
            return back()->withErrors(['failures' => 'Select between 1 and 25 failures.']);
        }

        DB::transaction(function () use ($request, $selected): void {
            $models = [
                'sync' => BrokerageSyncRun::class,
                'ask' => AskHelmioMessage::class,
                'insight' => AiInsightRun::class,
            ];

            foreach ($selected as $type => $ids) {
                if ($ids === []) {
                    continue;
                }

                $model = $models[$type];
                $records = $model::query()
                    ->whereIn('id', $ids)
                    ->where('status', 'failed')
                    ->lockForUpdate()
                    ->pluck('id');

                if ($records->count() !== count($ids)
                    || DB::table('operations_failure_acknowledgements')
                        ->where('failure_type', $type)
                        ->whereIn('failure_id', $ids)
                        ->exists()) {
                    abort(409, 'Some selected failures have changed. Refresh the page.');
                }
            }

            $now = now();
            foreach ($selected as $type => $ids) {
                foreach ($ids as $id) {
                    DB::table('operations_failure_acknowledgements')->insert([
                        'failure_type' => $type,
                        'failure_id' => $id,
                        'actor_user_id' => $request->user()->id,
                        'acknowledged_at' => $now,
                    ]);
                }
            }

            StaffAuditLog::query()->create([
                'actor_user_id' => $request->user()->id,
                'event' => 'operations.failures.acknowledged',
                'route_name' => $request->route()?->getName(),
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['failures' => $selected],
            ]);
        });

        return back()->with('success', "Acknowledged {$count} historical failure(s). The original records were preserved.");
    }

    private function acknowledgedIds(string $type): \Illuminate\Database\Query\Builder
    {
        return DB::table('operations_failure_acknowledgements')
            ->select('failure_id')
            ->where('failure_type', $type);
    }
}
