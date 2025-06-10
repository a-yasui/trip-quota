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
            $table->foreignId('travel_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by_member_id')->constrained('members')->onDelete('cascade');
            $table->string('name');
            $table->text('address')->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('price_per_night', 10, 2)->nullable();
            $table->string('currency', 3)->default('JPY');
            $table->text('notes')->nullable();
            $table->string('confirmation_number')->nullable();
            $table->timestamps();
            
            $table->index(['travel_plan_id', 'check_in_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};