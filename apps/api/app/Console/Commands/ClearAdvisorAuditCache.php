<?php

namespace App\Console\Commands;

use App\Services\Dashboard\DashboardService;
use Illuminate\Console\Command;

class ClearAdvisorAuditCache extends Command
{
    protected $signature =
        'advisor-audit:clear-cache
        {userId? : User whose cached audit should be cleared}';

    protected $description =
        'Clear a cached Advisor Audit for one user or all users.';

    public function handle(
        DashboardService $dashboardService
    ): int {
        $userId = $this->argument('userId');

        if ($userId !== null) {
            $dashboardService
                ->clearAdvisorAuditCache(
                    (int) $userId
                );

            $this->info(
                "Advisor Audit cache cleared for user {$userId}."
            );

            return self::SUCCESS;
        }

        /*
         * Laravel does not provide wildcard deletion for every
         * cache driver. Flushing is acceptable during development,
         * but avoid using this without a user ID in production.
         */
        $this->components->warn(
            'No user ID supplied. Flushing the entire application cache.'
        );

        cache()->flush();

        $this->info(
            'Application cache cleared.'
        );

        return self::SUCCESS;
    }
}