<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('securities', function (Blueprint $table): void {
            $table->id();
            $table->string('symbol')->nullable()->index();
            $table->string('name');
            $table->string('security_type');
            $table->string('cusip')->nullable()->index();
            $table->string('isin')->nullable()->index();
            $table->string('currency', 3)->default('USD');

            $table->string('asset_class')->nullable();
            $table->string('sector')->nullable();
            $table->string('category')->nullable();

            $table->decimal('expense_ratio', 8, 6)->nullable();
            $table->decimal('last_price', 18, 6)->nullable();
            $table->timestamp('price_as_of')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['symbol', 'security_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('securities');
    }
};
