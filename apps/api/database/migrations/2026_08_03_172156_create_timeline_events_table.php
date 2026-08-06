<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_events', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('event_date');

            $table->string('type',60);

            $table->string('category',40);

            $table->string('severity',20);

            $table->string('headline');

            $table->text('summary')->nullable();

            $table->json('before')->nullable();

            $table->json('after')->nullable();

            $table->json('metrics')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'event_date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};