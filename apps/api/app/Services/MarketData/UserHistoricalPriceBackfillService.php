<?php

namespace App\Services\MarketData;

use App\Models\Security;
use App\Models\User;
use Carbon\CarbonInterface;

class UserHistoricalPriceBackfillService
{
    public function __construct(
        private readonly HistoricalSecurityPriceImporter $importer,
    ) {
    }

    public function backfill(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
    ): array {
        $securities = Security::query()
            ->whereHas(
                'holdings.investmentAccount',
                fn ($query) =>
                    $query->where(
                        'user_id',
                        $user->id,
                    ),
            )
            ->get();

        $results = [];

        foreach ($securities as $security) {
            $results[] =
                $this->importer->import(
                    security:
                        $security,

                    startDate:
                        $startDate,

                    endDate:
                        $endDate,
                );
        }

        return [
            'user_id' =>
                $user->id,

            'security_count' =>
                $securities->count(),

            'results' =>
                $results,
        ];
    }
}