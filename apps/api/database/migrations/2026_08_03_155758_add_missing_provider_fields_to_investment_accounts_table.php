<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing(
            'investment_accounts',
        );

        Schema::table(
            'investment_accounts',
            function (Blueprint $table) use ($columns): void {
                if (! in_array(
                    'brokerage_connection_id',
                    $columns,
                    true,
                )) {
                    $table
                        ->foreignId('brokerage_connection_id')
                        ->nullable()
                        ->constrained()
                        ->nullOnDelete();
                }

                if (! in_array(
                    'provider_account_id',
                    $columns,
                    true,
                )) {
                    $table
                        ->string('provider_account_id')
                        ->nullable();
                }

                if (! in_array(
                    'provider',
                    $columns,
                    true,
                )) {
                    $table
                        ->string('provider', 50)
                        ->nullable();
                }

                if (! in_array(
                    'provider_synced_at',
                    $columns,
                    true,
                )) {
                    $table
                        ->timestamp('provider_synced_at')
                        ->nullable();
                }

                if (! in_array(
                    'provider_metadata',
                    $columns,
                    true,
                )) {
                    $table
                        ->json('provider_metadata')
                        ->nullable();
                }
            },
        );
    }

    public function down(): void
    {
        /*
         * Intentionally empty because this is a corrective migration
         * for an existing development schema.
         */
    }
};