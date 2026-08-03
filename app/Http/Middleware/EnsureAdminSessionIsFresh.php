<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fitur 01 — timeout sesi.
 *
 * `SESSION_LIFETIME` sudah mengakhiri sesi yang menganggur, tetapi tidak
 * memberi tahu admin apa yang terjadi — ia hanya terlempar ke halaman login
 * tanpa penjelasan. Middleware ini mencatat waktu aktivitas terakhir di dalam
 * sesi, sehingga sistem dapat membedakan "belum pernah masuk" dari
 * "sesi berakhir" dan menampilkan pesan yang tepat.
 */
class EnsureAdminSessionIsFresh
{
    private const KUNCI = 'admin_aktivitas_terakhir';

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            return $next($request);
        }

        $batasDetik = config('session.lifetime') * 60;
        $terakhir = $request->session()->get(self::KUNCI);

        if ($terakhir !== null && (time() - $terakhir) > $batasDetik) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->with('status', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        }

        $request->session()->put(self::KUNCI, time());

        return $next($request);
    }
}
