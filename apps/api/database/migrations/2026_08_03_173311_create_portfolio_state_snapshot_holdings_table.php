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

                // Portfolio State Snapshot
                $table->unsignedBigInteger(
                    'portfolio_state_snapshot_id'
                );

                $table->foreign(
                    'portfolio_state_snapshot_id',
                    'fk_pssh_snapshot'
                )
                    ->references('id')
                    ->on('portfolio_state_snapshots')
                    ->cascadeOnDelete();

                // Investment Account
                $table->unsignedBigInteger(
                    'investment_account_id'
                )->nullable();

                $table->foreign(
                    'investment_account_id',
                    'fk_pssh_account'
                )
                    ->references('id')
                    ->on('investment_accounts')
                    ->nullOnDelete();

                // Security
                $table->unsignedBigInteger(
                    'security_id'
                )->nullable();

                $table->foreign(
                    'security_id',
                    'fk_pssh_security'
                )
                    ->references('id')
                    ->on('securities')
                    ->nullOnDelete();

                $table->string(
                    'holding_key',
                    255,
                );

                $table->string(
                    'symbol',
                    50,
                )->nullable();

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

                $table->json('metadata')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'portfolio_state_snapshot_id',
                        'holding_key',
                    ],
                    'ux_pssh_snapshot_holding'
                );

                $table->index(
                    [
                        'security_id',
                        'portfolio_state_snapshot_id',
                    ],
                    'ix_pssh_security_snapshot'
                );
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