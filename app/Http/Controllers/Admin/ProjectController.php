<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Media;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::latest('created_at')->paginate(12);
        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allTags = Tag::orderBy('name')->get();
        return view('admin.projects.create', compact('allTags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:projects,slug',
            'summary' => 'nullable|string',
            'tech_stack' => 'nullable|string|max:255',
            'repo_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'completed_at' => 'nullable|date',
            'tags' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
        ]);
        $project = Project::create($data);

        // Sync tags from comma-separated list
        if ($request->filled('tags')) {
            $tagNames = collect(explode(',', $request->string('tags')))
                ->map(fn($t) => trim($t))
                ->filter();
            $tagIds = $tagNames->map(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            });
            $project->tags()->sync($tagIds);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('projects', 'public');
            $project->media()->create(['title' => 'Cover', 'type' => 'image', 'path' => $path]);
        }
        return redirect()->route('admin.projects.index')->with('status', 'Project created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $allTags = Tag::orderBy('name')->get();
        return view('admin.projects.edit', compact('project', 'allTags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:projects,slug,' . $project->id,
            'summary' => 'nullable|string',
            'tech_stack' => 'nullable|string|max:255',
            'repo_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'completed_at' => 'nullable|date',
            'tags' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
        ]);
        $project->update($data);

        if ($request->filled('tags')) {
            $tagNames = collect(explode(',', $request->string('tags')))
                ->map(fn($t) => trim($t))
                ->filter();
            $tagIds = $tagNames->map(function ($name) {
                $slug = strtolower(str_replace(' ', '-', $name));
                return Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            });
            $project->tags()->sync($tagIds);
        } else {
            $project->tags()->sync([]);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('projects', 'public');
            $project->media()->create(['title' => 'Cover', 'type' => 'image', 'path' => $path]);
        }
        return redirect()->route('admin.projects.index')->with('status', 'Project updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('admin.projects.index')->with('status', 'Project deleted');
    }
}
