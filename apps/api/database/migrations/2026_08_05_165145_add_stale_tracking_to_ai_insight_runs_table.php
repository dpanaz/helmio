<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'ai_insight_runs',
            function (Blueprint $table): void {
                $table->boolean('is_stale')
                    ->default(false)
                    ->after('status');

                $table->timestamp('stale_at')
                    ->nullable()
                    ->after('is_stale');

                $table->string('stale_reason')
                    ->nullable()
                    ->after('stale_at');

                $table->decimal(
                    'portfolio_value_at_generation',
                    18,
                    2
                )
                    ->nullable()
                    ->after('stale_reason');

                $table->unsignedInteger(
                    'account_count_at_generation'
                )
                    ->nullable()
                    ->after(
                        'portfolio_value_at_generation'
                    );

                $table->timestamp(
                    'portfolio_last_updated_at'
                )
                    ->nullable()
                    ->after(
                        'account_count_at_generation'
                    );

                $table->index([
                    'user_id',
                    'is_stale',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'ai_insight_runs',
            function (Blueprint $table): void {
                $table->dropIndex([
                    'user_id',
                    'is_stale',
                ]);

                $table->dropColumn([
                    'is_stale',
                    'stale_at',
                    'stale_reason',
                    'portfolio_value_at_generation',
                    'account_count_at_generation',
                    'portfolio_last_updated_at',
                ]);
            }
        );
    }
};
