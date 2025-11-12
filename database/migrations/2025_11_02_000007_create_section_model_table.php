<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates pivot table for polymorphic many-to-many relationship between Section and content models
     */
    public function up(): void
    {
        Schema::create('section_model', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->morphs('sectionable'); // This will add sectionable_id and sectionable_type
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['section_id', 'sectionable_id', 'sectionable_type'], 'section_model_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_model');
    }
};











