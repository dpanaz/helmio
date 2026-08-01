<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_transactions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('investment_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('security_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('transaction_type');
            $table->date('transaction_date');
            $table->date('settlement_date')->nullable();

            $table->decimal('quantity', 24, 8)->nullable();
            $table->decimal('price', 18, 6)->nullable();
            $table->decimal('gross_amount', 18, 2)->default(0);
            $table->decimal('fees', 18, 2)->default(0);
            $table->decimal('net_amount', 18, 2)->default(0);

            $table->string('currency', 3)->default('USD');
            $table->string('description')->nullable();
            $table->string('provider_transaction_id')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'investment_account_id',
                'transaction_date',
            ]);

            $table->index('transaction_type');

            $table->unique([
                'investment_account_id',
                'provider_transaction_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_transactions');
    }
};
