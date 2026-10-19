<?php

namespace App\Services\Onboarding;

use App\Models\InvestmentAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AccountProfileSetupService
{
    public const PURPOSES = [
        'retirement' => 'Retirement',
        'taxable_investing' => 'Long-term investing',
        'income' => 'Regular income',
        'education' => 'Education',
        'emergency_reserve' => 'Emergency reserve',
        'short_term_goal' => 'A near-term purchase or goal',
        'estate_or_trust' => 'Estate or trust',
        'other' => 'Another goal',
    ];

    public function pending(User $user): Builder
    {
        return InvestmentAccount::query()->where('user_id', $user->id)
            ->whereDoesntHave('profile', fn (Builder $query) => $query
                ->whereIn('purpose', array_keys(self::PURPOSES))
                ->whereBetween('time_horizon_years_override', [1, 60])
                ->whereIn('liquidity_needs_override', ['low', 'moderate', 'high']));
    }

    public function nextAccount(User $user): ?InvestmentAccount
    {
        return $this->pending($user)->with(['profile', 'institution'])->orderBy('id')->first();
    }
}
