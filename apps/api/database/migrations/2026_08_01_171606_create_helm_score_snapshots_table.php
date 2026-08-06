<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helm_score_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('overall_score')->nullable();
            $table->unsignedTinyInteger('cost_score')->nullable();
            $table->unsignedTinyInteger('diversification_score')->nullable();
            $table->unsignedTinyInteger('performance_score')->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->unsignedTinyInteger('trading_score')->nullable();
            $table->unsignedTinyInteger('tax_score')->nullable();

            $table->decimal('data_completeness', 5, 4)->default(0);
            $table->json('score_details');
            $table->string('formula_version');
            $table->date('calculated_for_date');
            $table->timestamps();

            $table->unique(
    [
        'user_id',
        'calculated_for_date',
        'formula_version',
    ],
    'ux_helm_score_user_date_version',
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helm_score_snapshots');
    }
};
