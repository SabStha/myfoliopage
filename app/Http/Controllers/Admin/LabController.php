<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabController extends Controller
{
    public function index() { 
        $labs = Lab::where('user_id', Auth::id())->latest('completed_at')->paginate(20); 
        return view('admin.labs.index', compact('labs')); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() { return view('admin.labs.create'); }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) { 
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'slug'=>'required|string|max:255|unique:labs,slug',
            'platform'=>'nullable|string|max:255',
            'room_url'=>'nullable|url',
            'completed_at'=>'nullable|date',
            'summary'=>'nullable|string'
        ]); 
        $data['user_id'] = Auth::id();
        Lab::create($data); 
        return redirect()->route('admin.labs.index')->with('status','Lab created'); 
    }

    /**
     * Display the specified resource.
     */
    public function show(Lab $lab) { 
        if ($lab->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return redirect()->route('admin.labs.edit', $lab); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lab $lab) { 
        if ($lab->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return view('admin.labs.edit', compact('lab')); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lab $lab) { 
        if ($lab->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'slug'=>'required|string|max:255|unique:labs,slug,'.$lab->id,
            'platform'=>'nullable|string|max:255',
            'room_url'=>'nullable|url',
            'completed_at'=>'nullable|date',
            'summary'=>'nullable|string'
        ]); 
        $lab->update($data); 
        return redirect()->route('admin.labs.index')->with('status','Lab updated'); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lab $lab) { 
        if ($lab->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $lab->delete(); 
        return redirect()->route('admin.labs.index')->with('status','Lab deleted'); 
    }
}
