<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavItem;
use App\Models\HomePageSection;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Http\Request;

class NavItemController extends Controller
{
    public function index() { 
        $items = NavItem::withCount('links')->orderBy('position')->get(); 
        $sections = HomePageSection::with('navItem')
            ->orderBy('position')
            ->get();
        $availableNavItems = NavItem::where('visible', true)
            ->orderBy('position')
            ->get();
        return view('admin.nav.index', compact('items', 'sections', 'availableNavItems')); 
    }
    public function create() { return view('admin.nav.create'); }
    public function store(Request $request) {
        $data = $request->validate([
            'label' => 'required|array',
            'label.en' => 'nullable|string|max:255',
            'label.ja' => 'nullable|string|max:255',
            'position'=>'nullable|integer',
            'visible'=>'nullable|boolean',
        ]);
        
        // Ensure at least one language is filled
        if (empty($data['label']['en']) && empty($data['label']['ja'])) {
            return back()->withErrors(['label' => 'At least one language (English or Japanese) must be filled.'])->withInput();
        }
        
        // Process label translations
        $data['label'] = [
            'en' => $data['label']['en'] ?? '',
            'ja' => $data['label']['ja'] ?? '',
        ];
        
        $data['visible'] = (bool)($data['visible'] ?? true);
        
        // Use English label for route derivation
        $labelForDerivation = $data['label']['en'] ?: $data['label']['ja'];
        $derived = $this->deriveFromLabel($labelForDerivation);
        NavItem::create(array_merge($data, $derived));
        return redirect()->route('admin.nav.index')->with('status','Nav item created');
    }
    public function edit(NavItem $nav) { return view('admin.nav.edit', ['item'=>$nav]); }
    public function update(Request $request, NavItem $nav) {
        $data = $request->validate([
            'label' => 'required|array',
            'label.en' => 'nullable|string|max:255',
            'label.ja' => 'nullable|string|max:255',
            'position'=>'nullable|integer',
            'visible'=>'nullable|boolean',
        ]);
        
        // Ensure at least one language is filled
        if (empty($data['label']['en']) && empty($data['label']['ja'])) {
            return back()->withErrors(['label' => 'At least one language (English or Japanese) must be filled.'])->withInput();
        }
        
        // Process label translations
        $data['label'] = [
            'en' => $data['label']['en'] ?? '',
            'ja' => $data['label']['ja'] ?? '',
        ];
        
        $data['visible'] = (bool)($data['visible'] ?? true);
        // Only derive route/icon/pattern if they don't already exist
        // This allows NavItems to exist without auto-routes when using NavLinks
        if (!$nav->route) {
            // Use English label for route derivation
            $labelForDerivation = $data['label']['en'] ?: $data['label']['ja'];
            $derived = $this->deriveFromLabel($labelForDerivation);
            $nav->update(array_merge($data, $derived));
        } else {
            $nav->update($data);
        }
        return redirect()->route('admin.nav.index')->with('status','Nav item updated');
    }
    public function destroy(NavItem $nav) { $nav->delete(); return redirect()->route('admin.nav.index')->with('status','Nav item deleted'); }

    private function deriveFromLabel(string $label): array
    {
        $key = strtolower(trim($label));
        $map = [
            'home'        => ['admin.dashboard', 'admin.dashboard', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/></svg>'],
            'dashboard'   => ['admin.dashboard', 'admin.dashboard', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/></svg>'],
            'tryhackme'   => ['admin.thm', 'admin.thm', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>'],
            'udemy'       => ['admin.udemy', 'admin.udemy', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4 4m-4-4l4-4"/></svg>'],
            'reports'     => ['admin.reports', 'admin.reports', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2z"/></svg>'],
            'tasks'       => ['admin.tasks', 'admin.tasks', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
            'projects'    => ['admin.projects.index', 'admin.projects.*', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>'],
            'certificates'=> ['admin.certificates.index', 'admin.certificates.*', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'],
            'labs'        => ['admin.labs.index', 'admin.labs.*', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>'],
            'books'       => ['admin.books.index', 'admin.books.*', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l9-4-9-4-9 4 9 4z"/></svg>'],
            'skills'      => ['admin.skills.index', 'admin.skills.*', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>'],
            'tags'        => ['admin.tags.index', 'admin.tags.*', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M3 5a2 2 0 012-2h6l7 7a2 2 0 010 2.828l-6.172 6.172a2 2 0 01-2.828 0L3 11V5z"/></svg>'],
            'timeline'    => ['admin.timeline.index', 'admin.timeline.*', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>'],
            'categories'  => ['admin.categories.index', 'admin.categories.*', '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>'],
        ];
        $route = $map[$key][0] ?? null;
        $pattern = $map[$key][1] ?? ($route ?? null);
        $icon = $map[$key][2] ?? null;

        if (!$route) {
            $candidate1 = 'admin.' . $key;
            $candidate2 = 'admin.' . $key . '.index';
            if (RouteFacade::has($candidate1)) {
                $route = $candidate1;
                $pattern = $candidate1 . '*';
            } elseif (RouteFacade::has($candidate2)) {
                $route = $candidate2;
                $pattern = 'admin.' . $key . '.*';
            }
        }
        if (!$icon) {
            // generic folder icon for unknown labels
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6l2 2h10v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>';
        }
        return [
            'route' => $route,
            'active_pattern' => $pattern,
            'icon_svg' => $icon,
            'url' => null,
        ];
    }
}


