<?php

namespace App\Services\Analytics\Risk;

use App\Models\Benchmark;
use App\Models\User;
use App\Services\Analytics\RiskAnalyticsService;
use Carbon\CarbonInterface;

class SuitabilityRiskService
{
    public const FORMULA_VERSION =
        'suitability-risk-0.2.0';

    public function __construct(
        private readonly RiskAnalyticsService $riskAnalyticsService,
        private readonly SuitabilityProfileService $suitabilityProfileService,
    ) {
    }

    /**
     * Compare actual portfolio risk with the investor's effective
     * risk tolerance and account-level overrides.
     *
     * @return array<string, mixed>
     */
    public function analyze(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?Benchmark $benchmark = null,
    ): array {
        $user->loadMissing([
            'investorProfile',
            'investmentAccounts.profile',
            'investmentAccounts.user.investorProfile',
        ]);

        $accounts =
            $user->investmentAccounts;

        $riskAnalytics =
            $this->riskAnalyticsService->analyze(
                user: $user,
                startDate: $startDate,
                endDate: $endDate,
                benchmark: $benchmark,
            );

        $suitability =
            $this->suitabilityProfileService
                ->portfolioSummary($accounts);

        $actualRiskLevel =
            data_get(
                $riskAnalytics,
                'risk_level'
            )
            ?? data_get(
                $riskAnalytics,
                'metrics.risk_level'
            )
            ?? data_get(
                $riskAnalytics,
                'data.risk_level'
            )
            ?? data_get(
                $riskAnalytics,
                'risk_metrics.risk_level'
            );

        $expectedRiskTolerance =
            $suitability[
                'weighted_risk_tolerance'
            ] ?? null;

        $actualRiskScore =
            $this->actualRiskScore(
                $actualRiskLevel
            );

        $expectedRiskScore =
            $this->expectedRiskScore(
                $expectedRiskTolerance
            );

        $riskGap = (
            $actualRiskScore !== null
            && $expectedRiskScore !== null
        )
            ? $actualRiskScore
                - $expectedRiskScore
            : null;

        $flags =
            $this->buildFlags(
                actualRiskLevel:
                    $actualRiskLevel,

                expectedRiskTolerance:
                    $expectedRiskTolerance,

                riskGap:
                    $riskGap,

                suitability:
                    $suitability,
            );

        $score =
            $this->calculateSuitabilityScore(
                riskGap: $riskGap,
                completeness: (float) (
                    $suitability[
                        'data_completeness'
                    ] ?? 0
                ),
            );

        return [
            'status' =>
                $score === null
                    ? 'insufficient_data'
                    : 'complete',

            'message' =>
                $score === null
                    ? 'A complete investor risk profile and sufficient portfolio risk history are required.'
                    : null,

            'score' =>
                $score,

            'label' =>
                $score !== null
                    ? $this->scoreLabel($score)
                    : 'Insufficient data',

            'metrics' => [
                'actual_risk_level' =>
                    $actualRiskLevel,

                'actual_risk_score' =>
                    $actualRiskScore,

                'expected_risk_tolerance' =>
                    $expectedRiskTolerance,

                'expected_risk_score' =>
                    $expectedRiskScore,

                'risk_gap' =>
                    $riskGap,

                'profile_completeness' =>
                    $suitability[
                        'data_completeness'
                    ] ?? 0,

                'account_override_count' =>
                    $suitability[
                        'override_count'
                    ] ?? 0,
            ],

            'flags' =>
                $flags,

            'warnings' =>
                $this->buildWarnings(
                    actualRiskLevel:
                        $actualRiskLevel,

                    expectedRiskTolerance:
                        $expectedRiskTolerance,

                    suitability:
                        $suitability,
                ),

            'recommendations' =>
                $this->recommendations(
                    riskGap: $riskGap
                ),

            'data' => [
                'risk_analytics' =>
                    $riskAnalytics,

                'suitability_profile' =>
                    $suitability,
            ],

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function actualRiskScore(
        ?string $riskLevel
    ): ?int {
        return match ($riskLevel) {
            'very_low' => 1,
            'low' => 2,
            'moderate' => 3,
            'high' => 4,
            'very_high' => 5,
            default => null,
        };
    }

    private function expectedRiskScore(
        ?string $riskTolerance
    ): ?int {
        return match ($riskTolerance) {
            'conservative' => 1,
            'moderately_conservative' => 2,
            'moderate' => 3,
            'moderately_aggressive' => 4,
            'aggressive' => 5,
            default => null,
        };
    }

    /**
     * Positive gap means actual risk exceeds expected risk.
     */
    private function calculateSuitabilityScore(
        ?int $riskGap,
        float $completeness
    ): ?int {
        if (
            $riskGap === null
            || $completeness <= 0
        ) {
            return null;
        }

        $score = match (abs($riskGap)) {
            0 => 100,
            1 => 78,
            2 => 48,
            3 => 25,
            default => 10,
        };

        if ($completeness < 1.0) {
            $score -= (int) round(
                (1.0 - $completeness) * 20
            );
        }

        return max(
            0,
            min(100, $score)
        );
    }

    /**
     * @param array<string, mixed> $suitability
     * @return array<int, array<string, mixed>>
     */
    private function buildFlags(
        ?string $actualRiskLevel,
        ?string $expectedRiskTolerance,
        ?int $riskGap,
        array $suitability,
    ): array {
        if (
            $actualRiskLevel === null
            || $expectedRiskTolerance === null
            || $riskGap === null
        ) {
            return [];
        }

        if ($riskGap >= 2) {
            return [
                [
                    'code' =>
                        'portfolio_materially_too_aggressive',

                    'severity' =>
                        'high',

                    'title' =>
                        'Portfolio risk substantially exceeds the investor profile',

                    'message' =>
                        sprintf(
                            'The portfolio risk level is %s, while the effective investor risk tolerance is %s.',
                            str_replace(
                                '_',
                                ' ',
                                $actualRiskLevel
                            ),
                            str_replace(
                                '_',
                                ' ',
                                $expectedRiskTolerance
                            ),
                        ),
                ],
            ];
        }

        if ($riskGap === 1) {
            return [
                [
                    'code' =>
                        'portfolio_somewhat_too_aggressive',

                    'severity' =>
                        'moderate',

                    'title' =>
                        'Portfolio risk is above the stated tolerance',

                    'message' =>
                        'The portfolio appears one risk level more aggressive than the effective investor profile.',
                ],
            ];
        }

        if ($riskGap <= -2) {
            return [
                [
                    'code' =>
                        'portfolio_materially_too_conservative',

                    'severity' =>
                        'moderate',

                    'title' =>
                        'Portfolio may be too conservative for the stated profile',

                    'message' =>
                        'The portfolio appears materially less aggressive than the effective risk tolerance, which may reduce long-term growth potential.',
                ],
            ];
        }

        if ($riskGap === -1) {
            return [
                [
                    'code' =>
                        'portfolio_somewhat_too_conservative',

                    'severity' =>
                        'informational',

                    'title' =>
                        'Portfolio risk is below the stated tolerance',

                    'message' =>
                        'The portfolio appears one risk level more conservative than the effective investor profile.',
                ],
            ];
        }

        return [
            [
                'code' =>
                    'portfolio_risk_aligned',

                'severity' =>
                    'informational',

                'title' =>
                    'Portfolio risk matches the investor profile',

                'message' =>
                    'The measured portfolio risk is aligned with the effective risk tolerance.',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $suitability
     * @return array<int, array<string, mixed>>
     */
    private function buildWarnings(
        ?string $actualRiskLevel,
        ?string $expectedRiskTolerance,
        array $suitability,
    ): array {
        $warnings = [];

        if ($actualRiskLevel === null) {
            $warnings[] = [
                'code' =>
                    'actual_risk_unavailable',

                'message' =>
                    'Portfolio risk could not be measured from the available valuation history.',
            ];
        }

        if ($expectedRiskTolerance === null) {
            $warnings[] = [
                'code' =>
                    'risk_tolerance_not_set',

                'message' =>
                    'The investor risk tolerance has not been completed.',
            ];
        }

        if (
            (
                $suitability[
                    'data_completeness'
                ] ?? 0
            ) < 1.0
        ) {
            $warnings[] = [
                'code' =>
                    'suitability_profile_incomplete',

                'message' =>
                    'One or more suitability fields are missing from the investor or account profiles.',
            ];
        }

        return $warnings;
    }

    /**
     * @return array<int, string>
     */
    private function recommendations(
        ?int $riskGap
    ): array {
        if ($riskGap === null) {
            return [
                'Complete the investor profile and add sufficient portfolio valuation history.',
            ];
        }

        if ($riskGap > 0) {
            return [
                'Ask the advisor to explain why the portfolio risk exceeds the stated risk tolerance.',
                'Review equity exposure, concentration, volatility, and maximum drawdown.',
                'Confirm that the documented investor profile is current and accurate.',
            ];
        }

        if ($riskGap < 0) {
            return [
                'Review whether the portfolio is too conservative for the investor’s time horizon and growth objective.',
                'Evaluate whether cash or fixed-income exposure is reducing expected long-term growth.',
            ];
        }

        return [
            'Continue monitoring portfolio risk as the investor’s goals, age, and time horizon change.',
        ];
    }

    private function scoreLabel(
        int $score
    ): string {
        return match (true) {
            $score >= 90 =>
                'Excellent alignment',

            $score >= 75 =>
                'Generally aligned',

            $score >= 60 =>
                'Some mismatch',

            $score >= 40 =>
                'Needs attention',

            default =>
                'Poor alignment',
        };
    }
}