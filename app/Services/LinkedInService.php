<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LinkedInService
{
    private $accessToken;
    private $apiBaseUrl = 'https://api.linkedin.com/v2';

    public function __construct()
    {
        $this->accessToken = config('services.linkedin.access_token');
    }

    /**
     * Set the access token
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    /**
     * Fetch posts from LinkedIn profile
     */
    public function fetchPosts($limit = 10)
    {
        if (!$this->accessToken) {
            throw new \Exception('LinkedIn access token not configured');
        }

        try {
            // Get user's URN (person ID)
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($this->apiBaseUrl . '/me');

            if (!$userResponse->successful()) {
                Log::error('LinkedIn API Error - Failed to get user info', [
                    'status' => $userResponse->status(),
                    'body' => $userResponse->body()
                ]);
                throw new \Exception('Failed to authenticate with LinkedIn');
            }

            $userData = $userResponse->json();
            $personUrn = $userData['id'] ?? null;

            if (!$personUrn) {
                throw new \Exception('Could not retrieve LinkedIn person ID');
            }

            // Fetch posts using UGC Posts API
            // Note: LinkedIn API v2 requires specific permissions and may need different endpoints
            // This is a simplified version - you may need to adjust based on your LinkedIn API access level
            
            // Alternative: Use activity feed API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'X-Restli-Protocol-Version' => '2.0.0',
            ])->get($this->apiBaseUrl . '/ugcPosts', [
                'q' => 'authors',
                'authors' => 'List(' . $personUrn . ')',
                'count' => $limit,
            ]);

            if ($response->successful()) {
                return $this->parsePosts($response->json());
            }

            // Fallback: Try Share API (for user shares)
            $shareResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'X-Restli-Protocol-Version' => '2.0.0',
            ])->get($this->apiBaseUrl . '/shares', [
                'owner' => $personUrn,
                'count' => $limit,
            ]);

            if ($shareResponse->successful()) {
                return $this->parseShares($shareResponse->json());
            }

            Log::warning('LinkedIn API - Could not fetch posts using standard endpoints');
            return [];

        } catch (\Exception $e) {
            Log::error('LinkedIn API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Parse UGC posts from LinkedIn API
     */
    private function parsePosts($data)
    {
        $posts = [];
        
        if (!isset($data['elements'])) {
            return $posts;
        }

        foreach ($data['elements'] as $element) {
            $post = [
                'linkedin_id' => $element['id'] ?? null,
                'title' => $this->extractTitle($element),
                'content' => $this->extractContent($element),
                'excerpt' => $this->extractExcerpt($element),
                'published_at' => $this->extractPublishedAt($element),
                'linkedin_url' => $this->extractUrl($element),
                'category' => 'LinkedIn Post',
            ];

            if ($post['linkedin_id']) {
                $posts[] = $post;
            }
        }

        return $posts;
    }

    /**
     * Parse shares from LinkedIn API
     */
    private function parseShares($data)
    {
        $posts = [];
        
        if (!isset($data['elements'])) {
            return $posts;
        }

        foreach ($data['elements'] as $element) {
            $post = [
                'linkedin_id' => $element['id'] ?? null,
                'title' => $this->extractTitleFromShare($element),
                'content' => $this->extractContentFromShare($element),
                'excerpt' => $this->extractExcerptFromShare($element),
                'published_at' => isset($element['created']['time']) 
                    ? date('Y-m-d H:i:s', $element['created']['time'] / 1000) 
                    : null,
                'linkedin_url' => $this->extractUrlFromShare($element),
                'category' => 'LinkedIn Post',
            ];

            if ($post['linkedin_id']) {
                $posts[] = $post;
            }
        }

        return $posts;
    }

    /**
     * Extract title from post element
     */
    private function extractTitle($element)
    {
        // Try various fields for title
        if (isset($element['specificContent']['com.linkedin.ugc.ShareContent']['title'])) {
            return $element['specificContent']['com.linkedin.ugc.ShareContent']['title'];
        }
        
        if (isset($element['lifecycleState'])) {
            return 'LinkedIn Post';
        }

        // Generate title from content
        $content = $this->extractContent($element);
        if ($content) {
            $words = explode(' ', strip_tags($content));
            return implode(' ', array_slice($words, 0, 8)) . '...';
        }

        return 'LinkedIn Post';
    }

    /**
     * Extract content from post element
     */
    private function extractContent($element)
    {
        if (isset($element['specificContent']['com.linkedin.ugc.ShareContent']['text']['text'])) {
            return $element['specificContent']['com.linkedin.ugc.ShareContent']['text']['text'];
        }
        
        if (isset($element['commentary'])) {
            return $element['commentary'];
        }

        return null;
    }

    /**
     * Extract excerpt (first 200 chars)
     */
    private function extractExcerpt($element)
    {
        $content = $this->extractContent($element);
        if ($content) {
            return substr(strip_tags($content), 0, 200) . '...';
        }
        return null;
    }

    /**
     * Extract published date
     */
    private function extractPublishedAt($element)
    {
        if (isset($element['created']['time'])) {
            return date('Y-m-d H:i:s', $element['created']['time'] / 1000);
        }
        
        if (isset($element['firstPublishedAt'])) {
            return date('Y-m-d H:i:s', $element['firstPublishedAt'] / 1000);
        }

        return null;
    }

    /**
     * Extract URL
     */
    private function extractUrl($element)
    {
        if (isset($element['id'])) {
            $postId = str_replace('urn:li:ugcPost:', '', $element['id']);
            return 'https://www.linkedin.com/feed/update/' . $postId;
        }
        return null;
    }

    /**
     * Extract title from share element
     */
    private function extractTitleFromShare($element)
    {
        $content = $this->extractContentFromShare($element);
        if ($content) {
            $words = explode(' ', strip_tags($content));
            return implode(' ', array_slice($words, 0, 8)) . '...';
        }
        return 'LinkedIn Post';
    }

    /**
     * Extract content from share element
     */
    private function extractContentFromShare($element)
    {
        if (isset($element['text']['text'])) {
            return $element['text']['text'];
        }
        
        if (isset($element['commentary'])) {
            return $element['commentary'];
        }

        return null;
    }

    /**
     * Extract excerpt from share element
     */
    private function extractExcerptFromShare($element)
    {
        $content = $this->extractContentFromShare($element);
        if ($content) {
            return substr(strip_tags($content), 0, 200) . '...';
        }
        return null;
    }

    /**
     * Extract URL from share element
     */
    private function extractUrlFromShare($element)
    {
        if (isset($element['id'])) {
            return 'https://www.linkedin.com/feed/update/' . $element['id'];
        }
        return null;
    }

    /**
     * Post a blog to LinkedIn
     */
    public function postToLinkedIn(Blog $blog)
    {
        if (!$this->accessToken) {
            throw new \Exception('LinkedIn access token not configured');
        }

        try {
            // Get user's URN
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($this->apiBaseUrl . '/me');

            if (!$userResponse->successful()) {
                throw new \Exception('Failed to authenticate with LinkedIn');
            }

            $userData = $userResponse->json();
            $personUrn = 'urn:li:person:' . $userData['id'];

            // Prepare post content
            $content = $blog->excerpt ?? substr(strip_tags($blog->content ?? ''), 0, 300);
            
            // Create UGC Post
            $postData = [
                'author' => $personUrn,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $blog->title . "\n\n" . $content
                        ],
                        'shareMediaCategory' => 'ARTICLE',
                        'media' => [
                            [
                                'status' => 'READY',
                                'description' => [
                                    'text' => $blog->excerpt ?? ''
                                ],
                                'originalUrl' => url('/blog/' . $blog->slug),
                                'title' => [
                                    'text' => $blog->title
                                ]
                            ]
                        ]
                    ]
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'X-Restli-Protocol-Version' => '2.0.0',
                'Content-Type' => 'application/json',
            ])->post($this->apiBaseUrl . '/ugcPosts', $postData);

            if ($response->successful()) {
                $postId = $response->json()['id'] ?? null;
                
                // Update blog with LinkedIn post ID
                $blog->update([
                    'linkedin_post_id' => $postId,
                    'linkedin_url' => 'https://www.linkedin.com/feed/update/' . str_replace('urn:li:ugcPost:', '', $postId),
                ]);

                return true;
            }

            Log::error('LinkedIn API Error - Failed to post', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception('Failed to post to LinkedIn: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('LinkedIn Post Error', [
                'message' => $e->getMessage(),
                'blog_id' => $blog->id
            ]);
            throw $e;
        }
    }

    /**
     * Sync LinkedIn posts to database
     */
    public function syncPostsToDatabase($limit = 20)
    {
        $posts = $this->fetchPosts($limit);
        $synced = 0;

        foreach ($posts as $postData) {
            // Check if post already exists
            $existingBlog = Blog::where('linkedin_post_id', $postData['linkedin_id'])->first();

            if ($existingBlog) {
                // Update existing post
                $existingBlog->update([
                    'title' => $postData['title'],
                    'content' => $postData['content'],
                    'excerpt' => $postData['excerpt'],
                    'published_at' => $postData['published_at'],
                    'linkedin_url' => $postData['linkedin_url'],
                    'category' => $postData['category'],
                    'is_published' => true,
                ]);
            } else {
                // Create new blog post
                $slug = \Illuminate\Support\Str::slug($postData['title']);
                $counter = 1;
                while (Blog::where('slug', $slug)->exists()) {
                    $slug = \Illuminate\Support\Str::slug($postData['title']) . '-' . $counter;
                    $counter++;
                }

                Blog::create([
                    'title' => $postData['title'],
                    'slug' => $slug,
                    'content' => $postData['content'],
                    'excerpt' => $postData['excerpt'],
                    'published_at' => $postData['published_at'] ?? now(),
                    'linkedin_post_id' => $postData['linkedin_id'],
                    'linkedin_url' => $postData['linkedin_url'],
                    'category' => $postData['category'],
                    'is_published' => true,
                ]);
                $synced++;
            }
        }

        return $synced;
    }
}












