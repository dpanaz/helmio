<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('billing_settings')->insert([
            ['key' => 'monthly_amount', 'value' => '19.95', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'annual_amount', 'value' => '199.95', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'monthly_price_id', 'value' => config('services.stripe.prices.monthly'), 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'annual_price_id', 'value' => config('services.stripe.prices.annual'), 'created_at' => $now, 'updated_at' => $now],
        ]);

        $permissionId = DB::table('staff_permissions')->insertGetId([
            'name' => 'Manage subscription pricing',
            'slug' => 'billing.manage',
            'group' => 'billing',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $adminRoleId = DB::table('staff_roles')
            ->where('slug', 'admin')
            ->value('id');

        if ($adminRoleId) {
            DB::table('staff_permission_staff_role')->insert([
                'staff_permission_id' => $permissionId,
                'staff_role_id' => $adminRoleId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('staff_permissions')
            ->where('slug', 'billing.manage')
            ->value('id');

        if ($permissionId) {
            DB::table('staff_permission_staff_role')
                ->where('staff_permission_id', $permissionId)
                ->delete();
            DB::table('staff_permissions')->where('id', $permissionId)->delete();
        }

        Schema::dropIfExists('billing_settings');
    }
};
