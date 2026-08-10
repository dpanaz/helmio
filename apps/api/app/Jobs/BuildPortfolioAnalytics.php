<?php

namespace App\Jobs;

use App\Models\PortfolioAnalysisRun;
use App\Models\User;
use App\Services\Analytics\Pipeline\PortfolioAnalyticsPipelineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class BuildPortfolioAnalytics implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 900;

    public function __construct(
        public readonly int $analysisRunId,
    ) {
        $this->onQueue(
            'analytics',
        );
    }

    public function backoff(): array
    {
        return [
            30,
            60,
            300,
            900,
            3600,
        ];
    }

    public function handle(
        PortfolioAnalyticsPipelineService $pipeline,
    ): void {
        $run =
            PortfolioAnalysisRun::query()
                ->findOrFail(
                    $this->analysisRunId,
                );

        /*
         * If another delivery already completed this run,
         * there is nothing left to do.
         */
        if (
            $run->status
            === PortfolioAnalysisRun::STATUS_READY
        ) {
            return;
        }

        $user =
            User::query()
                ->findOrFail(
                    $run->user_id,
                );

        $run->update([
            'status' =>
                PortfolioAnalysisRun::STATUS_SYNCING,

            'current_step' =>
                'starting',

            'started_at' =>
                $run->started_at
                ?? now(),

            'failed_at' =>
                null,

            'error_message' =>
                null,
        ]);

        $result =
            $pipeline->run(
                user:
                    $user,

                run:
                    $run,
            );

        $run->markReady(
            $result,
        );
    }

    public function failed(
        ?Throwable $exception,
    ): void {
        $run =
            PortfolioAnalysisRun::query()
                ->find(
                    $this->analysisRunId,
                );

        if ($run === null) {
            return;
        }

        $run->markFailed(
            $exception?->getMessage()
            ?? 'Portfolio analysis failed.',
        );
    }
}