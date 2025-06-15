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
            $table->foreignId('travel_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade')->comment('班グループ用');
            $table->foreignId('created_by_member_id')->constrained('members')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('timezone', 50)->default('Asia/Tokyo');

            // 移動手段関連
            $table->enum('transportation_type', ['walking', 'bike', 'car', 'ferry', 'bus', 'airplane'])->nullable();
            $table->string('airline')->nullable()->comment('飛行機の場合');
            $table->string('flight_number')->nullable()->comment('飛行機の場合');
            $table->datetime('departure_time')->nullable()->comment('出発時刻（現地時間）');
            $table->datetime('arrival_time')->nullable()->comment('到着時刻（現地時間）');
            $table->string('departure_location')->nullable();
            $table->string('arrival_location')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['travel_plan_id', 'date']);
            $table->index(['group_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itineraries');
    }
};
