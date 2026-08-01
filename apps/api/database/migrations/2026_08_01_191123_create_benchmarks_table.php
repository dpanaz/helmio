<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmarks', function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->string('symbol')->nullable()->unique();
            $table->string('benchmark_type')->default('index');
            $table->string('currency', 3)->default('USD');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index([
                'benchmark_type',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmarks');
    }
};
