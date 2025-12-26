<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Auto-login? Or redirect to PWA?
        // User requested: "Internal panel in backend and software landing pages also in bakend only with register"
        // Strategy: 
        // 1. Create account.
        // 2. Log them in via session (so they are auth'd on backend context).
        // 3. But their "App" is on a subdomain.
        // 4. Ideally, redirect them to `app.domain/login` or show a success page "Account Created! Go to App".

        // Let's go with Success Page for clarity, with a big "Launch App" button.
        // OR better: Redirect to the PWA login page with email pre-filled if possible, or just let them login.

        Auth::login($user); // Login on backend context (maybe useful generally)

        return redirect()->route('home')->with('success', 'Account created! Please log in to the app.');
    }
}
