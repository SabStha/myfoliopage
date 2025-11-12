<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HandlesTranslations;
use App\Models\Room;
use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    use HandlesTranslations;
    
    protected function getTranslatableFields(): array
    {
        return ['title', 'description', 'summary'];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::id();
        // Get rooms that belong to categories owned by this user
        $userCategoryIds = Category::where('user_id', $userId)->pluck('id')->toArray();
        $rooms = Room::whereHas('categories', function($query) use ($userId, $userCategoryIds) {
            $query->where('user_id', $userId)->orWhereIn('categories.id', $userCategoryIds);
        })
        ->orWhere('user_id', $userId)
        ->with('categories')
        ->latest('completed_at')
        ->paginate(20);
        return view('admin.rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $userId = Auth::id();
        
        // Always show all categories and sections for the user when creating content
        // This ensures users can always see and select from all their categories
        $categories = Category::where('user_id', $userId)
            ->orderBy('position')
            ->orderBy('slug')
            ->get();
        
        // Get sections that belong to the user, or sections without user_id that belong to user's categories (backward compatibility)
        $sections = CategoryItem::where(function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere(function($q) use ($userId) {
                          $q->whereNull('user_id')
                            ->whereHas('category', function($catQuery) use ($userId) {
                                $catQuery->where('user_id', $userId);
                            });
                      });
            })
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('position')
            ->get();
        
        $allTags = Tag::orderBy('name')->get();
        
        // If this is an AJAX request (for modal), return just the form
        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.rooms.create', compact('categories', 'allTags', 'sections'))
                ->render();
        }
        
        return view('admin.rooms.create', compact('categories', 'allTags', 'sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|array',
                'title.en' => 'required|string|max:255',
                'title.ja' => 'nullable|string|max:255',
                'slug' => 'nullable|string|max:255|unique:rooms,slug',
                'description' => 'nullable|array',
                'description.en' => 'nullable|string',
                'description.ja' => 'nullable|string',
                'summary' => 'required|array',
                'summary.en' => 'required|string',
                'summary.ja' => 'nullable|string',
                'platform' => 'nullable|string|max:255',
                'room_url' => 'nullable|url',
                'difficulty' => 'nullable|string|max:255',
                'completed_at' => 'nullable|date',
                // Learning & Purpose
                'objective_goal' => 'nullable|string',
                'key_techniques_used' => 'nullable|string',
                'tools_commands_used' => 'nullable|string',
                'attack_vector_summary' => 'nullable|string',
                'flag_evidence_proof' => 'nullable|string',
                'time_spent' => 'nullable|integer|min:0',
                'reflection_takeaways' => 'nullable|string',
                'difficulty_confirmation' => 'nullable|string|max:255',
                // Reproducibility
                'walkthrough_summary_steps' => 'nullable|string',
                'tools_environment' => 'nullable|string',
                'command_log_snippet' => 'nullable|string',
                'room_id_author' => 'nullable|string|max:255',
                'completion_screenshot_report_link' => 'nullable|url|max:500',
                // Traceability & Meta
                'platform_username' => 'nullable|string|max:255',
                'platform_profile_link' => 'nullable|url|max:500',
                'status' => 'nullable|in:completed,in_progress,retired',
                'score_points_earned' => 'nullable|integer|min:0',
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

        // Generate slug if not provided (use English title)
        if (empty($data['slug'])) {
            $titleForSlug = is_array($data['title']) ? ($data['title']['en'] ?? '') : $data['title'];
            $data['slug'] = Str::slug($titleForSlug);
        }

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());
        $data['user_id'] = Auth::id();

        $room = Room::create($data);

        // Sync categories
        if ($request->filled('categories')) {
            $room->categories()->sync($request->categories);
        }

        // Sync sections
        if ($request->filled('sections')) {
            $room->sections()->sync($request->sections);
        } else {
            $room->sections()->sync([]);
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
            $room->tags()->sync($tagIds);
        }

        // If this is an AJAX request (from modal), return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Room created successfully',
                'room' => $room->load('categories', 'sections')
            ]);
        }

        return redirect()->route('admin.rooms.index')->with('status', 'Room created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        return redirect()->route('admin.rooms.edit', $room);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room, Request $request)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $room->load('categories', 'sections', 'tags');
        $userId = Auth::id();
        
        // Filter categories and sections if navigation context is provided
        if ($request->has('nav_item_id')) {
            $navItem = \App\Models\NavItem::where('user_id', $userId)->find($request->nav_item_id);
            if ($navItem) {
                // Get categories from this NavItem's NavLinks, plus the room's current categories
                $navItemCategoryIds = $navItem->links()
                    ->where('user_id', $userId)
                    ->with('categories')
                    ->get()
                    ->pluck('categories')
                    ->flatten()
                    ->pluck('id')
                    ->unique()
                    ->toArray();
                
                $currentCategoryIds = $room->categories->pluck('id')->toArray();
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
        
        // If this is an AJAX request (for modal), return just the form content
        if ($request->ajax() || $request->has('ajax')) {
            return view('admin.rooms.edit', compact('room', 'categories', 'allTags', 'sections'));
        }
        
        return view('admin.rooms.edit', compact('room', 'categories', 'allTags', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:rooms,slug,' . $room->id,
            'description' => 'nullable|array',
            'description.en' => 'nullable|string',
            'description.ja' => 'nullable|string',
            'summary' => 'required|array',
            'summary.en' => 'required|string',
            'summary.ja' => 'nullable|string',
            'platform' => 'nullable|string|max:255',
            'room_url' => 'nullable|url',
            'difficulty' => 'nullable|string|max:255',
            'completed_at' => 'nullable|date',
            // Learning & Purpose
            'objective_goal' => 'nullable|string',
            'key_techniques_used' => 'nullable|string',
            'tools_commands_used' => 'nullable|string',
            'attack_vector_summary' => 'nullable|string',
            'flag_evidence_proof' => 'nullable|string',
            'time_spent' => 'nullable|integer|min:0',
            'reflection_takeaways' => 'nullable|string',
            'difficulty_confirmation' => 'nullable|string|max:255',
            // Reproducibility
            'walkthrough_summary_steps' => 'nullable|string',
            'tools_environment' => 'nullable|string',
            'command_log_snippet' => 'nullable|string',
            'room_id_author' => 'nullable|string|max:255',
            'completion_screenshot_report_link' => 'nullable|url|max:500',
            // Traceability & Meta
            'platform_username' => 'nullable|string|max:255',
            'platform_profile_link' => 'nullable|url|max:500',
            'status' => 'nullable|in:completed,in_progress,retired',
            'score_points_earned' => 'nullable|integer|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'sections' => 'nullable|array',
            'sections.*' => 'exists:category_items,id',
            'tags' => 'nullable|string',
        ]);

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());

        $room->update($data);

        // Sync categories
        if ($request->filled('categories')) {
            $room->categories()->sync($request->categories);
        } else {
            $room->categories()->sync([]);
        }

        // Sync sections
        if ($request->filled('sections')) {
            $room->sections()->sync($request->sections);
        } else {
            $room->sections()->sync([]);
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
            $room->tags()->sync($tagIds);
        } else {
            $room->tags()->sync([]);
        }

        return redirect()->route('admin.rooms.index')->with('status', 'Room updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('status', 'Room deleted');
    }
}

