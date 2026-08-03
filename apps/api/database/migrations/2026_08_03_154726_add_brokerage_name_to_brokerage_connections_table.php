<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn(
            'brokerage_connections',
            'brokerage_name'
        )) {
            Schema::table(
                'brokerage_connections',
                function (Blueprint $table): void {
                    $table
                        ->string('brokerage_name')
                        ->nullable()
                        ->after('provider_connection_id');
                },
            );
        }

        if (! Schema::hasColumn(
            'brokerage_connections',
            'brokerage_slug'
        )) {
            Schema::table(
                'brokerage_connections',
                function (Blueprint $table): void {
                    $table
                        ->string('brokerage_slug')
                        ->nullable()
                        ->after('brokerage_name');
                },
            );
        }
    }

    public function down(): void
    {
        $columns = [];

        if (Schema::hasColumn(
            'brokerage_connections',
            'brokerage_slug'
        )) {
            $columns[] = 'brokerage_slug';
        }

        if (Schema::hasColumn(
            'brokerage_connections',
            'brokerage_name'
        )) {
            $columns[] = 'brokerage_name';
        }

        if ($columns !== []) {
            Schema::table(
                'brokerage_connections',
                function (Blueprint $table) use ($columns): void {
                    $table->dropColumn($columns);
                },
            );
        }
    }
};