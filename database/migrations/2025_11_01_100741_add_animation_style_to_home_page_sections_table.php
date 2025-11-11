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
        Schema::table('home_page_sections', function (Blueprint $table) {
            $table->enum('animation_style', [
                'grid_editorial_collage',      // Asymmetric grid with rotated images (certificates/games)
                'list_alternating_cards',      // Horizontal cards with alternating image position (courses/rooms)
                'carousel_scroll_left',        // Horizontal scrolling carousel left direction
                'carousel_scroll_right',        // Horizontal scrolling carousel right direction
            ])->default('list_alternating_cards')->after('text_alignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_page_sections', function (Blueprint $table) {
            $table->dropColumn('animation_style');
        });
    }
};
