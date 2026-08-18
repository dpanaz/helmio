<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_runs', function (Blueprint $table) {
            $table->index(
                ['user_id', 'calculated_for_date', 'id'],
                'audit_runs_user_date_id_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('audit_runs', function (Blueprint $table) {
            $table->dropIndex('audit_runs_user_date_id_index');
        });
    }
};