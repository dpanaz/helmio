<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'securities',
            function (Blueprint $table): void {
                $table->string('comparison_group')
                    ->nullable()
                    ->after('category');

                $table->string('benchmark_name')
                    ->nullable()
                    ->after('comparison_group');

                $table->boolean('is_index_fund')
                    ->default(false)
                    ->after('benchmark_name');

                $table->decimal(
                    'trailing_1y_return',
                    10,
                    6
                )
                    ->nullable()
                    ->after('is_index_fund');

                $table->decimal(
                    'trailing_3y_annualized_return',
                    10,
                    6
                )
                    ->nullable()
                    ->after('trailing_1y_return');

                $table->decimal(
                    'trailing_5y_annualized_return',
                    10,
                    6
                )
                    ->nullable()
                    ->after(
                        'trailing_3y_annualized_return'
                    );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'securities',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'comparison_group',
                    'benchmark_name',
                    'is_index_fund',
                    'trailing_1y_return',
                    'trailing_3y_annualized_return',
                    'trailing_5y_annualized_return',
                ]);
            }
        );
    }
};