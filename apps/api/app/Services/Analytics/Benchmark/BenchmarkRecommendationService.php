<?php

namespace App\Services\Analytics\Benchmark;

use App\Models\Benchmark;
use App\Models\User;

class BenchmarkRecommendationService
{
    public const FORMULA_VERSION =
        'benchmark-recommendation-0.1.0';

    /**
     * Recommend the most appropriate comparison benchmark
     * from the investor's documented profile.
     *
     * Explicit user-selected benchmarks should still override
     * this recommendation at the controller/UI level.
     *
     * @return array<string, mixed>
     */
    public function recommend(
        User $user
    ): array {
        $profile =
            $user->investorProfile;

        if ($profile === null) {
            return $this->fallback(
                reason:
                    'Investor profile is unavailable.'
            );
        }

        $riskTolerance =
            strtolower(
                trim(
                    (string) (
                        $profile->risk_tolerance
                        ?? ''
                    )
                )
            );

        $objective =
            strtolower(
                trim(
                    (string) (
                        $profile->primary_objective
                        ?? ''
                    )
                )
            );

        $timeHorizon =
            is_numeric(
                $profile->time_horizon_years
            )
                ? (int)
                    $profile->time_horizon_years
                : null;

        $liquidityNeeds =
            strtolower(
                trim(
                    (string) (
                        $profile->liquidity_needs
                        ?? ''
                    )
                )
            );

        /*
         * ---------------------------------------------------------
         * Base recommendation from stated risk tolerance
         * ---------------------------------------------------------
         *
         * Conservative through moderate investors use Helmio's
         * balanced 60/40 comparison.
         *
         * Investors explicitly accepting above-average or high
         * market risk use the broad U.S. equity benchmark.
         */
        $symbol = match (
            $riskTolerance
        ) {
            'conservative',
            'moderately_conservative',
            'moderate'
                => 'HELMIO-60-40',

            'moderately_aggressive',
            'aggressive'
                => 'VTI',

            default
                => null,
        };

        $reasons = [];

        if ($riskTolerance !== '') {
            $reasons[] =
                sprintf(
                    'Investor risk tolerance is %s.',
                    str_replace(
                        '_',
                        ' ',
                        $riskTolerance
                    )
                );
        }

        /*
         * ---------------------------------------------------------
         * Time-horizon guardrail
         * ---------------------------------------------------------
         *
         * A short horizon should not automatically be benchmarked
         * against a 100% equity implementation even if the stated
         * tolerance is aggressive.
         */
        if (
            $timeHorizon !== null
            && $timeHorizon <= 5
        ) {
            $symbol =
                'HELMIO-60-40';

            $reasons[] =
                sprintf(
                    'The documented time horizon is %d years, which supports a balanced comparison.',
                    $timeHorizon
                );
        }

        /*
         * ---------------------------------------------------------
         * Liquidity guardrail
         * ---------------------------------------------------------
         */
        if (
            $liquidityNeeds === 'high'
        ) {
            $symbol =
                'HELMIO-60-40';

            $reasons[] =
                'High liquidity needs support using a balanced benchmark rather than a full-equity benchmark.';
        }

        /*
         * ---------------------------------------------------------
         * Objective context
         * ---------------------------------------------------------
         *
         * Do not override strong risk/time-horizon guardrails,
         * but use the objective to strengthen the explanation.
         */
        if ($objective !== '') {
            $reasons[] =
                sprintf(
                    'Primary investment objective is %s.',
                    str_replace(
                        '_',
                        ' ',
                        $objective
                    )
                );
        }

        /*
         * If risk tolerance is missing, use limited profile context.
         */
        if ($symbol === null) {
            if (
                $timeHorizon !== null
                && $timeHorizon >= 10
                && in_array(
                    $objective,
                    [
                        'growth',
                        'aggressive_growth',
                    ],
                    true,
                )
            ) {
                $symbol = 'VTI';

                $reasons[] =
                    'Long investment horizon and growth objective support a broad equity benchmark.';
            } else {
                $symbol =
                    'HELMIO-60-40';

                $reasons[] =
                    'A balanced benchmark is used because the investor profile does not support a higher-confidence equity-only comparison.';
            }
        }

        $benchmark =
            Benchmark::query()
                ->where(
                    'symbol',
                    $symbol
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();

        if ($benchmark === null) {
            return $this->fallback(
                reason:
                    "Recommended benchmark {$symbol} is unavailable."
            );
        }

        return [
            'status' =>
                'complete',

            'benchmark' =>
                $benchmark,

            'benchmark_id' =>
                $benchmark->id,

            'symbol' =>
                $benchmark->symbol,

            'name' =>
                $benchmark->name,

            'reason' =>
                $this->summaryReason(
                    $benchmark,
                    $riskTolerance,
                    $timeHorizon
                ),

            'reasons' =>
                $reasons,

            'profile' => [
                'risk_tolerance' =>
                    $riskTolerance !== ''
                        ? $riskTolerance
                        : null,

                'primary_objective' =>
                    $objective !== ''
                        ? $objective
                        : null,

                'time_horizon_years' =>
                    $timeHorizon,

                'liquidity_needs' =>
                    $liquidityNeeds !== ''
                        ? $liquidityNeeds
                        : null,
            ],

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fallback(
        string $reason
    ): array {
        $benchmark =
            Benchmark::query()
                ->where(
                    'symbol',
                    'HELMIO-60-40'
                )
                ->where(
                    'is_active',
                    true
                )
                ->first()
            ?? Benchmark::query()
                ->where(
                    'symbol',
                    'VTI'
                )
                ->where(
                    'is_active',
                    true
                )
                ->first()
            ?? Benchmark::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderByDesc(
                    'is_default'
                )
                ->orderBy('id')
                ->first();

        return [
            'status' =>
                $benchmark
                    ? 'fallback'
                    : 'unavailable',

            'benchmark' =>
                $benchmark,

            'benchmark_id' =>
                $benchmark?->id,

            'symbol' =>
                $benchmark?->symbol,

            'name' =>
                $benchmark?->name,

            'reason' =>
                $reason,

            'reasons' => [
                $reason,
            ],

            'profile' =>
                null,

            'formula_version' =>
                self::FORMULA_VERSION,
        ];
    }

    private function summaryReason(
        Benchmark $benchmark,
        string $riskTolerance,
        ?int $timeHorizon
    ): string {
        if (
            $benchmark->symbol
            === 'HELMIO-60-40'
        ) {
            if (
                $timeHorizon !== null
                && $timeHorizon <= 5
            ) {
                return
                    'Recommended because the investor has a shorter documented investment horizon.';
            }

            return
                'Recommended as a balanced comparison based on the investor’s documented risk profile.';
        }

        if ($benchmark->symbol === 'VTI') {
            return
                'Recommended as a broad equity comparison based on the investor’s documented growth and risk profile.';
        }

        return sprintf(
            '%s is the recommended benchmark for this investor profile.',
            $benchmark->name
        );
    }
}