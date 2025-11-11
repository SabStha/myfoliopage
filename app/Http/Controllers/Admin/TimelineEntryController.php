<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimelineEntry;
use Illuminate\Http\Request;

class TimelineEntryController extends Controller
{
    public function index() { $entries = TimelineEntry::orderByDesc('occurred_at')->paginate(20); return view('admin.timeline.index', compact('entries')); }

    /**
     * Show the form for creating a new resource.
     */
    public function create() { return view('admin.timeline.create'); }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) { $data = $request->validate(['title'=>'required|string|max:255','occurred_at'=>'required|date','description'=>'nullable|string']); TimelineEntry::create($data); return redirect()->route('admin.timeline.index')->with('status','Timeline entry created'); }

    /**
     * Display the specified resource.
     */
    public function show(TimelineEntry $timelineEntry) { return redirect()->route('admin.timeline.edit', $timelineEntry); }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimelineEntry $timelineEntry) { return view('admin.timeline.edit', ['entry' => $timelineEntry]); }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimelineEntry $timelineEntry) { $data = $request->validate(['title'=>'required|string|max:255','occurred_at'=>'required|date','description'=>'nullable|string']); $timelineEntry->update($data); return redirect()->route('admin.timeline.index')->with('status','Timeline entry updated'); }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimelineEntry $timelineEntry) { $timelineEntry->delete(); return redirect()->route('admin.timeline.index')->with('status','Timeline entry deleted'); }
}
