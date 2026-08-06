<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Analytics\Performance\PortfolioValuationGenerator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class GeneratePortfolioValuations extends Command
{
    protected $signature = 'helmio:generate-valuations
                            {--date= : Valuation date in YYYY-MM-DD format}
                            {--user= : Generate valuations for one user ID}';

    protected $description =
        'Generate account and consolidated portfolio valuations.';

    public function handle(
        PortfolioValuationGenerator $generator
    ): int {
        $valuationDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : now()->startOfDay();

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

        $successCount = 0;
        $failureCount = 0;

        foreach ($users as $user) {
            try {
                $result = $generator->generateForUser(
                    user: $user,
                    valuationDate: $valuationDate,
                );

                $successCount++;

                $this->info(
                    sprintf(
                        'User %d: %d accounts, portfolio value $%s',
                        $user->id,
                        $result['account_count'],
                        number_format(
                            $result['portfolio_valuation']['total_value'],
                            2
                        )
                    )
                );
            } catch (Throwable $exception) {
                $failureCount++;

                $this->error(
                    sprintf(
                        'User %d failed: %s',
                        $user->id,
                        $exception->getMessage()
                    )
                );
            }
        }

        $this->newLine();

        $this->table(
            ['Result', 'Count'],
            [
                ['Successful', $successCount],
                ['Failed', $failureCount],
            ]
        );

        return $failureCount > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}