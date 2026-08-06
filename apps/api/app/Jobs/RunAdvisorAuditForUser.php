<?php

namespace App\Jobs;

use App\Models\AuditRun;
use App\Models\MonthlyAuditSetting;
use App\Services\AdvisorAudit\AdvisorAuditNotificationService;
use App\Services\AdvisorAudit\AdvisorAuditPersistenceService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class RunAdvisorAuditForUser implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public readonly int $monthlyAuditSettingId,
    ) {
    }

    public function handle(
        AdvisorAuditPersistenceService $persistenceService,
        AdvisorAuditNotificationService $notificationService,
    ): void {
        $setting = MonthlyAuditSetting::query()
            ->with([
                'user',
                'benchmark',
            ])
            ->find($this->monthlyAuditSettingId);

        if (
            $setting === null
            || ! $setting->is_enabled
            || $setting->user === null
        ) {
            return;
        }

        $timezone =
            $setting->timezone
            ?: config(
                'app.timezone',
                'UTC'
            );

        $periodEnd =
            now($timezone)
                ->endOfDay();

        $periodStart =
            $periodEnd
                ->copy()
                ->subYear()
                ->startOfDay();

        $previousRun = AuditRun::query()
            ->where(
                'user_id',
                $setting->user_id
            )
            ->orderByDesc(
                'calculated_for_date'
            )
            ->orderByDesc('id')
            ->first();

        try {
            $result =
                $persistenceService
                    ->runAndPersist(
                        user:
                            $setting->user,

                        startDate:
                            $periodStart,

                        endDate:
                            $periodEnd,

                        benchmark:
                            $setting->benchmark,
                    );

            $currentRun = AuditRun::query()
                ->where(
                    'user_id',
                    $setting->user_id
                )
                ->where(
                    'formula_version',
                    $result[
                        'formula_version'
                    ]
                )
                ->orderByDesc(
                    'calculated_for_date'
                )
                ->orderByDesc('id')
                ->firstOrFail();

            $notificationService->send(
                setting:
                    $setting,

                currentRun:
                    $currentRun,

                previousRun:
                    $previousRun,
            );

            $setting->forceFill([
                'last_run_at' =>
                    now(),

                'next_run_at' =>
                    $this->calculateNextRunAt(
                        runDay:
                            $setting->run_day,

                        timezone:
                            $timezone,
                    ),
            ])->save();
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }

    private function calculateNextRunAt(
        int $runDay,
        string $timezone
    ): Carbon {
        $now =
            now($timezone);

        $candidate =
            $now->copy()
                ->startOfMonth()
                ->day($runDay)
                ->setTime(8, 0);

        if ($candidate->lessThanOrEqualTo($now)) {
            $candidate =
                $candidate
                    ->addMonthNoOverflow()
                    ->startOfMonth()
                    ->day($runDay)
                    ->setTime(8, 0);
        }

        return $candidate->utc();
    }
}