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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('provider')->default('manual');
            $table->string('provider_connection_id')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('requires_attention_at')->nullable();
            $table->text('status_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brokerage_connections');
    }
};
