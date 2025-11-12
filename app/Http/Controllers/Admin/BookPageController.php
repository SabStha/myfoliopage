<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookPage;
use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class BookPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Initialize empty paginator
        $bookPages = new LengthAwarePaginator([], 0, 20, 1);
        
        try {
            // Try to query the table - filter by user_id
            $userId = Auth::id();
            $bookPages = BookPage::where('user_id', $userId)
                ->with('categories')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist
            return view('admin.book-pages.index', compact('bookPages'))
                ->with('error', 'Database table not found. Please run: php artisan migrate');
        } catch (\Exception $e) {
            // Log other errors
            \Log::error('BookPageController index error: ' . $e->getMessage());
            return view('admin.book-pages.index', compact('bookPages'))
                ->with('error', 'Error: ' . $e->getMessage());
        }
        
        return view('admin.book-pages.index', compact('bookPages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Debug logging
        \Log::info('BookPageController::create called', [
            'isAjax' => $request->header('X-Requested-With') === 'XMLHttpRequest',
            'header' => $request->header('X-Requested-With'),
            'auth_check' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => $request->session()->getId(),
            'has_session' => $request->hasSession(),
        ]);
        
        // Ensure user is authenticated - middleware should handle this, but double-check
        if (!Auth::check()) {
            // Check if this is an AJAX request
            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                \Log::warning('Unauthenticated AJAX request to BookPageController::create');
                return response()->json(['error' => 'Unauthorized', 'message' => 'Please log in to continue'], 401);
            }
            return redirect()->route('login');
        }
        
        $userId = Auth::id();
        
        // Always show all categories and sections for the user when creating content
        // This ensures users can always see and select from all their categories
        $categories = Category::where('user_id', $userId)
            ->orderBy('position')
            ->orderBy('slug')
            ->get();
        
        $sections = CategoryItem::where('user_id', $userId)
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('position')
            ->get();
        
        $allTags = Tag::orderBy('name')->get();
        // Get sections grouped by category for easier selection
        $sectionsByCategory = $sections->groupBy('category_id');
        
        // If this is an AJAX request (for modal), return just the form
        // Check for AJAX request by checking the X-Requested-With header directly
        $isAjax = $request->header('X-Requested-With') === 'XMLHttpRequest' || $request->ajax() || $request->wantsJson();
        
        \Log::info('BookPageController::create - checking AJAX', [
            'isAjax' => $isAjax,
            'ajax_method' => $request->ajax(),
            'wantsJson' => $request->wantsJson(),
            'header' => $request->header('X-Requested-With'),
            'accept' => $request->header('Accept'),
        ]);
        
        if ($isAjax) {
            return view('admin.book-pages.create', compact('categories', 'allTags', 'sections', 'sectionsByCategory'))
                ->render();
        }
        
        return view('admin.book-pages.create', compact('categories', 'allTags', 'sections', 'sectionsByCategory'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Enhanced validation with quality guardrails
        $data = $request->validate([
            'title' => 'nullable|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:book_pages,slug',
            'content' => 'nullable|string',
            'summary' => 'nullable|array',
            'summary.en' => 'nullable|string',
            'summary.ja' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'book_title' => 'nullable|string|max:255',
            'page_number' => 'nullable|integer',
            'read_at' => 'nullable|date',
            // Learning outcomes & proof
            'key_objectives' => 'nullable|string',
            'reflection' => 'nullable|string',
            'applied_snippet' => 'nullable|string',
            'references' => 'nullable|string|max:500',
            // Reproducibility
            'how_to_run' => 'nullable|string',
            'result_evidence' => 'nullable|string',
            'difficulty' => 'nullable|in:Beginner,Intermediate,Advanced',
            'time_spent' => 'nullable|integer|min:0',
            'status' => 'nullable|in:completed,in_progress',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|string',
        ]);

        // Normalize title and summary arrays - ensure they have en and ja keys
        if (isset($data['title']) && is_array($data['title'])) {
            $data['title'] = [
                'en' => $data['title']['en'] ?? '',
                'ja' => $data['title']['ja'] ?? '',
            ];
        }
        if (isset($data['summary']) && is_array($data['summary'])) {
            $data['summary'] = [
                'en' => $data['summary']['en'] ?? '',
                'ja' => $data['summary']['ja'] ?? '',
            ];
        }

        // Validate that at least one language has content
        if (empty($data['title']['en']) && empty($data['title']['ja'])) {
            return back()->withErrors([
                'title' => 'Title is required in at least one language.'
            ])->withInput();
        }
        if (empty($data['summary']['en']) && empty($data['summary']['ja'])) {
            return back()->withErrors([
                'summary' => 'Summary is required in at least one language.'
            ])->withInput();
        }

        // Quality guardrails: If Applied Snippet is empty → require Result/Evidence
        if (empty($data['applied_snippet']) && empty($data['result_evidence'])) {
            return back()->withErrors([
                'result_evidence' => 'Result/Evidence is required when Applied Snippet is empty.'
            ])->withInput();
        }

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $titleForSlug = '';
            if (!empty($data['title'])) {
                if (is_array($data['title'])) {
                    $titleForSlug = $data['title']['en'] ?: $data['title']['ja'] ?? '';
                } else {
                    $titleForSlug = $data['title'];
                }
            }
            $baseSlug = !empty($titleForSlug)
                ? Str::slug($titleForSlug)
                : 'book-page';
            
            $slug = $baseSlug;
            $counter = 1;
            while (BookPage::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        // Normalize Read At to YYYY-MM-DD (ensure it's stored correctly)
        if (!empty($data['read_at'])) {
            try {
                $data['read_at'] = \Carbon\Carbon::parse($data['read_at'])->format('Y-m-d');
            } catch (\Exception $e) {
                // If parsing fails, remove it
                unset($data['read_at']);
            }
        }

        $data['user_id'] = Auth::id();
        $bookPage = BookPage::create($data);

        // Sync categories
        if ($request->filled('categories')) {
            $bookPage->categories()->sync($request->categories);
        }

        // Sync sections
        if ($request->filled('sections')) {
            $bookPage->sections()->sync($request->sections);
        } else {
            $bookPage->sections()->sync([]);
        }

        // Sync tags with quality guardrails: Cap Tags at 5; trim whitespace; lowercase
        if ($request->filled('tags')) {
            $tagNames = collect(explode(',', $request->string('tags')))
                ->map(fn($t) => trim($t))
                ->map(fn($t) => strtolower($t))
                ->filter()
                ->take(5); // Cap at 5 tags
            
            $tagIds = $tagNames->map(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            });
            $bookPage->tags()->sync($tagIds);
        }

        // If this is an AJAX request (from modal), return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Book page created successfully',
                'bookPage' => $bookPage->load('categories', 'sections')
            ]);
        }

        return redirect()->route('admin.book-pages.index')->with('status', 'Book page created');
    }

    /**
     * Display the specified resource.
     */
    public function show(BookPage $bookPage)
    {
        return redirect()->route('admin.book-pages.edit', $bookPage);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BookPage $bookPage, Request $request)
    {
        if ($bookPage->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $bookPage->load('categories', 'sections', 'tags');
        $userId = Auth::id();
        
        // Filter categories and sections if navigation context is provided
        if ($request->has('nav_item_id')) {
            $navItem = \App\Models\NavItem::where('user_id', $userId)->find($request->nav_item_id);
            if ($navItem) {
                // Get categories from this NavItem's NavLinks, plus the bookPage's current categories
                $navItemCategoryIds = $navItem->links()
                    ->where('user_id', $userId)
                    ->with('categories')
                    ->get()
                    ->pluck('categories')
                    ->flatten()
                    ->pluck('id')
                    ->unique()
                    ->toArray();
                
                $currentCategoryIds = $bookPage->categories->pluck('id')->toArray();
                $allRelevantCategoryIds = array_unique(array_merge($navItemCategoryIds, $currentCategoryIds));
                
                $categories = Category::where('user_id', $userId)
                    ->whereIn('id', $allRelevantCategoryIds)
                    ->orderBy('name')
                    ->get();
                $sections = CategoryItem::where('user_id', $userId)
                    ->with('category')
                    ->whereIn('category_id', $allRelevantCategoryIds)
                    ->orderBy('category_id')
                    ->orderBy('position')
                    ->get();
            } else {
                $categories = Category::where('user_id', $userId)->orderBy('name')->get();
                $sections = CategoryItem::where('user_id', $userId)
                    ->with('category')
                    ->orderBy('category_id')
                    ->orderBy('position')
                    ->get();
            }
        } else {
            $categories = Category::where('user_id', $userId)->orderBy('name')->get();
            $sections = CategoryItem::where('user_id', $userId)
                ->with('category')
                ->orderBy('category_id')
                ->orderBy('position')
                ->get();
        }
        
        $allTags = Tag::orderBy('name')->get();
        // Get sections grouped by category for easier selection
        $sectionsByCategory = $sections->groupBy('category_id');
        
        // If this is an AJAX request (for modal), return just the form content
        if ($request->ajax() || $request->has('ajax')) {
            return view('admin.book-pages.edit', compact('bookPage', 'categories', 'allTags', 'sections', 'sectionsByCategory'));
        }
        
        return view('admin.book-pages.edit', compact('bookPage', 'categories', 'allTags', 'sections', 'sectionsByCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookPage $bookPage)
    {
        if ($bookPage->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        // Enhanced validation with quality guardrails
        $data = $request->validate([
            'title' => 'nullable|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:book_pages,slug,' . $bookPage->id,
            'content' => 'nullable|string',
            'summary' => 'nullable|array',
            'summary.en' => 'nullable|string',
            'summary.ja' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'book_title' => 'nullable|string|max:255',
            'page_number' => 'nullable|integer',
            'read_at' => 'nullable|date',
            // Learning outcomes & proof
            'key_objectives' => 'nullable|string',
            'reflection' => 'nullable|string',
            'applied_snippet' => 'nullable|string',
            'references' => 'nullable|string|max:500',
            // Reproducibility
            'how_to_run' => 'nullable|string',
            'result_evidence' => 'nullable|string',
            'difficulty' => 'nullable|in:Beginner,Intermediate,Advanced',
            'time_spent' => 'nullable|integer|min:0',
            'status' => 'nullable|in:completed,in_progress',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'sections' => 'nullable|array',
            'sections.*' => 'exists:category_items,id',
            'tags' => 'nullable|string',
        ]);

        // Normalize title and summary arrays - ensure they have en and ja keys
        if (isset($data['title']) && is_array($data['title'])) {
            $data['title'] = [
                'en' => $data['title']['en'] ?? '',
                'ja' => $data['title']['ja'] ?? '',
            ];
        }
        if (isset($data['summary']) && is_array($data['summary'])) {
            $data['summary'] = [
                'en' => $data['summary']['en'] ?? '',
                'ja' => $data['summary']['ja'] ?? '',
            ];
        }

        // Quality guardrails: If Applied Snippet is empty → require Result/Evidence
        if (empty($data['applied_snippet']) && empty($data['result_evidence'])) {
            return back()->withErrors([
                'result_evidence' => 'Result/Evidence is required when Applied Snippet is empty.'
            ])->withInput();
        }

        // Normalize Read At to YYYY-MM-DD
        if (!empty($data['read_at'])) {
            try {
                $data['read_at'] = \Carbon\Carbon::parse($data['read_at'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['read_at']);
            }
        }

        $bookPage->update($data);

        // Sync categories
        if ($request->filled('categories')) {
            $bookPage->categories()->sync($request->categories);
        } else {
            $bookPage->categories()->sync([]);
        }

        // Sync sections
        if ($request->filled('sections')) {
            $bookPage->sections()->sync($request->sections);
        } else {
            $bookPage->sections()->sync([]);
        }

        // Sync tags with quality guardrails: Cap Tags at 5; trim whitespace; lowercase
        if ($request->filled('tags')) {
            $tagNames = collect(explode(',', $request->string('tags')))
                ->map(fn($t) => trim($t))
                ->map(fn($t) => strtolower($t))
                ->filter()
                ->take(5); // Cap at 5 tags
            
            $tagIds = $tagNames->map(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            });
            $bookPage->tags()->sync($tagIds);
        } else {
            $bookPage->tags()->sync([]);
        }

        return redirect()->route('admin.book-pages.index')->with('status', 'Book page updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BookPage $bookPage)
    {
        if ($bookPage->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $bookPage->delete();
        return redirect()->route('admin.book-pages.index')->with('status', 'Book page deleted');
    }

    /**
     * Process image with AI to extract text and information
     */
    public function aiCapture(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
        ]);

        try {
            $image = $request->file('image');
            
            // Convert image to base64
            $imageData = base64_encode(file_get_contents($image->getRealPath()));
            $mimeType = $image->getMimeType();
            
            // Initialize OpenAI client
            $apiKey = env('OPENAI_API_KEY');
            if (!$apiKey) {
                throw new \Exception('OpenAI API key not configured. Please set OPENAI_API_KEY in your .env file.');
            }
            
            $client = (new \OpenAI\Factory())
                ->withApiKey($apiKey)
                ->make();
            
            // Use GPT-4 Vision to extract text from the book page
            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'You are analyzing a book page image. Extract all the text content from this image and provide:
1. A title (if visible, or generate one based on the content)
2. The full text content of the page
3. A brief summary (2-3 sentences) of what this page covers

Format your response as JSON with the following structure:
{
  "title": "Title of the page or chapter",
  "content": "Full extracted text from the page",
  "summary": "Brief summary of the content"
}'
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$imageData}"
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.3
            ]);
            
            $aiResponse = $response->choices[0]->message->content;
            
            // Try to parse JSON response
            $extractedData = json_decode($aiResponse, true);
            
            // If JSON parsing fails, try to extract from text
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Fallback: extract information from text response
                $title = '';
                $content = $aiResponse;
                $summary = '';
                
                // Try to find title pattern
                if (preg_match('/title["\']?\s*:\s*["\']([^"\']+)["\']/i', $aiResponse, $titleMatch)) {
                    $title = $titleMatch[1];
                } elseif (preg_match('/#\s*(.+)/', $aiResponse, $titleMatch)) {
                    $title = trim($titleMatch[1]);
                }
                
                // Try to find summary pattern
                if (preg_match('/summary["\']?\s*:\s*["\']([^"\']+)["\']/i', $aiResponse, $summaryMatch)) {
                    $summary = $summaryMatch[1];
                }
                
                // If no title found, use first line or generate one
                if (empty($title)) {
                    $lines = explode("\n", trim($aiResponse));
                    $title = !empty($lines[0]) ? substr($lines[0], 0, 100) : 'Book Page Content';
                }
                
                return response()->json([
                    'success' => true,
                    'title' => $title,
                    'content' => $content,
                    'summary' => $summary ?: 'Summary will be generated from the content.'
                ]);
            }
            
            // Return parsed JSON data
            return response()->json([
                'success' => true,
                'title' => $extractedData['title'] ?? 'Book Page',
                'content' => $extractedData['content'] ?? $aiResponse,
                'summary' => $extractedData['summary'] ?? 'Summary extracted from the page content.'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('AI Capture Error: ' . $e->getMessage());
            \Log::error('AI Capture Stack Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing image: ' . $e->getMessage()
            ], 500);
        }
    }
}
