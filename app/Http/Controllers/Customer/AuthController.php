<?php

namespace App\Http\Controllers\Customer;

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
        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->filled('remember'))) {
            $user = Auth::guard('customer')->user();
            if ($user->isCustomer()) {
                $request->session()->regenerate();
                
                // Set initial presence status when user logs in
                \App\Models\UserPresence::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'status' => 'online',
                        'last_seen_at' => now(),
                    ]
                );
                
                return redirect()->intended(route('customer.dashboard'));
            } else {
                Auth::guard('customer')->logout();
                return back()->withErrors(['email' => 'Unauthorized access.']);
            }
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    public function showRegisterForm()
    {
        return view('customer.auth.register');
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
            'role' => 'customer',
        ]);

        Auth::guard('customer')->login($user);

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('customer')->user();
        
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
        
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}

