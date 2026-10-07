<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('onboarding_completed_at')->nullable();
        });
        // Customers who already met the old completion rules keep their access.
        DB::table('users')->whereExists(function ($query): void {
            $query->selectRaw('1')->from('investor_profiles')->whereColumn('investor_profiles.user_id', 'users.id');
        })->where(function ($query): void {
            $query->whereExists(function ($accounts): void {
                $accounts->selectRaw('1')->from('investment_accounts')->whereColumn('investment_accounts.user_id', 'users.id');
            })->orWhereExists(function ($connections): void {
                $connections->selectRaw('1')->from('brokerage_connections')->whereColumn('brokerage_connections.user_id', 'users.id')
                    ->whereNotIn('status', ['disabled', 'disconnected', 'failed']);
            });
        })->update(['onboarding_completed_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('onboarding_completed_at'));
    }
};
