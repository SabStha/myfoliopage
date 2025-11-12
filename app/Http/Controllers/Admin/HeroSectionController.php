<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use App\Models\Media;
use App\Models\PageSection;
use App\Traits\HandlesTranslations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class HeroSectionController extends Controller
{
    use HandlesTranslations;
    
    protected function getTranslatableFields(): array
    {
        return [
            'badge_text',
            'heading_text',
            'subheading_text',
            'button1_text',
            'button2_text',
        ];
    }
    public function edit()
    {
        $userId = Auth::id();
        $heroSection = HeroSection::where('user_id', $userId)->first();
        if (!$heroSection) {
            // Create with factory defaults from migration
            $heroSection = HeroSection::create(['user_id' => $userId]);
            // Set default text values that are nullable in migration
            $heroSection->update([
                'heading_text' => json_encode(['en' => '1️⃣ Typing Animation (Developer-style intro)', 'ja' => '1️⃣ タイピングアニメーション（開発者スタイルの紹介）']),
                'subheading_text' => json_encode(['en' => 'Each letter "types in," like a command line. Feels personal and smart.', 'ja' => '各文字が「入力される」ように、コマンドラインのように。個人的でスマートな感じ。']),
                'button1_link' => route('projects'),
                'navigation_links' => [
                    ['id' => 1, 'text' => ['en' => 'About', 'ja' => ''], 'section_id' => 'discover', 'order' => 1],
                    ['id' => 2, 'text' => ['en' => 'Projects', 'ja' => ''], 'section_id' => 'my-works', 'order' => 2],
                    ['id' => 3, 'text' => ['en' => 'Contacts', 'ja' => ''], 'section_id' => 'contact', 'order' => 3],
                ],
            ]);
        }
        
        // Ensure navigation_links exists and is an array
        if (empty($heroSection->navigation_links) || !is_array($heroSection->navigation_links)) {
            $heroSection->navigation_links = [
                ['id' => 1, 'text' => ['en' => 'About', 'ja' => ''], 'section_id' => 'discover', 'order' => 1],
                ['id' => 2, 'text' => ['en' => 'Projects', 'ja' => ''], 'section_id' => 'my-works', 'order' => 2],
                ['id' => 3, 'text' => ['en' => 'Contacts', 'ja' => ''], 'section_id' => 'contact', 'order' => 3],
            ];
            $heroSection->save();
        } else {
            // Normalize existing navigation links - convert string text to array format
            $navigationLinks = $heroSection->navigation_links;
            $normalized = false;
            
            foreach ($navigationLinks as $key => $link) {
                if (isset($link['text']) && is_string($link['text'])) {
                    $navigationLinks[$key]['text'] = ['en' => $link['text'], 'ja' => ''];
                    $normalized = true;
                } elseif (!isset($link['text']) || !is_array($link['text'])) {
                    $navigationLinks[$key]['text'] = ['en' => '', 'ja' => ''];
                    $normalized = true;
                }
            }
            
            if ($normalized) {
                $heroSection->navigation_links = $navigationLinks;
                $heroSection->save();
            }
        }
        
        // Get existing profile images from media relationship
        $profileImages = $heroSection->media()->where('type', 'image')->get();
        
        // Get available page sections dynamically
        $availableSections = PageSection::getActiveForPage('home')
            ->map(function ($section) {
                return [
                    'id' => $section->section_id,
                    'name' => $section->name,
                ];
            })
            ->toArray();
        
        // Fallback to default sections if none exist in database
        if (empty($availableSections)) {
            $availableSections = [
                ['id' => 'hero', 'name' => 'Hero Section'],
                ['id' => 'discover', 'name' => 'Discover Section'],
                ['id' => 'my-works', 'name' => 'My Works Section'],
                ['id' => 'contact', 'name' => 'Contact Section'],
            ];
        }
        
        return view('admin.hero.edit', compact('heroSection', 'profileImages', 'availableSections'));
    }

    public function update(Request $request)
    {
        $userId = Auth::id();
        
        // Get existing hero section or create a new one
        $heroSection = HeroSection::where('user_id', $userId)->first();
        
        if (!$heroSection) {
            // Create new hero section - it will have an ID after save
            $heroSection = new HeroSection();
            $heroSection->user_id = $userId;
            $heroSection->save();
            // Reload from database to ensure we have all default values and ID
            $heroSection = HeroSection::find($heroSection->id);
        }
        
        // Validate that we have an ID before proceeding
        if (!$heroSection || !$heroSection->id) {
            return redirect()->route('admin.hero.edit')
                ->withErrors(['error' => 'Failed to initialize hero section. Please try again.']);
        }
        
        // Store the ID in a variable to use later (in case model instance changes)
        $heroSectionId = $heroSection->id;
        
        $data = $request->validate([
            'background_color' => 'nullable|string|max:7',
            'badge_text' => 'nullable|array',
            'badge_text.en' => 'nullable|string|max:255',
            'badge_text.ja' => 'nullable|string|max:255',
            'badge_color' => 'nullable|string|max:7',
            'heading_text' => 'nullable|array',
            'heading_text.en' => 'nullable|string',
            'heading_text.ja' => 'nullable|string',
            'heading_size_mobile' => 'nullable|string|max:50',
            'heading_size_tablet' => 'nullable|string|max:50',
            'heading_size_desktop' => 'nullable|string|max:50',
            'subheading_text' => 'nullable|array',
            'subheading_text.en' => 'nullable|string',
            'subheading_text.ja' => 'nullable|string',
            'button1_text' => 'nullable|array',
            'button1_text.en' => 'nullable|string|max:255',
            'button1_text.ja' => 'nullable|string|max:255',
            'button1_link' => 'nullable|string|max:500',
            'button1_bg_color' => 'nullable|string|max:7',
            'button1_text_color' => 'nullable|string|max:7',
            'button1_visible' => 'nullable|boolean',
            'button2_text' => 'nullable|array',
            'button2_text.en' => 'nullable|string|max:255',
            'button2_text.ja' => 'nullable|string|max:255',
            'button2_link' => 'nullable|string|max:500',
            'button2_bg_color' => 'nullable|string|max:7',
            'button2_text_color' => 'nullable|string|max:7',
            'button2_border_color' => 'nullable|string|max:7',
            'button2_visible' => 'nullable|boolean',
            'nav_visible' => 'nullable|boolean',
                'navigation_links' => 'nullable|array',
                'navigation_links.*.id' => 'nullable|integer',
                'navigation_links.*.text' => 'required|array',
                'navigation_links.*.text.en' => 'nullable|string|max:255',
                'navigation_links.*.text.ja' => 'nullable|string|max:255',
                'navigation_links.*.section_id' => 'required|string|max:255',
                'navigation_links.*.order' => 'nullable|integer',
            'layout_reversed' => 'nullable|boolean',
            'text_horizontal_offset' => 'nullable|integer|min:-100|max:100',
            'image_horizontal_offset' => 'nullable|integer|min:-100|max:100',
            'badge_horizontal_offset' => 'nullable|integer|min:-100|max:100',
            'blob_media_horizontal_offset' => 'nullable|integer|min:-200|max:200',
            'blob_media_vertical_offset' => 'nullable|integer|min:-200|max:200',
            'blob_color' => 'nullable|string|max:7',
            'blob_visible' => 'nullable|boolean',
            'image_rotation_interval' => 'nullable|integer|min:500|max:10000',
            'profile_images.*' => 'nullable|image|max:4096',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'nullable|integer|exists:media,id',
            'blob_media' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,webm,ogg|max:20480',
            'remove_blob_media' => 'nullable|boolean',
        ]);

        // Process translations
        $data = $this->processTranslations($data, $this->getTranslatableFields());

        // Handle profile image uploads
        if ($request->hasFile('profile_images')) {
            // Double-check that we have a valid ID before creating media
            // Reload hero section from database to ensure we have the latest ID
            $userId = Auth::id();
            $heroSection = HeroSection::where('user_id', $userId)->find($heroSectionId);
            if (!$heroSection || !$heroSection->id) {
                // If find fails, try to get the user's first one
                $heroSection = HeroSection::where('user_id', $userId)->first();
                if (!$heroSection || !$heroSection->id) {
                    return redirect()->route('admin.hero.edit')
                        ->withErrors(['error' => 'Hero section not found. Cannot upload images.']);
                }
            }
            
            // Use the confirmed ID
            $heroSectionId = $heroSection->id;
            
            foreach ($request->file('profile_images') as $image) {
                $path = $image->store('profile', 'public');
                
                // Final validation before creating media
                if (!$heroSectionId || $heroSectionId <= 0) {
                    \Log::error('Invalid heroSectionId when creating media', [
                        'heroSectionId' => $heroSectionId,
                        'hero_section_exists' => HeroSection::where('id', $heroSectionId)->exists()
                    ]);
                    continue; // Skip this image
                }
                
                Media::create([
                    'title' => 'Hero Profile Image',
                    'type' => 'image',
                    'path' => $path,
                    'mediable_id' => $heroSectionId,
                    'mediable_type' => HeroSection::class,
                ]);
            }
        }

        // Handle image removals
        if ($request->has('remove_images')) {
            foreach ($request->input('remove_images') as $mediaId) {
                $media = Media::find($mediaId);
                if ($media && $media->mediable_type === HeroSection::class && $media->mediable_id === $heroSection->id) {
                    Storage::disk('public')->delete($media->path);
                    $media->delete();
                }
            }
        }

        // Handle blob media upload
        if ($request->hasFile('blob_media')) {
            // Remove existing blob media if any
            $existingBlobMedia = $heroSection->media()->where('title', 'Blob Media')->first();
            if ($existingBlobMedia) {
                Storage::disk('public')->delete($existingBlobMedia->path);
                $existingBlobMedia->delete();
            }
            
            $file = $request->file('blob_media');
            $mediaType = strpos($file->getMimeType(), 'video') !== false ? 'video' : 'image';
            $path = $file->store('blob-media', 'public');
            
            Media::create([
                'title' => 'Blob Media',
                'type' => $mediaType,
                'path' => $path,
                'mediable_id' => $heroSectionId,
                'mediable_type' => HeroSection::class,
            ]);
        }

        // Handle blob media removal
        if ($request->has('remove_blob_media')) {
            $existingBlobMedia = $heroSection->media()->where('title', 'Blob Media')->first();
            if ($existingBlobMedia) {
                Storage::disk('public')->delete($existingBlobMedia->path);
                $existingBlobMedia->delete();
            }
        }

        // Update hero section fields
        $heroSection->update([
            'background_color' => $data['background_color'] ?? $heroSection->background_color,
            'badge_text' => $data['badge_text'] ?? $heroSection->badge_text,
            'badge_color' => $data['badge_color'] ?? $heroSection->badge_color,
            'heading_text' => $data['heading_text'] ?? $heroSection->heading_text,
            'heading_size_mobile' => $data['heading_size_mobile'] ?? $heroSection->heading_size_mobile,
            'heading_size_tablet' => $data['heading_size_tablet'] ?? $heroSection->heading_size_tablet,
            'heading_size_desktop' => $data['heading_size_desktop'] ?? $heroSection->heading_size_desktop,
            'subheading_text' => $data['subheading_text'] ?? $heroSection->subheading_text,
            'button1_text' => $data['button1_text'] ?? $heroSection->button1_text,
            'button1_link' => $data['button1_link'] ?? $heroSection->button1_link,
            'button1_bg_color' => $data['button1_bg_color'] ?? $heroSection->button1_bg_color,
            'button1_text_color' => $data['button1_text_color'] ?? $heroSection->button1_text_color,
            'button1_visible' => $request->has('button1_visible') ? (bool)$request->input('button1_visible') : $heroSection->button1_visible,
            'button2_text' => $data['button2_text'] ?? $heroSection->button2_text,
            'button2_link' => $data['button2_link'] ?? $heroSection->button2_link,
            'button2_bg_color' => $data['button2_bg_color'] ?? $heroSection->button2_bg_color,
            'button2_text_color' => $data['button2_text_color'] ?? $heroSection->button2_text_color,
            'button2_border_color' => $data['button2_border_color'] ?? $heroSection->button2_border_color,
            'button2_visible' => $request->has('button2_visible') ? (bool)$request->input('button2_visible') : $heroSection->button2_visible,
            'nav_visible' => isset($data['nav_visible']) ? (bool)$data['nav_visible'] : $heroSection->nav_visible,
            'navigation_links' => $request->has('navigation_links') ? array_values(array_filter(array_map(function($link) {
                // Ensure text is an array with en/ja keys
                if (isset($link['text']) && is_array($link['text'])) {
                    return [
                        'id' => $link['id'] ?? null,
                        'text' => [
                            'en' => $link['text']['en'] ?? '',
                            'ja' => $link['text']['ja'] ?? ''
                        ],
                        'section_id' => $link['section_id'] ?? '',
                        'order' => $link['order'] ?? null
                    ];
                }
                return null;
            }, $request->input('navigation_links', [])), function($link) {
                // Allow links with either English OR Japanese text, and require section_id
                return $link !== null 
                    && !empty($link['section_id']) 
                    && (!empty($link['text']['en']) || !empty($link['text']['ja']));
            })) : $heroSection->navigation_links,
            'blob_color' => $data['blob_color'] ?? $heroSection->blob_color,
            'blob_visible' => $request->has('blob_visible') ? (bool)$request->input('blob_visible') : $heroSection->blob_visible,
            'image_rotation_interval' => $data['image_rotation_interval'] ?? $heroSection->image_rotation_interval,
            'layout_reversed' => $request->has('layout_reversed') ? (bool)$request->input('layout_reversed') : false,
            'text_horizontal_offset' => isset($data['text_horizontal_offset']) ? (int)$data['text_horizontal_offset'] : ($heroSection->text_horizontal_offset ?? 0),
            'image_horizontal_offset' => isset($data['image_horizontal_offset']) ? (int)$data['image_horizontal_offset'] : ($heroSection->image_horizontal_offset ?? 0),
            'badge_horizontal_offset' => isset($data['badge_horizontal_offset']) ? (int)$data['badge_horizontal_offset'] : ($heroSection->badge_horizontal_offset ?? 0),
            'blob_media_horizontal_offset' => isset($data['blob_media_horizontal_offset']) ? (int)$data['blob_media_horizontal_offset'] : ($heroSection->blob_media_horizontal_offset ?? 0),
            'blob_media_vertical_offset' => isset($data['blob_media_vertical_offset']) ? (int)$data['blob_media_vertical_offset'] : ($heroSection->blob_media_vertical_offset ?? 0),
        ]);

        // Clear any cached data
        \Cache::forget('hero_section_' . $heroSection->id);
        
        return redirect()->route('admin.hero.edit')->with('status', 'Hero section updated successfully');
    }

    public function reset()
    {
        $userId = Auth::id();
        $heroSection = HeroSection::where('user_id', $userId)->first();
        
        if (!$heroSection) {
            // If no hero section exists, create one with defaults
            $heroSection = HeroSection::create(['user_id' => $userId]);
            return redirect()->route('admin.hero.edit')->with('status', 'Hero section created with factory defaults');
        }
        
        // Delete all associated media/images
        foreach ($heroSection->media as $media) {
            Storage::disk('public')->delete($media->path);
            $media->delete();
        }
        
        // Reset to factory defaults (matching migration defaults)
        // Using a distinct light gray-blue to verify reset works (easily distinguishable)
        $heroSection->update([
            'background_color' => '#e0e7ff', // Light indigo/blue-gray
            'badge_text' => json_encode(['en' => 'IT / UIUX / Security', 'ja' => 'IT / UIUX / セキュリティ']),
            'badge_color' => '#ffb400',
            'heading_text' => json_encode(['en' => null, 'ja' => null]), // Migration has nullable
            'heading_size_mobile' => 'text-4xl',
            'heading_size_tablet' => 'text-5xl',
            'heading_size_desktop' => 'text-6xl',
            'subheading_text' => json_encode(['en' => null, 'ja' => null]), // Migration has nullable
            'button1_text' => json_encode(['en' => 'Projects', 'ja' => 'プロジェクト']),
            'button1_link' => route('projects'),
            'button1_bg_color' => '#ffb400',
            'button1_text_color' => '#111827',
            'button1_visible' => true,
            'button2_text' => json_encode(['en' => 'LinkedIn', 'ja' => 'LinkedIn']),
            'button2_link' => 'https://www.linkedin.com/in/...',
            'button2_bg_color' => '#ffffff',
            'button2_text_color' => '#1f2937',
            'button2_border_color' => '#d1d5db',
            'button2_visible' => true,
                'nav_visible' => true,
                'navigation_links' => [
                    ['id' => 1, 'text' => ['en' => 'About', 'ja' => ''], 'section_id' => 'discover', 'order' => 1],
                    ['id' => 2, 'text' => ['en' => 'Projects', 'ja' => ''], 'section_id' => 'my-works', 'order' => 2],
                    ['id' => 3, 'text' => ['en' => 'Contacts', 'ja' => ''], 'section_id' => 'contact', 'order' => 3],
                ],
            'blob_color' => '#ffb400',
            'blob_visible' => true,
            'image_rotation_interval' => 2000,
            'layout_reversed' => false,
            'text_horizontal_offset' => 0,
            'image_horizontal_offset' => 0,
            'badge_horizontal_offset' => 0,
        ]);
        
        return redirect()->route('admin.hero.edit')->with('status', '✅ Hero section successfully reset to factory defaults! Background color changed to #e0e7ff (Light Blue-Gray). All uploaded images have been removed.');
    }
}
