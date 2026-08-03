<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fitur 01 — menekan tombol kembali setelah logout tidak boleh menampilkan
 * halaman admin dari cache peramban.
 */
class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request)->withHeaders([
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}
