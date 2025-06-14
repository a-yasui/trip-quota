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
        Schema::create('system_branch_group_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('区別用名前（システム全体でユニーク）');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true)->comment('招待可能かどうか');
            $table->timestamp('expires_at')->nullable()->comment('招待リンクの有効期限');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_branch_group_keys');
    }
};
