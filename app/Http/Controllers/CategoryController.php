<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('navLinksMany')->orderBy('position')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        Log::info('CategoryController@store called', ['payload' => $request->all()]);
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:32',
                'position' => 'nullable|integer',
            ]);
            if (empty($data['slug'])) $data['slug'] = str()->slug($data['name']);
            $created = Category::create($data);
            Log::info('Category created', ['id' => $created->id]);
            return redirect()->route('admin.categories.index')->with('status','Category created');
        } catch (\Throwable $e) {
            Log::error('Category create failed', ['error'=>$e->getMessage()]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Category $category)
    {
        $category->load('navLinksMany.navItem');
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        Log::info('CategoryController@update called', ['id'=>$category->id, 'payload'=>$request->all()]);
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:32',
                'position' => 'nullable|integer',
            ]);
            if (empty($data['slug'])) $data['slug'] = str()->slug($data['name']);
            $category->update($data);
            Log::info('Category updated', ['id'=>$category->id]);
            return redirect()->route('admin.categories.index')->with('status','Category updated');
        } catch (\Throwable $e) {
            Log::error('Category update failed', ['id'=>$category->id, 'error'=>$e->getMessage()]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('status','Category deleted');
    }
}
