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
            $table->boolean('layout_reversed')->default(false)->after('image_rotation_interval');
            $table->integer('text_horizontal_offset')->default(0)->after('layout_reversed'); // -100 to 100 (px)
            $table->integer('image_horizontal_offset')->default(0)->after('text_horizontal_offset'); // -100 to 100 (px)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn(['layout_reversed', 'text_horizontal_offset', 'image_horizontal_offset']);
        });
    }
};
