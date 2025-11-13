<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Increase size of title fields in tables that store JSON translations
     * to accommodate longer JSON strings with both English and Japanese text
     */
    public function up(): void
    {
        // Rooms table
        if (Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'title')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->text('title')->change();
            });
        }
        
        // Book pages table
        if (Schema::hasTable('book_pages') && Schema::hasColumn('book_pages', 'title')) {
            Schema::table('book_pages', function (Blueprint $table) {
                $table->text('title')->change();
            });
        }
        
        // Code summaries table
        if (Schema::hasTable('code_summaries') && Schema::hasColumn('code_summaries', 'title')) {
            Schema::table('code_summaries', function (Blueprint $table) {
                $table->text('title')->change();
            });
        }
        
        // Category items table
        if (Schema::hasTable('category_items') && Schema::hasColumn('category_items', 'title')) {
            Schema::table('category_items', function (Blueprint $table) {
                $table->text('title')->nullable()->change();
            });
        }
        
        // Blogs table
        if (Schema::hasTable('blogs') && Schema::hasColumn('blogs', 'title')) {
            Schema::table('blogs', function (Blueprint $table) {
                $table->text('title')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to string (VARCHAR 255)
        if (Schema::hasTable('rooms') && Schema::hasColumn('rooms', 'title')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('title')->change();
            });
        }
        
        if (Schema::hasTable('book_pages') && Schema::hasColumn('book_pages', 'title')) {
            Schema::table('book_pages', function (Blueprint $table) {
                $table->string('title')->change();
            });
        }
        
        if (Schema::hasTable('code_summaries') && Schema::hasColumn('code_summaries', 'title')) {
            Schema::table('code_summaries', function (Blueprint $table) {
                $table->string('title')->change();
            });
        }
        
        if (Schema::hasTable('category_items') && Schema::hasColumn('category_items', 'title')) {
            Schema::table('category_items', function (Blueprint $table) {
                $table->string('title')->nullable()->change();
            });
        }
        
        if (Schema::hasTable('blogs') && Schema::hasColumn('blogs', 'title')) {
            Schema::table('blogs', function (Blueprint $table) {
                $table->string('title')->change();
            });
        }
    }
};
