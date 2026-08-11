<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holdings', function (Blueprint $table) {
            $table->dropUnique(
                'holdings_investment_account_id_provider_position_id_unique'
            );

            $table->unique(
                [
                    'investment_account_id',
                    'provider_position_id',
                    'as_of_date',
                ],
                'holdings_account_provider_position_date_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('holdings', function (Blueprint $table) {
            $table->dropUnique(
                'holdings_account_provider_position_date_unique'
            );

            $table->unique(
                [
                    'investment_account_id',
                    'provider_position_id',
                ],
                'holdings_investment_account_id_provider_position_id_unique',
            );
        });
    }
};