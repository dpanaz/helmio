<?php

namespace App\Services\Analytics\Risk;

use App\Models\InvestmentAccount;
use App\Models\InvestorProfile;
use Illuminate\Support\Collection;

class SuitabilityProfileService
{
    /**
     * Build the effective suitability profile for one account.
     *
     * @return array<string, mixed>
     */
    public function forAccount(
        InvestmentAccount $account
    ): array {
        $account->loadMissing([
            'profile',
            'user.investorProfile',
        ]);

        $investorProfile =
            $account->user
                ?->investorProfile;

        $accountProfile =
            $account->profile;

        $riskTolerance =
            $accountProfile
                ?->risk_tolerance_override
            ?? $investorProfile
                ?->risk_tolerance;

        $objective =
            $accountProfile
                ?->objective_override
            ?? $investorProfile
                ?->primary_objective;

        $timeHorizonYears =
            $accountProfile
                ?->time_horizon_years_override
            ?? $investorProfile
                ?->time_horizon_years;

        $liquidityNeeds =
            $accountProfile
                ?->liquidity_needs_override
            ?? $investorProfile
                ?->liquidity_needs;

        return [
            'account_id' =>
                $account->id,

            'purpose' =>
                $accountProfile?->purpose,

            'target_date' =>
                $accountProfile
                    ?->target_date
                    ?->toDateString(),

            'risk_tolerance' =>
                $riskTolerance,

            'risk_tolerance_label' =>
                $this->riskToleranceLabel(
                    $riskTolerance
                ),

            'risk_tolerance_score' =>
                $this->riskToleranceScore(
                    $riskTolerance
                ),

            'objective' =>
                $objective,

            'objective_label' =>
                $this->objectiveLabel(
                    $objective
                ),

            'time_horizon_years' =>
                $timeHorizonYears,

            'liquidity_needs' =>
                $liquidityNeeds,

            'liquidity_needs_label' =>
                $this->liquidityLabel(
                    $liquidityNeeds
                ),

            'age' =>
                $investorProfile?->age(),

            'planned_retirement_age' =>
                $investorProfile
                    ?->planned_retirement_age,

            'years_until_retirement' =>
                $investorProfile
                    ?->yearsUntilRetirement(),

            'investment_experience' =>
                $investorProfile
                    ?->investment_experience,

            'tax_bracket' =>
                $investorProfile
                    ?->tax_bracket !== null
                    ? (float) $investorProfile
                        ->tax_bracket
                    : null,

            'uses_account_override' =>
                $this->usesOverride(
                    $accountProfile
                ),

            'completeness' =>
                $this->completeness([
                    'risk_tolerance' =>
                        $riskTolerance,

                    'objective' =>
                        $objective,

                    'time_horizon_years' =>
                        $timeHorizonYears,

                    'liquidity_needs' =>
                        $liquidityNeeds,
                ]),
        ];
    }

    /**
     * Build suitability profiles for a collection of accounts.
     *
     * @param Collection<int, InvestmentAccount> $accounts
     * @return Collection<int, array<string, mixed>>
     */
    public function forAccounts(
        Collection $accounts
    ): Collection {
        return $accounts
            ->map(
                fn (
                    InvestmentAccount $account
                ): array =>
                    $this->forAccount(
                        $account
                    )
            )
            ->values();
    }

    /**
     * Build a portfolio-level summary while preserving account
     * differences.
     *
     * @param Collection<int, InvestmentAccount> $accounts
     * @return array<string, mixed>
     */
    public function portfolioSummary(
        Collection $accounts
    ): array {
        $profiles =
            $this->forAccounts($accounts);

        $totalValue =
            (float) $accounts->sum(
                fn (
                    InvestmentAccount $account
                ): float =>
                    (float) (
                        $account->current_value
                        ?? 0
                    )
            );

        $weightedRiskScore = null;

        if ($totalValue > 0) {
            $weightedRiskTotal =
                $accounts->sum(
                    function (
                        InvestmentAccount $account
                    ): float {
                        $profile =
                            $this->forAccount(
                                $account
                            );

                        $riskScore =
                            $profile[
                                'risk_tolerance_score'
                            ];

                        if ($riskScore === null) {
                            return 0.0;
                        }

                        return (
                            (float) (
                                $account->current_value
                                ?? 0
                            )
                        ) * $riskScore;
                    }
                );

            $coveredValue =
                $accounts->sum(
                    function (
                        InvestmentAccount $account
                    ): float {
                        $profile =
                            $this->forAccount(
                                $account
                            );

                        return $profile[
                            'risk_tolerance_score'
                        ] !== null
                            ? (float) (
                                $account->current_value
                                ?? 0
                            )
                            : 0.0;
                    }
                );

            if ($coveredValue > 0) {
                $weightedRiskScore =
                    $weightedRiskTotal
                    / $coveredValue;
            }
        }

        return [
            'profiles' =>
                $profiles->all(),

            'account_count' =>
                $accounts->count(),

            'override_count' =>
                $profiles
                    ->where(
                        'uses_account_override',
                        true
                    )
                    ->count(),

            'complete_profile_count' =>
                $profiles
                    ->where(
                        'completeness',
                        1.0
                    )
                    ->count(),

            'weighted_risk_score' =>
                $weightedRiskScore !== null
                    ? round(
                        $weightedRiskScore,
                        2
                    )
                    : null,

            'weighted_risk_tolerance' =>
                $this->riskToleranceFromScore(
                    $weightedRiskScore
                ),

            'weighted_risk_tolerance_label' =>
                $this->riskToleranceLabel(
                    $this->riskToleranceFromScore(
                        $weightedRiskScore
                    )
                ),

            'data_completeness' =>
                $profiles->isEmpty()
                    ? 0.0
                    : round(
                        (float) $profiles
                            ->avg(
                                'completeness'
                            ),
                        4
                    ),
        ];
    }

    private function usesOverride(
        mixed $accountProfile
    ): bool {
        if ($accountProfile === null) {
            return false;
        }

        return $accountProfile
                ->risk_tolerance_override
                !== null
            || $accountProfile
                ->objective_override
                !== null
            || $accountProfile
                ->time_horizon_years_override
                !== null
            || $accountProfile
                ->liquidity_needs_override
                !== null;
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function completeness(
        array $profile
    ): float {
        $fields = [
            'risk_tolerance',
            'objective',
            'time_horizon_years',
            'liquidity_needs',
        ];

        $complete = collect($fields)
            ->filter(
                fn (string $field): bool =>
                    $profile[$field] !== null
                    && $profile[$field] !== ''
            )
            ->count();

        return round(
            $complete / count($fields),
            4
        );
    }

    private function riskToleranceScore(
        ?string $riskTolerance
    ): ?int {
        return match ($riskTolerance) {
            InvestorProfile::RISK_CONSERVATIVE =>
                1,

            InvestorProfile::RISK_MODERATELY_CONSERVATIVE =>
                2,

            InvestorProfile::RISK_MODERATE =>
                3,

            InvestorProfile::RISK_MODERATELY_AGGRESSIVE =>
                4,

            InvestorProfile::RISK_AGGRESSIVE =>
                5,

            default =>
                null,
        };
    }

    private function riskToleranceFromScore(
        ?float $score
    ): ?string {
        if ($score === null) {
            return null;
        }

        return match (true) {
            $score < 1.5 =>
                InvestorProfile::RISK_CONSERVATIVE,

            $score < 2.5 =>
                InvestorProfile::RISK_MODERATELY_CONSERVATIVE,

            $score < 3.5 =>
                InvestorProfile::RISK_MODERATE,

            $score < 4.5 =>
                InvestorProfile::RISK_MODERATELY_AGGRESSIVE,

            default =>
                InvestorProfile::RISK_AGGRESSIVE,
        };
    }

    private function riskToleranceLabel(
        ?string $riskTolerance
    ): string {
        return InvestorProfile
            ::riskToleranceOptions()[
                $riskTolerance
            ] ?? 'Not set';
    }

    private function objectiveLabel(
        ?string $objective
    ): string {
        return InvestorProfile
            ::objectiveOptions()[
                $objective
            ] ?? 'Not set';
    }

    private function liquidityLabel(
        ?string $liquidityNeeds
    ): string {
        return match ($liquidityNeeds) {
            'low' =>
                'Low',

            'moderate' =>
                'Moderate',

            'high' =>
                'High',

            default =>
                'Not set',
        };
    }
}