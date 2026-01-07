<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserPresence;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $user = Auth::guard('admin')->user();
            if ($user->isAdmin()) {
                $request->session()->regenerate();
                
                // Set initial presence status when user logs in
                UserPresence::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'status' => 'online',
                        'last_seen_at' => now(),
                    ]
                );
                
                return redirect()->intended(route('admin.dashboard'));
            } else {
                Auth::guard('admin')->logout();
                return back()->withErrors(['email' => 'Unauthorized access.']);
            }
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    public function showRegisterForm()
    {
        return view('admin.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        Auth::guard('admin')->login($user);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        // Set presence to offline when user logs out
        if ($user) {
            UserPresence::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 'offline',
                    'last_seen_at' => now(),
                ]
            );
        }
        
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

