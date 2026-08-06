<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('benchmarks', function (Blueprint $table) {
            $table->boolean('is_default')
                ->default(false)
                ->after('is_active');

            $table->json('metadata')
                ->nullable()
                ->after('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('benchmarks', function (Blueprint $table) {
            $table->dropColumn([
                'is_default',
                'metadata',
            ]);
        });
    }
};