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
        // Add user_id to testimonials table if it doesn't exist
        if (Schema::hasTable('testimonials') && !Schema::hasColumn('testimonials', 'user_id')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                $table->index('user_id');
            });
        }
        
        // Add user_id to blogs table if it doesn't exist
        if (Schema::hasTable('blogs') && !Schema::hasColumn('blogs', 'user_id')) {
            Schema::table('blogs', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('testimonials') && Schema::hasColumn('testimonials', 'user_id')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
        
        if (Schema::hasTable('blogs') && Schema::hasColumn('blogs', 'user_id')) {
            Schema::table('blogs', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
