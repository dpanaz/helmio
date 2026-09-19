<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->index();
            $table->timestamps();
        });

        Schema::create('staff_role_user', function (Blueprint $table): void {
            $table->foreignId('staff_role_id')
                ->constrained('staff_roles')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['staff_role_id', 'user_id']);
        });

        Schema::create('staff_permission_staff_role', function (Blueprint $table): void {
            $table->foreignId('staff_permission_id')
                ->constrained('staff_permissions')
                ->cascadeOnDelete();
            $table->foreignId('staff_role_id')
                ->constrained('staff_roles')
                ->cascadeOnDelete();

            $table->primary([
                'staff_permission_id',
                'staff_role_id',
            ]);
        });

        Schema::create('staff_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('customer_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('event')->index();
            $table->string('route_name')->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('request_path')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['actor_user_id', 'created_at']);
            $table->index(['customer_user_id', 'created_at']);
        });

        $now = now();

        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Full Helmio operations access.'],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Business, customer support, and reporting access.'],
            ['name' => 'Support', 'slug' => 'support', 'description' => 'Customer support and read-only customer viewing.'],
        ];

        foreach ($roles as $role) {
            DB::table('staff_roles')->insert([
                ...$role,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissions = [
            ['name' => 'View business dashboard', 'slug' => 'dashboard.view', 'group' => 'business'],
            ['name' => 'View customers', 'slug' => 'customers.view', 'group' => 'customers'],
            ['name' => 'View as customer', 'slug' => 'customers.preview', 'group' => 'customers'],
            ['name' => 'View support inbox', 'slug' => 'support.view', 'group' => 'support'],
            ['name' => 'Manage support conversations', 'slug' => 'support.manage', 'group' => 'support'],
            ['name' => 'View marketing analytics', 'slug' => 'marketing.view', 'group' => 'marketing'],
            ['name' => 'View operations health', 'slug' => 'operations.view', 'group' => 'operations'],
            ['name' => 'View staff audit log', 'slug' => 'audit.view', 'group' => 'security'],
            ['name' => 'Manage staff access', 'slug' => 'staff.manage', 'group' => 'security'],
        ];

        foreach ($permissions as $permission) {
            DB::table('staff_permissions')->insert([
                ...$permission,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleIds = DB::table('staff_roles')->pluck('id', 'slug');
        $permissionIds = DB::table('staff_permissions')->pluck('id', 'slug');

        $assignments = [
            'admin' => array_keys($permissionIds->all()),
            'manager' => [
                'dashboard.view',
                'customers.view',
                'customers.preview',
                'support.view',
                'support.manage',
                'marketing.view',
                'operations.view',
                'audit.view',
            ],
            'support' => [
                'customers.view',
                'customers.preview',
                'support.view',
                'support.manage',
            ],
        ];

        foreach ($assignments as $roleSlug => $permissionSlugs) {
            foreach ($permissionSlugs as $permissionSlug) {
                DB::table('staff_permission_staff_role')->insert([
                    'staff_permission_id' => $permissionIds[$permissionSlug],
                    'staff_role_id' => $roleIds[$roleSlug],
                ]);
            }
        }

        DB::table('users')
            ->where('is_admin', true)
            ->orderBy('id')
            ->each(function (object $user) use ($roleIds, $now): void {
                DB::table('staff_role_user')->insertOrIgnore([
                    'staff_role_id' => $roleIds['admin'],
                    'user_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_audit_logs');
        Schema::dropIfExists('staff_permission_staff_role');
        Schema::dropIfExists('staff_role_user');
        Schema::dropIfExists('staff_permissions');
        Schema::dropIfExists('staff_roles');
    }
};
