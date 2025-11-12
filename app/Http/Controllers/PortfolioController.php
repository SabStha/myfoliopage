<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

class PortfolioController extends Controller
{
    /**
     * Display a user's public portfolio
     */
    public function show($username)
    {
        // Find user by username or slug
        $user = User::where('username', $username)
            ->orWhere('slug', $username)
            ->firstOrFail();

        // Load all portfolio data for this user
        $profile = Profile::where('user_id', $user->id)->with('media')->first();
        $heroSection = HeroSection::where('user_id', $user->id)->first();
        $engagementSection = EngagementSection::where('user_id', $user->id)->first();
        
        // Get engagement section video
        $engagementVideo = null;
        if ($engagementSection) {
            $videoMedia = $engagementSection->media()->where('type', 'video')->first();
            if ($videoMedia) {
                $path = $videoMedia->path;
                if (strpos($path, 'storage/') === 0 || strpos($path, '/storage/') === 0) {
                    $engagementVideo = asset($path);
                } elseif (strpos($path, 'http') === 0) {
                    $engagementVideo = $path;
                } else {
                    $engagementVideo = asset('storage/' . $path);
                }
            }
        }
        
        // Fallback to default video if no video uploaded
        if (!$engagementVideo) {
            // Check multiple possible locations for the fallback video
            $fallbackPaths = [
                'storage/videos/engagement-01.mp4',
                'engagement/engagement-01.mp4',
                'videos/engagement-01.mp4',
            ];
            
            foreach ($fallbackPaths as $fallbackPath) {
                if (file_exists(public_path($fallbackPath))) {
                    $engagementVideo = asset($fallbackPath);
                    break;
                }
            }
            
            // If still not found, use the default path (will show 404 but won't break)
            if (!$engagementVideo) {
                $engagementVideo = asset('storage/videos/engagement-01.mp4');
            }
        }
        
        // Get hero section profile images
        $heroProfileImages = [];
        if ($heroSection) {
            foreach ($heroSection->media()->where('type', 'image')->get() as $media) {
                $path = $media->path;
                if (strpos($path, 'storage/') === 0 || strpos($path, '/storage/') === 0) {
                    $heroProfileImages[] = asset($path);
                } elseif (strpos($path, 'http') === 0) {
                    $heroProfileImages[] = $path;
                } elseif (strpos($path, 'images/') === 0) {
                    // Handle images in public/images directory
                    $heroProfileImages[] = asset($path);
                } else {
                    $heroProfileImages[] = asset('storage/' . $path);
                }
            }
        }
        
        // If no hero images, try default images from public/images/
        if (empty($heroProfileImages)) {
            for ($i = 1; $i <= 3; $i++) {
                $defaultImage = "images/pp{$i}.jpg";
                if (file_exists(public_path($defaultImage))) {
                    $heroProfileImages[] = asset($defaultImage);
                }
            }
        }
        
        // Collect profile images from media relationship
        $profileImages = [];
        if ($profile && $profile->media) {
            foreach ($profile->media->where('type', 'image') as $media) {
                $path = $media->path;
                if (strpos($path, 'storage/') === 0 || strpos($path, '/storage/') === 0) {
                    $profileImages[] = asset($path);
                } elseif (strpos($path, 'http') === 0) {
                    $profileImages[] = $path;
                } elseif (strpos($path, 'images/') === 0) {
                    // Handle images in public/images directory
                    $profileImages[] = asset($path);
                } else {
                    $profileImages[] = asset('storage/' . $path);
                }
            }
        }
        
        // Also scan for profile images in public/images/ directory (pp1.jpg, pp2.jpg, pp3.jpg)
        if (empty($profileImages)) {
            for ($i = 1; $i <= 3; $i++) {
                $defaultImage = "images/pp{$i}.jpg";
                if (file_exists(public_path($defaultImage))) {
                    $profileImages[] = asset($defaultImage);
                }
            }
        }
        
        // Fallback to default image if no images found
        if (empty($profileImages)) {
            $profileImages[] = asset('images/profile_main.png');
        }
        
        $profileImages = array_unique($profileImages);
        
        // Compute finalProfileImages (hero images take priority)
        $finalProfileImages = !empty($heroProfileImages) ? $heroProfileImages : $profileImages;
        
        // Get categories for this user
        $categories = Category::where('user_id', $user->id)->orderBy('position')->get();
        
        // Get services (categories)
        $services = $categories->map(function($c){
            return [
                'icon' => '<span>⭐</span>',
                'title' => $c->name,
                'description' => $c->summary,
            ];
        })->toArray();
        
        // Get home page sections for this user
        $homePageSections = HomePageSection::with('navItem')
            ->where('user_id', $user->id)
            ->where('enabled', true)
            ->orderBy('position')
            ->get()
            ->map(function($section) {
                // Similar logic to current home page route
                $selectedNavLinkIds = $section->selected_nav_link_ids;
                $navLinks = [];
                
                if ($selectedNavLinkIds === null) {
                    // Show all NavLinks for this NavItem
                    $navLinks = NavLink::where('nav_item_id', $section->nav_item_id)
                        ->with(['categories' => function($query) {
                            $query->with('items')->orderBy('position');
                        }])
                        ->orderBy('position')
                        ->get();
                } elseif (is_array($selectedNavLinkIds) && count($selectedNavLinkIds) > 0) {
                    // Show only selected NavLinks
                    $navLinks = NavLink::where('nav_item_id', $section->nav_item_id)
                        ->whereIn('id', $selectedNavLinkIds)
                        ->with(['categories' => function($query) {
                            $query->with('items')->orderBy('position');
                        }])
                        ->orderBy('position')
                        ->get();
                }
                
                // Transform NavLinks data (similar to current home route)
                $currentLocaleForLink = app()->getLocale();
                $navLinks = $navLinks->map(function($link) use ($currentLocaleForLink) {
                    $categoriesArray = $link->categories->map(function($category) use ($currentLocaleForLink) {
                        // Ensure name is a string
                        $categoryName = $category->getTranslated('name', $currentLocaleForLink);
                        if (is_array($categoryName)) {
                            $categoryName = $categoryName[$currentLocaleForLink] ?? $categoryName['en'] ?? $categoryName['ja'] ?? '';
                        }
                        if (!is_string($categoryName)) {
                            $categoryName = (string)($categoryName ?? 'Untitled');
                        }
                        
                        return [
                            'id' => $category->id,
                            'name' => $categoryName,
                            'slug' => $category->slug,
                            'animation_style' => $category->animation_style,
                            'image_path' => $category->image_path,
                            'image_url' => $category->image_path ? asset('storage/' . $category->image_path) : null,
                            'items' => $category->items->map(function($item) use ($currentLocaleForLink) {
                                // Ensure title is a string
                                $itemTitle = $item->getTranslated('title', $currentLocaleForLink);
                                if (is_array($itemTitle)) {
                                    $itemTitle = $itemTitle[$currentLocaleForLink] ?? $itemTitle['en'] ?? $itemTitle['ja'] ?? '';
                                }
                                if (!is_string($itemTitle)) {
                                    $itemTitle = (string)($itemTitle ?? 'Untitled');
                                }
                                
                                // Ensure summary is a string
                                $itemSummary = $item->getTranslated('summary', $currentLocaleForLink);
                                if (is_array($itemSummary)) {
                                    $itemSummary = $itemSummary[$currentLocaleForLink] ?? $itemSummary['en'] ?? $itemSummary['ja'] ?? '';
                                }
                                if (!is_string($itemSummary)) {
                                    $itemSummary = (string)($itemSummary ?? '');
                                }
                                
                                return [
                                    'id' => $item->id,
                                    'title' => $itemTitle,
                                    'slug' => $item->slug,
                                    'image_path' => $item->image_path,
                                    'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                                    'url' => $item->url,
                                    'summary' => $itemSummary,
                                ];
                            })->toArray()
                        ];
                    })->toArray();
                    
                    // Ensure link title is a string
                    $linkTitle = '';
                    $titleValue = $link->title;
                    
                    // Check if title is a JSON string and decode it
                    if (is_string($titleValue)) {
                        $decoded = json_decode($titleValue, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $titleValue = $decoded;
                        }
                    }
                    
                    // Now extract the string value
                    if (is_string($titleValue)) {
                        $linkTitle = $titleValue;
                    } elseif (is_array($titleValue)) {
                        $linkTitle = $titleValue[$currentLocaleForLink] ?? $titleValue['en'] ?? $titleValue['ja'] ?? '';
                    } else {
                        $linkTitle = (string)($titleValue ?? 'Untitled');
                    }
                    
                    return [
                        'id' => $link->id,
                        'title' => $linkTitle,
                        'position' => $link->position,
                        'categories' => $categoriesArray,
                    ];
                })->toArray();
                
                $currentLocale = app()->getLocale();
                $navItemLabel = '';
                if ($section->navItem) {
                    $labelTranslated = $section->navItem->getTranslated('label', $currentLocale);
                    if (is_string($labelTranslated)) {
                        $navItemLabel = $labelTranslated;
                    } elseif (is_array($labelTranslated)) {
                        $navItemLabel = $labelTranslated[$currentLocale] ?? $labelTranslated['en'] ?? $labelTranslated['ja'] ?? '';
                    } elseif (is_array($section->navItem->label)) {
                        $navItemLabel = $section->navItem->label[$currentLocale] ?? $section->navItem->label['en'] ?? $section->navItem->label['ja'] ?? '';
                    } else {
                        $navItemLabel = (string)($section->navItem->label ?? '');
                    }
                }
                
                $titleRaw = $section->title;
                $sectionTitle = '';
                if (is_array($titleRaw)) {
                    $sectionTitle = $titleRaw[$currentLocale] ?? '';
                    if (empty($sectionTitle)) {
                        $otherLocale = $currentLocale === 'en' ? 'ja' : 'en';
                        $sectionTitle = $titleRaw[$otherLocale] ?? '';
                    }
                }
                
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
        
        // Get progress items (similar to current home route)
        $currentLocale = app()->getLocale();
        $progressItems = NavItem::where('user_id', $user->id)
            ->with(['links' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->where('visible', true)
            ->orderBy('position')
            ->get()
            ->map(function($navItem) use ($currentLocale, $user) {
                // Filter links by user_id
                $links = $navItem->links->where('user_id', $user->id);
                $totalLinks = $links->count();
                
                if ($totalLinks === 0) {
                    return null;
                }
                
                $completedLinks = $links->where('progress', 100)->count();
                $inProgressLinks = $links->where('progress', '>', 0)->where('progress', '<', 100)->count();
                
                // Calculate average progress across all links
                $avgProgress = $totalLinks > 0 ? round($links->avg('progress') ?? 0) : 0;
                
                // Use average progress as current value, 100 as goal (percentage-based)
                $currentValue = $avgProgress;
                $goalValue = 100;
                
                $label = $navItem->getTranslated('label', $currentLocale);
                if (is_array($label)) {
                    $label = $label[$currentLocale] ?? $label['en'] ?? $label['ja'] ?? '';
                }
                
                return [
                    'label' => $label,
                    'total' => $totalLinks,
                    'completed' => $completedLinks,
                    'in_progress' => $inProgressLinks,
                    'unit' => $this->deriveUnitFromLabel($label, $totalLinks),
                    'value' => $currentValue,
                    'goal' => $goalValue,
                    'pct' => $avgProgress,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
        
        // Get certificates data for React component
        $certificatesData = Certificate::where('user_id', $user->id)
            ->with(['categories', 'tags', 'media'])
            ->orderBy('issued_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($cert) use ($currentLocale) {
                // Ensure title is a string
                $certTitle = $cert->getTranslated('title', $currentLocale);
                if (is_array($certTitle)) {
                    $certTitle = $certTitle[$currentLocale] ?? $certTitle['en'] ?? $certTitle['ja'] ?? '';
                }
                if (!is_string($certTitle)) {
                    $certTitle = (string)($certTitle ?? 'Untitled');
                }
                
                // Ensure provider is a string
                $certProvider = $cert->getTranslated('provider', $currentLocale);
                if (is_array($certProvider)) {
                    $certProvider = $certProvider[$currentLocale] ?? $certProvider['en'] ?? $certProvider['ja'] ?? '';
                }
                if (!is_string($certProvider)) {
                    $certProvider = (string)($certProvider ?? 'Unknown');
                }
                
                return [
                    'id' => $cert->id,
                    'title' => $certTitle,
                    'provider' => $certProvider,
                    'issued_at' => $cert->issued_at?->format('Y-m-d'),
                    'level' => $cert->level,
                    'status' => $cert->status,
                ];
            })
            ->toArray();
        
        // Get courses data
        $coursesData = Course::where('user_id', $user->id)
            ->with(['tags', 'media'])
            ->orderBy('completed_at', 'desc')
            ->orderBy('issued_at', 'desc')
            ->limit(6)
            ->get()
            ->map(function($course) use ($currentLocale) {
                // Ensure title is a string
                $courseTitle = $course->getTranslated('title', $currentLocale);
                if (is_array($courseTitle)) {
                    $courseTitle = $courseTitle[$currentLocale] ?? $courseTitle['en'] ?? $courseTitle['ja'] ?? '';
                }
                if (!is_string($courseTitle)) {
                    $courseTitle = (string)($courseTitle ?? 'Untitled');
                }
                
                // Ensure provider is a string
                $courseProvider = $course->getTranslated('provider', $currentLocale);
                if (is_array($courseProvider)) {
                    $courseProvider = $courseProvider[$currentLocale] ?? $courseProvider['en'] ?? $courseProvider['ja'] ?? '';
                }
                if (!is_string($courseProvider)) {
                    $courseProvider = (string)($courseProvider ?? 'Unknown');
                }
                
                return [
                    'id' => $course->id,
                    'title' => $courseTitle,
                    'provider' => $courseProvider,
                    'status' => $course->status,
                    'difficulty' => $course->difficulty,
                    'issued_at' => $course->issued_at?->format('Y-m-d'),
                    'completed_at' => $course->completed_at?->format('Y-m-d'),
                ];
            })
            ->toArray();
        
        $roomsData = [];
        $badgesData = [];
        $gamesData = [];
        $simulationsData = [];
        $programsData = [];
        
        // Get blogs for this user
        $blogs = Blog::where('user_id', $user->id)
            ->where('is_published', true)
            ->with('media')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
        
        // Ensure heroSection is not null
        if (!$heroSection) {
            $heroSection = new HeroSection();
        }
        
        if (!$engagementSection) {
            $engagementSection = new EngagementSection();
        }
        
        return view('home', compact(
            'user',
            'profile',
            'heroSection',
            'engagementSection',
            'engagementVideo',
            'heroProfileImages',
            'profileImages',
            'finalProfileImages',
            'categories',
            'services',
            'homePageSections',
            'progressItems',
            'certificatesData',
            'coursesData',
            'roomsData',
            'badgesData',
            'gamesData',
            'simulationsData',
            'programsData',
            'blogs'
        ));
    }
    
    /**
     * Derive unit from label (helper method)
     */
    private function deriveUnitFromLabel($label, $linkCount = 0)
    {
        if (is_array($label)) {
            $label = $label['en'] ?? $label['ja'] ?? '';
        }
        if (!is_string($label)) {
            $label = (string)($label ?? '');
        }
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

