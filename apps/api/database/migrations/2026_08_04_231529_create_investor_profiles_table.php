<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'investor_profiles',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date(
                    'date_of_birth'
                )->nullable();

                $table->unsignedTinyInteger(
                    'planned_retirement_age'
                )->nullable();

                $table->string(
                    'employment_status',
                    50
                )->nullable();

                $table->decimal(
                    'annual_income',
                    18,
                    2
                )->nullable();

                $table->decimal(
                    'estimated_net_worth',
                    18,
                    2
                )->nullable();

                $table->decimal(
                    'tax_bracket',
                    6,
                    4
                )->nullable();

                $table->string(
                    'investment_experience',
                    30
                )->nullable();

                $table->string(
                    'primary_objective',
                    40
                )->nullable();

                $table->unsignedTinyInteger(
                    'time_horizon_years'
                )->nullable();

                $table->string(
                    'risk_tolerance',
                    40
                )->nullable();

                $table->string(
                    'liquidity_needs',
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
            'investor_profiles'
        );
    }
};