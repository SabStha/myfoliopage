<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\HeroSection;
use App\Models\EngagementSection;
use App\Models\HomePageSection;
use App\Models\NavItem;
use App\Models\NavLink;
use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\Project;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Blog;
use App\Models\Tag;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TemplateDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This creates a complete demo template with sample data
     */
    public function run(): void
    {
        // Clear existing data (optional - comment out if you want to keep existing data)
        // Note: This will delete ALL existing data. Use with caution!
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }
            
            DB::table('users')->truncate();
            DB::table('profiles')->truncate();
            DB::table('hero_sections')->truncate();
            DB::table('engagement_sections')->truncate();
            DB::table('home_page_sections')->truncate();
            DB::table('nav_items')->truncate();
            DB::table('nav_links')->truncate();
            DB::table('categories')->truncate();
            DB::table('category_items')->truncate();
            DB::table('projects')->truncate();
            DB::table('certificates')->truncate();
            DB::table('courses')->truncate();
            DB::table('blogs')->truncate();
            DB::table('tags')->truncate();
            DB::table('media')->truncate();
            DB::table('taggables')->truncate();
            
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        } catch (\Exception $e) {
            $this->command->warn('Could not truncate tables. You may need to clear data manually: ' . $e->getMessage());
        }

        // Create demo user
        $demoUser = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
            'username' => 'demo-user',
            'slug' => 'demo-user',
        ]);

        // Create Profile
        $profile = Profile::create([
            'user_id' => $demoUser->id,
            'name' => 'Demo User',
            'role' => 'Full Stack Developer',
            'photo_path' => 'images/pp1.jpg', // Default profile image
        ]);

        // Create Hero Section
        $heroSection = HeroSection::create([
            'user_id' => $demoUser->id,
            'background_color' => '#e0e7ff',
            'badge_text' => json_encode(['en' => 'Welcome', 'ja' => 'ようこそ']),
            'badge_color' => '#ffb400',
            'heading_text' => json_encode(['en' => 'Hi, I\'m Demo User', 'ja' => 'こんにちは、デモユーザーです']),
            'heading_size_mobile' => 'text-2xl',
            'heading_size_tablet' => 'min-[375px]:text-3xl sm:text-4xl md:text-5xl',
            'heading_size_desktop' => 'lg:text-6xl',
            'subheading_text' => json_encode(['en' => 'A passionate developer creating amazing web experiences', 'ja' => '素晴らしいWeb体験を作り出す情熱的な開発者']),
            'button1_text' => json_encode(['en' => 'View Projects', 'ja' => 'プロジェクトを見る']),
            'button1_link' => '#my-works',
            'button1_bg_color' => '#ffb400',
            'button1_text_color' => '#111827',
            'button1_visible' => true,
            'button2_text' => json_encode(['en' => 'Contact Me', 'ja' => 'お問い合わせ']),
            'button2_link' => '#contact',
            'button2_bg_color' => '#ffffff',
            'button2_text_color' => '#1f2937',
            'button2_border_color' => '#d1d5db',
            'button2_visible' => true,
            'nav_visible' => true,
            'navigation_links' => [
                ['id' => 1, 'text' => ['en' => 'About', 'ja' => 'について'], 'section_id' => 'discover', 'order' => 1],
                ['id' => 2, 'text' => ['en' => 'Projects', 'ja' => 'プロジェクト'], 'section_id' => 'my-works', 'order' => 2],
                ['id' => 3, 'text' => ['en' => 'Contact', 'ja' => '連絡先'], 'section_id' => 'contact', 'order' => 3],
            ],
            'blob_color' => '#ffb400',
            'blob_visible' => true,
            'image_rotation_interval' => 3000,
            'layout_reversed' => false,
            'text_horizontal_offset' => 0,
            'image_horizontal_offset' => 0,
            'badge_horizontal_offset' => 0,
        ]);

        // Add profile images to Hero Section media
        for ($i = 1; $i <= 3; $i++) {
            Media::create([
                'mediable_type' => HeroSection::class,
                'mediable_id' => $heroSection->id,
                'type' => 'image',
                'path' => "images/pp{$i}.jpg",
            ]);
        }

        // Create Engagement Section
        $engagementSection = EngagementSection::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'Discover My Work', 'ja' => '私の作品を発見']),
            'video_path' => 'storage/videos/engagement-01.mp4',
        ]);

        // Create Tags
        $tags = [];
        $tagNames = ['Laravel', 'PHP', 'Vue.js', 'React', 'JavaScript', 'Python', 'Node.js', 'MySQL', 'MongoDB', 'Docker'];
        foreach ($tagNames as $tagName) {
            $tags[$tagName] = Tag::create([
                'name' => $tagName,
                'slug' => strtolower(str_replace(' ', '-', $tagName)),
            ]);
        }

        // Create Nav Items
        $navItemProjects = NavItem::create([
            'user_id' => $demoUser->id,
            'label' => json_encode(['en' => 'Projects', 'ja' => 'プロジェクト']),
            'slug' => 'projects',
            'position' => 0,
            'visible' => true,
        ]);

        $navItemCertificates = NavItem::create([
            'user_id' => $demoUser->id,
            'label' => json_encode(['en' => 'Certificates', 'ja' => '証明書']),
            'slug' => 'certificates',
            'position' => 1,
            'visible' => true,
        ]);

        $navItemCourses = NavItem::create([
            'user_id' => $demoUser->id,
            'label' => json_encode(['en' => 'Courses', 'ja' => 'コース']),
            'slug' => 'courses',
            'position' => 2,
            'visible' => true,
        ]);

        // Create additional Nav Items for Progress tracking
        $navItemTryHackMe = NavItem::create([
            'user_id' => $demoUser->id,
            'label' => json_encode(['en' => 'TryHackMe', 'ja' => 'TryHackMe']),
            'slug' => 'tryhackme',
            'position' => 3,
            'visible' => true,
        ]);

        $navItemBooks = NavItem::create([
            'user_id' => $demoUser->id,
            'label' => json_encode(['en' => 'Books', 'ja' => '書籍']),
            'slug' => 'books',
            'position' => 4,
            'visible' => true,
        ]);

        $navItemUdemy = NavItem::create([
            'user_id' => $demoUser->id,
            'label' => json_encode(['en' => 'Udemy', 'ja' => 'Udemy']),
            'slug' => 'udemy',
            'position' => 5,
            'visible' => true,
        ]);

        // Create Nav Links
        $navLinkWeb = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemProjects->id,
            'title' => json_encode(['en' => 'Web Development', 'ja' => 'Web開発']),
            'slug' => 'web-development',
            'position' => 0,
            'progress' => 85,
        ]);

        $navLinkMobile = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemProjects->id,
            'title' => json_encode(['en' => 'Mobile Apps', 'ja' => 'モバイルアプリ']),
            'slug' => 'mobile-apps',
            'position' => 1,
            'progress' => 60,
        ]);

        // Create Nav Links for Certificates
        $navLinkCert1 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemCertificates->id,
            'title' => json_encode(['en' => 'Laravel Advanced Techniques', 'ja' => 'Laravel高度なテクニック']),
            'slug' => 'laravel-advanced',
            'position' => 0,
            'progress' => 100,
        ]);

        $navLinkCert2 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemCertificates->id,
            'title' => json_encode(['en' => 'React Complete Guide', 'ja' => 'React完全ガイド']),
            'slug' => 'react-complete',
            'position' => 1,
            'progress' => 100,
        ]);

        // Create Nav Links for Courses
        $navLinkCourse1 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemCourses->id,
            'title' => json_encode(['en' => 'Full Stack Web Development', 'ja' => 'フルスタックWeb開発']),
            'slug' => 'full-stack-web-dev',
            'position' => 0,
            'progress' => 100,
        ]);

        $navLinkCourse2 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemCourses->id,
            'title' => json_encode(['en' => 'Python Masterclass', 'ja' => 'Pythonマスタークラス']),
            'slug' => 'python-masterclass',
            'position' => 1,
            'progress' => 75,
        ]);

        // Create Nav Links for TryHackMe
        $navLinkTHM1 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemTryHackMe->id,
            'title' => json_encode(['en' => 'Complete Beginner', 'ja' => '完全初心者']),
            'slug' => 'complete-beginner',
            'position' => 0,
            'progress' => 100,
        ]);

        $navLinkTHM2 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemTryHackMe->id,
            'title' => json_encode(['en' => 'Web Fundamentals', 'ja' => 'Web基礎']),
            'slug' => 'web-fundamentals',
            'position' => 1,
            'progress' => 85,
        ]);

        $navLinkTHM3 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemTryHackMe->id,
            'title' => json_encode(['en' => 'Offensive Pentesting', 'ja' => '攻撃的ペネトレーションテスト']),
            'slug' => 'offensive-pentesting',
            'position' => 2,
            'progress' => 45,
        ]);

        // Create Nav Links for Books
        $navLinkBook1 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemBooks->id,
            'title' => json_encode(['en' => 'Clean Code', 'ja' => 'クリーンコード']),
            'slug' => 'clean-code',
            'position' => 0,
            'progress' => 100,
        ]);

        $navLinkBook2 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemBooks->id,
            'title' => json_encode(['en' => 'Design Patterns', 'ja' => 'デザインパターン']),
            'slug' => 'design-patterns',
            'position' => 1,
            'progress' => 60,
        ]);

        $navLinkBook3 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemBooks->id,
            'title' => json_encode(['en' => 'System Design', 'ja' => 'システム設計']),
            'slug' => 'system-design',
            'position' => 2,
            'progress' => 30,
        ]);

        // Create Nav Links for Udemy
        $navLinkUdemy1 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemUdemy->id,
            'title' => json_encode(['en' => 'Laravel Advanced', 'ja' => 'Laravel高度']),
            'slug' => 'laravel-advanced-udemy',
            'position' => 0,
            'progress' => 100,
        ]);

        $navLinkUdemy2 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemUdemy->id,
            'title' => json_encode(['en' => 'React Complete Guide', 'ja' => 'React完全ガイド']),
            'slug' => 'react-complete-udemy',
            'position' => 1,
            'progress' => 100,
        ]);

        $navLinkUdemy3 = NavLink::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemUdemy->id,
            'title' => json_encode(['en' => 'Vue.js Mastery', 'ja' => 'Vue.jsマスタリー']),
            'slug' => 'vuejs-mastery',
            'position' => 2,
            'progress' => 80,
        ]);

        // Create Categories
        $categoryFrontend = Category::create([
            'user_id' => $demoUser->id,
            'name' => json_encode(['en' => 'Frontend Projects', 'ja' => 'フロントエンドプロジェクト']),
            'slug' => 'frontend-projects',
            'summary' => json_encode(['en' => 'Modern web applications built with React and Vue.js', 'ja' => 'ReactとVue.jsで構築されたモダンなWebアプリケーション']),
            'position' => 0,
            'animation_style' => 'grid_editorial_collage',
        ]);

        $categoryBackend = Category::create([
            'user_id' => $demoUser->id,
            'name' => json_encode(['en' => 'Backend Projects', 'ja' => 'バックエンドプロジェクト']),
            'slug' => 'backend-projects',
            'summary' => json_encode(['en' => 'Robust APIs and server-side applications', 'ja' => '堅牢なAPIとサーバーサイドアプリケーション']),
            'position' => 1,
            'animation_style' => 'list_alternating_cards',
        ]);

        // Link Categories to Nav Links
        $navLinkWeb->categories()->attach([$categoryFrontend->id, $categoryBackend->id]);
        $navLinkMobile->categories()->attach([$categoryFrontend->id]);

        // Create Category Items (Projects)
        $project1 = CategoryItem::create([
            'user_id' => $demoUser->id,
            'category_id' => $categoryFrontend->id,
            'title' => json_encode(['en' => 'E-Commerce Platform', 'ja' => 'Eコマースプラットフォーム']),
            'slug' => 'ecommerce-platform',
            'summary' => json_encode(['en' => 'A full-featured e-commerce platform with shopping cart, payment integration, and admin dashboard.', 'ja' => 'ショッピングカート、決済統合、管理ダッシュボードを備えたフル機能のEコマースプラットフォーム']),
            'url' => 'https://github.com/demo/ecommerce',
            'position' => 0,
        ]);

        $project2 = CategoryItem::create([
            'user_id' => $demoUser->id,
            'category_id' => $categoryFrontend->id,
            'title' => json_encode(['en' => 'Task Management App', 'ja' => 'タスク管理アプリ']),
            'slug' => 'task-management-app',
            'summary' => json_encode(['en' => 'A collaborative task management application with real-time updates and team collaboration features.', 'ja' => 'リアルタイム更新とチームコラボレーション機能を備えた共同タスク管理アプリケーション']),
            'url' => 'https://github.com/demo/taskmanager',
            'position' => 1,
        ]);

        $project3 = CategoryItem::create([
            'user_id' => $demoUser->id,
            'category_id' => $categoryBackend->id,
            'title' => json_encode(['en' => 'RESTful API', 'ja' => 'RESTful API']),
            'slug' => 'restful-api',
            'summary' => json_encode(['en' => 'A scalable REST API built with Laravel for managing user data and authentication.', 'ja' => 'ユーザーデータと認証を管理するためにLaravelで構築されたスケーラブルなREST API']),
            'url' => 'https://github.com/demo/api',
            'position' => 0,
        ]);

        // Add more Category Items for better preview
        CategoryItem::create([
            'user_id' => $demoUser->id,
            'category_id' => $categoryFrontend->id,
            'title' => json_encode(['en' => 'Portfolio Website', 'ja' => 'ポートフォリオウェブサイト']),
            'slug' => 'portfolio-website',
            'summary' => json_encode(['en' => 'A modern portfolio website showcasing projects and skills.', 'ja' => 'プロジェクトとスキルを紹介するモダンなポートフォリオウェブサイト']),
            'url' => 'https://github.com/demo/portfolio',
            'position' => 2,
        ]);

        CategoryItem::create([
            'user_id' => $demoUser->id,
            'category_id' => $categoryBackend->id,
            'title' => json_encode(['en' => 'Authentication System', 'ja' => '認証システム']),
            'slug' => 'authentication-system',
            'summary' => json_encode(['en' => 'Secure authentication system with JWT tokens and OAuth integration.', 'ja' => 'JWTトークンとOAuth統合を備えた安全な認証システム']),
            'url' => 'https://github.com/demo/auth',
            'position' => 1,
        ]);

        // Create Projects (for Projects page)
        Project::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'E-Commerce Platform', 'ja' => 'Eコマースプラットフォーム']),
            'slug' => 'ecommerce-platform',
            'summary' => json_encode(['en' => 'A full-featured e-commerce platform', 'ja' => 'フル機能のEコマースプラットフォーム']),
            'tech_stack' => 'Laravel, Vue.js, MySQL',
            'repo_url' => 'https://github.com/demo/ecommerce',
            'demo_url' => 'https://demo-ecommerce.example.com',
            'completed_at' => now()->subMonths(2)->toDateString(),
        ])->tags()->sync([$tags['Laravel']->id, $tags['Vue.js']->id, $tags['MySQL']->id]);

        Project::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'Task Management App', 'ja' => 'タスク管理アプリ']),
            'slug' => 'task-management-app',
            'summary' => json_encode(['en' => 'A collaborative task management application', 'ja' => '共同タスク管理アプリケーション']),
            'tech_stack' => 'React, Node.js, MongoDB',
            'repo_url' => 'https://github.com/demo/taskmanager',
            'demo_url' => 'https://demo-taskmanager.example.com',
            'completed_at' => now()->subMonths(1)->toDateString(),
        ])->tags()->sync([$tags['React']->id, $tags['Node.js']->id, $tags['MongoDB']->id]);

        // Create Certificates
        Certificate::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'Laravel Advanced Techniques', 'ja' => 'Laravel高度なテクニック']),
            'provider' => json_encode(['en' => 'Udemy', 'ja' => 'Udemy']),
            'credential_id' => 'UD-LARAVEL-2024',
            'verify_url' => 'https://www.udemy.com/certificate/UD-LARAVEL-2024',
            'issued_at' => now()->subMonths(3)->toDateString(),
            'level' => 'Advanced',
            'status' => 'completed',
        ])->tags()->sync([$tags['Laravel']->id, $tags['PHP']->id]);

        Certificate::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'React Complete Guide', 'ja' => 'React完全ガイド']),
            'provider' => json_encode(['en' => 'Udemy', 'ja' => 'Udemy']),
            'credential_id' => 'UD-REACT-2024',
            'verify_url' => 'https://www.udemy.com/certificate/UD-REACT-2024',
            'issued_at' => now()->subMonths(2)->toDateString(),
            'level' => 'Intermediate',
            'status' => 'completed',
        ])->tags()->sync([$tags['React']->id, $tags['JavaScript']->id]);

        // Add more Certificates
        Certificate::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'Vue.js Mastery', 'ja' => 'Vue.jsマスタリー']),
            'provider' => json_encode(['en' => 'Udemy', 'ja' => 'Udemy']),
            'credential_id' => 'UD-VUE-2024',
            'verify_url' => 'https://www.udemy.com/certificate/UD-VUE-2024',
            'issued_at' => now()->subMonths(1)->toDateString(),
            'level' => 'Intermediate',
            'status' => 'completed',
        ])->tags()->sync([$tags['Vue.js']->id, $tags['JavaScript']->id]);

        Certificate::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'Python Programming', 'ja' => 'Pythonプログラミング']),
            'provider' => json_encode(['en' => 'Coursera', 'ja' => 'Coursera']),
            'credential_id' => 'COURSERA-PYTHON-2024',
            'verify_url' => 'https://www.coursera.org/verify/COURSERA-PYTHON-2024',
            'issued_at' => now()->subMonths(4)->toDateString(),
            'level' => 'Beginner',
            'status' => 'completed',
        ])->tags()->sync([$tags['Python']->id]);

        // Create Courses
        Course::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'Full Stack Web Development', 'ja' => 'フルスタックWeb開発']),
            'slug' => 'full-stack-web-development',
            'provider' => json_encode(['en' => 'Udemy', 'ja' => 'Udemy']),
            'course_url' => 'https://www.udemy.com/course/fullstack',
            'instructor_organization' => 'John Doe',
            'status' => 'completed',
            'difficulty' => 'Intermediate',
            'estimated_hours' => 40,
            'issued_at' => now()->subMonths(4)->toDateString(),
            'completed_at' => now()->subMonths(3)->toDateString(),
        ])->tags()->sync([$tags['Laravel']->id, $tags['Vue.js']->id]);

        Course::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'Python Masterclass', 'ja' => 'Pythonマスタークラス']),
            'slug' => 'python-masterclass',
            'provider' => json_encode(['en' => 'Udemy', 'ja' => 'Udemy']),
            'course_url' => 'https://www.udemy.com/course/python-masterclass',
            'instructor_organization' => 'Jane Smith',
            'status' => 'in_progress',
            'difficulty' => 'Advanced',
            'estimated_hours' => 50,
            'issued_at' => now()->subMonths(2)->toDateString(),
            'completed_at' => null,
        ])->tags()->sync([$tags['Python']->id]);

        Course::create([
            'user_id' => $demoUser->id,
            'title' => json_encode(['en' => 'Docker & Kubernetes', 'ja' => 'DockerとKubernetes']),
            'slug' => 'docker-kubernetes',
            'provider' => json_encode(['en' => 'Pluralsight', 'ja' => 'Pluralsight']),
            'course_url' => 'https://www.pluralsight.com/courses/docker-kubernetes',
            'instructor_organization' => 'Tech Academy',
            'status' => 'completed',
            'difficulty' => 'Intermediate',
            'estimated_hours' => 25,
            'issued_at' => now()->subMonths(5)->toDateString(),
            'completed_at' => now()->subMonths(4)->toDateString(),
        ])->tags()->sync([$tags['Docker']->id]);

        // Create Home Page Sections
        $homePageSection1 = HomePageSection::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemProjects->id,
            'position' => 0,
            'text_alignment' => 'left',
            'animation_style' => 'grid_editorial_collage',
            'title' => json_encode(['en' => 'My Projects', 'ja' => '私のプロジェクト']),
            'subtitle' => json_encode(['en' => 'A collection of my best work', 'ja' => '私の最高の作品のコレクション']),
            'enabled' => true,
            'selected_nav_link_ids' => [$navLinkWeb->id, $navLinkMobile->id],
        ]);

        $homePageSection2 = HomePageSection::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemCertificates->id,
            'position' => 1,
            'text_alignment' => 'left',
            'animation_style' => 'list_alternating_cards',
            'title' => json_encode(['en' => 'Certifications', 'ja' => '認定資格']),
            'subtitle' => json_encode(['en' => 'Professional certifications and achievements', 'ja' => '専門的な認定資格と成果']),
            'enabled' => true,
            'selected_nav_link_ids' => null, // Show all
        ]);

        $homePageSection3 = HomePageSection::create([
            'user_id' => $demoUser->id,
            'nav_item_id' => $navItemCourses->id,
            'position' => 2,
            'text_alignment' => 'left',
            'animation_style' => 'grid_editorial_collage',
            'title' => json_encode(['en' => 'My Courses', 'ja' => '私のコース']),
            'subtitle' => json_encode(['en' => 'Online courses and learning paths', 'ja' => 'オンラインコースと学習パス']),
            'enabled' => true,
            'selected_nav_link_ids' => null, // Show all
        ]);

        // Create Blog Posts
        $blog1 = Blog::create([
            'title' => json_encode(['en' => 'Getting Started with Laravel', 'ja' => 'Laravelの始め方']),
            'slug' => 'getting-started-with-laravel',
            'excerpt' => json_encode(['en' => 'Learn the basics of Laravel framework and build your first application.', 'ja' => 'Laravelフレームワークの基礎を学び、最初のアプリケーションを構築しましょう。']),
            'content' => json_encode(['en' => 'Laravel is a powerful PHP framework that makes web development a breeze. In this blog post, we\'ll explore the fundamentals and get you started on your journey.', 'ja' => 'Laravelは、Web開発を簡単にする強力なPHPフレームワークです。このブログ投稿では、基礎を探求し、あなたの旅を始めましょう。']),
            'category' => 'Web Development',
            'published_at' => now()->subDays(5),
            'is_published' => true,
        ]);
        $blog1->tags()->sync([$tags['Laravel']->id, $tags['PHP']->id]);

        $blog2 = Blog::create([
            'title' => json_encode(['en' => 'React Hooks Explained', 'ja' => 'React Hooksの説明']),
            'slug' => 'react-hooks-explained',
            'excerpt' => json_encode(['en' => 'Understanding React Hooks and how to use them effectively in your applications.', 'ja' => 'React Hooksを理解し、アプリケーションで効果的に使用する方法。']),
            'content' => json_encode(['en' => 'React Hooks revolutionized how we write React components. Let\'s dive deep into useState, useEffect, and more.', 'ja' => 'React Hooksは、Reactコンポーネントの書き方を革命的に変えました。useState、useEffectなどについて詳しく見ていきましょう。']),
            'category' => 'Frontend',
            'published_at' => now()->subDays(3),
            'is_published' => true,
        ]);
        $blog2->tags()->sync([$tags['React']->id, $tags['JavaScript']->id]);

        $blog3 = Blog::create([
            'title' => json_encode(['en' => 'Building RESTful APIs', 'ja' => 'RESTful APIの構築']),
            'slug' => 'building-restful-apis',
            'excerpt' => json_encode(['en' => 'Best practices for designing and implementing RESTful APIs.', 'ja' => 'RESTful APIを設計および実装するためのベストプラクティス。']),
            'content' => json_encode(['en' => 'RESTful APIs are the backbone of modern web applications. Learn how to design them properly.', 'ja' => 'RESTful APIは、モダンなWebアプリケーションの基盤です。それらを適切に設計する方法を学びましょう。']),
            'category' => 'Backend',
            'published_at' => now()->subDays(1),
            'is_published' => true,
        ]);
        $blog3->tags()->sync([$tags['Laravel']->id, $tags['Node.js']->id]);

        $this->command->info('Demo template data created successfully!');
        $this->command->info('Demo User: demo@example.com / password');
        $this->command->info('Username: demo-user');
        $this->command->info('Please add pp1.jpg, pp2.jpg, pp3.jpg to public/images/ directory');
    }
}

