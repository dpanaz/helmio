<?php

namespace App\Services\Analytics\Performance;

use InvalidArgumentException;

class ReturnCalculator
{
    /**
     * Calculate a simple holding-period return.
     *
     * Cash flows are removed from the gain because deposits and withdrawals
     * are not investment performance.
     */
    public function simpleReturn(
        float $beginningValue,
        float $endingValue,
        float $netCashFlow = 0
    ): ?float {
        if ($beginningValue <= 0) {
            return null;
        }

        return (
            $endingValue
            - $beginningValue
            - $netCashFlow
        ) / $beginningValue;
    }

    /**
     * Calculate a time-weighted return from subperiod returns.
     *
     * Example input:
     * [0.02, -0.01, 0.03]
     */
    public function timeWeightedReturn(array $periodReturns): ?float
    {
        if ($periodReturns === []) {
            return null;
        }

        $growthFactor = 1.0;

        foreach ($periodReturns as $periodReturn) {
            if (! is_numeric($periodReturn)) {
                throw new InvalidArgumentException(
                    'Every period return must be numeric.'
                );
            }

            $periodReturn = (float) $periodReturn;

            if ($periodReturn < -1) {
                throw new InvalidArgumentException(
                    'A period return cannot be less than -100%.'
                );
            }

            $growthFactor *= 1 + $periodReturn;
        }

        return $growthFactor - 1;
    }

    public function benchmarkReturn(
        float $beginningPrice,
        float $endingPrice
    ): ?float {
        if ($beginningPrice <= 0) {
            return null;
        }

        return ($endingPrice / $beginningPrice) - 1;
    }

    public function annualizeReturn(
        float $totalReturn,
        int $days
    ): ?float {
        if ($days <= 0 || $totalReturn <= -1) {
            return null;
        }

        return pow(
            1 + $totalReturn,
            365 / $days
        ) - 1;
    }

    public function alpha(
        ?float $portfolioReturn,
        ?float $benchmarkReturn
    ): ?float {
        if (
            $portfolioReturn === null
            || $benchmarkReturn === null
        ) {
            return null;
        }

        return $portfolioReturn - $benchmarkReturn;
    }

    public function opportunityCost(
        float $beginningValue,
        ?float $portfolioReturn,
        ?float $benchmarkReturn
    ): ?float {
        $alpha = $this->alpha(
            $portfolioReturn,
            $benchmarkReturn
        );

        if ($alpha === null || $beginningValue <= 0) {
            return null;
        }

        return max(0, -$alpha * $beginningValue);
    }
}