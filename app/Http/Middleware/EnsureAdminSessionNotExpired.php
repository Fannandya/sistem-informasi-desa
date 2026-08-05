<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSessionNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is authenticated
        if (!auth('admin')->check()) {
            return $next($request);
        }

        // Get last activity timestamp
        $lastActivity = session('last_activity');
        $timeout = config('session.lifetime') * 60; // Convert to seconds

        // Check if session has expired
        if ($lastActivity && (time() - $lastActivity) > $timeout) {
            auth('admin')->logout();

            // If AJAX request, return 401
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired'], 401);
            }

            // Redirect to login with message
            return redirect(route('admin.login'))
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }

        // Update last activity
        session(['last_activity' => time()]);

        return $next($request);
    }
}
