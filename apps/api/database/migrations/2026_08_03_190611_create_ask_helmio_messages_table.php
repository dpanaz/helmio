<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'ask_helmio_messages',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'ask_helmio_conversation_id',
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('role', 20);

                $table->longText('content');

                $table->string('provider', 50)
                    ->nullable();

                $table->string('model', 100)
                    ->nullable();

                $table->string('status', 20)
                    ->default('completed');

                $table->string('confidence', 20)
                    ->nullable();

                $table->json('citations')->nullable();
                $table->json('limitations')->nullable();
                $table->json('context_snapshot')->nullable();
                $table->json('response_payload')->nullable();

                $table->unsignedInteger('input_tokens')
                    ->nullable();

                $table->unsignedInteger('output_tokens')
                    ->nullable();

                $table->timestamp('generated_at')
                    ->nullable();

                $table->text('error_message')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'ask_helmio_conversation_id',
                    'created_at',
                ]);

                $table->index([
                    'user_id',
                    'role',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ask_helmio_messages',
        );
    }
};