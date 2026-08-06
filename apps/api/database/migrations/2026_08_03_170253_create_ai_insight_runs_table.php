<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_insight_runs', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('provider', 50);
            $table->string('model', 100)->nullable();
            $table->string('status', 20)->default('completed');

            $table->string('context_version', 100);
            $table->string('prompt_version', 100);

            $table->string('headline')->nullable();
            $table->text('summary')->nullable();

            $table->json('priorities')->nullable();
            $table->json('positive_changes')->nullable();
            $table->json('limitations')->nullable();

            $table->json('context_snapshot');
            $table->json('response_payload')->nullable();

            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();

            $table->timestamp('generated_at');
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'generated_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_insight_runs');
    }
};