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
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Hotel name
            $table->string('address');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('booking_reference')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Pivot table for accommodation members
        Schema::create('accommodation_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['accommodation_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_member');
        Schema::dropIfExists('accommodations');
    }
};
