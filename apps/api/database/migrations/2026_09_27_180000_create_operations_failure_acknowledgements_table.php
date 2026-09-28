<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations_failure_acknowledgements', function (Blueprint $table): void {
            $table->id();
            $table->string('failure_type', 16);
            $table->unsignedBigInteger('failure_id');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at');

            $table->unique(['failure_type', 'failure_id'], 'operations_failure_ack_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations_failure_acknowledgements');
    }
};
