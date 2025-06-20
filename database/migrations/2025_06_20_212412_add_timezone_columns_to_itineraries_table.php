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
        Schema::table('itineraries', function (Blueprint $table) {
            $table->string('departure_timezone', 50)->nullable()->after('arrival_date')->comment('出発時のタイムゾーン');
            $table->string('arrival_timezone', 50)->nullable()->after('departure_timezone')->comment('到着時のタイムゾーン（departure_timezoneと異なる場合）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            $table->dropColumn(['departure_timezone', 'arrival_timezone']);
        });
    }
};
