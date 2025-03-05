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
        Schema::create('travel_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_member_id')->constrained('members')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type'); // MIME type
            $table->bigInteger('file_size')->unsigned(); // Size in bytes
            $table->string('category')->nullable(); // Ticket, reservation, etc.
            $table->text('description')->nullable();
            $table->boolean('is_shared_with_all')->default(true); // Whether all members can see this document
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Pivot table for document visibility if not shared with all
        Schema::create('document_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['travel_document_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_member');
        Schema::dropIfExists('travel_documents');
    }
};
