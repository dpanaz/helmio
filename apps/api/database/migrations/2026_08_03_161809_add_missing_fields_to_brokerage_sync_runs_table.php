<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing(
            'brokerage_sync_runs',
        );

        Schema::table(
            'brokerage_sync_runs',
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
                        ->cascadeOnDelete();
                }

                if (! in_array(
                    'user_id',
                    $columns,
                    true,
                )) {
                    $table
                        ->foreignId('user_id')
                        ->nullable()
                        ->constrained()
                        ->cascadeOnDelete();
                }

                if (! in_array('provider', $columns, true)) {
                    $table
                        ->string('provider', 50)
                        ->nullable();
                }

                if (! in_array('status', $columns, true)) {
                    $table
                        ->string('status', 20)
                        ->default('running');
                }

                if (! in_array('started_at', $columns, true)) {
                    $table
                        ->timestamp('started_at')
                        ->nullable();
                }

                if (! in_array('finished_at', $columns, true)) {
                    $table
                        ->timestamp('finished_at')
                        ->nullable();
                }

                if (! in_array('accounts_imported', $columns, true)) {
                    $table
                        ->unsignedInteger('accounts_imported')
                        ->default(0);
                }

                if (! in_array('positions_imported', $columns, true)) {
                    $table
                        ->unsignedInteger('positions_imported')
                        ->default(0);
                }

                if (! in_array('transactions_imported', $columns, true)) {
                    $table
                        ->unsignedInteger('transactions_imported')
                        ->default(0);
                }

                if (! in_array('duration_ms', $columns, true)) {
                    $table
                        ->unsignedInteger('duration_ms')
                        ->nullable();
                }

                if (! in_array('error_message', $columns, true)) {
                    $table
                        ->text('error_message')
                        ->nullable();
                }

                if (! in_array('metadata', $columns, true)) {
                    $table
                        ->json('metadata')
                        ->nullable();
                }
            },
        );
    }

    public function down(): void
    {
        /*
         * Intentionally left empty.
         * This is a corrective migration for an existing table.
         */
    }
};