<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holdings', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('investment_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('security_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('quantity', 24, 8)->default(0);
            $table->decimal('price', 18, 6)->default(0);
            $table->decimal('market_value', 18, 2)->default(0);
            $table->decimal('cost_basis', 18, 2)->nullable();
            $table->decimal('unrealized_gain_loss', 18, 2)->nullable();

            $table->decimal('portfolio_weight', 10, 6)->nullable();

            $table->date('as_of_date');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique([
                'investment_account_id',
                'security_id',
                'as_of_date',
            ]);

            $table->index([
                'investment_account_id',
                'as_of_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holdings');
    }
};
