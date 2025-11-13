<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HandlesTranslations;
use App\Models\Certificate;
use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\Tag;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CertificateController extends Controller
{
    use HandlesTranslations;
    
    protected function getTranslatableFields(): array
    {
        return ['title', 'provider', 'learning_outcomes', 'reflection'];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $certificates = Certificate::where('user_id', Auth::id())->latest('issued_at')->paginate(12);
        return view('admin.certificates.index', compact('certificates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized', 'message' => 'Please log in to continue'], 401);
            }
            return redirect()->route('login');
        }
        
        try {
            $userId = Auth::id();
            
            // Always show all categories and sections for the user when creating content
            // This ensures users can always see and select from all their categories
            $categories = Category::where('user_id', $userId)
                ->orderBy('position')
                ->orderBy('slug')
                ->get();
            
            // Get sections that belong to the user, or sections without user_id that belong to user's categories (backward compatibility)
            // First get user's category IDs
            $userCategoryIds = Category::where('user_id', $userId)->pluck('id')->toArray();
            
            $sections = CategoryItem::where(function($query) use ($userId, $userCategoryIds) {
                    $query->where('user_id', $userId)
                          ->orWhere(function($q) use ($userCategoryIds) {
                              $q->whereNull('user_id')
                                ->whereIn('category_id', $userCategoryIds);
                          });
                })
                ->with('category')
                ->orderBy('category_id')
                ->orderBy('position')
                ->get();
            
            $allTags = Tag::orderBy('name')->get();
            $projects = Project::orderBy('title')->get();
            
            // Prepare sections data for JavaScript safely
            $sectionsData = [];
            if ($sections && $sections->count() > 0) {
                try {
                    $sectionsData = $sections->map(function($s) {
                        if (!$s || !is_object($s)) {
                            return null;
                        }
                        
                        try {
                            $categoryName = 'Unknown';
                            if (isset($s->category) && $s->category && is_object($s->category)) {
                                try {
                                    $catName = $s->category->getTranslated('name');
                                    if (is_string($catName) && !empty($catName)) {
                                        $categoryName = $catName;
                                    } elseif (isset($s->category->slug) && is_string($s->category->slug)) {
                                        $categoryName = $s->category->slug;
                                    }
                                } catch (\Exception $e) {
                                    // If getTranslated fails, try slug
                                    if (isset($s->category->slug) && is_string($s->category->slug)) {
                                        $categoryName = $s->category->slug;
                                    }
                                }
                            }
                            
                            $sectionTitle = 'Untitled';
                            try {
                                $titleRaw = $s->getTranslated('title');
                                if (is_string($titleRaw) && !empty($titleRaw)) {
                                    $sectionTitle = $titleRaw;
                                } elseif (isset($s->slug) && is_string($s->slug)) {
                                    $sectionTitle = $s->slug;
                                }
                            } catch (\Exception $e) {
                                if (isset($s->slug) && is_string($s->slug)) {
                                    $sectionTitle = $s->slug;
                                }
                            }
                            
                            return [
                                'id' => isset($s->id) ? (int)$s->id : 0,
                                'name' => $sectionTitle,
                                'title' => $sectionTitle,
                                'category_id' => isset($s->category_id) ? (int)$s->category_id : null,
                                'category_name' => $categoryName
                            ];
                        } catch (\Exception $e) {
                            \Log::warning('Error processing section: ' . $e->getMessage());
                            return null;
                        }
                    })->filter(function($item) {
                        return $item !== null;
                    })->values()->all();
                } catch (\Exception $e) {
                    \Log::error('Error preparing sections data: ' . $e->getMessage());
                    $sectionsData = [];
                }
            }
            
            // If this is an AJAX request (for modal), return just the form
            if ($request->ajax() || $request->wantsJson()) {
                return view('admin.certificates.create', compact('categories', 'allTags', 'sections', 'projects', 'sectionsData'))
                    ->render();
            }
            
            return view('admin.certificates.create', compact('categories', 'allTags', 'sections', 'projects', 'sectionsData'));
        } catch (\Exception $e) {
            \Log::error('Certificate create error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            // Return empty collections on error
            $categories = collect();
            $sections = collect();
            $allTags = Tag::orderBy('name')->get();
            $projects = Project::orderBy('title')->get();
            $sectionsData = [];
            
            return view('admin.certificates.create', compact('categories', 'allTags', 'sections', 'projects', 'sectionsData'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Enhanced validation with quality guardrails
            $data = $request->validate([
                'title' => 'required|array',
                'title.en' => 'required|string|max:255',
                'title.ja' => 'nullable|string|max:255',
                'provider' => 'required|array',
                'provider.en' => 'required|string|max:255',
                'provider.ja' => 'nullable|string|max:255',
                'issued_by' => 'nullable|string|max:255',
                'credential_id' => 'nullable|string|max:255',
                'verify_url' => 'nullable|url|max:500',
                'issued_at' => 'required|date', // Required per validation rules
                'has_expiry' => 'nullable|in:0,1',
                'expiry_date' => 'nullable|date|after_or_equal:issued_at',
                'level' => 'nullable|in:Beginner,Intermediate,Advanced',
                'learning_hours' => 'nullable|integer|min:0',
                'learning_outcomes' => 'nullable|array',
                'learning_outcomes.en' => 'nullable|string',
                'learning_outcomes.ja' => 'nullable|string',
                'reflection' => 'nullable|array',
                'reflection.en' => 'nullable|string',
                'reflection.ja' => 'nullable|string',
                'status' => 'nullable|in:completed,in_progress',
                'project_id' => 'nullable|exists:projects,id',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
                'sections' => 'nullable|array',
                'sections.*' => 'exists:category_items,id',
                'tags' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240', // Certificate image file (10MB max)
            ]);
        } catch (ValidationException $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        // Quality guardrails: Require either Verify URL or uploaded file
        if (empty($data['verify_url']) && !$request->hasFile('image')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either Verify URL or Certificate Image is required.',
                    'errors' => ['verify_url' => ['Either Verify URL or Certificate Image is required.']]
                ], 422);
            }
            return back()->withErrors([
                'verify_url' => 'Either Verify URL or Certificate Image is required.'
            ])->withInput();
        }

        // Quality guardrails: If certificate has expiration → require Valid Until date
        if (!empty($data['has_expiry']) && empty($data['expiry_date'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expiry Date is required when certificate has expiration.',
                    'errors' => ['expiry_date' => ['Expiry Date is required when certificate has expiration.']]
                ], 422);
            }
            return back()->withErrors([
                'expiry_date' => 'Expiry Date is required when certificate has expiration.'
            ])->withInput();
        }

        // Normalize dates to YYYY-MM-DD
        if (!empty($data['issued_at'])) {
            try {
                $data['issued_at'] = \Carbon\Carbon::parse($data['issued_at'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['issued_at']);
            }
        }
        if (!empty($data['expiry_date'])) {
            try {
                $data['expiry_date'] = \Carbon\Carbon::parse($data['expiry_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['expiry_date']);
            }
        }

        // Handle checkbox - convert to boolean
        $data['has_expiry'] = isset($data['has_expiry']) && $data['has_expiry'] == '1';

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());
        
        // Set user_id for the certificate
        $data['user_id'] = Auth::id();

        $certificate = Certificate::create($data);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('certificates', 'public');
            
            $certificate->media()->create([
                'path' => $path,
                'type' => 'image',
                'name' => $file->getClientOriginalName(),
            ]);
        }
        
        // Sync categories
        if ($request->filled('categories')) {
            $certificate->categories()->sync($request->categories);
        }
        
        // Sync sections
        if ($request->filled('sections')) {
            $certificate->sections()->sync($request->sections);
        } else {
            $certificate->sections()->sync([]);
        }
        
        // Sync tags with quality guardrails: Cap Tags at 5; trim whitespace; lowercase
        if ($request->filled('tags')) {
            $tagNames = collect(explode(',', $request->string('tags')))
                ->map(fn($t) => trim($t))
                ->map(fn($t) => strtolower($t))
                ->filter()
                ->unique()
                ->take(5); // Cap at 5 tags
            
            $tagIds = $tagNames->map(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            });
            $certificate->tags()->sync($tagIds);
        }
        
        // If this is an AJAX request (from modal), return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate created successfully',
                'certificate' => $certificate->load('categories', 'sections', 'media')
            ]);
        }

        return redirect()->route('admin.certificates.index')->with('status', 'Certificate created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return view('admin.certificates.show', compact('certificate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificate $certificate, Request $request)
    {
        if ($certificate->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $certificate->load('categories', 'sections', 'tags');
        
        // Filter categories and sections if navigation context is provided
        if ($request->has('nav_item_id')) {
            $navItem = \App\Models\NavItem::find($request->nav_item_id);
            if ($navItem) {
                // Get categories from this NavItem's NavLinks, plus the certificate's current categories
                $navItemCategoryIds = $navItem->links()->with('categories')->get()
                    ->pluck('categories')
                    ->flatten()
                    ->pluck('id')
                    ->unique()
                    ->toArray();
                
                $currentCategoryIds = $certificate->categories->pluck('id')->toArray();
                $allRelevantCategoryIds = array_unique(array_merge($navItemCategoryIds, $currentCategoryIds));
                
                $categories = Category::whereIn('id', $allRelevantCategoryIds)->orderBy('name')->get();
                $sections = CategoryItem::with('category')
                    ->whereIn('category_id', $allRelevantCategoryIds)
                    ->orderBy('category_id')
                    ->orderBy('position')
                    ->get();
            } else {
                $categories = Category::orderBy('name')->get();
                $sections = CategoryItem::with('category')->orderBy('category_id')->orderBy('position')->get();
            }
        } else {
            $categories = Category::orderBy('name')->get();
            $sections = CategoryItem::with('category')->orderBy('category_id')->orderBy('position')->get();
        }
        
        $allTags = Tag::orderBy('name')->get();
        $projects = Project::orderBy('title')->get();
        
        // If this is an AJAX request (for modal), return just the form content
        if ($request->ajax() || $request->has('ajax')) {
            return view('admin.certificates.edit', compact('certificate', 'categories', 'allTags', 'sections', 'projects'));
        }
        
        return view('admin.certificates.edit', compact('certificate', 'categories', 'allTags', 'sections', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        // Enhanced validation with quality guardrails
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'provider' => 'required|array',
            'provider.en' => 'required|string|max:255',
            'provider.ja' => 'nullable|string|max:255',
            'issued_by' => 'nullable|string|max:255',
            'credential_id' => 'nullable|string|max:255',
            'verify_url' => 'nullable|url|max:500',
            'issued_at' => 'required|date', // Required per validation rules
            'has_expiry' => 'boolean',
            'expiry_date' => 'nullable|date|after_or_equal:issued_at',
            'level' => 'nullable|in:Beginner,Intermediate,Advanced',
            'learning_hours' => 'nullable|integer|min:0',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.en' => 'nullable|string',
            'learning_outcomes.ja' => 'nullable|string',
            'reflection' => 'nullable|array',
            'reflection.en' => 'nullable|string',
            'reflection.ja' => 'nullable|string',
            'status' => 'nullable|in:completed,in_progress',
            'project_id' => 'nullable|exists:projects,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'sections' => 'nullable|array',
            'sections.*' => 'exists:category_items,id',
            'tags' => 'nullable|string',
            'image' => 'nullable|image|max:8192', // Certificate image file
            'delete_image' => 'nullable|integer|exists:media,id',
        ]);

        // Quality guardrails: Require either Verify URL or uploaded file (or existing image)
        $hasImage = $certificate->media()->where('type', 'image')->exists();
        if (empty($data['verify_url']) && !$request->hasFile('image') && !$hasImage) {
            return back()->withErrors([
                'verify_url' => 'Either Verify URL or Certificate Image is required.'
            ])->withInput();
        }

        // Quality guardrails: If certificate has expiration → require Valid Until date
        if (!empty($data['has_expiry']) && empty($data['expiry_date'])) {
            return back()->withErrors([
                'expiry_date' => 'Expiry Date is required when certificate has expiration.'
            ])->withInput();
        }

        // Normalize dates to YYYY-MM-DD
        if (!empty($data['issued_at'])) {
            try {
                $data['issued_at'] = \Carbon\Carbon::parse($data['issued_at'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['issued_at']);
            }
        }
        if (!empty($data['expiry_date'])) {
            try {
                $data['expiry_date'] = \Carbon\Carbon::parse($data['expiry_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['expiry_date']);
            }
        }

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());

        $certificate->update($data);
        
        // Handle image deletion
        if ($request->filled('delete_image')) {
            $media = $certificate->media()->find($request->delete_image);
            if ($media) {
                \Storage::disk('public')->delete($media->path);
                $media->delete();
            }
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('certificates', 'public');
            
            $certificate->media()->create([
                'path' => $path,
                'type' => 'image',
                'name' => $file->getClientOriginalName(),
            ]);
        }
        
        // Sync categories
        if ($request->filled('categories')) {
            $certificate->categories()->sync($request->categories);
        } else {
            $certificate->categories()->sync([]);
        }
        
        // Sync sections
        if ($request->filled('sections')) {
            $certificate->sections()->sync($request->sections);
        } else {
            $certificate->sections()->sync([]);
        }
        
        // Sync tags with quality guardrails: Cap Tags at 5; trim whitespace; lowercase
        if ($request->filled('tags')) {
            $tagNames = collect(explode(',', $request->string('tags')))
                ->map(fn($t) => trim($t))
                ->map(fn($t) => strtolower($t))
                ->filter()
                ->unique()
                ->take(5); // Cap at 5 tags
            
            $tagIds = $tagNames->map(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            });
            $certificate->tags()->sync($tagIds);
        } else {
            $certificate->tags()->sync([]);
        }
        
        return redirect()->route('admin.certificates.index')->with('status', 'Certificate updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $certificate->delete();
        return redirect()->route('admin.certificates.index')->with('status', 'Certificate deleted');
    }
}
