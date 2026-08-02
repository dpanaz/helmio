<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_transactions', function (Blueprint $table): void {
            $table
                ->decimal('realized_gain_loss', 18, 2)
                ->nullable()
                ->after('net_amount');

            $table
                ->unsignedInteger('holding_period_days')
                ->nullable()
                ->after('realized_gain_loss');

            $table
                ->boolean('is_qualified_dividend')
                ->nullable()
                ->after('holding_period_days');

            $table
                ->boolean('is_tax_exempt')
                ->default(false)
                ->after('is_qualified_dividend');

            $table
                ->decimal('tax_withheld', 18, 2)
                ->default(0)
                ->after('is_tax_exempt');
        });
    }

    public function down(): void
    {
        Schema::table('investment_transactions', function (Blueprint $table): void {
            $table->dropColumn([
                'realized_gain_loss',
                'holding_period_days',
                'is_qualified_dividend',
                'is_tax_exempt',
                'tax_withheld',
            ]);
        });
    }
};
