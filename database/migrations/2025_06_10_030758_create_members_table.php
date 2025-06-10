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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('ユーザが未登録の場合null');
            $table->foreignId('account_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name')->comment('表示用の名前');
            $table->string('email')->nullable()->comment('招待用メールアドレス');
            $table->boolean('is_confirmed')->default(false)->comment('参加確認済み');
            $table->timestamps();
            
            $table->index(['travel_plan_id', 'user_id']);
            $table->index(['travel_plan_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};