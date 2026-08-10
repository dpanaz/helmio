<?php

namespace App\Services\Analytics\Pipeline;

use App\Jobs\BuildPortfolioAnalytics;
use App\Models\BrokerageConnection;
use App\Models\PortfolioAnalysisRun;
use App\Models\User;

class PortfolioAnalyticsDispatcher
{
    public function dispatch(
        User $user,
        string $trigger,
        ?BrokerageConnection $connection = null,
    ): PortfolioAnalysisRun {
        /*
         * Avoid creating multiple simultaneous portfolio analysis
         * runs for the same user.
         */
        $existing =
            PortfolioAnalysisRun::query()
                ->where(
                    'user_id',
                    $user->id,
                )
                ->whereIn(
                    'status',
                    [
                        PortfolioAnalysisRun::STATUS_PENDING,
                        PortfolioAnalysisRun::STATUS_SYNCING,
                        PortfolioAnalysisRun::STATUS_BUILDING_HISTORY,
                        PortfolioAnalysisRun::STATUS_ANALYZING,
                    ],
                )
                ->latest()
                ->first();

        if ($existing !== null) {
            return $existing;
        }

        $run =
            PortfolioAnalysisRun::query()
                ->create([
                    'user_id' =>
                        $user->id,

                    'brokerage_connection_id' =>
                        $connection?->id,

                    'trigger' =>
                        $trigger,

                    'status' =>
                        PortfolioAnalysisRun::STATUS_PENDING,

                    'current_step' =>
                        'queued',

                    'metadata' => [],
                ]);

        BuildPortfolioAnalytics::dispatch(
            $run->id,
        );

        return $run;
    }
}