<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Analytics\Performance\PortfolioValuationGenerator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Throwable;

class GenerateHistoricalPortfolioValuations extends Command
{
    protected $signature = 'helmio:generate-valuation-history
                            {--user= : Generate valuations for one user ID}
                            {--start= : Start date in YYYY-MM-DD format}
                            {--end= : End date in YYYY-MM-DD format}
                            {--include-weekends : Generate Saturday and Sunday valuations}
                            {--continue-on-error : Continue processing after a failed date}';

    protected $description =
        'Generate portfolio valuation history across a date range.';

    public function handle(
        PortfolioValuationGenerator $generator
    ): int {
        $startDate = $this->resolveStartDate();
        $endDate = $this->resolveEndDate();

        if ($startDate->greaterThan($endDate)) {
            $this->error(
                'The start date must be on or before the end date.'
            );

            return self::FAILURE;
        }

        $users = User::query()
            ->when(
                $this->option('user'),
                fn ($query) => $query->whereKey(
                    $this->option('user')
                )
            )
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No matching users were found.');

            return self::SUCCESS;
        }

        $dates = collect(
            CarbonPeriod::create(
                $startDate,
                '1 day',
                $endDate
            )
        )
            ->map(
                fn ($date) => Carbon::instance($date)
                    ->startOfDay()
            )
            ->when(
                ! $this->option('include-weekends'),
                fn ($dates) => $dates->reject(
                    fn (Carbon $date): bool =>
                        $date->isWeekend()
                )
            )
            ->values();

        if ($dates->isEmpty()) {
            $this->warn(
                'No eligible valuation dates were found.'
            );

            return self::SUCCESS;
        }

        $this->info(
            sprintf(
                'Generating %d valuation dates for %d user(s).',
                $dates->count(),
                $users->count()
            )
        );

        $totalSuccessful = 0;
        $totalFailed = 0;

        foreach ($users as $user) {
            $this->newLine();

            $this->info(
                sprintf(
                    'Processing user %d: %s',
                    $user->id,
                    $user->email
                )
            );

            $progressBar = $this->output->createProgressBar(
                $dates->count()
            );

            $progressBar->start();

            foreach ($dates as $date) {
                try {
                    $generator->generateForUser(
                        user: $user,
                        valuationDate: $date,
                    );

                    $totalSuccessful++;
                } catch (Throwable $exception) {
                    $totalFailed++;

                    $progressBar->clear();

                    $this->error(
                        sprintf(
                            'User %d failed on %s: %s',
                            $user->id,
                            $date->toDateString(),
                            $exception->getMessage()
                        )
                    );

                    if (
                        ! $this->option(
                            'continue-on-error'
                        )
                    ) {
                        return self::FAILURE;
                    }

                    $progressBar->display();
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();
        }

        $this->newLine();

        $this->table(
            ['Result', 'Count'],
            [
                ['Successful valuations', $totalSuccessful],
                ['Failed valuations', $totalFailed],
            ]
        );

        return $totalFailed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function resolveStartDate(): Carbon
    {
        return $this->option('start')
            ? Carbon::parse(
                $this->option('start')
            )->startOfDay()
            : now()->subYear()->startOfDay();
    }

    private function resolveEndDate(): Carbon
    {
        return $this->option('end')
            ? Carbon::parse(
                $this->option('end')
            )->startOfDay()
            : now()->startOfDay();
    }
}