<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    public function index() { 
        $skills = Skill::where('user_id', Auth::id())->orderBy('category')->orderBy('name')->paginate(20); 
        return view('admin.skills.index', compact('skills')); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() { return view('admin.skills.create'); }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) { 
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'category'=>'nullable|string|max:255',
            'level'=>'nullable|string|max:255'
        ]); 
        $data['user_id'] = Auth::id();
        Skill::create($data); 
        return redirect()->route('admin.skills.index')->with('status','Skill created'); 
    }

    /**
     * Display the specified resource.
     */
    public function show(Skill $skill) { 
        if ($skill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return redirect()->route('admin.skills.edit', $skill); 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Skill $skill) { 
        if ($skill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        return view('admin.skills.edit', compact('skill')); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Skill $skill) { 
        if ($skill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'category'=>'nullable|string|max:255',
            'level'=>'nullable|string|max:255'
        ]); 
        $skill->update($data); 
        return redirect()->route('admin.skills.index')->with('status','Skill updated'); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill) { 
        if ($skill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }
        $skill->delete(); 
        return redirect()->route('admin.skills.index')->with('status','Skill deleted'); 
    }
}
