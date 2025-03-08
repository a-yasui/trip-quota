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
        Schema::create('expense_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('receiver_member_id')->constrained('members')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3); // ISO 4217 currency code
            $table->boolean('is_settled')->default(false);
            $table->date('settlement_date')->nullable();
            $table->string('settlement_method')->nullable(); // Cash, bank transfer, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate settlements between the same members
            $table->unique(['travel_plan_id', 'payer_member_id', 'receiver_member_id'], 'unique_settlement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_settlements');
    }
};
