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
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->string('heading_text_color')->default('#111827')->after('heading_size_desktop');
            $table->string('subheading_text_color')->default('#6b7280')->after('subheading_text');
            $table->string('navigation_text_color')->default('#374151')->after('nav_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn(['heading_text_color', 'subheading_text_color', 'navigation_text_color']);
        });
    }
};
