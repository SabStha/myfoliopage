<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\HandlesTranslations;

class LinkedInController extends Controller
{
    use HandlesTranslations;
    /**
     * Show LinkedIn import page
     */
    public function index()
    {
        $blogs = Blog::whereNotNull('linkedin_url')
            ->latest('published_at')
            ->paginate(15);
        
        return view('admin.linkedin.index', compact('blogs'));
    }

    /**
     * Import a post from LinkedIn (manual paste)
     */
    public function import(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'content' => 'required|array',
            'content.en' => 'required|string',
            'content.ja' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'category' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ]);

        // Process translation fields
        $data = $this->processTranslations($data, ['title', 'content', 'excerpt']);

        // Generate slug from English title
        $titleForSlug = is_array($data['title']) ? ($data['title']['en'] ?? '') : $data['title'];
        $slug = Str::slug($titleForSlug);
        $counter = 1;
        while (Blog::where('slug', $slug)->exists()) {
            $slug = Str::slug($titleForSlug) . '-' . $counter;
            $counter++;
        }

        // Create excerpt from English content
        $contentForExcerpt = is_array($data['content']) ? ($data['content']['en'] ?? '') : $data['content'];
        $excerptText = strip_tags($contentForExcerpt);
        if (strlen($excerptText) > 200) {
            $excerptText = substr($excerptText, 0, 200) . '...';
        }
        
        // Create excerpt in same format as content (JSON with translations)
        $excerpt = [
            'en' => $excerptText,
            'ja' => '', // Will be auto-translated if needed
        ];
        $data['excerpt'] = json_encode($excerpt);

        $blog = Blog::create([
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'excerpt' => $data['excerpt'],
            'category' => $data['category'] ?? 'LinkedIn Post',
            'published_at' => $data['published_at'] ?? now(),
            'linkedin_url' => $data['linkedin_url'],
            'is_published' => true,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.blogs.edit', $blog)
            ->with('status', 'Blog post imported from LinkedIn successfully!');
    }
    
    /**
     * Get translatable fields for this controller
     */
    protected function getTranslatableFields(): array
    {
        return ['title', 'content', 'excerpt'];
    }

    /**
     * Get formatted content for LinkedIn (copy to clipboard)
     */
    public function getLinkedInFormat(Blog $blog)
    {
        // Use getTranslated to get the current locale's content
        $title = $blog->getTranslated('title') ?: $blog->title;
        $content = $title . "\n\n";
        
        $excerpt = $blog->getTranslated('excerpt');
        if ($excerpt) {
            $content .= $excerpt . "\n\n";
        } else {
            $blogContent = $blog->getTranslated('content');
            $text = strip_tags($blogContent ?? '');
            $content .= substr($text, 0, 300) . "\n\n";
        }
        
        $content .= "Read more: " . url('/blog/' . $blog->slug);
        
        return response()->json([
            'content' => $content,
            'title' => $title
        ]);
    }
}

