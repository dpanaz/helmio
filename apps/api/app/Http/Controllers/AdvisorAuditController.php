<?php

namespace App\Http\Controllers;

use App\Models\AuditRun;
use App\Models\Benchmark;
use App\Models\User;
use App\Services\AdvisorAudit\AdvisorAuditPersistenceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvisorAuditController extends Controller
{
    public function index(): View
    {
        $benchmarks = Benchmark::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('advisor-audit.index', [
            'benchmarks' => $benchmarks,
        ]);
    }

    /**
     * Return the latest persisted Advisor Audit.
     *
     * IMPORTANT:
     * This endpoint must not recalculate Advisor Audit.
     * The persisted AuditRun is the application-wide source of truth.
     *
     * A new score is calculated only by run(), which also persists it.
     */
    public function data(
        Request $request,
    ): JsonResponse {
        // Keep validating the request because the existing frontend sends
        // these values and relies on validation feedback.
        $this->validateAuditRequest($request);

        $result = $this->latestPersistedAudit(
            $request->user(),
        );

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Calculate and persist a new authoritative Advisor Audit run.
     */
    public function run(
        Request $request,
        AdvisorAuditPersistenceService $persistenceService
    ): JsonResponse {
        $validated = $this->validateAuditRequest(
            $request
        );

        $benchmark = $this->resolveBenchmark(
            $validated['benchmark_id']
                ?? null
        );

        $result = $persistenceService
            ->runAndPersist(
                user: $request->user(),
                startDate: Carbon::parse(
                    $validated['start_date']
                ),
                endDate: Carbon::parse(
                    $validated['end_date']
                ),
                benchmark: $benchmark,
            );

        /*
         * Re-read the persisted run instead of returning a separate
         * in-memory calculation. This guarantees the response matches
         * Dashboard, history, notifications, and monthly reviews.
         */
        $persisted = $this->latestPersistedAudit(
            $request->user(),
        );

        return response()->json([
            'data' => $persisted ?? $result,
            'message' =>
                'Advisor Audit completed and saved.',
        ]);
    }

    /**
     * Build the Advisor Audit API payload from the latest persisted AuditRun.
     *
     * @return array<string, mixed>
     */
    private function latestPersistedAudit(
        User $user,
    ): array {
        /*
         * Use MAX(id) to stay consistent with DashboardService and avoid
         * forcing an ORDER BY on production MySQL.
         */
        $auditRunId = AuditRun::query()
            ->where('user_id', $user->id)
            ->max('id');

        if ($auditRunId === null) {
            return [
                'status' => 'pending',
                'message' =>
                    'Advisor Audit has not been calculated yet.',
                'overall_score' => null,
                'overall_label' =>
                    'Not yet calculated',
                'advisor_rating' => null,
                'confidence' => null,
                'data_completeness' => 0.0,
                'available_weight' => 0.0,
                'available_category_count' => 0,
                'total_category_count' => 0,
                'categories' => [],
                'findings' => [
                    'critical' => [],
                    'important' => [],
                    'opportunities' => [],
                    'recommendations' => [],
                    'all' => [],
                    'summary' => [
                        'critical_count' => 0,
                        'important_count' => 0,
                        'opportunity_count' => 0,
                        'recommendation_count' => 0,
                        'total_finding_count' => 0,
                    ],
                ],
                'executive_summary' => [],
                'calculated_for_date' => null,
            ];
        }

        $auditRun = AuditRun::query()
            ->with('findings')
            ->findOrFail($auditRunId);

        $details = $auditRun->audit_details;

        if (is_string($details)) {
            $decoded = json_decode(
                $details,
                true,
            );

            $details = is_array($decoded)
                ? $decoded
                : [];
        }

        if (! is_array($details)) {
            $details = [];
        }

        /*
         * AuditRun columns are canonical.
         * audit_details is supporting detail captured at the same run.
         */
        $details['overall_score'] =
            $auditRun->audit_score !== null
                ? (int) $auditRun->audit_score
                : null;

        $details['overall_label'] =
            $auditRun->audit_label
            ?? data_get(
                $details,
                'overall_label',
            );

        $details['calculated_for_date'] =
            $auditRun->calculated_for_date
                ?->toDateString();

        $details['audit_run_id'] =
            $auditRun->id;

        return $details;
    }

    /**
     * @return array{
     *     start_date: string,
     *     end_date: string,
     *     benchmark_id?: int|null
     * }
     */
    private function validateAuditRequest(
        Request $request
    ): array {
        return $request->validate([
            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],

            'benchmark_id' => [
                'nullable',
                'integer',
                'exists:benchmarks,id',
            ],
        ]);
    }

    private function resolveBenchmark(
        ?int $benchmarkId
    ): ?Benchmark {
        if ($benchmarkId !== null) {
            return Benchmark::query()
                ->where('is_active', true)
                ->findOrFail($benchmarkId);
        }

        return Benchmark::query()
            ->where('is_active', true)
            ->where('symbol', 'SPY')
            ->first();
    }
}