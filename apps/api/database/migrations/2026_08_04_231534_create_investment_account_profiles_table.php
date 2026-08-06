<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'investment_account_profiles',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'investment_account_id'
                )
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'purpose',
                    50
                )->nullable();

                $table->date(
                    'target_date'
                )->nullable();

                $table->string(
                    'risk_tolerance_override',
                    40
                )->nullable();

                $table->string(
                    'objective_override',
                    40
                )->nullable();

                $table->unsignedTinyInteger(
                    'time_horizon_years_override'
                )->nullable();

                $table->string(
                    'liquidity_needs_override',
                    40
                )->nullable();

                $table->text(
                    'notes'
                )->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'investment_account_profiles'
        );
    }
};