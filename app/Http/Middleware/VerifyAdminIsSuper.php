<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAdminIsSuper {
    public function handle(Request $request, Closure $next) {
        if (!auth('admin')->check() || !auth('admin')->user()->is_super_admin) {
            abort(403, 'Hanya super admin yang bisa akses halaman ini.');
        }
        return $next($request);
    }
}
