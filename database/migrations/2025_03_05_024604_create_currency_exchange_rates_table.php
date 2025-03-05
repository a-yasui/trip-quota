<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3); // ISO 4217 currency code
            $table->string('to_currency', 3); // ISO 4217 currency code
            $table->decimal('rate', 10, 6);
            $table->date('rate_date');
            $table->string('source')->nullable(); // Source of the exchange rate data
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['from_currency', 'to_currency', 'rate_date'], 'currency_rates_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_exchange_rates');
    }
};
