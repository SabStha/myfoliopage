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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('color');
            $table->string('document_path')->nullable()->after('image_path');
            $table->text('summary')->nullable()->after('document_path');
            $table->string('animation_style')->nullable()->after('summary');
            $table->string('download_url')->nullable()->after('animation_style');
            $table->string('view_url')->nullable()->after('download_url');
            $table->string('visit_url')->nullable()->after('view_url');
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
