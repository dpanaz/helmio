<?php

namespace App\Jobs;

use App\Models\Benchmark;
use App\Models\User;
use App\Services\AdvisorAudit\AdvisorAuditPersistenceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class RecalculateAdvisorAuditForUser implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public readonly int $userId,
    ) {
    }

    public function handle(
        AdvisorAuditPersistenceService $persistenceService,
    ): void {
        $user = User::query()
            ->find($this->userId);

        if ($user === null) {
            return;
        }

        $benchmark = Benchmark::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if ($benchmark === null) {
            $benchmark = Benchmark::query()
                ->where('is_active', true)
                ->where('symbol', 'SPY')
                ->first();
        }

        $periodEnd =
            now()->endOfDay();

        $periodStart =
            $periodEnd
                ->copy()
                ->subYear()
                ->startOfDay();

        try {
            $persistenceService->runAndPersist(
                user:
                    $user,

                startDate:
                    $periodStart,

                endDate:
                    $periodEnd,

                benchmark:
                    $benchmark,
            );
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }
}