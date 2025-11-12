<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimelineEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimelineEntryController extends Controller
{
    public function index() { 
        $entries = TimelineEntry::where('user_id', Auth::id())->orderByDesc('occurred_at')->paginate(20); 
        return view('admin.timeline.index', compact('entries')); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() { return view('admin.timeline.create'); }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) { 
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'occurred_at'=>'required|date',
            'description'=>'nullable|string'
        ]); 
        $data['user_id'] = Auth::id();
        TimelineEntry::create($data); 
        return redirect()->route('admin.timeline.index')->with('status','Timeline entry created'); 
    }

    /**
     * Display the specified resource.
     */
    public function show(TimelineEntry $timelineEntry) { 
        if ($timelineEntry->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return redirect()->route('admin.timeline.edit', $timelineEntry); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimelineEntry $timelineEntry) { 
        if ($timelineEntry->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return view('admin.timeline.edit', ['entry' => $timelineEntry]); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimelineEntry $timelineEntry) { 
        if ($timelineEntry->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'occurred_at'=>'required|date',
            'description'=>'nullable|string'
        ]); 
        $timelineEntry->update($data); 
        return redirect()->route('admin.timeline.index')->with('status','Timeline entry updated'); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimelineEntry $timelineEntry) { 
        if ($timelineEntry->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $timelineEntry->delete(); 
        return redirect()->route('admin.timeline.index')->with('status','Timeline entry deleted'); 
    }
}
