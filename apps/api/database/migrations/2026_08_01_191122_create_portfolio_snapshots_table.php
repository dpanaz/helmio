<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_snapshots', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('investment_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('snapshot_date');

            $table->decimal('ending_value', 18, 2);
            $table->decimal('cash_value', 18, 2)->default(0);

            /*
             * External flows only:
             * deposits, withdrawals and account transfers.
             *
             * Dividends, interest, fees and trades remain part of return.
             */
            $table->decimal('external_cash_flow', 18, 2)->default(0);

            $table->string('source')->default('manual');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique([
                'investment_account_id',
                'snapshot_date',
            ]);

            $table->index('snapshot_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_snapshots');
    }
};
