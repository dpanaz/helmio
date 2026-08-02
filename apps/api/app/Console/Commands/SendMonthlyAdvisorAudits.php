<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\MonthlyAdvisorAuditNotification;
use App\Services\Audit\AdvisorAuditReportDataService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendMonthlyAdvisorAudits extends Command
{
    protected $signature = 'helmio:send-monthly-audits
        {--user= : Send only to a specific user ID}
        {--force : Ignore the configured date and duplicate-send checks}';

    protected $description =
        'Calculate and email scheduled monthly Advisor Audit reports';

    public function handle(
        AdvisorAuditReportDataService $reportService,
    ): int {
        $query = User::query()
            ->where('monthly_audit_enabled', true);

        if ($this->option('user')) {
            $query->where(
                'id',
                (int) $this->option('user'),
            );
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        $query->chunkById(
            100,
            function ($users) use (
                $reportService,
                &$sent,
                &$skipped,
                &$failed,
            ): void {
                foreach ($users as $user) {
                    if (
                        ! $this->option('force')
                        && ! $this->isDue($user)
                    ) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $data = $reportService->build(
                            $user,
                        );

                        $deliveryEmail =
                            $user->monthly_audit_email
                            ?: $user->email;

                        Notification::route(
                            'mail',
                            $deliveryEmail,
                        )->notify(
                            new MonthlyAdvisorAuditNotification(
                                $data,
                            ),
                        );

                        $user->forceFill([
                            'last_monthly_audit_sent_at' =>
                                now(),
                        ])->save();

                        $this->info(
                            "Queued monthly audit for user {$user->id}."
                        );

                        $sent++;
                    } catch (Throwable $exception) {
                        report($exception);

                        $this->error(
                            "Failed user {$user->id}: "
                            .$exception->getMessage()
                        );

                        $failed++;
                    }
                }
            },
        );

        $this->newLine();

        $this->table(
            ['Queued', 'Skipped', 'Failed'],
            [[
                $sent,
                $skipped,
                $failed,
            ]],
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function isDue(User $user): bool
    {
        $timezone =
            $user->timezone
            ?: config('app.timezone');

        $localNow = CarbonImmutable::now(
            $timezone,
        );

        if (
            $localNow->day
            !== (int) $user->monthly_audit_day
        ) {
            return false;
        }

        $scheduledTime = substr(
            $user->monthly_audit_time
                ?: '08:00:00',
            0,
            5,
        );

        if (
            $localNow->format('H:i')
            !== $scheduledTime
        ) {
            return false;
        }

        if (
            $user->last_monthly_audit_sent_at
            === null
        ) {
            return true;
        }

        $lastSentLocal =
            CarbonImmutable::instance(
                $user->last_monthly_audit_sent_at,
            )->setTimezone($timezone);

        return ! $lastSentLocal->isSameMonth(
            $localNow,
        );
    }
}
