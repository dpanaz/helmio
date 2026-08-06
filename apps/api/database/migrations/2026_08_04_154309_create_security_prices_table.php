<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('security_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('price_date');

            $table->decimal('open_price', 18, 6)->nullable();
            $table->decimal('high_price', 18, 6)->nullable();
            $table->decimal('low_price', 18, 6)->nullable();
            $table->decimal('close_price', 18, 6);

            $table->decimal(
                'adjusted_close_price',
                18,
                6
            )->nullable();

            $table->unsignedBigInteger('volume')->nullable();

            $table->string('currency', 3)->default('USD');

            $table->string('source')
                ->default('manual');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'security_id',
                    'price_date',
                ],
                'security_price_unique'
            );

            $table->index([
                'price_date',
                'security_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_prices');
    }
};