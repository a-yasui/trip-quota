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
        Schema::create('travel_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('DB内で唯一の名前（UUID）');
            $table->string('plan_name');
            $table->foreignId('creator_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_user_id')->constrained('users')->onDelete('cascade')->comment('削除権限を持つユーザー');
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->string('timezone', 50)->default('Asia/Tokyo');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_plans');
    }
};
