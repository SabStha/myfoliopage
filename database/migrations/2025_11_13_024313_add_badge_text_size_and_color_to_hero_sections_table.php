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
            $table->string('badge_size_mobile')->default('text-xs')->after('badge_color');
            $table->string('badge_size_tablet')->default('text-sm')->after('badge_size_mobile');
            $table->string('badge_size_desktop')->default('text-sm')->after('badge_size_tablet');
            $table->string('badge_text_color')->default('#000000')->after('badge_size_desktop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn(['badge_size_mobile', 'badge_size_tablet', 'badge_size_desktop', 'badge_text_color']);
        });
    }
};
