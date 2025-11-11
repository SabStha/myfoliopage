<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $testimonials = Testimonial::ordered()->paginate(20);
        return view('admin.testimonials.index', compact('testimonials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.testimonials.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'photo_url' => 'nullable|string|max:500',
            'sns_url' => 'nullable|url|max:500',
            'position' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'images.*' => 'nullable|image|max:4096',
        ]);

        $data['is_published'] = $request->has('is_published');
        $data['position'] = $data['position'] ?? 0;

        $testimonial = Testimonial::create($data);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('testimonials', 'public');
                $testimonial->media()->create(['title' => 'Photo', 'type' => 'image', 'path' => $path]);
            }
        }

        return redirect()->route('admin.testimonials.index')
            ->with('status', 'Testimonial created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'photo_url' => 'nullable|string|max:500',
            'sns_url' => 'nullable|url|max:500',
            'position' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'images.*' => 'nullable|image|max:4096',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:media,id',
        ]);

        $data['is_published'] = $request->has('is_published');
        $data['position'] = $data['position'] ?? 0;

        $testimonial->update($data);

        // Handle image deletion
        if ($request->has('delete_images')) {
            $testimonial->media()->whereIn('id', $request->delete_images)->delete();
        }

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('testimonials', 'public');
                $testimonial->media()->create(['title' => 'Photo', 'type' => 'image', 'path' => $path]);
            }
        }

        return redirect()->route('admin.testimonials.index')
            ->with('status', 'Testimonial updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        // Delete associated media
        $testimonial->media()->delete();
        
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('status', 'Testimonial deleted successfully!');
    }
}
