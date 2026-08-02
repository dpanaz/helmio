<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_findings', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('fingerprint', 64);

            $table->string('category', 50);
            $table->string('title');
            $table->text('description');
            $table->text('recommendation')->nullable();

            $table->string('severity', 20);
            $table->string('status', 20)->default('open');

            $table->unsignedTinyInteger('score')->nullable();
            $table->string('route_name')->nullable();

            $table->timestamp('first_detected_at');
            $table->timestamp('last_detected_at');

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->text('review_notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'fingerprint',
            ]);

            $table->index([
                'user_id',
                'status',
                'severity',
            ]);

            $table->index([
                'user_id',
                'category',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_findings');
    }
};
