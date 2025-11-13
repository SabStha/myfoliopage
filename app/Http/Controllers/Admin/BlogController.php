<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HandlesTranslations;
use App\Models\Blog;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    use HandlesTranslations;
    
    protected function getTranslatableFields(): array
    {
        return ['title', 'excerpt', 'content'];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $blogs = Blog::where('user_id', Auth::id())
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(12);
        
        // If editing a blog, load it
        $editingBlog = null;
        $allTags = collect();
        $blogTags = '';
        if ($request->has('edit')) {
            $editingBlog = Blog::with('media', 'tags')
                ->where('id', $request->edit)
                ->where('user_id', Auth::id())
                ->first();
            if ($editingBlog) {
                $allTags = Tag::orderBy('name')->get();
                $blogTags = $editingBlog->tags->pluck('name')->join(', ');
            }
        }
        
        return view('admin.blogs.index', compact('blogs', 'editingBlog', 'allTags', 'blogTags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allTags = Tag::orderBy('name')->get();
        return view('admin.blogs.create', compact('allTags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'excerpt' => 'nullable|array',
            'excerpt.en' => 'nullable|string',
            'excerpt.ja' => 'nullable|string',
            'content' => 'nullable|array',
            'content.en' => 'nullable|string',
            'content.ja' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
            'tags' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'linkedin_url' => 'nullable|url|max:500',
        ]);

        // Generate slug if not provided (use English title)
        if (empty($data['slug'])) {
            $titleForSlug = is_array($data['title']) ? ($data['title']['en'] ?? '') : $data['title'];
            $baseSlug = Str::slug($titleForSlug);
            $slug = $baseSlug;
            $counter = 1;
            while (Blog::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());

        $data['is_published'] = $request->has('is_published');
        $data['user_id'] = Auth::id();
        
        // Normalize LinkedIn URL if provided
        if (!empty($data['linkedin_url'])) {
            $data['linkedin_url'] = $this->normalizeUrl($data['linkedin_url']);
        }
        
        $blog = Blog::create($data);

        // Sync tags from comma-separated list
        if ($request->filled('tags')) {
            $tagNames = collect(explode(',', $request->string('tags')))
                ->map(fn($t) => trim($t))
                ->filter();
            $tagIds = $tagNames->map(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            });
            $blog->tags()->sync($tagIds);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('blogs', 'public');
            $blog->media()->create(['title' => 'Cover', 'type' => 'image', 'path' => $path]);
        }

        return redirect()->route('admin.blogs.index')->with('status', 'Blog post created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        return view('admin.blogs.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        // If blog has no owner, assign it to current user (for backward compatibility)
        if ($blog->user_id === null) {
            $blog->user_id = Auth::id();
            $blog->save();
        } elseif ($blog->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access. This blog belongs to another user.');
        }
        // Redirect to unified page with edit parameter
        return redirect()->route('admin.blogs.index', ['edit' => $blog->id])->with('status', 'Edit blog post');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        if ($blog->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:blogs,slug,' . $blog->id,
            'excerpt' => 'nullable|array',
            'excerpt.en' => 'nullable|string',
            'excerpt.ja' => 'nullable|string',
            'content' => 'nullable|array',
            'content.en' => 'nullable|string',
            'content.ja' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
            'tags' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'linkedin_url' => 'nullable|url|max:500',
        ]);

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());

        $data['is_published'] = $request->has('is_published');
        
        // Normalize LinkedIn URL if provided
        if (!empty($data['linkedin_url'])) {
            $data['linkedin_url'] = $this->normalizeUrl($data['linkedin_url']);
        }
        
        $blog->update($data);

        if ($request->filled('tags')) {
            $tagNames = collect(explode(',', $request->string('tags')))
                ->map(fn($t) => trim($t))
                ->filter();
            $tagIds = $tagNames->map(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            });
            $blog->tags()->sync($tagIds);
        } else {
            $blog->tags()->sync([]);
        }

        if ($request->hasFile('image')) {
            // Delete old image
            $blog->media()->where('type', 'image')->delete();
            $path = $request->file('image')->store('blogs', 'public');
            $blog->media()->create(['title' => 'Cover', 'type' => 'image', 'path' => $path]);
        }

        return redirect()->route('admin.blogs.index')->with('status', 'Blog post updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        if ($blog->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('status', 'Blog post deleted');
    }

    /**
     * Normalize URL to ensure it's a valid absolute URL
     * Adds https:// if missing and handles relative URLs
     */
    private function normalizeUrl(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }

        // Trim whitespace
        $url = trim($url);

        // If it's already a full URL (starts with http:// or https://), return as is
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        // If it starts with //, add https:
        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }

        // If it's a relative URL (starts with # or /), return as is (for internal links)
        if (strpos($url, '#') === 0 || strpos($url, '/') === 0) {
            return $url;
        }

        // If it looks like a domain (contains a dot and doesn't start with special chars), add https://
        if (strpos($url, '.') !== false && !preg_match('/^[#\/]/', $url)) {
            return 'https://' . $url;
        }

        // For any other case, return as is (might be a route name or invalid)
        return $url;
    }
}
