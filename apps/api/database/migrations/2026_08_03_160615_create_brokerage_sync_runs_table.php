<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brokerage_sync_runs', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('brokerage_connection_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('provider', 50);
            $table->string('status', 20)->default('running');

            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();

            $table->unsignedInteger('accounts_imported')->default(0);
            $table->unsignedInteger('positions_imported')->default(0);
            $table->unsignedInteger('transactions_imported')->default(0);

            $table->unsignedInteger('duration_ms')->nullable();

            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'brokerage_connection_id',
                'started_at',
            ]);

            $table->index([
                'user_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brokerage_sync_runs');
    }
};