<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->uuid('visitor_uuid')->index();
            $table->string('session_id')->nullable()->index();

            $table->string('source')->nullable()->index();
            $table->string('medium')->nullable();
            $table->string('campaign')->nullable()->index();
            $table->string('content')->nullable();
            $table->string('term')->nullable();

            $table->string('reddit_click_id')
                ->nullable()
                ->index();

            $table->text('landing_page')->nullable();
            $table->text('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');

            $table->timestamps();

            $table->unique(
                [
                    'visitor_uuid',
                    'source',
                    'campaign',
                    'content',
                ],
                'marketing_visit_attribution_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_visits');
    }
};