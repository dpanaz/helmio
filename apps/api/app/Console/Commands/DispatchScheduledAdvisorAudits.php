<?php

namespace App\Console\Commands;

use App\Jobs\RunAdvisorAuditForUser;
use App\Models\MonthlyAuditSetting;
use Illuminate\Console\Command;

class DispatchScheduledAdvisorAudits extends Command
{
    protected $signature =
        'advisor-audit:dispatch-scheduled
        {--sync : Run jobs immediately instead of dispatching them}';

    protected $description =
        'Dispatch all monthly Advisor Audits that are due to run.';

    public function handle(): int
    {
        $dueSettings = MonthlyAuditSetting::query()
            ->where('is_enabled', true)
            ->whereNotNull('next_run_at')
            ->where(
                'next_run_at',
                '<=',
                now()
            )
            ->orderBy('next_run_at')
            ->get();

        if ($dueSettings->isEmpty()) {
            $this->components->info(
                'No monthly Advisor Audits are due.'
            );

            return self::SUCCESS;
        }

        $processed = 0;

        foreach ($dueSettings as $setting) {
            $job = new RunAdvisorAuditForUser(
                monthlyAuditSettingId:
                    $setting->id
            );

            if ($this->option('sync')) {
                dispatch_sync($job);
            } else {
                dispatch($job);
            }

            $processed++;
        }

        $verb = $this->option('sync')
            ? 'ran'
            : 'dispatched';

        $this->components->info(
            sprintf(
                '%d monthly Advisor Audit job(s) %s.',
                $processed,
                $verb
            )
        );

        return self::SUCCESS;
    }
}