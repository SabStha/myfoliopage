<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This creates a polymorphic many-to-many relationship between categories and various models
     * (BookPage, CodeSummary, Room, Certificate, Course)
     */
    public function up(): void
    {
        Schema::create('category_model', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->morphs('categorizable'); // Creates categorizable_id and categorizable_type
            $table->integer('position')->default(0);
            $table->timestamps();
            
            // Ensure unique combinations
            $table->unique(['category_id', 'categorizable_id', 'categorizable_type'], 'category_model_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_model');
    }
};









