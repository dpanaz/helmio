<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'marketing_conversions',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('marketing_visit_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('conversion_id')->unique();
                $table->string('type')->index();

                $table->decimal('value', 12, 2)->nullable();
                $table->string('currency', 3)->default('USD');

                $table->timestamp('converted_at');

                $table->string('reddit_status')
                    ->default('pending')
                    ->index();

                $table->unsignedSmallInteger('reddit_attempts')
                    ->default(0);

                $table->timestamp('reddit_sent_at')->nullable();
                $table->text('reddit_error')->nullable();
                $table->json('metadata')->nullable();

                $table->timestamps();
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_conversions');
    }
};