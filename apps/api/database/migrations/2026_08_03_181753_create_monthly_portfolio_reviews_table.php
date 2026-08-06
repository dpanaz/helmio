<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'monthly_portfolio_reviews',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->date('period_start');
                $table->date('period_end');

                $table->string('status', 20)
                    ->default('completed');

                $table->string('headline')->nullable();
                $table->text('summary')->nullable();

                $table->decimal(
                    'starting_portfolio_value',
                    18,
                    2,
                )->nullable();

                $table->decimal(
                    'ending_portfolio_value',
                    18,
                    2,
                )->nullable();

                $table->decimal(
                    'portfolio_value_change',
                    18,
                    2,
                )->nullable();

                $table->decimal(
                    'portfolio_value_change_rate',
                    12,
                    8,
                )->nullable();

                $table->integer(
                    'starting_helm_score',
                )->nullable();

                $table->integer(
                    'ending_helm_score',
                )->nullable();

                $table->integer(
                    'helm_score_change',
                )->nullable();

                $table->string(
                    'starting_audit_grade',
                    10,
                )->nullable();

                $table->string(
                    'ending_audit_grade',
                    10,
                )->nullable();

                $table->decimal(
                    'starting_annual_cost',
                    18,
                    2,
                )->nullable();

                $table->decimal(
                    'ending_annual_cost',
                    18,
                    2,
                )->nullable();

                $table->decimal(
                    'annual_cost_change',
                    18,
                    2,
                )->nullable();

                $table->unsignedInteger(
                    'event_count',
                )->default(0);

                $table->unsignedInteger(
                    'positive_event_count',
                )->default(0);

                $table->unsignedInteger(
                    'attention_event_count',
                )->default(0);

                $table->json('key_changes')->nullable();
                $table->json('positive_changes')->nullable();
                $table->json('review_items')->nullable();
                $table->json('limitations')->nullable();
                $table->json('data_snapshot')->nullable();

                $table->timestamp('generated_at');
                $table->timestamps();

                $table->unique([
                    'user_id',
                    'period_start',
                    'period_end',
                ]);

                $table->index([
                    'user_id',
                    'generated_at',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'monthly_portfolio_reviews',
        );
    }
};