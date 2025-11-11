<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Certificate;
use App\Models\Tag;
use App\Models\NavItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Basic sample user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Tags
        $tags = collect(['Laravel', 'PHP', 'Vue', 'Blade', 'Mobile', 'Security'])
            ->mapWithKeys(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return [$name => Tag::firstOrCreate(['slug' => $slug], ['name' => $name])];
            });

        // Projects
        $amko = Project::create([
            'title' => 'AmkoShop',
            'slug' => 'amkoshop',
            'summary' => 'E-commerce app built with Laravel and Blade.',
            'tech_stack' => 'Laravel, Blade, Tailwind',
            'repo_url' => null,
            'demo_url' => null,
            'completed_at' => now()->subDays(10)->toDateString(),
        ]);
        $amko->tags()->sync([$tags['Laravel']->id, $tags['Blade']->id]);

        Project::create([
            'title' => 'Mobile Companion App',
            'slug' => 'mobile-companion-app',
            'summary' => 'Companion mobile app with API backend.',
            'tech_stack' => 'Laravel API, Mobile',
            'repo_url' => null,
            'demo_url' => null,
            'completed_at' => now()->subDays(30)->toDateString(),
        ])->tags()->sync([$tags['Mobile']->id]);

        // Certificates
        Certificate::create([
            'title' => 'Udemy Laravel Bootcamp',
            'provider' => 'Udemy',
            'credential_id' => 'ABC-123',
            'verify_url' => 'https://www.udemy.com/certificate/ABC-123',
            'issued_at' => now()->subMonths(2)->toDateString(),
        ])->tags()->sync([$tags['Laravel']->id, $tags['PHP']->id]);

        // Default sidebar
        $defaults = [
            ['label'=>'Dashboard','route'=>'admin.dashboard','icon_svg'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/></svg>', 'position'=>0],
            ['label'=>'TryHackMe','route'=>'admin.thm','icon_svg'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>', 'position'=>1],
            ['label'=>'Udemy','route'=>'admin.udemy','icon_svg'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4 4m-4-4l4-4"/></svg>', 'position'=>2],
            ['label'=>'Reports','route'=>'admin.reports','icon_svg'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2z"/></svg>', 'position'=>3],
            ['label'=>'Tasks','route'=>'admin.tasks','icon_svg'=>'<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'position'=>4],
        ];
        foreach ($defaults as $i) {
            NavItem::firstOrCreate(['label'=>$i['label']], $i);
        }
    }
}
