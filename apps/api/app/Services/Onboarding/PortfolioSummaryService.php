<?php

namespace App\Services\Onboarding;

use App\Models\User;
use Illuminate\Support\Collection;

class PortfolioSummaryService
{
    public function build(User $user): array
    {
        $accounts = $user->investmentAccounts()
            ->with(['institution', 'holdings', 'transactions'])
            ->get();

        $portfolioValue = (float) $accounts->sum(
            fn ($account): float => (float) (
                $account->current_value
                ?? $account->market_value
                ?? $account->value
                ?? 0
            ),
        );

        $holdingCount = (int) $accounts->sum(
            fn ($account): int => (int) (
                $account->holdings_count
                ?? $account->holdings->count()
            ),
        );

        $transactionCount = (int) $accounts->sum(
            fn ($account): int => (int) (
                $account->transactions_count
                ?? $account->transactions->count()
            ),
        );

        $connectedAccounts = $accounts
            ->map(
                fn ($account): array => [
                    'name' => $account->name
                        ?? 'Investment Account',
                    'institution' => $account->institution?->name
                        ?? $account->institution_name
                        ?? 'Connected Institution',
                    'value' => (float) (
                        $account->current_value
                        ?? $account->market_value
                        ?? $account->value
                        ?? 0
                    ),
                ],
            )
            ->sortByDesc('value')
            ->values();

        return [
            'portfolio_value' => $portfolioValue,
            'account_count' => $accounts->count(),
            'holding_count' => $holdingCount,
            'transaction_count' => $transactionCount,
            'connected_accounts' => $connectedAccounts,
        ];
    }
}