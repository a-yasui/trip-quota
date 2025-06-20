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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_plan_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['CORE', 'BRANCH'])->comment('コアグループ・班グループ');
            $table->string('name');
            $table->string('branch_key')->nullable()->comment('班グループの区別用名前（system_branch_group_keys参照）');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['travel_plan_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
