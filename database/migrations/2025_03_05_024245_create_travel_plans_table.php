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
            $table->string('title');
            $table->foreignId('creator_id')->constrained('users');
            $table->foreignId('deletion_permission_holder_id')->constrained('users');
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->string('timezone')->default('Asia/Tokyo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
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
