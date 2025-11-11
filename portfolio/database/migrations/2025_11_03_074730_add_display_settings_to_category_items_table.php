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
        Schema::table('category_items', function (Blueprint $table) {
            // Visibility toggles
            $table->boolean('show_title')->default(true)->after('title');
            $table->boolean('show_description')->default(true)->after('summary');
            $table->boolean('show_slug')->default(false)->after('show_description');
            $table->boolean('show_buttons')->default(true)->after('visit_url');
            
            // Button design settings (stored as JSON)
            $table->json('button_settings')->nullable()->after('show_buttons');
            // Example structure: {
            //   "download": {"enabled": true, "color": "#10b981", "size": "md", "style": "solid"},
            //   "view": {"enabled": true, "color": "#3b82f6", "size": "md", "style": "solid"},
            //   "visit": {"enabled": true, "color": "#8b5cf6", "size": "md", "style": "solid"}
            // }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_items', function (Blueprint $table) {
            $table->dropColumn(['show_title', 'show_description', 'show_slug', 'show_buttons', 'button_settings']);
        });
    }
};
