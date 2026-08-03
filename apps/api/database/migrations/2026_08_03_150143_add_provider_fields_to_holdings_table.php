<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holdings', function (Blueprint $table): void {
            $table->string('provider_position_id')
                ->nullable()
                ->after('security_id');

            $table->timestamp('provider_synced_at')
                ->nullable()
                ->after('provider_position_id');

            $table->json('provider_metadata')
                ->nullable()
                ->after('provider_synced_at');

            $table->unique([
                'investment_account_id',
                'provider_position_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('holdings', function (Blueprint $table): void {
            $table->dropUnique([
                'investment_account_id',
                'provider_position_id',
            ]);

            $table->dropColumn([
                'provider_position_id',
                'provider_synced_at',
                'provider_metadata',
            ]);
        });
    }
};
