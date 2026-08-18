<?php

namespace App\Jobs;

use App\Models\AiInsightRun;
use App\Models\User;
use App\Services\AI\AiPortfolioInsightService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class GenerateAiPortfolioInsight implements
    ShouldQueue,
    ShouldBeUnique
{
    use Queueable;

    public int $uniqueFor = 300;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public readonly int $userId,
        public readonly string $trigger =
            'portfolio_changed',
    ) {
        $this->onQueue('ai-insights');
    }

    public function uniqueId(): string
    {
        return 'user-'.$this->userId;
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(
                'ai-insight-user-'.$this->userId,
            ))
                ->releaseAfter(60)
                ->expireAfter(360),
        ];
    }

    public function handle(
        AiPortfolioInsightService $insightService,
    ): void {
        $user = User::query()->find(
            $this->userId,
        );

        if ($user === null) {
            return;
        }

        $latestInsight = AiInsightRun::query()
            ->where(
                'user_id',
                $user->id,
            )
            ->latest('generated_at')
            ->first();

        /*
         * Manual requests should always generate a fresh insight.
         *
         * Automatic jobs can safely exit if another request already
         * generated a current insight while this job was waiting.
         */
        $forceGeneration = in_array(
            $this->trigger,
            [
                'manual',
                'manual_regenerate',
            ],
            true,
        );

        if (
            ! $forceGeneration
            && $latestInsight !== null
            && ! $latestInsight->is_stale
        ) {
            return;
        }

        $insightService->generate(
            $user,
        );
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [
            60,
            180,
            300,
        ];
    }
}