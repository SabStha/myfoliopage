<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OngoingProgressController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        // Fetch NavItems with their links to calculate progress - filtered by user
        $navItems = NavItem::where('user_id', $userId)
            ->with(['links' => function($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->where('visible', true)
            ->orderBy('position')
            ->get()
            ->map(function($navItem) use ($userId) {
                // Filter links by user_id
                $links = $navItem->links->where('user_id', $userId);
                $totalLinks = $links->count();
                $avgProgress = $totalLinks > 0 ? round($links->avg('progress') ?? 0) : 0;
                $completedLinks = $links->where('progress', 100)->count();
                
                return [
                    'id' => $navItem->id,
                    'label' => $navItem->getTranslated('label') ?: 'Untitled',
                    'total_items' => $totalLinks,
                    'completed_items' => $completedLinks,
                    'progress' => $avgProgress,
                    'nav_item' => $navItem,
                ];
            })
            ->filter(function($item) {
                // Only show NavItems that have links
                return $item['total_items'] > 0;
            });
        
        return view('admin.ongoing-progress.index', compact('navItems'));
    }

    // Progress is managed through NavItems - redirect to nav management
    public function create()
    {
        return redirect()->route('admin.nav.index')->with('info', 'Add a NavItem in the sidebar navigation to create a progress item. Then add NavLinks under it with progress percentages.');
    }
}

