<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentAccountProfile extends Model
{
    protected $fillable = [
        'investment_account_id',
        'purpose',
        'target_date',
        'risk_tolerance_override',
        'objective_override',
        'time_horizon_years_override',
        'liquidity_needs_override',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_date' =>
                'date',

            'time_horizon_years_override' =>
                'integer',
        ];
    }

    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(
            InvestmentAccount::class
        );
    }

    public function effectiveRiskTolerance(): ?string
    {
        return $this->risk_tolerance_override
            ?? $this->investmentAccount
                ?->user
                ?->investorProfile
                ?->risk_tolerance;
    }

    public function effectiveObjective(): ?string
    {
        return $this->objective_override
            ?? $this->investmentAccount
                ?->user
                ?->investorProfile
                ?->primary_objective;
    }

    public function effectiveTimeHorizonYears(): ?int
    {
        return $this
            ->time_horizon_years_override
            ?? $this->investmentAccount
                ?->user
                ?->investorProfile
                ?->time_horizon_years;
    }

    public function effectiveLiquidityNeeds(): ?string
    {
        return $this->liquidity_needs_override
            ?? $this->investmentAccount
                ?->user
                ?->investorProfile
                ?->liquidity_needs;
    }
}