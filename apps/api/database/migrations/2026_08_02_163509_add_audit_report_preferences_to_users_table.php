<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('monthly_audit_enabled')
                ->default(false)
                ->after('email');

            $table->string('monthly_audit_email')
                ->nullable()
                ->after('monthly_audit_enabled');

            $table->unsignedTinyInteger('monthly_audit_day')
                ->default(1)
                ->after('monthly_audit_email');

            $table->time('monthly_audit_time')
                ->default('08:00:00')
                ->after('monthly_audit_day');

            $table->string('timezone')
                ->default('America/Chicago')
                ->after('monthly_audit_time');

            $table->timestamp('last_monthly_audit_sent_at')
                ->nullable()
                ->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'monthly_audit_enabled',
                'monthly_audit_email',
                'monthly_audit_day',
                'monthly_audit_time',
                'timezone',
                'last_monthly_audit_sent_at',
            ]);
        });
    }
};
