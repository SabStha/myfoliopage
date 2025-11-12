<?php

namespace App\Console\Commands;

use App\Services\LinkedInService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchLinkedInPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'linkedin:fetch-posts {--limit=20}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch posts from LinkedIn and sync them to the database';

    /**
     * Execute the console command.
     */
    public function handle(LinkedInService $linkedInService)
    {
        $this->info('Starting LinkedIn posts sync...');

        try {
            $limit = (int) $this->option('limit');
            
            $synced = $linkedInService->syncPostsToDatabase($limit);

            $this->info("Successfully synced {$synced} posts from LinkedIn.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error syncing LinkedIn posts: ' . $e->getMessage());
            Log::error('LinkedIn fetch command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}









