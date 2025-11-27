<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryItem;
use App\Models\Category;
use Illuminate\Support\Facades\App;

class ContentApiController extends Controller
{
    /**
     * Get content for a specific section (CategoryItem).
     */
    public function getSectionContent(CategoryItem $section)
    {
        $section->load([
            'bookPages.media', 
            'codeSummaries.media', 
            'rooms.media',
            'certificates.media', 
            'courses.media'
        ]);

        $locale = App::getLocale();
        
        // Helper to resolve translation
        $resolveTranslation = function($value, $locale) {
            if (is_array($value)) {
                return $value[$locale] ?? $value['en'] ?? $value['ja'] ?? '';
            }
            return (string)($value ?? '');
        };

        $content = [];

        // Map Book Pages
        if ($section->bookPages->isNotEmpty()) {
            $content['book_pages'] = $section->bookPages->map(function($item) use ($locale, $resolveTranslation) {
                return [
                    'id' => $item->id,
                    'title' => $resolveTranslation($item->getTranslated('title', $locale), $locale),
                    'author' => $resolveTranslation($item->getTranslated('author', $locale), $locale),
                    'summary' => $resolveTranslation($item->getTranslated('summary', $locale), $locale),
                    'image_url' => $item->media->first() ? $this->resolveAssetUrl($item->media->first()->path) : null,
                    'page_count' => $item->page_count,
                    'current_page' => $item->current_page,
                    'status' => $item->status,
                ];
            });
        }

        // Map Code Summaries
        if ($section->codeSummaries->isNotEmpty()) {
            $content['code_summaries'] = $section->codeSummaries->map(function($item) use ($locale, $resolveTranslation) {
                return [
                    'id' => $item->id,
                    'title' => $resolveTranslation($item->getTranslated('title', $locale), $locale),
                    'language' => $item->language,
                    'summary' => $resolveTranslation($item->getTranslated('summary', $locale), $locale),
                    'github_url' => $item->github_url,
                    'image_url' => $item->media->first() ? $this->resolveAssetUrl($item->media->first()->path) : null,
                ];
            });
        }

        // Map Rooms (TryHackMe)
        if ($section->rooms->isNotEmpty()) {
            $content['rooms'] = $section->rooms->map(function($item) use ($locale, $resolveTranslation) {
                return [
                    'id' => $item->id,
                    'title' => $resolveTranslation($item->getTranslated('title', $locale), $locale),
                    'difficulty' => $item->difficulty,
                    'type' => $item->type,
                    'image_url' => $item->media->first() ? $this->resolveAssetUrl($item->media->first()->path) : null,
                    'completed_at' => $item->completed_at,
                ];
            });
        }
        
        // Map Certificates
        if ($section->certificates->isNotEmpty()) {
            $content['certificates'] = $section->certificates->map(function($item) use ($locale, $resolveTranslation) {
                return [
                    'id' => $item->id,
                    'title' => $resolveTranslation($item->getTranslated('title', $locale), $locale),
                    'provider' => $resolveTranslation($item->getTranslated('provider', $locale), $locale),
                    'image_url' => $item->media->first() ? $this->resolveAssetUrl($item->media->first()->path) : null,
                    'issued_at' => $item->issued_at,
                    'credential_url' => $item->credential_url,
                ];
            });
        }

        // Map Courses
        if ($section->courses->isNotEmpty()) {
            $content['courses'] = $section->courses->map(function($item) use ($locale, $resolveTranslation) {
                return [
                    'id' => $item->id,
                    'title' => $resolveTranslation($item->getTranslated('title', $locale), $locale),
                    'provider' => $resolveTranslation($item->getTranslated('provider', $locale), $locale),
                    'image_url' => $item->media->first() ? $this->resolveAssetUrl($item->media->first()->path) : null,
                    'completed_at' => $item->completed_at,
                    'certificate_url' => $item->certificate_url,
                ];
            });
        }

        return response()->json($content);
    }
    
    private function resolveAssetUrl(string $path): string
    {
        if (strpos($path, 'storage/') === 0 || strpos($path, '/storage/') === 0) {
            return asset($path);
        } elseif (strpos($path, 'http') === 0) {
            return $path;
        } elseif (strpos($path, 'images/') === 0) {
            return asset($path);
        } else {
            return asset('storage/' . $path);
        }
    }
}
