<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brokerage_provider_users', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('provider', 50);
            $table->string('provider_user_id');

            /*
             * Store encrypted text, never plaintext credentials.
             * Laravel encryption increases stored length, so TEXT is used.
             */
            $table->text('provider_user_secret')->nullable();

            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique([
                'user_id',
                'provider',
            ]);

            $table->unique([
                'provider',
                'provider_user_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brokerage_provider_users');
    }
};
