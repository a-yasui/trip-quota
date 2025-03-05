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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('language')->default('ja'); // Default language
            $table->string('timezone')->default('Asia/Tokyo'); // Default timezone
            $table->string('currency')->default('JPY'); // Default currency
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->json('notification_preferences')->nullable(); // Detailed notification settings
            $table->json('ui_preferences')->nullable(); // UI preferences like theme, etc.
            $table->timestamps();
            
            // A user can only have one settings record
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
