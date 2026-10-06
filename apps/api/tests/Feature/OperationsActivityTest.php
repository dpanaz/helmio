<?php

namespace Tests\Feature;

use App\Models\StaffRole;
use App\Models\User;
use App\Services\Operations\OperationsActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationsActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_uses_successful_completion_times_and_a_rolling_24_hour_window(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 6)->setTime(13, 0));
        [$customer, $connection] = $this->customer();
        foreach ([
            ['success', now()->subHour()],
            ['success', now()->subDay()],
            ['success', now()->subDay()->subSecond()],
            ['failed', now()->subMinute()],
            ['running', null],
            ['success', null],
            ['success', now()->addMinute()],
        ] as [$status, $finished]) {
            $this->sync($customer, $connection, $status, $finished);
        }
        foreach ([
            ['ready', now()->subHour()],
            ['ready', now()->subDay()],
            ['ready', now()->subDay()->subSecond()],
            ['failed', now()->subMinute()],
            ['analyzing', null],
            ['ready', null],
            ['ready', now()->addMinute()],
        ] as [$status, $completed]) {
            $this->analysis($customer, $status, $completed);
        }
        foreach ([now()->subDay(), now()->subDay()->subSecond()] as $failed) {
            DB::table('failed_jobs')->insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'connection' => 'database', 'queue' => 'analytics',
                'payload' => '{}', 'exception' => 'Historical error', 'failed_at' => $failed,
            ]);
        }
        $activity = app(OperationsActivityService::class)->snapshot();
        $this->assertSame(2, $activity['successfulSyncCount']);
        $this->assertSame(2, $activity['successfulAnalysisCount']);
        $this->assertCount(2, $activity['recentSyncs']);
        $this->assertCount(2, $activity['recentAnalyses']);
        $this->assertSame(1, $activity['recentFailedJobCount']);
        $this->assertSame(1, $activity['historicalFailedJobCount']);
        $this->assertTrue($activity['recentSyncs']->first()->finished_at->equalTo(now()->subHour()));
        $this->assertTrue($activity['recentAnalyses']->first()->completed_at->equalTo(now()->subHour()));

        $this->actingAs($this->admin())->get(route('admin.operations.index'))->assertOk()
            ->assertSee('Successful syncs')->assertSee('Completed analyses')
            ->assertSee('New failed queue jobs')->assertSee('Older failed queue jobs')
            ->assertSee('Recent successful syncs')->assertSee('Recent completed analyses');
    }

    public function test_customer_history_uses_latest_success_time_and_includes_customers_without_success(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 6)->setTime(13, 0));
        [$customer, $connection] = $this->customer();
        [$never] = $this->customer();
        $analysisOnly = User::factory()->create();
        $this->analysis($analysisOnly, 'pending', null);
        // Insert an older success last, then failures, so MAX(id) would give the wrong answer.
        $this->sync($customer, $connection, 'success', now()->subHour());
        $this->sync($customer, $connection, 'success', now()->subDays(3));
        $this->sync($customer, $connection, 'failed', now());
        $this->analysis($customer, 'ready', now()->subHours(2));
        $this->analysis($customer, 'ready', now()->subDays(4));
        $this->analysis($customer, 'failed', now());

        $customers = app(OperationsActivityService::class)->snapshot()['customerActivity']->getCollection()->keyBy('id');
        $this->assertCount(3, $customers);
        $this->assertSame(now()->subHour()->toDateTimeString(), $customers[$customer->id]->last_sync_at);
        $this->assertSame(now()->subHours(2)->toDateTimeString(), $customers[$customer->id]->last_analysis_at);
        $this->assertNull($customers[$never->id]->last_sync_at);
        $this->assertNull($customers[$never->id]->last_analysis_at);
        $this->assertNull($customers[$analysisOnly->id]->last_analysis_at);
        $this->actingAs($this->admin())->get(route('admin.operations.index'))->assertOk()
            ->assertSee('Latest completion by customer')->assertSee('No successful run recorded');
    }

    public function test_queue_distinguishes_ready_delayed_and_reserved_jobs_without_showing_payloads(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 6)->setTime(13, 0));
        $epoch = now()->timestamp;
        foreach ([
            ['available_at' => $epoch, 'reserved_at' => null],
            ['available_at' => $epoch + 3600, 'reserved_at' => null],
            ['available_at' => $epoch + 3600, 'reserved_at' => $epoch - 10],
        ] as $timestamps) {
            DB::table('jobs')->insert($timestamps + [
                'queue' => 'analytics', 'payload' => '{"secret":"do-not-display-payload"}',
                'attempts' => 1, 'created_at' => $epoch - 7200,
            ]);
        }
        $activity = app(OperationsActivityService::class)->snapshot();
        $this->assertSame(1, $activity['queueReadyCount']);
        $this->assertSame(1, $activity['queueDelayedCount']);
        $this->assertSame(1, $activity['queueReservedCount']);
        $this->assertCount(3, $activity['pendingJobs']);
        $this->assertFalse(property_exists($activity['pendingJobs']->first(), 'payload'));
        $this->actingAs($this->admin())->get(route('admin.operations.index'))->assertOk()
            ->assertSee('Queue activity')->assertSee('Ready')->assertSee('Delayed')
            ->assertSee('Reserved by worker')->assertSee('Queued 2 hours ago')
            ->assertDontSee('do-not-display-payload');
    }

    public function test_lists_are_bounded_and_customer_history_is_paginated(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 6)->setTime(13, 0));
        for ($i = 0; $i < 30; $i++) {
            [$customer, $connection] = $this->customer();
            $this->sync($customer, $connection, 'success', now()->subMinutes($i));
            $this->analysis($customer, 'ready', now()->subMinutes($i));
            DB::table('jobs')->insert([
                'queue' => 'analytics', 'payload' => '{}', 'attempts' => 0,
                'reserved_at' => null, 'created_at' => now()->subMinutes($i)->timestamp,
                'available_at' => now()->timestamp,
            ]);
        }
        $activity = app(OperationsActivityService::class)->snapshot();
        $this->assertSame(30, $activity['successfulSyncCount']);
        $this->assertSame(30, $activity['successfulAnalysisCount']);
        $this->assertCount(20, $activity['recentSyncs']);
        $this->assertCount(20, $activity['recentAnalyses']);
        $this->assertCount(25, $activity['pendingJobs']);
        $this->assertCount(25, $activity['customerActivity']);
        $this->assertSame(30, $activity['customerActivity']->total());
        $this->assertSame(now()->subMinutes(29)->timestamp, $activity['pendingJobs']->first()->created_at);
        $response = $this->actingAs($this->admin())->get(route('admin.operations.index', ['activity_page' => 2]))->assertOk();
        $this->assertCount(5, $response->viewData('customerActivity'));
        $response->assertSee('Latest 20 of 30 in this period.');
    }

    public function test_empty_state_and_existing_staff_access_are_preserved(): void
    {
        $this->get(route('admin.operations.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.operations.index'))->assertForbidden();
        $response = $this->actingAs($this->admin())->get(route('admin.operations.index'))->assertOk();
        $response->assertSee('No successful syncs recorded in the last 24 hours.')
            ->assertSee('No completed analyses recorded in the last 24 hours.')
            ->assertSee('No customers with recorded brokerage or analysis activity.')
            ->assertSee('No queued jobs.')->assertSee('No failed queue jobs.');
        $this->assertSame(0, $response->viewData('successfulSyncCount'));
        $this->assertSame(0, $response->viewData('successfulAnalysisCount'));
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->staffRoles()->attach(StaffRole::where('slug', 'admin')->firstOrFail());

        return $admin;
    }

    private function customer(): array
    {
        $user = User::factory()->create();
        $connection = DB::table('brokerage_connections')->insertGetId([
            'user_id' => $user->id, 'provider' => 'snaptrade',
            'brokerage_name' => 'Fidelity', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$user, $connection];
    }

    private function sync(User $user, int $connection, string $status, $finished): void
    {
        DB::table('brokerage_sync_runs')->insert([
            'user_id' => $user->id, 'brokerage_connection_id' => $connection, 'provider' => 'snaptrade',
            'status' => $status, 'started_at' => now()->subDays(5), 'finished_at' => $finished,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function analysis(User $user, string $status, $completed): void
    {
        DB::table('portfolio_analysis_runs')->insert([
            'user_id' => $user->id, 'status' => $status, 'started_at' => now()->subDays(5),
            'completed_at' => $completed, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
