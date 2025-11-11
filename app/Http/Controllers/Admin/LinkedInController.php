<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkedInController extends Controller
{
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
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'linkedin_url' => 'nullable|url',
            'category' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ]);

        // Generate slug
        $slug = Str::slug($data['title']);
        $counter = 1;
        while (Blog::where('slug', $slug)->exists()) {
            $slug = Str::slug($data['title']) . '-' . $counter;
            $counter++;
        }

        // Create excerpt from content
        $excerpt = $data['content'];
        if (strlen($excerpt) > 200) {
            $excerpt = substr(strip_tags($excerpt), 0, 200) . '...';
        }

        $blog = Blog::create([
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'excerpt' => $excerpt,
            'category' => $data['category'] ?? 'LinkedIn Post',
            'published_at' => $data['published_at'] ?? now(),
            'linkedin_url' => $data['linkedin_url'],
            'is_published' => true,
        ]);

        return redirect()->route('admin.blogs.edit', $blog)
            ->with('status', 'Blog post imported from LinkedIn successfully!');
    }

    /**
     * Get formatted content for LinkedIn (copy to clipboard)
     */
    public function getLinkedInFormat(Blog $blog)
    {
        $content = $blog->title . "\n\n";
        
        if ($blog->excerpt) {
            $content .= $blog->excerpt . "\n\n";
        } else {
            $text = strip_tags($blog->content ?? '');
            $content .= substr($text, 0, 300) . "\n\n";
        }
        
        $content .= "Read more: " . url('/blog/' . $blog->slug);
        
        return response()->json([
            'content' => $content,
            'title' => $blog->title
        ]);
    }
}

