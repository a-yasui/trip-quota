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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_name')->unique()->comment('^[a-zA-Z][\w\-_]{3,}$ 大文字小文字区別なし');
            $table->string('display_name')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
            
            $table->index('account_name')->comment('account_nameの大文字小文字を区別しないためのユニーク制約');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};