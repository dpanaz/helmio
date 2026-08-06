<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing(
            'brokerage_connections',
        );

        Schema::table(
            'brokerage_connections',
            function (Blueprint $table) use ($columns): void {
                if (! in_array('brokerage_name', $columns, true)) {
                    $table
                        ->string('brokerage_name')
                        ->nullable();
                }

                if (! in_array('brokerage_slug', $columns, true)) {
                    $table
                        ->string('brokerage_slug')
                        ->nullable();
                }

                if (! in_array('read_only', $columns, true)) {
                    $table
                        ->boolean('read_only')
                        ->default(true);
                }

                if (! in_array('connected_at', $columns, true)) {
                    $table
                        ->timestamp('connected_at')
                        ->nullable();
                }

                if (! in_array('last_sync_started_at', $columns, true)) {
                    $table
                        ->timestamp('last_sync_started_at')
                        ->nullable();
                }

                if (! in_array('last_synced_at', $columns, true)) {
                    $table
                        ->timestamp('last_synced_at')
                        ->nullable();
                }

                if (! in_array('last_successful_sync_at', $columns, true)) {
                    $table
                        ->timestamp('last_successful_sync_at')
                        ->nullable();
                }

                if (! in_array('disabled_at', $columns, true)) {
                    $table
                        ->timestamp('disabled_at')
                        ->nullable();
                }

                if (! in_array('last_error', $columns, true)) {
                    $table
                        ->text('last_error')
                        ->nullable();
                }

                if (! in_array('capabilities', $columns, true)) {
                    $table
                        ->json('capabilities')
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
         *
         * This is a corrective migration for an existing schema.
         * Dropping columns here could remove fields created by older
         * migrations.
         */
    }
};