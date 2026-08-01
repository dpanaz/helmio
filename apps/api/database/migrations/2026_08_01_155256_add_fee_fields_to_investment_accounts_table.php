<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_accounts', function (Blueprint $table): void {
            $table
                ->decimal('annual_advisory_fee_rate', 8, 6)
                ->nullable()
                ->after('cash_value');

            $table
                ->decimal('annual_account_fee', 18, 2)
                ->default(0)
                ->after('annual_advisory_fee_rate');

            $table
                ->boolean('advisory_fee_applies_to_cash')
                ->default(true)
                ->after('annual_account_fee');
        });
    }

    public function down(): void
    {
        Schema::table('investment_accounts', function (Blueprint $table): void {
            $table->dropColumn([
                'annual_advisory_fee_rate',
                'annual_account_fee',
                'advisory_fee_applies_to_cash',
            ]);
        });
    }
};
