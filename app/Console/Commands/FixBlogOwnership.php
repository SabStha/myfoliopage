<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Blog;
use App\Models\User;

class FixBlogOwnership extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:fix-ownership {blog_id?} {--user_id=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix blog ownership by assigning blogs to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $blogId = $this->argument('blog_id');
        $userId = $this->option('user_id');
        $fixAll = $this->option('all');

        // If no user_id provided, use the first user
        if (!$userId) {
            $user = User::first();
            if (!$user) {
                $this->error('No users found in the database.');
                return 1;
            }
            $userId = $user->id;
            $this->info("Using user ID: {$userId} ({$user->email})");
        } else {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
            $this->info("Using user ID: {$userId} ({$user->email})");
        }

        if ($fixAll) {
            // Fix all blogs with null user_id
            $blogs = Blog::whereNull('user_id')->get();
            if ($blogs->isEmpty()) {
                $this->info('No blogs with null user_id found.');
                return 0;
            }
            
            $count = $blogs->count();
            $this->info("Found {$count} blog(s) with null user_id. Updating...");
            
            Blog::whereNull('user_id')->update(['user_id' => $userId]);
            
            $this->info("✅ Successfully updated {$count} blog(s) to user ID {$userId}.");
            return 0;
        }

        if ($blogId) {
            // Fix specific blog
            $blog = Blog::find($blogId);
            if (!$blog) {
                $this->error("Blog with ID {$blogId} not found.");
                return 1;
            }

            $oldUserId = $blog->user_id;
            $blog->user_id = $userId;
            $blog->save();

            $this->info("✅ Blog ID {$blogId} ownership updated:");
            $this->line("   Old user_id: " . ($oldUserId ?? 'null'));
            $this->line("   New user_id: {$userId}");
            return 0;
        }

        // Show help if no options provided
        $this->info('Blog Ownership Fixer');
        $this->line('');
        $this->line('Usage:');
        $this->line('  php artisan blog:fix-ownership {blog_id}              - Fix specific blog');
        $this->line('  php artisan blog:fix-ownership --all                 - Fix all blogs with null user_id');
        $this->line('  php artisan blog:fix-ownership {blog_id} --user_id=2 - Fix blog and assign to specific user');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan blog:fix-ownership 9');
        $this->line('  php artisan blog:fix-ownership --all');
        $this->line('  php artisan blog:fix-ownership 9 --user_id=2');

        return 0;
    }
}
