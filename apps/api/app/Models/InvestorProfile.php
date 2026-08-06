<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorProfile extends Model
{
    public const RISK_CONSERVATIVE =
        'conservative';

    public const RISK_MODERATELY_CONSERVATIVE =
        'moderately_conservative';

    public const RISK_MODERATE =
        'moderate';

    public const RISK_MODERATELY_AGGRESSIVE =
        'moderately_aggressive';

    public const RISK_AGGRESSIVE =
        'aggressive';

    public const OBJECTIVE_CAPITAL_PRESERVATION =
        'capital_preservation';

    public const OBJECTIVE_INCOME =
        'income';

    public const OBJECTIVE_GROWTH_AND_INCOME =
        'growth_and_income';

    public const OBJECTIVE_GROWTH =
        'growth';

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'planned_retirement_age',
        'employment_status',
        'annual_income',
        'estimated_net_worth',
        'tax_bracket',
        'investment_experience',
        'primary_objective',
        'time_horizon_years',
        'risk_tolerance',
        'liquidity_needs',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' =>
                'date',

            'planned_retirement_age' =>
                'integer',

            'annual_income' =>
                'decimal:2',

            'estimated_net_worth' =>
                'decimal:2',

            'tax_bracket' =>
                'decimal:4',

            'time_horizon_years' =>
                'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function age(): ?int
    {
        return $this->date_of_birth
            ? $this->date_of_birth->age
            : null;
    }

    public function yearsUntilRetirement(): ?int
    {
        $age = $this->age();

        if (
            $age === null
            || $this->planned_retirement_age === null
        ) {
            return null;
        }

        return max(
            0,
            $this->planned_retirement_age
                - $age
        );
    }

    /**
     * @return array<string, string>
     */
    public static function riskToleranceOptions(): array
    {
        return [
            self::RISK_CONSERVATIVE =>
                'Conservative',

            self::RISK_MODERATELY_CONSERVATIVE =>
                'Moderately Conservative',

            self::RISK_MODERATE =>
                'Moderate',

            self::RISK_MODERATELY_AGGRESSIVE =>
                'Moderately Aggressive',

            self::RISK_AGGRESSIVE =>
                'Aggressive',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function objectiveOptions(): array
    {
        return [
            self::OBJECTIVE_CAPITAL_PRESERVATION =>
                'Capital Preservation',

            self::OBJECTIVE_INCOME =>
                'Income',

            self::OBJECTIVE_GROWTH_AND_INCOME =>
                'Growth and Income',

            self::OBJECTIVE_GROWTH =>
                'Growth',
        ];
    }
}