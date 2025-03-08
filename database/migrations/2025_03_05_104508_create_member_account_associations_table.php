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
        Schema::create('member_account_associations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('previous_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('changed_by_user_id')->constrained('users');
            $table->text('change_reason')->nullable();
            $table->timestamps();

            // A member can only be associated with one account at a time
            $table->unique('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_account_associations');
    }
};
