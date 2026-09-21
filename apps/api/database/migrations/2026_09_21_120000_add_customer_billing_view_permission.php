<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissionId = DB::table('staff_permissions')->insertGetId([
            'name' => 'View customer billing',
            'slug' => 'billing.view',
            'group' => 'billing',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $roleIds = DB::table('staff_roles')
            ->whereIn('slug', ['admin', 'manager', 'support'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('staff_permission_staff_role')->insert([
                'staff_permission_id' => $permissionId,
                'staff_role_id' => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('staff_permissions')
            ->where('slug', 'billing.view')
            ->value('id');

        if ($permissionId) {
            DB::table('staff_permission_staff_role')
                ->where('staff_permission_id', $permissionId)
                ->delete();
            DB::table('staff_permissions')->where('id', $permissionId)->delete();
        }
    }
};
