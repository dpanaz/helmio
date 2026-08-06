<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'portfolio_state_snapshot_holdings',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId(
                    'portfolio_state_snapshot_id',
                )
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId(
                    'investment_account_id',
                )
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId('security_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string(
                    'holding_key',
                    255,
                );

                $table->string('symbol', 50)
                    ->nullable();

                $table->string('name');

                $table->string(
                    'security_type',
                    50,
                )->nullable();

                $table->string(
                    'asset_class',
                    100,
                )->nullable();

                $table->string(
                    'sector',
                    100,
                )->nullable();

                $table->decimal(
                    'quantity',
                    24,
                    8,
                )->default(0);

                $table->decimal(
                    'price',
                    18,
                    6,
                )->nullable();

                $table->decimal(
                    'market_value',
                    18,
                    2,
                )->default(0);

                $table->decimal(
                    'cost_basis',
                    18,
                    2,
                )->nullable();

                $table->decimal(
                    'portfolio_weight',
                    12,
                    8,
                )->nullable();

                $table->json('metadata')->nullable();

                $table->timestamps();

                $table->unique([
                    'portfolio_state_snapshot_id',
                    'holding_key',
                ]);

                $table->index([
                    'security_id',
                    'portfolio_state_snapshot_id',
                ]);
            },
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'portfolio_state_snapshot_holdings',
        );
    }
};