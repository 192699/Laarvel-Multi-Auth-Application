<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated with customer guard
        if (!auth('customer')->check()) {
            return redirect()->route('customer.login');
        }

        // Check if user has customer role
        $user = auth('customer')->user();
        if (!$user || !$user->isCustomer()) {
            auth('customer')->logout();
            return redirect()->route('customer.login')->withErrors(['email' => 'Unauthorized access.']);
        }

        return $next($request);
    }
}

