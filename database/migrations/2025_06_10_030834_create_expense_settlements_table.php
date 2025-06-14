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
            $table->foreignId('travel_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('payer_member_id')->constrained('members')->onDelete('cascade')->comment('支払う人');
            $table->foreignId('payee_member_id')->constrained('members')->onDelete('cascade')->comment('受け取る人');
            $table->decimal('amount', 10, 2)->comment('精算金額');
            $table->string('currency', 3)->default('JPY')->comment('精算通貨');
            $table->boolean('is_settled')->default(false)->comment('精算完了');
            $table->timestamp('settled_at')->nullable()->comment('精算完了日時');
            $table->timestamps();

            $table->index(['travel_plan_id', 'is_settled']);
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
