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
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_plan_id')->constrained()->cascadeOnDelete();
            $table->string('transportation_type');
            $table->string('departure_location');
            $table->string('arrival_location');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->string('company_name')->nullable(); // Airline, train company, etc.
            $table->string('reference_number')->nullable(); // Flight number, train number, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Pivot table for itinerary members
        Schema::create('itinerary_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['itinerary_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itinerary_member');
        Schema::dropIfExists('itineraries');
    }
};
