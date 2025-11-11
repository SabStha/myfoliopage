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
        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            
            // Background
            $table->string('background_color')->default('#e0e7ff'); // Light indigo/blue-gray
            
            // Badge/Tagline
            $table->string('badge_text')->default('IT / UIUX / Security');
            $table->string('badge_color')->default('#ffb400');
            
            // Heading
            $table->text('heading_text')->nullable();
            $table->string('heading_size_mobile')->default('text-4xl');
            $table->string('heading_size_tablet')->default('text-5xl');
            $table->string('heading_size_desktop')->default('text-6xl');
            
            // Subheading
            $table->text('subheading_text')->nullable();
            
            // Button 1
            $table->string('button1_text')->default('Projects');
            $table->string('button1_link')->nullable();
            $table->string('button1_bg_color')->default('#ffb400');
            $table->string('button1_text_color')->default('#111827');
            $table->boolean('button1_visible')->default(true);
            
            // Button 2
            $table->string('button2_text')->default('LinkedIn');
            $table->string('button2_link')->nullable();
            $table->string('button2_bg_color')->default('#ffffff');
            $table->string('button2_text_color')->default('#1f2937');
            $table->string('button2_border_color')->default('#d1d5db');
            $table->boolean('button2_visible')->default(true);
            
            // Navigation Links
            $table->string('nav_about_text')->default('About');
            $table->string('nav_projects_text')->default('Projects');
            $table->string('nav_contact_text')->default('Contacts');
            $table->boolean('nav_visible')->default(true);
            
            // Blob/Decorative Element
            $table->string('blob_color')->default('#ffb400');
            $table->boolean('blob_visible')->default(true);
            
            // Profile Images Rotation
            $table->integer('image_rotation_interval')->default(2000); // milliseconds
            
            $table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_sections');
    }
};
