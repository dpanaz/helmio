<?php

namespace App\Services\Onboarding;

use App\Models\AiInsightRun;
use App\Models\HelmScoreSnapshot;
use App\Models\PortfolioAnalysisRun;
use App\Models\User;

class ReviewPreparationService
{
    /** Read persisted job state; never run analytics or AI in a web request. */
    public function status(User $user): array
    {
        $run = PortfolioAnalysisRun::query()->where('user_id', $user->id)->latest('id')->first();
        $snapshot = HelmScoreSnapshot::query()->where('user_id', $user->id)->latest('id')->first();
        $insight = AiInsightRun::query()->where('user_id', $user->id)->latest('id')->first();
        $failed = $run?->status === PortfolioAnalysisRun::STATUS_FAILED;
        $analyzed = ! $failed && $snapshot !== null
            && ($run === null || $run->status === PortfolioAnalysisRun::STATUS_READY);
        $currentInsight = $insight !== null && ! $insight->is_stale
            && ($run?->completed_at === null || $insight->generated_at?->gte($run->completed_at));
        $summaryReady = $currentInsight && $insight->status === AiInsightRun::STATUS_COMPLETED;
        $summaryUnavailable = $analyzed && (
            ($currentInsight && in_array($insight->status, [AiInsightRun::STATUS_FAILED, AiInsightRun::STATUS_BLOCKED], true))
            || ($run?->completed_at?->lte(now()->subMinutes(5)) ?? false)
            || ($run === null && ! $summaryReady)
        );
        $stage = $analyzed ? 2 : (in_array($run?->current_step, ['starting', 'syncing', 'brokerage_sync', 'accounts', 'holdings', 'transactions', null], true) ? 0 : 1);

        return [
            'ready' => $analyzed && ($summaryReady || $summaryUnavailable),
            'failed' => $failed,
            'stage' => $stage,
            'summary_unavailable' => $summaryUnavailable,
            'message' => $failed
                ? 'We could not finish your review. Your connection is unchanged. Try again below.'
                : ($summaryUnavailable
                    ? 'Your calculated review is ready. The plain-English summary is unavailable; you can still review the results.'
                    : ['Connecting your accounts.', 'Reviewing your investments.', 'Preparing your plain-English summary.'][$stage]),
        ];
    }
}
