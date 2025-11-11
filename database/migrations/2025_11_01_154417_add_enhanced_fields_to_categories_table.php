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
        // Check if table exists before trying to alter it
        if (!Schema::hasTable('categories')) {
            return;
        }
        
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'image_path')) {
                $table->string('image_path')->nullable()->after('color');
            }
            if (!Schema::hasColumn('categories', 'document_path')) {
                $table->string('document_path')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('categories', 'summary')) {
                $table->text('summary')->nullable()->after('document_path');
            }
            if (!Schema::hasColumn('categories', 'animation_style')) {
                $table->string('animation_style')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('categories', 'download_url')) {
                $table->string('download_url')->nullable()->after('animation_style');
            }
            if (!Schema::hasColumn('categories', 'view_url')) {
                $table->string('view_url')->nullable()->after('download_url');
            }
            if (!Schema::hasColumn('categories', 'visit_url')) {
                $table->string('visit_url')->nullable()->after('view_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'image_path',
                'document_path',
                'summary',
                'animation_style',
                'download_url',
                'view_url',
                'visit_url'
            ]);
        });
    }
};
