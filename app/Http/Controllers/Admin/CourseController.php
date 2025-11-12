<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HandlesTranslations;
use App\Models\Course;
use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    use HandlesTranslations;
    
    protected function getTranslatableFields(): array
    {
        return ['title', 'provider', 'key_skills', 'module_outline', 'takeaways'];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::where('user_id', Auth::id())->latest('completed_at')->latest('issued_at')->paginate(12);
        return view('admin.courses.index', compact('courses'));
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
                
                // If nav item has categories, filter by them; otherwise show all
                if (!empty($navItemCategoryIds)) {
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
                    // Fallback to all categories/sections if nav item has none
                    $categories = Category::where('user_id', $userId)->orderBy('name')->get();
                    $sections = CategoryItem::where('user_id', $userId)
                        ->with('category')
                        ->orderBy('category_id')
                        ->orderBy('position')
                        ->get();
                }
            } else {
                // Nav item not found, show all
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
        
        // If this is an AJAX request (for modal), return just the form
        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.courses.create', compact('categories', 'allTags', 'sections'))
                ->render();
        }
        
        return view('admin.courses.create', compact('categories', 'allTags', 'sections'));
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
                'course_url' => 'required|url|max:500', // Required per validation rules
                'instructor_organization' => 'nullable|string|max:255',
                'difficulty' => 'nullable|in:Beginner,Intermediate,Advanced',
                'estimated_hours' => 'nullable|string|max:255',
                'prerequisites' => 'nullable|string',
                'key_skills' => 'nullable|array',
                'key_skills.en' => 'nullable|string',
                'key_skills.ja' => 'nullable|string',
                'module_outline' => 'nullable|array',
                'module_outline.en' => 'nullable|string',
                'module_outline.ja' => 'nullable|string',
                'assessments_grading' => 'nullable|string',
                'artifacts_assignments' => 'nullable|string',
                'highlight_project_title' => 'nullable|string|max:255',
                'highlight_project_goal' => 'nullable|string',
                'highlight_project_link' => 'nullable|url|max:500',
                'proof_completion_url' => 'nullable|url|max:500',
                'takeaways' => 'nullable|array',
                'takeaways.en' => 'nullable|string',
                'takeaways.ja' => 'nullable|string',
                'applied_in' => 'nullable|string',
                'next_actions' => 'nullable|string',
                'status' => 'required|in:in_progress,completed,retired', // Required per validation rules
                'completion_percent' => 'nullable|integer|min:0|max:100',
                'credential_id' => 'nullable|string|max:255',
                'verify_url' => 'nullable|url|max:500',
                'issued_at' => 'nullable|date',
                'completed_at' => 'nullable|date',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
                'sections' => 'nullable|array',
                'sections.*' => 'exists:category_items,id',
                'tags' => 'nullable|string',
                'image' => 'nullable|image|max:8192', // Certificate/image file
                'screenshots' => 'nullable|array',
                'screenshots.*' => 'image|max:8192',
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

        // Quality guardrails: If Status = Completed, require Completed At and one evidence
        if ($data['status'] === 'completed') {
            if (empty($data['completed_at'])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Completed At date is required when status is Completed.',
                        'errors' => ['completed_at' => ['Completed At date is required when status is Completed.']]
                    ], 422);
                }
                return back()->withErrors([
                    'completed_at' => 'Completed At date is required when status is Completed.'
                ])->withInput();
            }
            
            // Require one evidence: certificate file OR artifact link OR proof_completion_url
            $hasEvidence = $request->hasFile('image') || 
                          !empty($data['artifacts_assignments']) || 
                          !empty($data['proof_completion_url']);
            
            if (!$hasEvidence) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'For completed courses, please provide: Certificate Image, Artifacts/Assignments, or Proof Completion URL.',
                        'errors' => ['proof_completion_url' => ['One evidence field is required for completed courses.']]
                    ], 422);
                }
                return back()->withErrors([
                    'proof_completion_url' => 'For completed courses, please provide: Certificate Image, Artifacts/Assignments, or Proof Completion URL.'
                ])->withInput();
            }
        }

        // Normalize dates to YYYY-MM-DD
        if (!empty($data['issued_at'])) {
            try {
                $data['issued_at'] = \Carbon\Carbon::parse($data['issued_at'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['issued_at']);
            }
        }
        if (!empty($data['completed_at'])) {
            try {
                $data['completed_at'] = \Carbon\Carbon::parse($data['completed_at'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['completed_at']);
            }
        }

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());

        $data['user_id'] = Auth::id();
        $course = Course::create($data);
        
        // Handle image upload (certificate/proof)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('courses', 'public');
            $course->media()->create([
                'path' => $path,
                'type' => 'image',
                'name' => $file->getClientOriginalName(),
            ]);
        }
        
        // Handle screenshots
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('courses/screenshots', 'public');
                $course->media()->create([
                    'path' => $path,
                    'type' => 'image',
                    'name' => $file->getClientOriginalName(),
                ]);
            }
        }
        
        // Sync categories
        if ($request->filled('categories')) {
            $course->categories()->sync($request->categories);
        }
        
        // Sync sections
        if ($request->filled('sections')) {
            $course->sections()->sync($request->sections);
        } else {
            $course->sections()->sync([]);
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
            $course->tags()->sync($tagIds);
        }
        
        // If this is an AJAX request (from modal), return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Course created successfully',
                'course' => $course->load('categories', 'sections', 'media')
            ]);
        }

        return redirect()->route('admin.courses.index')->with('status', 'Course created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course, Request $request)
    {
        $course->load('categories', 'sections', 'tags', 'media');
        
        // Filter categories and sections if navigation context is provided
        if ($request->has('nav_item_id')) {
            $navItem = \App\Models\NavItem::find($request->nav_item_id);
            if ($navItem) {
                // Get categories from this NavItem's NavLinks, plus the course's current categories
                $navItemCategoryIds = $navItem->links()->with('categories')->get()
                    ->pluck('categories')
                    ->flatten()
                    ->pluck('id')
                    ->unique()
                    ->toArray();
                
                $currentCategoryIds = $course->categories->pluck('id')->toArray();
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
        
        // If this is an AJAX request (for modal), return just the form content
        if ($request->ajax() || $request->has('ajax')) {
            return view('admin.courses.edit', compact('course', 'categories', 'allTags', 'sections'));
        }
        
        return view('admin.courses.edit', compact('course', 'categories', 'allTags', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
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
                'course_url' => 'required|url|max:500', // Required per validation rules
                'instructor_organization' => 'nullable|string|max:255',
                'difficulty' => 'nullable|in:Beginner,Intermediate,Advanced',
                'estimated_hours' => 'nullable|string|max:255',
                'prerequisites' => 'nullable|string',
                'key_skills' => 'nullable|array',
                'key_skills.en' => 'nullable|string',
                'key_skills.ja' => 'nullable|string',
                'module_outline' => 'nullable|array',
                'module_outline.en' => 'nullable|string',
                'module_outline.ja' => 'nullable|string',
                'assessments_grading' => 'nullable|string',
                'artifacts_assignments' => 'nullable|string',
                'highlight_project_title' => 'nullable|string|max:255',
                'highlight_project_goal' => 'nullable|string',
                'highlight_project_link' => 'nullable|url|max:500',
                'proof_completion_url' => 'nullable|url|max:500',
                'takeaways' => 'nullable|array',
                'takeaways.en' => 'nullable|string',
                'takeaways.ja' => 'nullable|string',
                'applied_in' => 'nullable|string',
                'next_actions' => 'nullable|string',
                'status' => 'required|in:in_progress,completed,retired', // Required per validation rules
                'completion_percent' => 'nullable|integer|min:0|max:100',
                'credential_id' => 'nullable|string|max:255',
                'verify_url' => 'nullable|url|max:500',
                'issued_at' => 'nullable|date',
                'completed_at' => 'nullable|date',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
                'sections' => 'nullable|array',
                'sections.*' => 'exists:category_items,id',
                'tags' => 'nullable|string',
                'image' => 'nullable|image|max:8192', // Certificate/image file
                'screenshots' => 'nullable|array',
                'screenshots.*' => 'image|max:8192',
                'delete_image' => 'nullable|integer|exists:media,id',
                'delete_screenshots' => 'nullable|array',
                'delete_screenshots.*' => 'integer|exists:media,id',
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

        // Quality guardrails: If Status = Completed, require Completed At and one evidence
        if ($data['status'] === 'completed') {
            if (empty($data['completed_at'])) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Completed At date is required when status is Completed.',
                        'errors' => ['completed_at' => ['Completed At date is required when status is Completed.']]
                    ], 422);
                }
                return back()->withErrors([
                    'completed_at' => 'Completed At date is required when status is Completed.'
                ])->withInput();
            }
            
            // Require one evidence: certificate file OR artifact link OR proof_completion_url OR existing image
            $hasImage = $course->media()->where('type', 'image')->exists();
            $hasEvidence = $request->hasFile('image') || 
                          !empty($data['artifacts_assignments']) || 
                          !empty($data['proof_completion_url']) ||
                          $hasImage;
            
            if (!$hasEvidence) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'For completed courses, please provide: Certificate Image, Artifacts/Assignments, or Proof Completion URL.',
                        'errors' => ['proof_completion_url' => ['One evidence field is required for completed courses.']]
                    ], 422);
                }
                return back()->withErrors([
                    'proof_completion_url' => 'For completed courses, please provide: Certificate Image, Artifacts/Assignments, or Proof Completion URL.'
                ])->withInput();
            }
        }

        // Normalize dates to YYYY-MM-DD
        if (!empty($data['issued_at'])) {
            try {
                $data['issued_at'] = \Carbon\Carbon::parse($data['issued_at'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['issued_at']);
            }
        }
        if (!empty($data['completed_at'])) {
            try {
                $data['completed_at'] = \Carbon\Carbon::parse($data['completed_at'])->format('Y-m-d');
            } catch (\Exception $e) {
                unset($data['completed_at']);
            }
        }

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());

        $course->update($data);
        
        // Handle image deletion
        if ($request->filled('delete_image')) {
            $media = $course->media()->find($request->delete_image);
            if ($media) {
                \Storage::disk('public')->delete($media->path);
                $media->delete();
            }
        }
        
        // Handle screenshot deletion
        if ($request->filled('delete_screenshots')) {
            foreach ($request->delete_screenshots as $mediaId) {
                $media = $course->media()->find($mediaId);
                if ($media) {
                    \Storage::disk('public')->delete($media->path);
                    $media->delete();
                }
            }
        }
        
        // Handle image upload (certificate/proof)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('courses', 'public');
            $course->media()->create([
                'path' => $path,
                'type' => 'image',
                'name' => $file->getClientOriginalName(),
            ]);
        }
        
        // Handle screenshots
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('courses/screenshots', 'public');
                $course->media()->create([
                    'path' => $path,
                    'type' => 'image',
                    'name' => $file->getClientOriginalName(),
                ]);
            }
        }
        
        // Sync categories
        if ($request->filled('categories')) {
            $course->categories()->sync($request->categories);
        } else {
            $course->categories()->sync([]);
        }
        
        // Sync sections
        if ($request->filled('sections')) {
            $course->sections()->sync($request->sections);
        } else {
            $course->sections()->sync([]);
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
            $course->tags()->sync($tagIds);
        } else {
            $course->tags()->sync([]);
        }
        
        // If this is an AJAX request (from modal), return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully',
                'course' => $course->load('categories', 'sections', 'media')
            ]);
        }
        
        return redirect()->route('admin.courses.index')->with('status', 'Course updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('status', 'Course deleted');
    }
}
