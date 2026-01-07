<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated with admin guard
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login');
        }

        // Check if user has admin role
        $user = auth('admin')->user();
        if (!$user || !$user->isAdmin()) {
            auth('admin')->logout();
            return redirect()->route('admin.login')->withErrors(['email' => 'Unauthorized access.']);
        }

        return $next($request);
    }
}

