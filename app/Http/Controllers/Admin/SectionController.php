<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    /**
     * Display sections for a specific category
     */
    public function index(Category $category, Request $request)
    {
        $sections = $category->sections()->orderBy('position')->get();
        
        // Get the item if provided via query parameter
        $item = null;
        if ($request->has('item')) {
            $item = \App\Models\CategoryItem::find($request->get('item'));
            // Ensure item belongs to this category
            if ($item && $item->category_id !== $category->id) {
                $item = null;
            }
        }
        
        return view('admin.sections.index', compact('category', 'sections', 'item'));
    }

    /**
     * Show the form for creating a new section
     */
    public function create(Category $category, Request $request)
    {
        // Get the item if provided via query parameter
        $item = null;
        if ($request->has('item')) {
            $item = \App\Models\CategoryItem::find($request->get('item'));
            // Ensure item belongs to this category
            if ($item && $item->category_id !== $category->id) {
                $item = null;
            }
        }
        
        return view('admin.sections.create', compact('category', 'item'));
    }

    /**
     * Store a newly created section
     */
    public function store(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            
            // Ensure unique slug within category
            while (Section::where('category_id', $category->id)
                ->where('slug', $slug)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        } else {
            // Validate unique slug within category
            $providedSlug = Str::slug($data['slug']);
            $slug = $providedSlug;
            $counter = 1;
            
            while (Section::where('category_id', $category->id)
                ->where('slug', $slug)
                ->exists()) {
                $slug = $providedSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        }

        $data['category_id'] = $category->id;
        $data['position'] = $data['position'] ?? 0;

        Section::create($data);

        // If item was provided, redirect back to items page
        if ($request->has('item')) {
            $item = \App\Models\CategoryItem::find($request->get('item'));
            if ($item && $item->category_id === $category->id) {
                $nav = $category->navLinksMany->first()->navItem ?? null;
                $link = $category->navLinksMany->first() ?? null;
                if ($nav && $link) {
                    return redirect()->route('admin.nav.links.categories.items.index', [$nav, $link, $category])
                        ->with('status', 'Section created');
                }
            }
        }

        return redirect()->route('admin.sections.index', $category)
            ->with('status', 'Section created');
    }

    /**
     * Show the form for editing a section
     */
    public function edit(Category $category, Section $section)
    {
        // Ensure section belongs to category
        if ($section->category_id !== $category->id) {
            abort(404);
        }
        
        return view('admin.sections.edit', compact('category', 'section'));
    }

    /**
     * Update a section
     */
    public function update(Request $request, Category $category, Section $section)
    {
        // Ensure section belongs to category
        if ($section->category_id !== $category->id) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        // Handle slug generation
        if (empty($data['slug'])) {
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (Section::where('category_id', $category->id)
                ->where('slug', $slug)
                ->where('id', '!=', $section->id)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        } else {
            $providedSlug = Str::slug($data['slug']);
            $slug = $providedSlug;
            $counter = 1;
            
            while (Section::where('category_id', $category->id)
                ->where('slug', $slug)
                ->where('id', '!=', $section->id)
                ->exists()) {
                $slug = $providedSlug . '-' . $counter;
                $counter++;
            }
            
            $data['slug'] = $slug;
        }

        $section->update($data);

        return redirect()->route('admin.sections.index', $category)
            ->with('status', 'Section updated');
    }

    /**
     * Remove a section
     */
    public function destroy(Category $category, Section $section)
    {
        // Ensure section belongs to category
        if ($section->category_id !== $category->id) {
            abort(404);
        }

        $section->delete();

        // If item was provided, redirect back to items page
        if ($request->has('redirect_to')) {
            return redirect($request->get('redirect_to'))
                ->with('status', 'Section deleted');
        }

        return redirect()->route('admin.sections.index', $category)
            ->with('status', 'Section deleted');
    }
}

