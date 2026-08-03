<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn(
            'investment_transactions',
            'provider_synced_at'
        )) {
            Schema::table(
                'investment_transactions',
                function (Blueprint $table): void {
                    $table
                        ->timestamp('provider_synced_at')
                        ->nullable();
                },
            );
        }

        if (! Schema::hasColumn(
            'investment_transactions',
            'provider_metadata'
        )) {
            Schema::table(
                'investment_transactions',
                function (Blueprint $table): void {
                    $table
                        ->json('provider_metadata')
                        ->nullable();
                },
            );
        }
    }

    public function down(): void
    {
        $columns = [];

        if (Schema::hasColumn(
            'investment_transactions',
            'provider_synced_at'
        )) {
            $columns[] = 'provider_synced_at';
        }

        if (Schema::hasColumn(
            'investment_transactions',
            'provider_metadata'
        )) {
            $columns[] = 'provider_metadata';
        }

        if ($columns !== []) {
            Schema::table(
                'investment_transactions',
                function (Blueprint $table) use ($columns): void {
                    $table->dropColumn($columns);
                },
            );
        }
    }
};