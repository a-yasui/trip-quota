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
        Schema::create('expense_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->boolean('is_participating')->default(true)->comment('割り勘に参加するか');
            $table->decimal('amount', 10, 2)->nullable()->comment('金額調整がある場合の個別金額');
            $table->boolean('is_confirmed')->default(false)->comment('メンバーが確認済みか');
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
    }
};