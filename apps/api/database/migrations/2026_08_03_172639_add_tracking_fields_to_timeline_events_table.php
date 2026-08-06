<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = Schema::getColumnListing(
            'timeline_events',
        );

        Schema::table(
            'timeline_events',
            function (Blueprint $table) use ($columns): void {
                if (! in_array('fingerprint', $columns, true)) {
                    $table
                        ->string('fingerprint', 64)
                        ->nullable();
                }

                if (! in_array('source_type', $columns, true)) {
                    $table
                        ->string('source_type', 60)
                        ->nullable();
                }

                if (! in_array('source_id', $columns, true)) {
                    $table
                        ->unsignedBigInteger('source_id')
                        ->nullable();
                }

                if (! in_array('route_name', $columns, true)) {
                    $table
                        ->string('route_name')
                        ->nullable();
                }

                if (! in_array('detected_at', $columns, true)) {
                    $table
                        ->timestamp('detected_at')
                        ->nullable();
                }
            },
        );
    }

    public function down(): void
    {
        // Intentionally empty because this corrects an existing schema.
    }
};