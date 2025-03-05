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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_member_id')->constrained('members')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('JPY'); // ISO 4217 currency code
            $table->string('description');
            $table->date('expense_date');
            $table->string('category')->nullable(); // Food, transportation, accommodation, etc.
            $table->text('notes')->nullable();
            $table->boolean('is_settled')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Pivot table for expense members (who shares this expense)
        Schema::create('expense_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('share_amount', 10, 2)->nullable(); // Custom amount if not split evenly
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            
            $table->unique(['expense_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_member');
        Schema::dropIfExists('expenses');
    }
};
