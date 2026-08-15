<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'benchmarks',
            function (Blueprint $table): void {
                $table
                    ->decimal(
                        'expense_ratio',
                        10,
                        8
                    )
                    ->nullable()
                    ->after('currency');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'benchmarks',
            function (Blueprint $table): void {
                $table->dropColumn(
                    'expense_ratio'
                );
            }
        );
    }
};