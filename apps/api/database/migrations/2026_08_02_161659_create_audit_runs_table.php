<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_runs', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('calculated_for_date');
            $table->string('formula_version', 100);

            $table->unsignedTinyInteger('audit_score')->nullable();
            $table->string('audit_grade', 5)->nullable();
            $table->string('audit_label')->nullable();

            $table->decimal('portfolio_value', 18, 2)->default(0);
            $table->decimal('annual_cost', 18, 2)->default(0);
            $table->decimal('potential_savings', 18, 2)->default(0);

            $table->unsignedInteger('issue_count')->default(0);
            $table->unsignedInteger('critical_count')->default(0);
            $table->unsignedInteger('high_count')->default(0);
            $table->unsignedInteger('medium_count')->default(0);
            $table->unsignedInteger('positive_count')->default(0);

            $table->json('category_scores');
            $table->json('audit_details')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'calculated_for_date',
                'formula_version',
            ]);

            $table->index([
                'user_id',
                'calculated_for_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_runs');
    }
};
