<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CodeSummary;
use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CodeSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $codeSummaries = CodeSummary::where('user_id', Auth::id())
            ->with('categories')
            ->latest()
            ->paginate(20);
        return view('admin.code-summaries.index', compact('codeSummaries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $userId = Auth::id();
        
        // Filter categories and sections if navigation context is provided
        if ($request->has('nav_item_id')) {
            $navItem = \App\Models\NavItem::where('user_id', $userId)->find($request->nav_item_id);
            if ($navItem) {
                // Get categories from this NavItem's NavLinks
                $navItemCategoryIds = $navItem->links()
                    ->where('user_id', $userId)
                    ->with('categories')
                    ->get()
                    ->pluck('categories')
                    ->flatten()
                    ->pluck('id')
                    ->unique()
                    ->toArray();
                
                $categories = Category::where('user_id', $userId)
                    ->whereIn('id', $navItemCategoryIds)
                    ->orderBy('name')
                    ->get();
                $sections = CategoryItem::where('user_id', $userId)
                    ->with('category')
                    ->whereIn('category_id', $navItemCategoryIds)
                    ->orderBy('category_id')
                    ->orderBy('position')
                    ->get();
            } else {
                $categories = collect();
                $sections = collect();
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
        
        // If this is an AJAX request (for modal), return just the form
        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.code-summaries.create', compact('categories', 'allTags', 'sections'))
                ->render();
        }
        
        return view('admin.code-summaries.create', compact('categories', 'allTags', 'sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'nullable|array',
                'title.en' => 'nullable|string|max:255',
                'title.ja' => 'nullable|string|max:255',
                'slug' => 'nullable|string|max:255|unique:code_summaries,slug',
                'code' => 'nullable|string',
                'summary' => 'nullable|array',
                'summary.en' => 'nullable|string',
                'summary.ja' => 'nullable|string',
                'language' => 'nullable|string|max:255',
                'repository_url' => 'nullable|url',
                'file_path' => 'nullable|string|max:255',
                // Context & Purpose
                'problem_statement' => 'nullable|string|max:500',
                'learning_goal' => 'nullable|string',
                'use_case' => 'nullable|string',
                // Proof & Reproducibility
                'how_to_run' => 'nullable|string',
                'expected_output' => 'nullable|string',
                'dependencies' => 'nullable|string|max:500',
                'test_status' => 'nullable|string|max:255',
                // Evaluation & Reflection
                'complexity_notes' => 'nullable|string',
                'security_notes' => 'nullable|string',
                'reflection' => 'nullable|string',
                // Traceability
                'commit_sha' => 'nullable|string|max:255',
                'license' => 'nullable|string|max:255',
                'file_path_repo' => 'nullable|string|max:500',
                // Metadata
                'framework' => 'nullable|string|max:255',
                'difficulty' => 'nullable|in:Beginner,Intermediate,Advanced',
                'time_spent' => 'nullable|integer|min:0',
                'status' => 'nullable|in:completed,in_progress',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
                'sections' => 'nullable|array',
                'sections.*' => 'exists:category_items,id',
                'tags' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'title' => ['Title is required in at least one language.']
                    ]
                ], 422);
            }
            return back()->withErrors([
                'title' => 'Title is required in at least one language.'
            ])->withInput();
        }
        if (empty($data['summary']['en']) && empty($data['summary']['ja'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'summary' => ['Summary is required in at least one language.']
                    ]
                ], 422);
            }
            return back()->withErrors([
                'summary' => 'Summary is required in at least one language.'
            ])->withInput();
        }

        // Quality guardrails: If code is empty → require Expected Output
        if (empty($data['code']) && empty($data['expected_output'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'expected_output' => ['Expected Output is required when Code is empty.']
                    ]
                ], 422);
            }
            return back()->withErrors([
                'expected_output' => 'Expected Output is required when Code is empty.'
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
                : 'code-summary';
            
            $slug = $baseSlug;
            $counter = 1;
            while (CodeSummary::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $data['slug'] = $slug;
        }

        $data['user_id'] = Auth::id();
        $codeSummary = CodeSummary::create($data);

        // Sync categories
        if ($request->filled('categories')) {
            $codeSummary->categories()->sync($request->categories);
        }

        // Sync sections
        if ($request->filled('sections')) {
            $codeSummary->sections()->sync($request->sections);
        } else {
            $codeSummary->sections()->sync([]);
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
            $codeSummary->tags()->sync($tagIds);
        }

        // If this is an AJAX request (from modal), return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Code summary created successfully',
                'codeSummary' => $codeSummary->load('categories', 'sections')
            ]);
        }

        return redirect()->route('admin.code-summaries.index')->with('status', 'Code summary created');
    }

    /**
     * Display the specified resource.
     */
    public function show(CodeSummary $codeSummary)
    {
        return redirect()->route('admin.code-summaries.edit', $codeSummary);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CodeSummary $codeSummary, Request $request)
    {
        if ($codeSummary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $codeSummary->load('categories', 'tags', 'sections');
        $userId = Auth::id();
        
        // Filter categories and sections if navigation context is provided
        if ($request->has('nav_item_id')) {
            $navItem = \App\Models\NavItem::where('user_id', $userId)->find($request->nav_item_id);
            if ($navItem) {
                // Get categories from this NavItem's NavLinks, plus the codeSummary's current categories
                $navItemCategoryIds = $navItem->links()
                    ->where('user_id', $userId)
                    ->with('categories')
                    ->get()
                    ->pluck('categories')
                    ->flatten()
                    ->pluck('id')
                    ->unique()
                    ->toArray();
                
                $currentCategoryIds = $codeSummary->categories->pluck('id')->toArray();
                $allRelevantCategoryIds = array_unique(array_merge($navItemCategoryIds, $currentCategoryIds));
                
                $categories = Category::where('user_id', $userId)
                    ->whereIn('id', $allRelevantCategoryIds)
                    ->orderBy('name')
                    ->get();
                
                // Get sections from relevant categories, plus sections already linked to this codeSummary
                $currentSectionIds = $codeSummary->sections->pluck('id')->toArray();
                $sectionsFromNav = CategoryItem::where('user_id', $userId)
                    ->with('category')
                    ->whereIn('category_id', $allRelevantCategoryIds)
                    ->orderBy('category_id')
                    ->orderBy('position')
                    ->get();
                
                $sectionsAlreadyLinked = CategoryItem::where('user_id', $userId)
                    ->whereIn('id', $currentSectionIds)
                    ->get();
                $sections = $sectionsFromNav->merge($sectionsAlreadyLinked)->unique('id');
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
        
        // If this is an AJAX request (for modal), return just the form content
        if ($request->ajax() || $request->has('ajax')) {
            return view('admin.code-summaries.edit', compact('codeSummary', 'categories', 'allTags', 'sections'));
        }
        
        return view('admin.code-summaries.edit', compact('codeSummary', 'categories', 'allTags', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CodeSummary $codeSummary)
    {
        if ($codeSummary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $data = $request->validate([
            'title' => 'nullable|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:code_summaries,slug,' . $codeSummary->id,
            'code' => 'nullable|string',
            'summary' => 'nullable|array',
            'summary.en' => 'nullable|string',
            'summary.ja' => 'nullable|string',
            'language' => 'nullable|string|max:255',
            'repository_url' => 'nullable|url',
            'file_path' => 'nullable|string|max:255',
            // Context & Purpose
            'problem_statement' => 'nullable|string|max:500',
            'learning_goal' => 'nullable|string',
            'use_case' => 'nullable|string',
            // Proof & Reproducibility
            'how_to_run' => 'nullable|string',
            'expected_output' => 'nullable|string',
            'dependencies' => 'nullable|string|max:500',
            'test_status' => 'nullable|string|max:255',
            // Evaluation & Reflection
            'complexity_notes' => 'nullable|string',
            'security_notes' => 'nullable|string',
            'reflection' => 'nullable|string',
            // Traceability
            'commit_sha' => 'nullable|string|max:255',
            'license' => 'nullable|string|max:255',
            'file_path_repo' => 'nullable|string|max:500',
            // Metadata
            'framework' => 'nullable|string|max:255',
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

        // Quality guardrails: If code is empty → require Expected Output
        if (empty($data['code']) && empty($data['expected_output'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'expected_output' => ['Expected Output is required when Code is empty.']
                    ]
                ], 422);
            }
            return back()->withErrors([
                'expected_output' => 'Expected Output is required when Code is empty.'
            ])->withInput();
        }

        $codeSummary->update($data);

        // Sync categories
        if ($request->filled('categories')) {
            $codeSummary->categories()->sync($request->categories);
        } else {
            $codeSummary->categories()->sync([]);
        }

        // Sync sections
        if ($request->filled('sections')) {
            $codeSummary->sections()->sync($request->sections);
        } else {
            $codeSummary->sections()->sync([]);
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
            $codeSummary->tags()->sync($tagIds);
        } else {
            $codeSummary->tags()->sync([]);
        }

        return redirect()->route('admin.code-summaries.index')->with('status', 'Code summary updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CodeSummary $codeSummary)
    {
        if ($codeSummary->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $codeSummary->delete();
        return redirect()->route('admin.code-summaries.index')->with('status', 'Code summary deleted');
    }
}

