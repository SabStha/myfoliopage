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
        $this->call([
            TemplateDemoSeeder::class,
        ]);
    }
}
