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
            $table->foreignId('travel_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade')->comment('班グループ内での割り勘');
            $table->foreignId('paid_by_member_id')->constrained('members')->onDelete('cascade')->comment('支払いをしたメンバー');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->comment('支払い金額');
            $table->string('currency', 3)->default('JPY')->comment('支払い通貨');
            $table->date('expense_date')->comment('支払い日');
            $table->boolean('is_split_confirmed')->default(false)->comment('割り勘計算確定済み');
            $table->timestamps();

            $table->index(['travel_plan_id', 'expense_date']);
            $table->index(['group_id', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
