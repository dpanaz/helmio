<?php

namespace App\Services\AdvisorAudit\FindingRules;

class SuitabilityFindingRules
    extends DefaultFindingRules
{
    public function supports(string $category): bool
    {
        return $category === 'suitability';
    }

    public function categoryLabel(
        string $category
    ): string {
        return 'Suitability';
    }

    public function categoryWeight(
        string $category
    ): int {
        return 18;
    }

    public function codeWeight(
        string $code
    ): int {
        return match ($code) {
            'portfolio_materially_too_aggressive' => 28,
            'portfolio_somewhat_too_aggressive' => 20,
            'portfolio_materially_too_conservative' => 18,
            'portfolio_somewhat_too_conservative' => 10,
            'portfolio_risk_aligned' => 5,
            'risk_tolerance_not_set' => 8,
            'suitability_profile_incomplete' => 6,
            'actual_risk_unavailable' => 5,
            default => 0,
        };
    }

    public function normalizeSeverity(
        string $code,
        ?string $severity
    ): string {
        return match ($code) {
            'portfolio_materially_too_aggressive' =>
                'critical',

            'portfolio_somewhat_too_aggressive' =>
                'high',

            'portfolio_materially_too_conservative' =>
                'high',

            'portfolio_somewhat_too_conservative' =>
                'moderate',

            'portfolio_risk_aligned' =>
                'informational',

            default =>
                parent::normalizeSeverity(
                    $code,
                    $severity
                ),
        };
    }

    public function typeForCode(
        string $code,
        string $severity
    ): string {
        return $code === 'portfolio_risk_aligned'
            ? 'opportunity'
            : parent::typeForCode(
                $code,
                $severity
            );
    }

    public function title(
        string $code,
        string $category,
        ?string $providedTitle = null
    ): string {
        return match ($code) {
            'portfolio_materially_too_aggressive' =>
                'Portfolio risk substantially exceeds your stated tolerance',

            'portfolio_somewhat_too_aggressive' =>
                'Portfolio risk is above your stated tolerance',

            'portfolio_materially_too_conservative' =>
                'Portfolio may be too conservative for your goals',

            'portfolio_somewhat_too_conservative' =>
                'Portfolio risk is below your stated tolerance',

            'portfolio_risk_aligned' =>
                'Portfolio risk aligns with your investor profile',

            'risk_tolerance_not_set' =>
                'Investor risk tolerance is not set',

            'suitability_profile_incomplete' =>
                'Investor suitability profile is incomplete',

            'actual_risk_unavailable' =>
                'Portfolio risk could not be measured',

            default =>
                parent::title(
                    $code,
                    $category,
                    $providedTitle
                ),
        };
    }

    public function message(
        string $code,
        string $category,
        ?string $providedMessage = null
    ): string {
        return $providedMessage
            ?: match ($code) {
                'portfolio_materially_too_aggressive' =>
                    'The measured portfolio risk is materially higher than the effective investor risk tolerance.',

                'portfolio_somewhat_too_aggressive' =>
                    'The measured portfolio risk is one level above the effective investor risk tolerance.',

                'portfolio_materially_too_conservative' =>
                    'The portfolio is materially less aggressive than the investor profile and may reduce long-term growth potential.',

                'portfolio_somewhat_too_conservative' =>
                    'The portfolio is one level below the effective investor risk tolerance.',

                'portfolio_risk_aligned' =>
                    'The measured portfolio risk is aligned with the investor’s effective risk tolerance.',

                default =>
                    parent::message(
                        $code,
                        $category,
                        $providedMessage
                    ),
            };
    }

    public function recommendation(
        string $code,
        string $category
    ): ?string {
        return match ($code) {
            'portfolio_materially_too_aggressive',
            'portfolio_somewhat_too_aggressive' =>
                'Ask the advisor to explain why the portfolio risk exceeds the effective investor risk tolerance and whether the allocation should be reduced.',

            'portfolio_materially_too_conservative',
            'portfolio_somewhat_too_conservative' =>
                'Review whether the allocation is too conservative for the investor’s time horizon, objective, and required long-term growth.',

            'portfolio_risk_aligned' =>
                'Continue monitoring alignment as the investor’s age, goals, liquidity needs, and account purposes change.',

            'risk_tolerance_not_set',
            'suitability_profile_incomplete' =>
                'Complete the investor profile and any relevant account-level suitability overrides.',

            'actual_risk_unavailable' =>
                'Add sufficient portfolio valuation history so Helmio can measure actual risk.',

            default =>
                parent::recommendation(
                    $code,
                    $category
                ),
        };
    }
}