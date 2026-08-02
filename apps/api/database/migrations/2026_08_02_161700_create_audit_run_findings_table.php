<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_run_findings', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('audit_run_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('audit_finding_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('fingerprint', 64);
            $table->string('category', 50);
            $table->string('title');
            $table->text('description');
            $table->text('recommendation')->nullable();

            $table->string('severity', 20);
            $table->string('status', 20);
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('route_name')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique([
                'audit_run_id',
                'fingerprint',
            ]);

            $table->index([
                'audit_run_id',
                'severity',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_run_findings');
    }
};
