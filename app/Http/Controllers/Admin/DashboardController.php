<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Certificate;
use App\Models\Lab;
use App\Models\Category;
use App\Models\NavLink;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        // Get categories that belong to this user OR have NavLinks belonging to this user
        $userCategoryIds = NavLink::where('user_id', $userId)
            ->whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id')
            ->toArray();
        
        // Get categories filtered by user_id or by NavLinks
        $categories = Category::where(function($query) use ($userId, $userCategoryIds) {
            $query->where('user_id', $userId)
                  ->orWhereIn('id', $userCategoryIds);
        })->orderBy('position')->get();
        
        // dynamic categories from NavLink.category_id; fallback to core counts
        $categoryCounts = $categories->map(function($c) use ($userId) {
            return [
                'label' => $c->getTranslated('name'),
                'value' => NavLink::where('category_id', $c->id)
                    ->where('user_id', $userId)
                    ->count(),
                'color' => $c->color,
            ];
        })->filter(function($item) {
            // Only include categories that have at least one NavLink
            return $item['value'] > 0;
        })->values();
        
        // Fallback to core counts if no categories with NavLinks
        if ($categoryCounts->isEmpty()) {
            $projectsCount = Project::where('user_id', $userId)->count();
            $certificatesCount = Certificate::where('user_id', $userId)->count();
            
            // Only show metrics that have content
            $fallbackMetrics = [];
            if ($projectsCount > 0) {
                $fallbackMetrics[] = ['label' => 'Projects', 'value' => $projectsCount];
            }
            if ($certificatesCount > 0) {
                $fallbackMetrics[] = ['label' => 'Certificates', 'value' => $certificatesCount];
            }
            
            $categoryCounts = collect($fallbackMetrics);
        }

        // Build last 12 months activity counts from NavLinks (issued_at fallback to created_at)
        $labels = [];
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M');
            $start = $month->copy();
            $end = $month->copy()->endOfMonth();
            $count = NavLink::where('user_id', $userId)
                ->where(function($q) use ($start, $end) {
                    $q->whereBetween('issued_at', [$start, $end])
                      ->orWhere(function($q2) use ($start, $end) {
                          $q2->whereNull('issued_at')->whereBetween('created_at', [$start, $end]);
                      });
                })->count();
            $data[] = $count;
        }
        $chart = [ 'labels' => $labels, 'data' => $data ];

        // Preserve old keys for the existing view while also sending categories
        $projects_count = Project::where('user_id', $userId)->count();
        $certificates_count = Certificate::where('user_id', $userId)->count();
        $labs_count = Lab::count();
        $overall = [
            'labels' => $categoryCounts->pluck('label')->all(),
            'data' => $categoryCounts->pluck('value')->all(),
        ];

        return view('admin.dashboard', compact('projects_count','certificates_count','labs_count','chart','overall') + [
            'categories' => $categoryCounts,
        ]);
    }
}


