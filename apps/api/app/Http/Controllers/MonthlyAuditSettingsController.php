<?php

namespace App\Http\Controllers;

use App\Models\Benchmark;
use App\Models\MonthlyAuditSetting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyAuditSettingsController extends Controller
{
    public function edit(
        Request $request
    ): View {
        $setting =
            MonthlyAuditSetting::query()
                ->firstOrCreate(
                    [
                        'user_id' =>
                            $request->user()->id,
                    ],
                    [
                        'is_enabled' =>
                            false,

                        'run_day' =>
                            1,

                        'timezone' =>
                            config(
                                'app.timezone',
                                'America/Chicago'
                            ),

                        'notify_on_completion' =>
                            true,

                        'notify_on_new_critical' =>
                            true,

                        'notify_on_score_change' =>
                            true,

                        'score_change_threshold' =>
                            5,

                        'next_run_at' =>
                            null,
                    ],
                );

        $benchmarks =
            Benchmark::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

        return view(
            'advisor-audit.monthly-settings',
            [
                'setting' =>
                    $setting,

                'benchmarks' =>
                    $benchmarks,

                'timezones' =>
                    $this->timezones(),
            ]
        );
    }

    public function update(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'is_enabled' => [
                    'nullable',
                    'boolean',
                ],

                'run_day' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:28',
                ],

                'timezone' => [
                    'required',
                    'string',
                    'timezone',
                ],

                'benchmark_id' => [
                    'nullable',
                    'integer',
                    'exists:benchmarks,id',
                ],

                'notify_on_completion' => [
                    'nullable',
                    'boolean',
                ],

                'notify_on_new_critical' => [
                    'nullable',
                    'boolean',
                ],

                'notify_on_score_change' => [
                    'nullable',
                    'boolean',
                ],

                'score_change_threshold' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:25',
                ],
            ]);

        $isEnabled =
            $request->boolean(
                'is_enabled'
            );

        $setting =
            MonthlyAuditSetting::query()
                ->firstOrNew([
                    'user_id' =>
                        $request->user()->id,
                ]);

        $setting->fill([
            'is_enabled' =>
                $isEnabled,

            'run_day' =>
                (int) $validated[
                    'run_day'
                ],

            'timezone' =>
                $validated[
                    'timezone'
                ],

            'benchmark_id' =>
                $validated[
                    'benchmark_id'
                ] ?? null,

            'notify_on_completion' =>
                $request->boolean(
                    'notify_on_completion'
                ),

            'notify_on_new_critical' =>
                $request->boolean(
                    'notify_on_new_critical'
                ),

            'notify_on_score_change' =>
                $request->boolean(
                    'notify_on_score_change'
                ),

            'score_change_threshold' =>
                (int) $validated[
                    'score_change_threshold'
                ],

            'next_run_at' =>
                $isEnabled
                    ? $this->calculateNextRunAt(
                        runDay: (int) $validated[
                            'run_day'
                        ],

                        timezone: $validated[
                            'timezone'
                        ],
                    )
                    : null,
        ]);

        $setting->save();

        return redirect()
            ->route(
                'advisor-audit.monthly-settings'
            )
            ->with(
                'status',
                $isEnabled
                    ? 'Monthly Advisor Audits are enabled.'
                    : 'Monthly Advisor Audits are disabled.'
            );
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

    /**
     * @return array<string, string>
     */
    private function timezones(): array
    {
        return [
            'America/New_York' =>
                'Eastern Time',

            'America/Chicago' =>
                'Central Time',

            'America/Denver' =>
                'Mountain Time',

            'America/Phoenix' =>
                'Arizona Time',

            'America/Los_Angeles' =>
                'Pacific Time',

            'America/Anchorage' =>
                'Alaska Time',

            'Pacific/Honolulu' =>
                'Hawaii Time',
        ];
    }
}