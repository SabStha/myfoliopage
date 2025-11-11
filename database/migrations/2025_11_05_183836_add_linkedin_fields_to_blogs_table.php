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
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('linkedin_post_id')->nullable()->unique()->after('is_published');
            $table->string('linkedin_url')->nullable()->after('linkedin_post_id');
            $table->boolean('auto_sync_from_linkedin')->default(false)->after('linkedin_url');
            $table->boolean('auto_post_to_linkedin')->default(false)->after('auto_sync_from_linkedin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['linkedin_post_id', 'linkedin_url', 'auto_sync_from_linkedin', 'auto_post_to_linkedin']);
        });
    }
};
