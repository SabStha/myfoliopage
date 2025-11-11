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
        // Add user_id to main portfolio tables
        $tables = [
            'projects',
            'certificates',
            'courses',
            'nav_items',
            'nav_links',
            'categories',
            'category_items',
            'profiles',
            'hero_sections',
            'engagement_sections',
            'home_page_sections',
            'timeline_entries',
            'books',
            'labs',
            'skills',
            'code_summaries',
            'book_pages',
            'rooms',
            'testimonials',
            'blogs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Check if column already exists before adding it
                    if (!Schema::hasColumn($tableName, 'user_id')) {
                        $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                        $table->index('user_id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'projects',
            'certificates',
            'courses',
            'nav_items',
            'nav_links',
            'categories',
            'category_items',
            'profiles',
            'hero_sections',
            'engagement_sections',
            'home_page_sections',
            'timeline_entries',
            'books',
            'labs',
            'skills',
            'code_summaries',
            'book_pages',
            'rooms',
            'testimonials',
            'blogs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                });
            }
        }
    }
};

