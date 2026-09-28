<?php

namespace Tests\Feature;

use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationsFailureAcknowledgementTest extends TestCase
{
    use RefreshDatabase;

    public function test_acknowledging_failed_sync_and_ai_records_preserves_customer_history(): void
    {
        $admin = User::factory()->create();
        $admin->staffRoles()->attach(StaffRole::where('slug', 'admin')->firstOrFail());
        $customer = User::factory()->create();
        $now = now();

        $connection = DB::table('brokerage_connections')->insertGetId([
            'user_id' => $customer->id, 'provider' => 'snaptrade',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $sync = DB::table('brokerage_sync_runs')->insertGetId([
            'brokerage_connection_id' => $connection, 'user_id' => $customer->id,
            'provider' => 'snaptrade', 'status' => 'failed', 'started_at' => $now,
            'error_message' => 'old sync error', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $conversation = DB::table('ask_helmio_conversations')->insertGetId([
            'user_id' => $customer->id, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $ask = DB::table('ask_helmio_messages')->insertGetId([
            'ask_helmio_conversation_id' => $conversation, 'user_id' => $customer->id,
            'role' => 'assistant', 'content' => '', 'status' => 'failed',
            'error_message' => 'old ask error', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $insight = DB::table('ai_insight_runs')->insertGetId([
            'user_id' => $customer->id, 'provider' => 'fake', 'status' => 'failed',
            'context_version' => 'v1', 'prompt_version' => 'v1', 'context_snapshot' => '{}',
            'generated_at' => $now, 'error_message' => 'old insight error',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->actingAs($admin)->post(route('admin.operations.failures.acknowledge'), [
            'sync_ids' => [$sync], 'ask_ids' => [$ask], 'insight_ids' => [$insight],
        ])->assertRedirect()->assertSessionHas('success');

        $this->get(route('admin.operations.index'))
            ->assertOk()
            ->assertSee('No brokerage sync failures.')
            ->assertSee('No AI failures.');

        $this->assertDatabaseHas('brokerage_sync_runs', ['id' => $sync, 'status' => 'failed']);
        $this->assertDatabaseHas('ask_helmio_messages', ['id' => $ask, 'status' => 'failed']);
        $this->assertDatabaseHas('ai_insight_runs', ['id' => $insight, 'status' => 'failed']);
        $this->assertDatabaseCount('operations_failure_acknowledgements', 3);
        $this->assertDatabaseHas('staff_audit_logs', ['event' => 'operations.failures.acknowledged']);
    }

    public function test_manager_cannot_acknowledge_and_completed_records_are_rejected(): void
    {
        $manager = User::factory()->create();
        $manager->staffRoles()->attach(StaffRole::where('slug', 'manager')->firstOrFail());
        $admin = User::factory()->create();
        $admin->staffRoles()->attach(StaffRole::where('slug', 'admin')->firstOrFail());
        $completed = DB::table('ai_insight_runs')->insertGetId([
            'user_id' => $manager->id, 'provider' => 'fake', 'status' => 'completed',
            'context_version' => 'v1', 'prompt_version' => 'v1', 'context_snapshot' => '{}',
            'generated_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($manager)->post(route('admin.operations.failures.acknowledge'), [
            'insight_ids' => [$completed],
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('admin.operations.failures.acknowledge'), [
            'insight_ids' => [$completed],
        ])->assertStatus(409);

        $this->assertDatabaseCount('operations_failure_acknowledgements', 0);
    }
}
