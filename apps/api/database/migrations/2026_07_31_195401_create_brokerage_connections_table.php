<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brokerage_connections', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('provider', 50);
            $table->string('provider_connection_id')->nullable();

            $table->string('brokerage_name')->nullable();
            $table->string('brokerage_slug')->nullable();

            $table->string('status', 30)->default('pending');

            $table->boolean('read_only')->default(true);

            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_sync_started_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('last_successful_sync_at')->nullable();
            $table->timestamp('disabled_at')->nullable();

            $table->text('last_error')->nullable();

            $table->json('capabilities')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique([
                'provider',
                'provider_connection_id',
            ]);

            $table->index([
                'user_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brokerage_connections');
    }
};
