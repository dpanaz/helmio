<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_constituents', function (Blueprint $table) {
            $table->id();

            /*
             * The ETF or mutual fund owned by the investor.
             */
            $table->foreignId('fund_security_id')
                ->constrained('securities')
                ->cascadeOnDelete();

            /*
             * The underlying company/security owned by the fund.
             */
            $table->foreignId('constituent_security_id')
                ->constrained('securities')
                ->cascadeOnDelete();

            /*
             * Percentage of the fund represented by this constituent.
             *
             * Example:
             * 0.071 = 7.1%
             */
            $table->decimal(
                'weight',
                12,
                10
            );

            $table->date(
                'as_of_date'
            )->nullable();

            $table->string(
                'source',
                50
            )->nullable();

            $table->json(
                'metadata'
            )->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'fund_security_id',
                    'constituent_security_id',
                    'as_of_date',
                ],
                'fund_constituent_unique'
            );

            $table->index(
                'fund_security_id'
            );

            $table->index(
                'constituent_security_id'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'fund_constituents'
        );
    }
};