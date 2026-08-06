<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'monthly_audit_settings',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();

                $table->boolean('is_enabled')
                    ->default(false);

                $table->unsignedTinyInteger('run_day')
                    ->default(1);

                $table->string('timezone')
                    ->default('America/Chicago');

                $table->foreignId('benchmark_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->boolean('notify_on_completion')
                    ->default(true);

                $table->boolean('notify_on_new_critical')
                    ->default(true);

                $table->boolean('notify_on_score_change')
                    ->default(true);

                $table->unsignedTinyInteger(
                    'score_change_threshold'
                )->default(5);

                $table->timestamp(
                    'last_run_at'
                )->nullable();

                $table->timestamp(
                    'next_run_at'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'monthly_audit_settings'
        );
    }
};