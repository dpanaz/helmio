<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MarketData\UserHistoricalPriceBackfillService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class BackfillHistoricalSecurityPrices extends Command
{
    protected $signature =
        'helmio:backfill-prices
        {userId : Helmio user ID}
        {--start= : Start date YYYY-MM-DD}
        {--end= : End date YYYY-MM-DD}';

    protected $description =
        'Backfill historical security prices for a Helmio user.';

    public function handle(
        UserHistoricalPriceBackfillService $backfillService,
    ): int {
        $user = User::query()
            ->findOrFail(
                (int) $this->argument(
                    'userId',
                ),
            );

        $endDate =
            $this->option('end')
                ? CarbonImmutable::parse(
                    $this->option(
                        'end',
                    ),
                )
                : CarbonImmutable::now();

        $startDate =
            $this->option('start')
                ? CarbonImmutable::parse(
                    $this->option(
                        'start',
                    ),
                )
                : $endDate->subYear();

        $result =
            $backfillService->backfill(
                user:
                    $user,

                startDate:
                    $startDate,

                endDate:
                    $endDate,
            );

        $this->info(
            sprintf(
                'Processed %d securities for user %d.',
                $result[
                    'security_count'
                ],
                $user->id,
            ),
        );

        foreach (
            $result['results']
            as $item
        ) {
            $this->line(
                sprintf(
                    '%s: %s (%d prices)',
                    $item['symbol']
                        ?? 'unknown',

                    $item['status'],

                    $item['imported'],
                ),
            );
        }

        return self::SUCCESS;
    }
}