<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('endpoint');

            $table->text('public_key');

            $table->text('auth_token');

            $table->string(
                'content_encoding',
                50,
            )->default('aes128gcm');

            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'endpoint',
            ]);

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};