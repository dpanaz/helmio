<?php

namespace App\Http\Controllers;

use App\Models\Benchmark;
use App\Services\AdvisorAudit\AdvisorAuditPersistenceService;
use App\Services\AdvisorAudit\AdvisorAuditService;
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
     * Calculate an audit without writing to the database.
     */
    public function data(
        Request $request,
        AdvisorAuditService $advisorAuditService
    ): JsonResponse {
        $validated = $this->validateAuditRequest(
            $request
        );

        $benchmark = $this->resolveBenchmark(
            $validated['benchmark_id']
                ?? null
        );

        $result = $advisorAuditService->analyze(
            user: $request->user(),
            startDate: Carbon::parse(
                $validated['start_date']
            ),
            endDate: Carbon::parse(
                $validated['end_date']
            ),
            benchmark: $benchmark,
        );

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Calculate and persist a new audit run.
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

        return response()->json([
            'data' => $result,
            'message' =>
                'Advisor Audit completed and saved.',
        ]);
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