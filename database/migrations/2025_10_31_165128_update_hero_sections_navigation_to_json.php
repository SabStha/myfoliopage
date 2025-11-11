<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            // Add JSON column for navigation links
            $table->json('navigation_links')->nullable()->after('nav_visible');
        });

        // Migrate existing data
        DB::table('hero_sections')->get()->each(function ($hero) {
            $navigationLinks = [
                [
                    'id' => 1,
                    'text' => $hero->nav_about_text ?? 'About',
                    'section_id' => 'discover',
                    'order' => 1,
                ],
                [
                    'id' => 2,
                    'text' => $hero->nav_projects_text ?? 'Projects',
                    'section_id' => 'my-works',
                    'order' => 2,
                ],
                [
                    'id' => 3,
                    'text' => $hero->nav_contact_text ?? 'Contacts',
                    'section_id' => 'contact',
                    'order' => 3,
                ],
            ];

            DB::table('hero_sections')
                ->where('id', $hero->id)
                ->update(['navigation_links' => json_encode($navigationLinks)]);
        });

        // Remove old columns
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn(['nav_about_text', 'nav_projects_text', 'nav_contact_text']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            // Restore old columns
            $table->string('nav_about_text')->default('About')->after('nav_visible');
            $table->string('nav_projects_text')->default('Projects')->after('nav_about_text');
            $table->string('nav_contact_text')->default('Contacts')->after('nav_projects_text');
        });

        // Migrate data back
        DB::table('hero_sections')->get()->each(function ($hero) {
            $links = json_decode($hero->navigation_links ?? '[]', true);
            $navAbout = $links[0]['text'] ?? 'About';
            $navProjects = $links[1]['text'] ?? 'Projects';
            $navContact = $links[2]['text'] ?? 'Contacts';

            DB::table('hero_sections')
                ->where('id', $hero->id)
                ->update([
                    'nav_about_text' => $navAbout,
                    'nav_projects_text' => $navProjects,
                    'nav_contact_text' => $navContact,
                ]);
        });

        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn('navigation_links');
        });
    }
};
