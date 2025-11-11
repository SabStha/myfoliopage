<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PageSection;

class PageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['section_id' => 'hero', 'name' => 'Hero Section', 'page' => 'home', 'order' => 1],
            ['section_id' => 'discover', 'name' => 'Discover Section', 'page' => 'home', 'order' => 2],
            ['section_id' => 'my-works', 'name' => 'My Works Section', 'page' => 'home', 'order' => 3],
            ['section_id' => 'blog', 'name' => 'Blog Section', 'page' => 'home', 'order' => 4],
            ['section_id' => 'testimonials', 'name' => 'Testimonials Section', 'page' => 'home', 'order' => 5],
            ['section_id' => 'contact', 'name' => 'Contact Section (Footer)', 'page' => 'home', 'order' => 6],
        ];
        
        foreach ($sections as $section) {
            PageSection::firstOrCreate(
                ['section_id' => $section['section_id'], 'page' => $section['page']],
                $section
            );
        }
    }
}
