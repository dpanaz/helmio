<?php

namespace App\Services\AI;

use App\Models\AiInsightRun;
use App\Models\InvestmentAccount;

class AiInsightStalenessService
{
    public function markIfPortfolioChanged(
        int $userId,
        string $reason =
            'Portfolio data changed.',
    ): int {
        $currentPortfolioValue =
            (float) InvestmentAccount::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->sum('current_value');

        $currentAccountCount =
            InvestmentAccount::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->count();

        return AiInsightRun::query()
            ->where(
                'user_id',
                $userId
            )
            ->where(
                'is_stale',
                false
            )
            ->where(
                function ($query) use (
                    $currentPortfolioValue,
                    $currentAccountCount,
                ): void {
                    $query
                        ->whereNull(
                            'portfolio_value_at_generation'
                        )
                        ->orWhere(
                            'portfolio_value_at_generation',
                            '!=',
                            $currentPortfolioValue
                        )
                        ->orWhereNull(
                            'account_count_at_generation'
                        )
                        ->orWhere(
                            'account_count_at_generation',
                            '!=',
                            $currentAccountCount
                        );
                }
            )
            ->update([
                'is_stale' =>
                    true,

                'stale_at' =>
                    now(),

                'stale_reason' =>
                    $reason,
            ]);
    }
}