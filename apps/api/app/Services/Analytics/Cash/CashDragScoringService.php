<?php

namespace App\Services\Analytics\Cash;

class CashDragScoringService
{
    public function score(
        ?float $averageCashPercent,
        ?float $opportunityCostRate
    ): array {
        if ($averageCashPercent === null) {
            return [
                'score' => null,
                'rating' => null,
            ];
        }

        $score = match (true) {
            $averageCashPercent <= 0.05 => 100,
            $averageCashPercent <= 0.10 => 85,
            $averageCashPercent <= 0.20 => 65,
            $averageCashPercent <= 0.35 => 40,
            default => 20,
        };

        if (
            $opportunityCostRate !== null
            && $opportunityCostRate >= 0.05
        ) {
            $score -= 10;
        }

        $score = max(0, min(100, $score));

        return [
            'score' => $score,

            'rating' => match (true) {
                $score >= 90 => 'excellent',
                $score >= 75 => 'good',
                $score >= 55 => 'moderate',
                $score >= 35 => 'poor',
                default => 'critical',
            },
        ];
    }
}