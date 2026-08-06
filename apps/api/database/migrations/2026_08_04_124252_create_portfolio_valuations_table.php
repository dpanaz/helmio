<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_valuations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('investment_account_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->date('valuation_date');

            $table->decimal('market_value', 18, 2);
            $table->decimal('cash_value', 18, 2)->default(0);

            /*
             * External cash flows are separated from investment performance.
             *
             * Deposits should be positive.
             * Withdrawals should be negative.
             */
            $table->decimal('net_cash_flow', 18, 2)->default(0);

            $table->string('currency', 3)->default('USD');

            $table->string('source')->default('calculated');
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'user_id',
                    'investment_account_id',
                    'valuation_date',
                ],
                'portfolio_valuation_unique'
            );

            $table->index([
                'user_id',
                'valuation_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_valuations');
    }
};