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
        $response = $next($request);

        // Header diatur lewat bag-nya, bukan withHeaders(): metode itu hanya
        // ada pada Illuminate\Http\Response, sedangkan unduhan berkas
        // mengembalikan StreamedResponse. Memakai withHeaders() membuat
        // seluruh unduhan di panel admin gagal dengan error 500.
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
