<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_analysis_runs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('brokerage_connection_id')
                ->nullable()
                ->constrained('brokerage_connections')
                ->nullOnDelete();

            $table->string('trigger', 50)
                ->default('manual');

            $table->string('status', 50)
                ->default('pending');

            $table->string('current_step', 100)
                ->nullable();

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamp('failed_at')
                ->nullable();

            $table->text('error_message')
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'status',
            ]);

            $table->index([
                'user_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'portfolio_analysis_runs',
        );
    }
};