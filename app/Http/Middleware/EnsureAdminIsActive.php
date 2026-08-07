<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menutup akses admin yang dinonaktifkan di tengah sesi.
 *
 * Pemeriksaan saat login saja tidak cukup: admin yang sudah masuk lebih dulu
 * akan tetap membawa sesi yang sah sampai sesinya kedaluwarsa. Padahal alasan
 * sebuah akun dinonaktifkan biasanya justru mendesak — perangkat desa
 * berhenti menjabat, atau akunnya diduga dipakai orang lain.
 */
class EnsureAdminIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin !== null && ! $admin->is_active) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->with('status', 'Akun Anda sedang dinonaktifkan. Hubungi admin utama desa.');
        }

        return $next($request);
    }
}
