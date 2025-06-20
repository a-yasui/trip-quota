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
        Schema::table('expense_settlements', function (Blueprint $table) {
            $table->dropIndex(['travel_plan_id', 'is_settled']);
            $table->dropColumn('is_settled');
            $table->index(['travel_plan_id', 'settled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_settlements', function (Blueprint $table) {
            $table->dropIndex(['travel_plan_id', 'settled_at']);
            $table->boolean('is_settled')->default(false)->comment('精算完了');
            $table->index(['travel_plan_id', 'is_settled']);
        });
    }
};
