<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HandlesTranslations;
use App\Models\EngagementSection;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EngagementSectionController extends Controller
{
    use HandlesTranslations;
    
    protected function getTranslatableFields(): array
    {
        return ['title'];
    }
    public function edit()
    {
        $userId = Auth::id();
        $engagementSection = EngagementSection::where('user_id', $userId)->first();
        if (!$engagementSection) {
            // Create with factory defaults
            $engagementSection = EngagementSection::create([
                'user_id' => $userId,
                'title' => json_encode(['en' => 'Discover our engagements', 'ja' => '']),
            ]);
        }
        
        // Get existing video from media relationship
        $videoMedia = $engagementSection->media()->where('type', 'video')->first();
        
        return view('admin.engagement.edit', compact('engagementSection', 'videoMedia'));
    }

    public function update(Request $request)
    {
        $userId = Auth::id();
        // Get existing engagement section or create a new one
        $engagementSection = EngagementSection::where('user_id', $userId)->first();
        
        if (!$engagementSection) {
            $engagementSection = new EngagementSection();
            $engagementSection->user_id = $userId;
            $engagementSection->save();
            $engagementSection = EngagementSection::find($engagementSection->id);
        }
        
        if (!$engagementSection || !$engagementSection->id) {
            return redirect()->route('admin.engagement.edit')
                ->withErrors(['error' => 'Failed to initialize engagement section. Please try again.']);
        }
        
        $engagementSectionId = $engagementSection->id;
        
        $data = $request->validate([
            'title' => 'nullable|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'video' => 'nullable|file|mimes:mp4,webm,ogg|max:102400', // 100MB max
            'remove_video' => 'nullable|boolean',
        ]);

        // Process translation fields
        $data = $this->processTranslations($data, $this->getTranslatableFields());

        // Handle video upload
        if ($request->hasFile('video')) {
            // Remove existing video media if any
            $existingVideo = $engagementSection->media()->where('type', 'video')->first();
            if ($existingVideo) {
                Storage::disk('public')->delete($existingVideo->path);
                $existingVideo->delete();
            }
            
            $file = $request->file('video');
            $path = $file->store('videos/engagement', 'public');
            
            Media::create([
                'title' => 'Engagement Video',
                'type' => 'video',
                'path' => $path,
                'mediable_id' => $engagementSectionId,
                'mediable_type' => EngagementSection::class,
            ]);
        }

        // Handle video removal
        if ($request->has('remove_video')) {
            $existingVideo = $engagementSection->media()->where('type', 'video')->first();
            if ($existingVideo) {
                Storage::disk('public')->delete($existingVideo->path);
                $existingVideo->delete();
            }
        }

        // Update engagement section fields
        if (isset($data['title'])) {
            $engagementSection->update(['title' => $data['title']]);
        }
        
        return redirect()->route('admin.engagement.edit')->with('status', 'Engagement section updated successfully');
    }

    public function reset()
    {
        $userId = Auth::id();
        $engagementSection = EngagementSection::where('user_id', $userId)->first();
        
        if (!$engagementSection) {
            // If no engagement section exists, create one with defaults
            $engagementSection = EngagementSection::create([
                'user_id' => $userId,
                'title' => 'Discover our engagements',
            ]);
            return redirect()->route('admin.engagement.edit')->with('status', 'Engagement section created with factory defaults');
        }
        
        // Delete all associated video media
        foreach ($engagementSection->media as $media) {
            Storage::disk('public')->delete($media->path);
            $media->delete();
        }
        
        // Reset to factory defaults (matching migration defaults)
        $engagementSection->update([
            'title' => json_encode(['en' => 'Discover our engagements', 'ja' => '']),
        ]);
        
        return redirect()->route('admin.engagement.edit')->with('status', '✅ Engagement section successfully reset to factory defaults! All uploaded videos have been removed.');
    }
}

