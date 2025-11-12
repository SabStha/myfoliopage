<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms_accepted' => ['required', 'accepted'],
            'privacy_accepted' => ['required', 'accepted'],
        ], [
            'terms_accepted.required' => 'You must accept the Terms and Conditions to register.',
            'terms_accepted.accepted' => 'You must accept the Terms and Conditions to register.',
            'privacy_accepted.required' => 'You must accept the Privacy Policy to register.',
            'privacy_accepted.accepted' => 'You must accept the Privacy Policy to register.',
        ]);

        // Current version identifiers (update these when you change terms/privacy)
        $currentTermsVersion = '1.0';
        $currentPrivacyVersion = '1.0';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'terms_version' => $currentTermsVersion,
            'privacy_version' => $currentPrivacyVersion,
            'acceptance_ip_address' => $request->ip(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Always redirect to admin dashboard after registration
        return redirect(route('admin.dashboard', absolute: false));
    }
}
