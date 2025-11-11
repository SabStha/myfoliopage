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
        Schema::create('home_page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('nav_item_id')->constrained('nav_items')->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->enum('text_alignment', ['left', 'right'])->default('left');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->boolean('enabled')->default(true);
            $table->json('selected_nav_link_ids')->nullable(); // Array of NavLink IDs to display
            $table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_page_sections');
    }
};
