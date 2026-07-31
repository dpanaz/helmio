<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brokerage_connection_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('institution_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('account_type');
            $table->string('account_number_mask', 8)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('current_value', 18, 2)->default(0);
            $table->decimal('cash_value', 18, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('account_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_accounts');
    }
};
