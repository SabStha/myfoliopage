<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavItem;
use App\Models\NavLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class NavLinkController extends Controller
{
    public function index(NavItem $nav)
    {
        if ($nav->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        // Load categories using the many-to-many relationship, filtered by user
        $links = $nav->links()
            ->where('user_id', Auth::id())
            ->with('categories')
            ->orderBy('position')
            ->get();
        
        return view('admin.nav_links.index', compact('nav','links'));
    }

    public function create(NavItem $nav)
    {
        if ($nav->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        // Show all categories that belong to this user and are used by ANY NavLink in this NavItem
        $categoryIds = $nav->links()
            ->where('user_id', Auth::id())
            ->with('categories')
            ->get()
            ->pluck('categories')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();
        
        // Get categories that are linked to NavLinks in this NavItem, filtered by user_id for security
        // Also include categories without user_id if they're linked (for backward compatibility)
        if (!empty($categoryIds)) {
            $categories = \App\Models\Category::where(function($query) use ($categoryIds) {
                $query->whereIn('id', $categoryIds)
                    ->where(function($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhereNull('user_id'); // Include categories without user_id for backward compatibility
                    });
            })
            ->orderBy('position')
            ->get();
        } else {
            // No categories linked yet - show empty list
            $categories = collect([]);
        }
        
        return view('admin.nav_links.create', compact('nav','categories'));
    }

    public function store(Request $request, NavItem $nav)
    {
        if ($nav->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'url'=>'nullable|url|max:1024',
            'proof_url'=>'nullable|url|max:1024',
            'progress'=>'nullable|integer|min:0|max:100',
            'issued_at'=>'nullable|date',
            'notes'=>'nullable|string',
            'position'=>'nullable|integer',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'image' => 'nullable|image|max:5120',
            'document' => 'nullable|mimetypes:application/pdf|max:10240',
        ], [
            'title.required' => 'The title field is required. Please enter at least one language.',
            'title.array' => 'The title must be an array.',
        ]);
        
        // Validate that at least one title field is filled
        if (empty($data['title']['en']) && empty($data['title']['ja'])) {
            return back()->withErrors(['title' => 'Please enter a title in at least one language.'])->withInput();
        }
        
        // Process title translation
        if (isset($data['title']) && is_array($data['title'])) {
            $data['title'] = [
                'en' => $data['title']['en'] ?? '',
                'ja' => $data['title']['ja'] ?? '',
            ];
        }
        
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('nav_links', 'public');
        }
        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('nav_links', 'public');
        }
        
        // Store the NavLink first, then sync categories
        $categories = $data['categories'] ?? [];
        unset($data['categories']);
        $data['user_id'] = Auth::id();
        $navLink = $nav->links()->create($data);
        
        // Sync categories (many-to-many)
        $navLink->categories()->sync($categories);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Item created',
                'nav_link_id' => $navLink->id
            ]);
        }
        
        return redirect()->route('admin.nav.links.index', $nav)->with('status','Item created');
    }

    public function edit(NavItem $nav, NavLink $link, Request $request)
    {
        // Ensure we're editing a NavLink, not a NavItem
        if (!$link instanceof \App\Models\NavLink) {
            abort(404, 'NavLink not found');
        }
        
        if ($nav->user_id !== Auth::id() || $link->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        // Verify the link belongs to this nav item
        if ($link->nav_item_id !== $nav->id) {
            abort(404, 'NavLink does not belong to this NavItem');
        }
        
        $link->load('categories');
        // Only show categories that are used by NavLinks in this NavItem
        $categoryIds = $nav->links()->with('categories')->get()
            ->pluck('categories')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();
        
        // Get categories that are already associated with this nav's links, plus the link's current categories
        $currentCategoryIds = $link->categories->pluck('id')->toArray();
        $allRelevantCategoryIds = array_unique(array_merge($categoryIds, $currentCategoryIds));
        
        $categories = \App\Models\Category::whereIn('id', $allRelevantCategoryIds)
            ->orderBy('position')
            ->get();
        
        // If this is an AJAX request (for modal), return just the form content
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.nav_links.edit', compact('nav','link','categories'))
                ->render();
        }
        
        return view('admin.nav_links.edit', compact('nav','link','categories'));
    }

    public function update(Request $request, NavItem $nav, NavLink $link)
    {
        if ($nav->user_id !== Auth::id() || $link->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'url'=>'nullable|url|max:1024',
            'proof_url'=>'nullable|url|max:1024',
            'progress'=>'nullable|integer|min:0|max:100',
            'issued_at'=>'nullable|date',
            'notes'=>'nullable|string',
            'position'=>'nullable|integer',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean',
            'document' => 'nullable|mimetypes:application/pdf|max:10240',
            'remove_document' => 'nullable|boolean',
        ]);
        
        // Process title translation
        if (isset($data['title']) && is_array($data['title'])) {
            $data['title'] = [
                'en' => $data['title']['en'] ?? '',
                'ja' => $data['title']['ja'] ?? '',
            ];
        }
        
        if (!empty($data['remove_image'])) {
            if ($link->image_path) {
                Storage::disk('public')->delete($link->image_path);
            }
            $data['image_path'] = null;
        }
        if ($request->hasFile('image')) {
            if ($link->image_path) {
                Storage::disk('public')->delete($link->image_path);
            }
            $data['image_path'] = $request->file('image')->store('nav_links', 'public');
        }
        if (!empty($data['remove_document'])) {
            if ($link->document_path) {
                Storage::disk('public')->delete($link->document_path);
            }
            $data['document_path'] = null;
        }
        if ($request->hasFile('document')) {
            if ($link->document_path) {
                Storage::disk('public')->delete($link->document_path);
            }
            $data['document_path'] = $request->file('document')->store('nav_links', 'public');
        }
        
        // Sync categories (many-to-many)
        $categories = $data['categories'] ?? [];
        unset($data['categories']);
        $link->categories()->sync($categories);
        
        $link->update($data);
        return redirect()->route('admin.nav.links.index', $nav)->with('status','Item updated');
    }

    public function destroy(NavItem $nav, NavLink $link)
    {
        if ($nav->user_id !== Auth::id() || $link->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $link->delete();
        return redirect()->route('admin.nav.links.index', $nav)->with('status','Sub-nav deleted');
    }

    // Categories management for a specific NavLink
    public function categoriesIndex(NavItem $nav, NavLink $link)
    {
        $link->load(['categories.items' => function($query) {
            $query->orderBy('position');
        }]);
        
        // Only show categories that are used by NavLinks in this NavItem
        $categoryIds = $nav->links()->with('categories')->get()
            ->pluck('categories')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();
        
        $allCategories = \App\Models\Category::whereIn('id', $categoryIds)
            ->orderBy('position')
            ->get();
        
        $assignedCategoryIds = $link->categories->pluck('id')->toArray();
        return view('admin.nav_links.categories.index', compact('nav', 'link', 'allCategories', 'assignedCategoryIds'));
    }

    public function categoriesStore(Request $request, NavItem $nav, NavLink $link)
    {
        $data = $request->validate([
            'name' => 'required|array',
            'name.en' => 'nullable|string|max:255',
            'name.ja' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:32',
            'position' => 'nullable|integer',
        ]);
        
        // Ensure at least one language is filled
        if (empty($data['name']['en']) && empty($data['name']['ja'])) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one language (English or Japanese) must be filled.',
                    'errors' => ['name' => 'At least one language (English or Japanese) must be filled.']
                ], 422);
            }
            return back()->withErrors(['name' => 'At least one language (English or Japanese) must be filled.'])->withInput();
        }
        
        // Process name translations
        $data['name'] = [
            'en' => $data['name']['en'] ?? '',
            'ja' => $data['name']['ja'] ?? '',
        ];
        
        // Handle slug generation - use English name (or Japanese as fallback) for slug generation
        if (empty($data['slug'])) {
            $nameForSlug = $data['name']['en'] ?: $data['name']['ja'];
            $baseSlug = \Illuminate\Support\Str::slug($nameForSlug);
            $slug = $baseSlug;
            $counter = 1;
            
            // Check if slug already exists
            while (\App\Models\Category::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        } else {
            // If slug is provided, ensure it's unique
            $providedSlug = \Illuminate\Support\Str::slug($data['slug']);
            $slug = $providedSlug;
            $counter = 1;
            
            // Check if slug already exists
            while (\App\Models\Category::where('slug', $slug)->exists()) {
                $slug = $providedSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        }
        
        $data['user_id'] = Auth::id();
        $category = \App\Models\Category::create($data);
        $link->categories()->attach($category->id);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created and assigned',
                'category_id' => $category->id
            ]);
        }
        
        return redirect()->route('admin.nav.links.categories.index', [$nav, $link])->with('status', 'Category created and assigned');
    }

    public function categoriesAttach(Request $request, NavItem $nav, NavLink $link)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);
        $link->categories()->syncWithoutDetaching([$data['category_id']]);
        return redirect()->route('admin.nav.links.categories.index', [$nav, $link])->with('status', 'Category attached');
    }

    public function categoriesDetach(NavItem $nav, NavLink $link, \App\Models\Category $category)
    {
        $link->categories()->detach($category->id);
        return redirect()->route('admin.nav.links.categories.index', [$nav, $link])->with('status', 'Category detached');
    }

    public function categoriesUpdate(Request $request, NavItem $nav, NavLink $link, \App\Models\Category $category)
    {
        $data = $request->validate([
            'name' => 'required|array',
            'name.en' => 'nullable|string|max:255',
            'name.ja' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:32',
            'position' => 'nullable|integer',
            'summary' => 'nullable|array',
            'summary.en' => 'nullable|string',
            'summary.ja' => 'nullable|string',
            'animation_style' => 'nullable|string|max:255',
            'download_url' => 'nullable|url|max:500',
            'view_url' => 'nullable|url|max:500',
            'visit_url' => 'nullable|url|max:500',
            'image' => 'nullable|image|max:4096',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'remove_image' => 'nullable|boolean',
            'remove_document' => 'nullable|boolean',
        ]);
        
        // Ensure at least one language is filled for name
        if (empty($data['name']['en']) && empty($data['name']['ja'])) {
            return back()->withErrors(['name' => 'At least one language (English or Japanese) must be filled.'])->withInput();
        }
        
        // Process name translations
        $data['name'] = [
            'en' => $data['name']['en'] ?? '',
            'ja' => $data['name']['ja'] ?? '',
        ];
        
        // Process summary translations
        if (isset($data['summary']) && is_array($data['summary'])) {
            $data['summary'] = [
                'en' => $data['summary']['en'] ?? '',
                'ja' => $data['summary']['ja'] ?? '',
            ];
            // Remove if both are empty
            if (empty($data['summary']['en']) && empty($data['summary']['ja'])) {
                $data['summary'] = null;
            }
        }
        
        // Handle slug generation - use English name (or Japanese as fallback) for slug generation
        if (empty($data['slug'])) {
            $nameForSlug = $data['name']['en'] ?: $data['name']['ja'];
            $baseSlug = \Illuminate\Support\Str::slug($nameForSlug);
            $slug = $baseSlug;
            $counter = 1;
            
            // Check if slug already exists for a different category
            while (\App\Models\Category::where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        } else {
            // If slug is provided, ensure it's unique (but allow keeping current category's slug)
            $providedSlug = \Illuminate\Support\Str::slug($data['slug']);
            $slug = $providedSlug;
            $counter = 1;
            
            // Check if slug already exists for a different category
            while (\App\Models\Category::where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()) {
                $slug = $providedSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image_path && \Storage::disk('public')->exists($category->image_path)) {
                \Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->has('remove_image') && $request->boolean('remove_image')) {
            // Remove image if checkbox is checked
            if ($category->image_path && \Storage::disk('public')->exists($category->image_path)) {
                \Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = null;
        } else {
            // Keep existing image_path
            unset($data['image']);
        }
        
        // Handle document upload
        if ($request->hasFile('document')) {
            // Delete old document if exists
            if ($category->document_path && \Storage::disk('public')->exists($category->document_path)) {
                \Storage::disk('public')->delete($category->document_path);
            }
            $data['document_path'] = $request->file('document')->store('categories', 'public');
        } elseif ($request->has('remove_document') && $request->boolean('remove_document')) {
            // Remove document if checkbox is checked
            if ($category->document_path && \Storage::disk('public')->exists($category->document_path)) {
                \Storage::disk('public')->delete($category->document_path);
            }
            $data['document_path'] = null;
        } else {
            // Keep existing document_path
            unset($data['document']);
        }
        
        // Remove form-only fields before updating
        unset($data['remove_image'], $data['remove_document'], $data['image'], $data['document']);
        
        $category->update($data);
        return redirect()->route('admin.nav.links.categories.index', [$nav, $link])->with('status', 'Category updated');
    }

    public function categoriesUpdateAnimationStyle(Request $request, NavItem $nav, NavLink $link)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'animation_style' => 'nullable|string|max:255',
        ]);
        
        $category = \App\Models\Category::findOrFail($data['category_id']);
        
        // Verify the category belongs to this nav link
        if (!$link->categories->contains($category->id)) {
            return redirect()->route('admin.nav.links.categories.index', [$nav, $link])
                ->with('error', 'Category not found in this navigation link');
        }
        
        $category->update(['animation_style' => $data['animation_style']]);
        
        return redirect()->route('admin.nav.links.categories.index', [$nav, $link])
            ->with('status', 'Animation style updated successfully');
    }

    public function categoriesDestroy(NavItem $nav, NavLink $link, \App\Models\Category $category)
    {
        // Check if this is the only link before detaching
        $wasOnlyLink = $category->navLinksMany()->count() <= 1;
        
        // Detach from this link
        $link->categories()->detach($category->id);
        
        // Delete category if it had no other links
        if ($wasOnlyLink) {
            $category->delete();
            return redirect()->route('admin.nav.links.categories.index', [$nav, $link])->with('status', 'Category deleted');
        }
        return redirect()->route('admin.nav.links.categories.index', [$nav, $link])->with('status', 'Category removed from this sub-nav');
    }
    
    // Category Items Management
    public function categoriesItemsIndex(NavItem $nav, NavLink $link, \App\Models\Category $category)
    {
        $category->load(['items.navLink']);
        $navLinks = $nav->links()->orderBy('position')->get(); // Available sub-navs for linking
        
        // Get all categories that belong to this NavItem (through its NavLinks)
        $navItemCategoryIds = $nav->links()->with('categories')->get()
            ->pluck('categories')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();
        
        // Get ALL content items that belong to categories from this NavItem
        // Show items that have ANY category in the NavItem, or if they have sections linked to this NavItem's categories
        $allBookPages = \App\Models\BookPage::when(count($navItemCategoryIds) > 0, function($query) use ($navItemCategoryIds) {
            return $query->where(function($q) use ($navItemCategoryIds) {
                // Items with categories in this NavItem
                $q->whereHas('categories', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereIn('categories.id', $navItemCategoryIds);
                })
                // OR items with sections that belong to categories in this NavItem
                ->orWhereHas('sections', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereHas('category', function($catQ) use ($navItemCategoryIds) {
                        $catQ->whereIn('categories.id', $navItemCategoryIds);
                    });
                });
            });
        }, function($query) {
            // If no categories, still show all items (no filtering)
            return $query;
        })->orderBy('title')->get(['id', 'title', 'slug']);
        
        $allCodeSummaries = \App\Models\CodeSummary::when(count($navItemCategoryIds) > 0, function($query) use ($navItemCategoryIds) {
            return $query->where(function($q) use ($navItemCategoryIds) {
                $q->whereHas('categories', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereIn('categories.id', $navItemCategoryIds);
                })
                ->orWhereHas('sections', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereHas('category', function($catQ) use ($navItemCategoryIds) {
                        $catQ->whereIn('categories.id', $navItemCategoryIds);
                    });
                });
            });
        }, function($query) {
            return $query;
        })->orderBy('title')->get(['id', 'title', 'slug']);
        
        $allRooms = \App\Models\Room::when(count($navItemCategoryIds) > 0, function($query) use ($navItemCategoryIds) {
            return $query->where(function($q) use ($navItemCategoryIds) {
                $q->whereHas('categories', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereIn('categories.id', $navItemCategoryIds);
                })
                ->orWhereHas('sections', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereHas('category', function($catQ) use ($navItemCategoryIds) {
                        $catQ->whereIn('categories.id', $navItemCategoryIds);
                    });
                });
            });
        }, function($query) {
            return $query;
        })->orderBy('title')->get(['id', 'title', 'slug']);
        
        $allCertificates = \App\Models\Certificate::when(count($navItemCategoryIds) > 0, function($query) use ($navItemCategoryIds) {
            return $query->where(function($q) use ($navItemCategoryIds) {
                $q->whereHas('categories', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereIn('categories.id', $navItemCategoryIds);
                })
                ->orWhereHas('sections', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereHas('category', function($catQ) use ($navItemCategoryIds) {
                        $catQ->whereIn('categories.id', $navItemCategoryIds);
                    });
                });
            });
        }, function($query) {
            return $query;
        })->orderBy('title')->get(['id', 'title']);
        
        $allCourses = \App\Models\Course::when(count($navItemCategoryIds) > 0, function($query) use ($navItemCategoryIds) {
            return $query->where(function($q) use ($navItemCategoryIds) {
                $q->whereHas('categories', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereIn('categories.id', $navItemCategoryIds);
                })
                ->orWhereHas('sections', function($subQ) use ($navItemCategoryIds) {
                    $subQ->whereHas('category', function($catQ) use ($navItemCategoryIds) {
                        $catQ->whereIn('categories.id', $navItemCategoryIds);
                    });
                });
            });
        }, function($query) {
            return $query;
        })->orderBy('title')->get(['id', 'title']);
        
        // Load content for all sections to display in unified view
        $sectionsWithContent = [];
        foreach ($category->items->sortBy('position') as $item) {
            $bookPages = $item->bookPages()->with('categories', 'tags', 'media', 'sections')->get();
            $codeSummaries = $item->codeSummaries()->with('categories', 'tags', 'media', 'sections')->get();
            $rooms = $item->rooms()->with('categories', 'tags', 'media', 'sections')->get();
            $certificates = $item->certificates()->with('categories', 'tags', 'media', 'sections')->get();
            $courses = $item->courses()->with('categories', 'tags', 'media', 'sections')->get();
            
            $sectionsWithContent[] = [
                'item' => $item,
                'bookPages' => $bookPages,
                'codeSummaries' => $codeSummaries,
                'rooms' => $rooms,
                'certificates' => $certificates,
                'courses' => $courses,
            ];
        }
        
        return view('admin.nav_links.categories.items.index', compact(
            'nav', 'link', 'category', 'navLinks',
            'allBookPages', 'allCodeSummaries', 'allRooms', 'allCertificates', 'allCourses',
            'sectionsWithContent'
        ));
    }
    
    public function categoriesItemsStore(Request $request, NavItem $nav, NavLink $link, \App\Models\Category $category)
    {
        $data = $request->validate([
            'title' => 'nullable|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'nav_link_id' => 'nullable|exists:nav_links,id',
            'image' => 'nullable|image|max:4096',
            'url' => 'nullable|url|max:500',
            'summary' => 'nullable|array',
            'summary.en' => 'nullable|string',
            'summary.ja' => 'nullable|string',
            'download_url' => 'nullable|url|max:500',
            'view_url' => 'nullable|url|max:500',
            'visit_url' => 'nullable|url|max:500',
            'position' => 'nullable|integer',
            // Note: linked_model_type and linked_model_id are deprecated in favor of many-to-many relationships
            // Sections can now have multiple content items via the pivot table
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
        
        // Handle slug generation - ensure uniqueness
        if (empty($data['slug'])) {
            // Generate from title if available, otherwise use "item"
            $titleForSlug = '';
            if (!empty($data['title'])) {
                if (is_array($data['title'])) {
                    $titleForSlug = $data['title']['en'] ?: $data['title']['ja'] ?? '';
                } else {
                    $titleForSlug = $data['title'];
                }
            }
            $baseSlug = !empty($titleForSlug) 
                ? \Illuminate\Support\Str::slug($titleForSlug) 
                : 'item';
            $slug = $baseSlug;
            $counter = 1;
            
            // Check if slug already exists
            while (\App\Models\CategoryItem::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        } else {
            // If slug is provided, ensure it's unique
            $providedSlug = \Illuminate\Support\Str::slug($data['slug']);
            $slug = $providedSlug;
            $counter = 1;
            
            // Check if slug already exists
            while (\App\Models\CategoryItem::where('slug', $slug)->exists()) {
                $slug = $providedSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('category_items', 'public');
        }
        
        $data['category_id'] = $category->id;
        $data['user_id'] = Auth::id();
        $data['position'] = $data['position'] ?? 0;
        
        $item = \App\Models\CategoryItem::create($data);
        
        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Section created successfully',
                'item' => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                ]
            ]);
        }
        
        return redirect()->route('admin.nav.links.categories.items.index', [$nav, $link, $category])->with('status', 'Item added');
    }
    
    public function categoriesItemsUpdate(Request $request, NavItem $nav, NavLink $link, \App\Models\Category $category, \App\Models\CategoryItem $item)
    {
        $data = $request->validate([
            'title' => 'nullable|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'nav_link_id' => 'nullable|exists:nav_links,id',
            'image' => 'nullable|image|max:4096',
            'url' => 'nullable|url|max:500',
            'summary' => 'nullable|array',
            'summary.en' => 'nullable|string',
            'summary.ja' => 'nullable|string',
            'download_url' => 'nullable|url|max:500',
            'view_url' => 'nullable|url|max:500',
            'visit_url' => 'nullable|url|max:500',
            'position' => 'nullable|integer',
            'remove_image' => 'nullable|boolean',
            // Note: linked_model_type and linked_model_id are deprecated in favor of many-to-many relationships
            // Sections can now have multiple content items via the pivot table
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
        
        // Handle slug generation - ensure uniqueness when updating
        if (empty($data['slug'])) {
            // Generate from title if available
            $titleForSlug = '';
            if (!empty($data['title'])) {
                if (is_array($data['title'])) {
                    $titleForSlug = $data['title']['en'] ?: $data['title']['ja'] ?? '';
                } else {
                    $titleForSlug = $data['title'];
                }
            }
            if (empty($titleForSlug) && $item->title) {
                $itemTitle = is_array($item->title) ? ($item->title['en'] ?? $item->title['ja'] ?? '') : $item->title;
                $titleForSlug = $itemTitle;
            }
            $baseSlug = !empty($titleForSlug) 
                ? \Illuminate\Support\Str::slug($titleForSlug) 
                : 'item';
            $slug = $baseSlug;
            $counter = 1;
            
            // Check if slug already exists for a different item
            while (\App\Models\CategoryItem::where('slug', $slug)
                ->where('id', '!=', $item->id)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        } else {
            // If slug is provided, ensure it's unique (but allow keeping current item's slug)
            $providedSlug = \Illuminate\Support\Str::slug($data['slug']);
            $slug = $providedSlug;
            $counter = 1;
            
            // Check if slug already exists for a different item
            while (\App\Models\CategoryItem::where('slug', $slug)
                ->where('id', '!=', $item->id)
                ->exists()) {
                $slug = $providedSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        }
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $request->file('image')->store('category_items', 'public');
        } elseif ($request->has('remove_image') && $request->boolean('remove_image')) {
            // Remove image if checkbox is checked
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = null;
        } else {
            unset($data['image']);
        }
        
        unset($data['remove_image']);
        
        $item->update($data);
        
        return redirect()->route('admin.nav.links.categories.items.index', [$nav, $link, $category])->with('status', 'Item updated');
    }
    
    public function categoriesItemsDestroy(NavItem $nav, NavLink $link, \App\Models\Category $category, \App\Models\CategoryItem $item)
    {
        // Delete image if exists
        if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }
        
        $item->delete();
        
        return redirect()->route('admin.nav.links.categories.items.index', [$nav, $link, $category])->with('status', 'Item deleted');
    }
    
    /**
     * Show all content items (BookPages, CodeSummaries, Rooms, Certificates, Courses) linked to a CategoryItem
     */
    public function categoryItemContentIndex(NavItem $nav, NavLink $link, \App\Models\Category $category, \App\Models\CategoryItem $item)
    {
        // Ensure item belongs to category
        if ($item->category_id !== $category->id) {
            abort(404);
        }
        
        // Reload item to get fresh data (in case sections were just created)
        $item->refresh();
        
        // Check pivot table directly to see what's linked
        $pivotCourses = \DB::table('category_item_model')
            ->where('category_item_id', $item->id)
            ->where('sectionable_type', 'App\Models\Course')
            ->get();
        $pivotCertificates = \DB::table('category_item_model')
            ->where('category_item_id', $item->id)
            ->where('sectionable_type', 'App\Models\Certificate')
            ->get();
        $pivotRooms = \DB::table('category_item_model')
            ->where('category_item_id', $item->id)
            ->where('sectionable_type', 'App\Models\Room')
            ->get();
        
        // Load all content items linked to this section
        $bookPages = $item->bookPages()->with('categories', 'tags', 'media', 'sections')->get();
        $codeSummaries = $item->codeSummaries()->with('categories', 'tags', 'media', 'sections')->get();
        $rooms = $item->rooms()->with('categories', 'tags', 'media', 'sections')->get();
        $certificates = $item->certificates()->with('categories', 'tags', 'media', 'sections')->get();
        $courses = $item->courses()->with('categories', 'tags', 'media', 'sections')->get();
        
        // If pivot table has entries but relationships return empty, there might be a relationship issue
        // Let's manually load if needed
        if ($pivotCourses->isNotEmpty() && $courses->isEmpty()) {
            $courseIds = $pivotCourses->pluck('sectionable_id');
            $courses = \App\Models\Course::whereIn('id', $courseIds)
                ->with('categories', 'tags', 'media', 'sections')
                ->get();
        }
        if ($pivotCertificates->isNotEmpty() && $certificates->isEmpty()) {
            $certificateIds = $pivotCertificates->pluck('sectionable_id');
            $certificates = \App\Models\Certificate::whereIn('id', $certificateIds)
                ->with('categories', 'tags', 'media', 'sections')
                ->get();
        }
        if ($pivotRooms->isNotEmpty() && $rooms->isEmpty()) {
            $roomIds = $pivotRooms->pluck('sectionable_id');
            $rooms = \App\Models\Room::whereIn('id', $roomIds)
                ->with('categories', 'tags', 'media', 'sections')
                ->get();
        }
        
        // Get all available items for selection (for adding new items)
        // Reload sections to include newly created ones
        $allBookPages = \App\Models\BookPage::with('categories', 'sections')->orderBy('title')->get();
        $allCodeSummaries = \App\Models\CodeSummary::with('categories', 'sections')->orderBy('title')->get();
        $allRooms = \App\Models\Room::with('categories', 'sections')->orderBy('title')->get();
        $allCertificates = \App\Models\Certificate::with('categories', 'sections')->orderBy('title')->get();
        $allCourses = \App\Models\Course::with('categories', 'sections')->orderBy('title')->get();
        
        return view('admin.nav_links.categories.items.content.index', compact(
            'nav', 'link', 'category', 'item',
            'bookPages', 'codeSummaries', 'rooms', 'certificates', 'courses',
            'allBookPages', 'allCodeSummaries', 'allRooms', 'allCertificates', 'allCourses'
        ));
    }
    
    /**
     * Attach a content item to a section (CategoryItem)
     */
    public function sectionAttach(Request $request, \App\Models\CategoryItem $section)
    {
        $data = $request->validate([
            'content_type' => 'required|string|in:App\Models\BookPage,App\Models\CodeSummary,App\Models\Room,App\Models\Certificate,App\Models\Course',
            'content_ids' => 'required|array',
            'content_ids.*' => 'required|integer',
            'position' => 'nullable|integer',
        ]);
        
        $modelType = $data['content_type'];
        $contentIds = $data['content_ids'];
        $position = $data['position'] ?? 0;
        
        // Filter out IDs that are already attached to prevent duplicates
        $relationshipName = $this->getRelationshipName($modelType);
        // Get existing items as collection to avoid SQL ambiguous column error
        $existingItems = $section->{$relationshipName}()->get();
        $existingIds = $existingItems->pluck('id')->toArray();
        $newIds = array_diff($contentIds, $existingIds);
        
        if (empty($newIds)) {
            return redirect()->back()->with('error', 'All selected items are already attached to this section.');
        }
        
        // Check if all models exist
        $models = $modelType::whereIn('id', $newIds)->get();
        if ($models->count() !== count($newIds)) {
            return redirect()->back()->withErrors(['content_ids' => 'Some selected items were not found.']);
        }
        
        // Attach all selected items to section using the polymorphic relationship
        $attachData = [];
        foreach ($newIds as $modelId) {
            $attachData[$modelId] = ['position' => $position];
        }
        $section->{$relationshipName}()->syncWithoutDetaching($attachData);
        
        $count = count($newIds);
        $message = $count === 1 
            ? 'Content item added to section' 
            : "{$count} content items added to section";
        
        // Get category and nav for redirect
        $category = $section->category;
        $nav = $category->navLinksMany->first()->navItem ?? null;
        $link = $category->navLinksMany->first() ?? null;
        
        if ($nav && $link) {
            return redirect()->route('admin.nav.links.categories.items.index', [$nav, $link, $category])
                ->with('status', $message);
        }
        
        return redirect()->back()->with('status', $message);
    }
    
    /**
     * Detach a content item from a section
     */
    public function sectionDetach(Request $request, \App\Models\CategoryItem $section)
    {
        $data = $request->validate([
            'sectionable_type' => 'required|string|in:App\Models\BookPage,App\Models\CodeSummary,App\Models\Room,App\Models\Certificate,App\Models\Course',
            'sectionable_id' => 'required|integer',
        ]);
        
        $modelType = $data['sectionable_type'];
        $modelId = $data['sectionable_id'];
        
        // Detach from section
        $section->{$this->getRelationshipName($modelType)}()->detach($modelId);
        
        // Get category and nav for redirect
        $category = $section->category;
        $nav = $category->navLinksMany->first()->navItem ?? null;
        $link = $category->navLinksMany->first() ?? null;
        
        if ($nav && $link) {
            return redirect()->route('admin.nav.links.categories.items.index', [$nav, $link, $category])
                ->with('status', 'Content item removed from section');
        }
        
        return redirect()->back()->with('status', 'Content item removed from section');
    }
    
    /**
     * Get relationship name for model type
     */
    private function getRelationshipName(string $modelType): string
    {
        $map = [
            'App\Models\BookPage' => 'bookPages',
            'App\Models\CodeSummary' => 'codeSummaries',
            'App\Models\Room' => 'rooms',
            'App\Models\Certificate' => 'certificates',
            'App\Models\Course' => 'courses',
        ];
        
        return $map[$modelType] ?? 'bookPages';
    }
}


