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
            $table->integer('blob_media_horizontal_offset')->default(0)->after('badge_horizontal_offset');
            $table->integer('blob_media_vertical_offset')->default(0)->after('blob_media_horizontal_offset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn(['blob_media_horizontal_offset', 'blob_media_vertical_offset']);
        });
    }
};
