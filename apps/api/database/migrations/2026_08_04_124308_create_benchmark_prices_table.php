<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmark_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('benchmark_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('price_date');

            /*
             * Adjusted close should account for dividends and splits whenever
             * the market-data provider supplies it.
             */
            $table->decimal('close_price', 18, 6);
            $table->decimal('adjusted_close_price', 18, 6)->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique([
                'benchmark_id',
                'price_date',
            ]);

            $table->index('price_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmark_prices');
    }
};