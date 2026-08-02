<?php

namespace App\Services\Dashboard;

use App\Models\InvestmentAccount;
use App\Services\Analytics\HelmScoreService;

class DashboardService
{
    public function __construct(
        private readonly HelmScoreService $helmScoreService,
    ) {
    }

    public function build(int $userId): array
    {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $userId)
            ->with([
                'institution',
                'holdings.security',
                'transactions',
                'portfolioSnapshots',
                'benchmark.returns',
            ])
            ->get();

        $helm = $this->helmScoreService->calculate($accounts);

        return [
            'accounts' => $accounts,

            'portfolioValue' =>
                $accounts->sum('current_value'),

            'cashValue' =>
                $accounts->sum('cash_value'),

            'accountCount' =>
                $accounts->count(),

            'helm' => $helm,

            'largestAccount' => $accounts
                ->sortByDesc('current_value')
                ->first(),
        ];
    }
}