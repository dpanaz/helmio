<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'portfolio_state_snapshots',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('brokerage_connection_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId('brokerage_sync_run_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('source', 30)
                    ->default('brokerage_sync');

                $table->timestamp('captured_at');

                $table->decimal(
                    'portfolio_value',
                    18,
                    2,
                )->default(0);

                $table->decimal(
                    'cash_value',
                    18,
                    2,
                )->default(0);

                $table->decimal(
                    'invested_value',
                    18,
                    2,
                )->default(0);

                $table->unsignedInteger(
                    'account_count',
                )->default(0);

                $table->unsignedInteger(
                    'holding_count',
                )->default(0);

                $table->json('metadata')->nullable();

                $table->timestamps();

                $table->index([
                    'user_id',
                    'captured_at',
                ]);

                $table->index(
    [
        'brokerage_connection_id',
        'captured_at',
    ],
    'ix_port_state_conn_captured',
);

                $table->unique([
                    'brokerage_sync_run_id',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'portfolio_state_snapshots',
        );
    }
};