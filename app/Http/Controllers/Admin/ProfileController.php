<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $userId = Auth::id();
        $profile = Profile::where('user_id', $userId)->first();
        return view('admin.profile.edit', compact('profile'));
    }

    public function update(Request $request)
    {
        $userId = Auth::id();
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:4096',
            'remove_photo' => 'nullable|boolean',
        ]);
        $profile = Profile::firstOrCreate(['user_id' => $userId]);
        if (!empty($data['remove_photo'])) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $profile->photo_path = null;
        }
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile', 'public');
            $profile->photo_path = $path;
        }
        if (isset($data['name'])) $profile->name = $data['name'];
        if (isset($data['role'])) $profile->role = $data['role'];
        $profile->save();
        return redirect()->route('admin.profile.edit')->with('status','Profile updated');
    }
}


