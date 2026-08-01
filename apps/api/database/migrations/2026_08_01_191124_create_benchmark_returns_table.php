<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmark_returns', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('benchmark_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('return_date');

            /*
             * Store decimal returns:
             * 1.25% = 0.0125
             */
            $table->decimal('period_return', 12, 8);

            $table->string('period_type')->default('monthly');
            $table->string('source')->default('manual');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique([
                'benchmark_id',
                'return_date',
                'period_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmark_returns');
    }
};
