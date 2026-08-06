<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasColumn(
                'securities',
                'provider_security_id'
            )
        ) {
            return;
        }

        Schema::table(
            'securities',
            function (Blueprint $table): void {
                $table->string(
                    'provider_security_id'
                )
                    ->nullable()
                    ->after('id');

                $table->index(
                    'provider_security_id',
                    'securities_provider_security_id_index'
                );
            }
        );
    }

    public function down(): void
    {
        if (
            ! Schema::hasColumn(
                'securities',
                'provider_security_id'
            )
        ) {
            return;
        }

        Schema::table(
            'securities',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'securities_provider_security_id_index'
                );

                $table->dropColumn(
                    'provider_security_id'
                );
            }
        );
    }
};