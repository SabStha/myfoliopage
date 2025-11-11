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
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_id')->unique(); // e.g., 'hero', 'discover', 'my-works'
            $table->string('name'); // Display name: 'Hero Section', 'Discover Section'
            $table->string('page')->default('home'); // Which page this section belongs to
            $table->integer('order')->default(0); // Order on the page
            $table->boolean('is_active')->default(true); // Whether section is visible
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
