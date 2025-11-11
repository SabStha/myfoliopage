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
            $table->json('subsection_configurations')->nullable()->after('selected_nav_link_ids');
            // This will store: { "nav_link_id": { "animation_style": "...", "layout_style": "..." }, ... }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_page_sections', function (Blueprint $table) {
            $table->dropColumn('subsection_configurations');
        });
    }
};
