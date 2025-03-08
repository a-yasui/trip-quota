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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Account name (username)
            $table->string('display_name')->nullable();
            $table->string('thumbnail_path')->nullable(); // Path to profile image
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Account name must be unique (case-insensitive)
            // This will be enforced at the application level since MySQL's UNIQUE is case-sensitive
        });

        // Add index for case-insensitive account name lookups
        DB::statement('CREATE INDEX accounts_name_index ON accounts (LOWER(name))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
