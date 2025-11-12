<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePageSection;
use App\Models\NavItem;
use App\Models\NavLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomePageSectionController extends Controller
{
    public function index()
    {
        // Redirect to combined navigation page
        return redirect()->route('admin.nav.index');
    }

    public function create()
    {
        $userId = Auth::id();
        $navItems = NavItem::where('user_id', $userId)
            ->where('visible', true)
            ->orderBy('position')
            ->get();
        
        return view('admin.home-page-sections.create', compact('navItems'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nav_item_id' => 'required|exists:nav_items,id',
            'position' => 'required|integer|min:0',
            'text_alignment' => 'required|in:left,right',
            'animation_style' => 'nullable|in:grid_editorial_collage,list_alternating_cards,carousel_scroll_left,carousel_scroll_right',
            'title' => 'nullable|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'subtitle' => 'nullable|array',
            'subtitle.en' => 'nullable|string|max:255',
            'subtitle.ja' => 'nullable|string|max:255',
            'enabled' => 'nullable|boolean',
            'selected_nav_link_ids' => 'nullable|array',
            'selected_nav_link_ids.*' => 'exists:nav_links,id',
            'subsection_configurations' => 'nullable|array',
            'subsection_configurations.*.animation_style' => 'required|in:grid_editorial_collage,list_alternating_cards,carousel_scroll_left,carousel_scroll_right',
            'subsection_configurations.*.layout_style' => 'nullable|string|max:255',
        ]);

        $data['enabled'] = (bool)$request->input('enabled', true);
        
        // Process title and subtitle translations
        if (isset($data['title']) && is_array($data['title'])) {
            $data['title'] = [
                'en' => $data['title']['en'] ?? '',
                'ja' => $data['title']['ja'] ?? '',
            ];
            // Remove if both are empty
            if (empty($data['title']['en']) && empty($data['title']['ja'])) {
                $data['title'] = null;
            }
        }
        
        if (isset($data['subtitle']) && is_array($data['subtitle'])) {
            $data['subtitle'] = [
                'en' => $data['subtitle']['en'] ?? '',
                'ja' => $data['subtitle']['ja'] ?? '',
            ];
            // Remove if both are empty
            if (empty($data['subtitle']['en']) && empty($data['subtitle']['ja'])) {
                $data['subtitle'] = null;
            }
        }
        
        // Handle selected_nav_link_ids - if all are selected or null, save null (means show all)
        // If some are unchecked, save only the checked ones
        if ($request->has('selected_nav_link_ids') && is_array($request->input('selected_nav_link_ids'))) {
            $selectedIds = array_filter($request->input('selected_nav_link_ids'), function($id) {
                return !empty($id);
            });
            // Convert to integers and ensure they're valid
            $selectedIds = array_map('intval', $selectedIds);
            $selectedIds = array_values($selectedIds);
            
            // Get all navLinks for this navItem
            $allNavLinkIds = NavLink::where('nav_item_id', $request->input('nav_item_id'))
                ->pluck('id')
                ->map('intval')
                ->sort()
                ->values()
                ->toArray();
            
            // If all navLinks are selected (or no navLinks exist), save null (means show all)
            if (empty($selectedIds) || (count($selectedIds) === count($allNavLinkIds) && count($allNavLinkIds) > 0)) {
                $data['selected_nav_link_ids'] = null;
            } else {
                $data['selected_nav_link_ids'] = $selectedIds;
            }
        } else {
            // If no selection provided, null means show all subsections
            $data['selected_nav_link_ids'] = null;
        }
        
        // Process subsection configurations - ensure animation_style is required for each configured subsection
        if ($request->has('subsection_configurations')) {
            $configs = [];
            foreach ($request->input('subsection_configurations', []) as $navLinkId => $config) {
                if (!empty($config['animation_style'])) {
                    $configs[$navLinkId] = [
                        'animation_style' => $config['animation_style'],
                        'layout_style' => $config['layout_style'] ?? null,
                    ];
                }
            }
            $data['subsection_configurations'] = !empty($configs) ? $configs : null;
        }
        
        $data['user_id'] = Auth::id();
        HomePageSection::create($data);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Home page section created successfully']);
        }
        
        return redirect()->route('admin.home-page-sections.index')->with('status', 'Home page section created successfully');
    }

    public function edit(HomePageSection $homePageSection)
    {
        if ($homePageSection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $userId = Auth::id();
        $navItems = NavItem::where('user_id', $userId)
            ->where('visible', true)
            ->orderBy('position')
            ->get();
        
        $navLinks = NavLink::where('user_id', $userId)
            ->where('nav_item_id', $homePageSection->nav_item_id)
            ->orderBy('position')
            ->get();
        
        return view('admin.home-page-sections.edit', compact('homePageSection', 'navItems', 'navLinks'));
    }

    public function update(Request $request, HomePageSection $homePageSection)
    {
        if ($homePageSection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $data = $request->validate([
            'nav_item_id' => 'required|exists:nav_items,id',
            'position' => 'required|integer|min:0',
            'text_alignment' => 'required|in:left,right',
            'animation_style' => 'nullable|in:grid_editorial_collage,list_alternating_cards,carousel_scroll_left,carousel_scroll_right',
            'title' => 'nullable|array',
            'title.en' => 'nullable|string|max:255',
            'title.ja' => 'nullable|string|max:255',
            'subtitle' => 'nullable|array',
            'subtitle.en' => 'nullable|string|max:255',
            'subtitle.ja' => 'nullable|string|max:255',
            'enabled' => 'nullable|boolean',
            'selected_nav_link_ids' => 'nullable|array',
            'selected_nav_link_ids.*' => 'exists:nav_links,id',
            'subsection_configurations' => 'nullable|array',
            'subsection_configurations.*.animation_style' => 'required|in:grid_editorial_collage,list_alternating_cards,carousel_scroll_left,carousel_scroll_right',
            'subsection_configurations.*.layout_style' => 'nullable|string|max:255',
        ]);

        $data['enabled'] = (bool)$request->input('enabled', true);
        
        // Process title and subtitle translations
        if (isset($data['title']) && is_array($data['title'])) {
            $data['title'] = [
                'en' => $data['title']['en'] ?? '',
                'ja' => $data['title']['ja'] ?? '',
            ];
            // Remove if both are empty
            if (empty($data['title']['en']) && empty($data['title']['ja'])) {
                $data['title'] = null;
            }
        }
        
        if (isset($data['subtitle']) && is_array($data['subtitle'])) {
            $data['subtitle'] = [
                'en' => $data['subtitle']['en'] ?? '',
                'ja' => $data['subtitle']['ja'] ?? '',
            ];
            // Remove if both are empty
            if (empty($data['subtitle']['en']) && empty($data['subtitle']['ja'])) {
                $data['subtitle'] = null;
            }
        }
        
        // Handle selected_nav_link_ids - if all are selected or null, save null (means show all)
        // If some are unchecked, save only the checked ones
        if ($request->has('selected_nav_link_ids') && is_array($request->input('selected_nav_link_ids'))) {
            $selectedIds = array_filter($request->input('selected_nav_link_ids'), function($id) {
                return !empty($id);
            });
            // Convert to integers and ensure they're valid
            $selectedIds = array_map('intval', $selectedIds);
            $selectedIds = array_values($selectedIds);
            
            // Get all navLinks for this navItem
            $allNavLinkIds = NavLink::where('nav_item_id', $request->input('nav_item_id'))
                ->pluck('id')
                ->map('intval')
                ->sort()
                ->values()
                ->toArray();
            
            // If all navLinks are selected (or no navLinks exist), save null (means show all)
            if (empty($selectedIds) || (count($selectedIds) === count($allNavLinkIds) && count($allNavLinkIds) > 0)) {
                $data['selected_nav_link_ids'] = null;
            } else {
                $data['selected_nav_link_ids'] = $selectedIds;
            }
        } else {
            // If no selection provided, null means show all subsections
            $data['selected_nav_link_ids'] = null;
        }
        
        // Process subsection configurations - ensure animation_style is required for each configured subsection
        if ($request->has('subsection_configurations')) {
            $configs = [];
            foreach ($request->input('subsection_configurations', []) as $navLinkId => $config) {
                if (!empty($config['animation_style'])) {
                    $configs[$navLinkId] = [
                        'animation_style' => $config['animation_style'],
                        'layout_style' => $config['layout_style'] ?? null,
                    ];
                }
            }
            $data['subsection_configurations'] = !empty($configs) ? $configs : null;
        } else {
            $data['subsection_configurations'] = null;
        }
        
        $homePageSection->update($data);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Home page section updated successfully']);
        }
        
        return redirect()->route('admin.home-page-sections.index')->with('status', 'Home page section updated successfully');
    }

    public function toggleEnabled(HomePageSection $homePageSection)
    {
        if ($homePageSection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $homePageSection->enabled = !$homePageSection->enabled;
        $homePageSection->save();
        
        return redirect()->route('admin.nav.index')
            ->with('status', $homePageSection->enabled ? 'Section enabled' : 'Section disabled');
    }

    public function destroy(HomePageSection $homePageSection)
    {
        if ($homePageSection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        
        $homePageSection->delete();
        return redirect()->route('admin.home-page-sections.index')->with('status', 'Home page section deleted successfully');
    }

    public function getNavLinks(Request $request, $navItemId)
    {
        $userId = Auth::id();
        
        // Verify NavItem belongs to user
        $navItem = NavItem::where('user_id', $userId)->find($navItemId);
        if (!$navItem) {
            return response()->json([], 403);
        }
        
        // ONLY return NavLinks from the database - NO hardcoded items
        // If no NavLinks exist for this NavItem, return empty array
        $navLinks = NavLink::where('user_id', $userId)
            ->where('nav_item_id', $navItemId)
            ->orderBy('position')
            ->get();
        
        // Transform to include translated title
        $navLinksArray = $navLinks->map(function($link) {
            return [
                'id' => $link->id,
                'title' => $link->title, // Keep original array for compatibility
                'title_translated' => $link->getTranslated('title') ?: 'Untitled',
                'position' => $link->position,
            ];
        })->toArray();
        
        // Log for debugging
        \Log::info('getNavLinks called', [
            'nav_item_id' => $navItemId,
            'nav_links_count' => count($navLinksArray),
            'nav_links' => $navLinksArray
        ]);
        
        // Return empty array if no NavLinks exist - NO fallback to hardcoded items
        return response()->json($navLinksArray);
    }
    
    public function getNavItems()
    {
        $userId = Auth::id();
        $navItems = NavItem::where('user_id', $userId)
            ->where('visible', true)
            ->orderBy('position')
            ->get();
        
        return response()->json($navItems->map(function($item) {
            return [
                'id' => $item->id,
                'label' => $item->getTranslated('label') ?: 'Untitled',
            ];
        }));
    }
}

