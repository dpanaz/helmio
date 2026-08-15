<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'investor_profiles',
            function (Blueprint $table): void {
                $table->json('target_allocation')
                    ->nullable()
                    ->after('risk_tolerance');
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'investor_profiles',
            function (Blueprint $table): void {
                $table->dropColumn(
                    'target_allocation',
                );
            },
        );
    }
};