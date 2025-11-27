<?php

namespace App\Services;

use App\Models\User;
use App\Models\Profile;
use App\Models\HeroSection;
use App\Models\EngagementSection;
use App\Models\HomePageSection;
use App\Models\Category;
use App\Models\NavItem;
use App\Models\NavLink;
use App\Models\Project;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Blog;
use Illuminate\Support\Collection;

class PortfolioService
{
    /**
     * Get a sample user for the landing page preview.
     */
    public function getSampleUser(): ?User
    {
        $sampleUser = User::whereHas('projects')
            ->orWhereHas('certificates')
            ->orWhereHas('courses')
            ->first();

        return $sampleUser ?? User::first();
    }

    /**
     * Get user by username or slug.
     */
    public function getUser(string $username): User
    {
        return User::where('username', $username)
            ->orWhere('slug', $username)
            ->firstOrFail();
    }

    /**
     * Get user profile with media.
     */
    public function getProfile(int $userId): ?Profile
    {
        return Profile::where('user_id', $userId)->with('media')->first();
    }

    /**
     * Get hero section for user.
     */
    public function getHeroSection(int $userId): HeroSection
    {
        $heroSection = HeroSection::where('user_id', $userId)->first();
        return $heroSection ?? new HeroSection();
    }

    /**
     * Get engagement section for user.
     */
    public function getEngagementSection(int $userId): EngagementSection
    {
        $engagementSection = EngagementSection::where('user_id', $userId)->first();
        return $engagementSection ?? new EngagementSection();
    }

    /**
     * Get engagement video URL.
     */
    public function getEngagementVideo(?EngagementSection $engagementSection): string
    {
        if ($engagementSection) {
            $videoMedia = $engagementSection->media()->where('type', 'video')->first();
            if ($videoMedia) {
                $path = $videoMedia->path;
                if (strpos($path, 'storage/') === 0 || strpos($path, '/storage/') === 0) {
                    return asset($path);
                } elseif (strpos($path, 'http') === 0) {
                    return $path;
                } else {
                    return asset('storage/' . $path);
                }
            }
        }

        // Fallback paths
        $fallbackPaths = [
            'storage/videos/engagement-01.mp4',
            'engagement/engagement-01.mp4',
            'videos/engagement-01.mp4',
        ];

        foreach ($fallbackPaths as $fallbackPath) {
            if (file_exists(public_path($fallbackPath))) {
                return asset($fallbackPath);
            }
        }

        return asset('storage/videos/engagement-01.mp4');
    }

    /**
     * Get profile images (hero and standard).
     */
    public function getProfileImages(int $userId, ?HeroSection $heroSection, ?Profile $profile): array
    {
        $heroProfileImages = [];
        if ($heroSection) {
            foreach ($heroSection->media()->where('type', 'image')->get() as $media) {
                $heroProfileImages[] = $this->resolveAssetUrl($media->path);
            }
        }

        // Default hero images
        if (empty($heroProfileImages)) {
            for ($i = 1; $i <= 3; $i++) {
                $defaultImage = "images/pp{$i}.jpg";
                if (file_exists(public_path($defaultImage))) {
                    $heroProfileImages[] = asset($defaultImage);
                }
            }
        }

        $profileImages = [];
        if ($profile && $profile->media) {
            foreach ($profile->media->where('type', 'image') as $media) {
                $profileImages[] = $this->resolveAssetUrl($media->path);
            }
        }

        // Default profile images
        if (empty($profileImages)) {
            for ($i = 1; $i <= 3; $i++) {
                $defaultImage = "images/pp{$i}.jpg";
                if (file_exists(public_path($defaultImage))) {
                    $profileImages[] = asset($defaultImage);
                }
            }
            if (empty($profileImages)) {
                $profileImages[] = asset('images/profile_main.png');
            }
        }

        $profileImages = array_unique($profileImages);
        $finalProfileImages = !empty($heroProfileImages) ? $heroProfileImages : $profileImages;

        return [
            'heroProfileImages' => $heroProfileImages,
            'profileImages' => $profileImages,
            'finalProfileImages' => $finalProfileImages,
        ];
    }

    /**
     * Get categories and services.
     */
    public function getCategories(int $userId): array
    {
        $categories = Category::where('user_id', $userId)->orderBy('position')->get();
        
        $services = $categories->map(function($c){
            return [
                'icon' => '<span>⭐</span>',
                'title' => $c->name,
                'description' => $c->summary,
            ];
        })->toArray();

        return compact('categories', 'services');
    }

    /**
     * Get home page sections.
     */
    public function getHomePageSections(int $userId): array
    {
        return HomePageSection::with('navItem')
            ->where('user_id', $userId)
            ->where('enabled', true)
            ->orderBy('position')
            ->get()
            ->map(function($section) {
                $selectedNavLinkIds = $section->selected_nav_link_ids;
                $navLinks = [];
                
                if ($selectedNavLinkIds === null) {
                    $navLinks = NavLink::where('nav_item_id', $section->nav_item_id)
                        ->with(['categories' => function($query) {
                            $query->with('items')->orderBy('position');
                        }])
                        ->orderBy('position')
                        ->get();
                } elseif (is_array($selectedNavLinkIds) && count($selectedNavLinkIds) > 0) {
                    $navLinks = NavLink::where('nav_item_id', $section->nav_item_id)
                        ->whereIn('id', $selectedNavLinkIds)
                        ->with(['categories' => function($query) {
                            $query->with('items')->orderBy('position');
                        }])
                        ->orderBy('position')
                        ->get();
                }
                
                $currentLocaleForLink = app()->getLocale();
                $navLinks = $navLinks->map(function($link) use ($currentLocaleForLink) {
                    $categoriesArray = $link->categories->map(function($category) use ($currentLocaleForLink) {
                        $categoryName = $this->resolveTranslation($category->getTranslated('name', $currentLocaleForLink), $currentLocaleForLink);
                        
                        return [
                            'id' => $category->id,
                            'name' => $categoryName,
                            'slug' => $category->slug,
                            'animation_style' => $category->animation_style,
                            'image_path' => $category->image_path,
                            'image_url' => $category->image_path ? asset('storage/' . $category->image_path) : null,
                            'items' => $category->items->map(function($item) use ($currentLocaleForLink) {
                                return [
                                    'id' => $item->id,
                                    'title' => $this->resolveTranslation($item->getTranslated('title', $currentLocaleForLink), $currentLocaleForLink),
                                    'slug' => $item->slug,
                                    'image_path' => $item->image_path,
                                    'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                                    'url' => $item->url,
                                    'summary' => $this->resolveTranslation($item->getTranslated('summary', $currentLocaleForLink), $currentLocaleForLink),
                                ];
                            })->toArray()
                        ];
                    })->toArray();
                    
                    return [
                        'id' => $link->id,
                        'title' => $this->resolveTranslation($link->title, $currentLocaleForLink),
                        'position' => $link->position,
                        'categories' => $categoriesArray,
                    ];
                })->toArray();
                
                $currentLocale = app()->getLocale();
                $navItemLabel = '';
                if ($section->navItem) {
                    $navItemLabel = $this->resolveTranslation($section->navItem->getTranslated('label', $currentLocale), $currentLocale);
                }
                
                $sectionTitle = $this->resolveTranslation($section->title, $currentLocale);
                if (empty($sectionTitle) && $section->navItem) {
                    $sectionTitle = $navItemLabel;
                }
                
                return [
                    'id' => $section->id,
                    'nav_item_id' => $section->nav_item_id,
                    'nav_item_label' => $navItemLabel,
                    'position' => $section->position,
                    'text_alignment' => $section->text_alignment,
                    'animation_style' => $section->animation_style ?? null,
                    'title' => $sectionTitle,
                    'subtitle' => $section->getTranslated('subtitle', $currentLocale),
                    'selected_nav_link_ids' => $selectedNavLinkIds,
                    'nav_links' => $navLinks,
                    'subsection_configurations' => $section->subsection_configurations ?? [],
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get progress items.
     */
    public function getProgressItems(int $userId): array
    {
        $currentLocale = app()->getLocale();
        return NavItem::where('user_id', $userId)
            ->with(['links' => function($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->where('visible', true)
            ->orderBy('position')
            ->get()
            ->map(function($navItem) use ($currentLocale, $userId) {
                $links = $navItem->links->where('user_id', $userId);
                $totalLinks = $links->count();
                
                if ($totalLinks === 0) {
                    return null;
                }
                
                $completedLinks = $links->where('progress', 100)->count();
                $inProgressLinks = $links->where('progress', '>', 0)->where('progress', '<', 100)->count();
                $avgProgress = $totalLinks > 0 ? round($links->avg('progress') ?? 0) : 0;
                
                $label = $this->resolveTranslation($navItem->getTranslated('label', $currentLocale), $currentLocale);
                
                return [
                    'label' => $label,
                    'total' => $totalLinks,
                    'completed' => $completedLinks,
                    'in_progress' => $inProgressLinks,
                    'unit' => $this->deriveUnitFromLabel($label, $totalLinks),
                    'value' => $avgProgress,
                    'goal' => 100,
                    'pct' => $avgProgress,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get certificates.
     */
    public function getCertificates(int $userId): array
    {
        $currentLocale = app()->getLocale();
        return Certificate::where('user_id', $userId)
            ->with(['categories', 'tags', 'media'])
            ->orderBy('issued_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($cert) use ($currentLocale) {
                return [
                    'id' => $cert->id,
                    'title' => $this->resolveTranslation($cert->getTranslated('title', $currentLocale), $currentLocale),
                    'provider' => $this->resolveTranslation($cert->getTranslated('provider', $currentLocale), $currentLocale),
                    'issued_at' => $cert->issued_at?->format('Y-m-d'),
                    'level' => $cert->level,
                    'status' => $cert->status,
                ];
            })
            ->toArray();
    }

    /**
     * Get courses.
     */
    public function getCourses(int $userId): array
    {
        $currentLocale = app()->getLocale();
        return Course::where('user_id', $userId)
            ->with(['tags', 'media'])
            ->orderBy('completed_at', 'desc')
            ->orderBy('issued_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($course) use ($currentLocale) {
                return [
                    'id' => $course->id,
                    'title' => $this->resolveTranslation($course->getTranslated('title', $currentLocale), $currentLocale),
                    'provider' => $this->resolveTranslation($course->getTranslated('provider', $currentLocale), $currentLocale),
                    'status' => $course->status,
                    'difficulty' => $course->difficulty,
                    'issued_at' => $course->issued_at?->format('Y-m-d'),
                    'completed_at' => $course->completed_at?->format('Y-m-d'),
                ];
            })
            ->toArray();
    }

    /**
     * Get rooms (TryHackMe etc).
     */
    public function getRooms(int $userId): array
    {
        $currentLocale = app()->getLocale();
        // Check if Room model exists and is imported (it is not imported at top yet, need to check)
        // Actually I should add the import first or use full path.
        // But I can't easily add import with replace_file_content in the middle.
        // I'll use \App\Models\Room
        return \App\Models\Room::where('user_id', $userId)
            ->with(['tags', 'media'])
            ->orderBy('completed_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($room) use ($currentLocale) {
                return [
                    'id' => $room->id,
                    'title' => $this->resolveTranslation($room->getTranslated('title', $currentLocale), $currentLocale),
                    'difficulty' => $room->difficulty,
                    'type' => $room->type,
                    'completed_at' => $room->completed_at?->format('Y-m-d'),
                    'image_url' => $room->media->first() ? $this->resolveAssetUrl($room->media->first()->path) : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get blogs.
     */
    public function getBlogs(int $userId): Collection
    {
        return Blog::where('user_id', $userId)
            ->where('is_published', true)
            ->with('media')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
    }

    /**
     * Helper to resolve asset URL.
     */
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

    /**
     * Helper to resolve translation.
     */
    private function resolveTranslation($value, $locale): string
    {
        if (is_array($value)) {
            $value = $value[$locale] ?? $value['en'] ?? $value['ja'] ?? '';
        }
        return (string)($value ?? '');
    }

    /**
     * Derive unit from label.
     */
    private function deriveUnitFromLabel($label, $linkCount = 0): string
    {
        $labelLower = strtolower($label);
        if (strpos($labelLower, 'tryhackme') !== false || strpos($labelLower, 'thm') !== false) {
            return 'rooms';
        } elseif (strpos($labelLower, 'udemy') !== false) {
            return $linkCount > 0 ? 'courses' : 'hours';
        } elseif (strpos($labelLower, 'book') !== false) {
            return 'pages';
        }
        return $linkCount > 0 ? 'items' : 'items';
    }
}
