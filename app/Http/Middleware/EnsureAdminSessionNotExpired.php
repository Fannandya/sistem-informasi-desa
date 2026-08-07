<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Timeout sesi panel admin (fitur 01).
 *
 * SESSION_LIFETIME sendiri sudah mengakhiri sesi yang menganggur, tetapi
 * tidak memberi tahu apa yang terjadi — admin hanya terlempar ke halaman
 * login tanpa penjelasan. Middleware ini mencatat waktu aktivitas terakhir
 * sehingga sistem bisa membedakan "belum pernah masuk" dari "sesi berakhir".
 *
 * Terpasang pada grup rute admin, bukan secara global: yang dihitung adalah
 * kelambanan di panel admin. Membaca halaman publik desa bukan aktivitas
 * admin dan tidak memperpanjang sesinya.
 */
class EnsureAdminSessionNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('admin')->check()) {
            return $next($request);
        }

        $aktivitasTerakhir = session('last_activity');
        $batasDetik = config('session.lifetime') * 60;

        if ($aktivitasTerakhir && (time() - $aktivitasTerakhir) > $batasDetik) {
            return $this->akhiriSesi($request);
        }

        session(['last_activity' => time()]);

        return $next($request);
    }

    private function akhiriSesi(Request $request): Response
    {
        auth('admin')->logout();

        // Melepas autentikasi saja tidak cukup: sisa isi sesi masih menempel
        // pada id sesi yang sama.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Permintaan latar (grafik dasbor) tidak boleh menerima halaman login
        // sebagai jawaban — pemanggilnya mengharapkan JSON.
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Session expired'], 401);
        }

        return redirect(route('admin.login'))
            ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
    }
}
