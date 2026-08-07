<?php

namespace App\Http\Middleware;

use App\Support\EmbedPeta;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan tingkat peramban untuk seluruh halaman.
 *
 * Perlindungan di sini tidak menuntut apa pun dari perangkat keras hosting —
 * cukup memberi tahu peramban apa yang boleh dan tidak boleh dilakukan
 * terhadap halaman ini.
 *
 * Catatan jujur soal script-src
 * -----------------------------
 * script-src memakai 'unsafe-inline' dan 'unsafe-eval', jadi ia TIDAK
 * menghentikan skrip yang berhasil disuntikkan ke dalam halaman. Dua sebabnya
 * nyata dan tidak murah dihilangkan:
 *
 *   - Alpine menilai ekspresi `x-data`/`x-show` lewat new Function(), yang
 *     menuntut 'unsafe-eval'. Menghindarinya berarti memakai build CSP Alpine
 *     dan menulis ulang seluruh ekspresi di 18 berkas tampilan.
 *   - Ada enam blok <script> sebaris yang menyuntikkan data dari Blade.
 *     Menghilangkan 'unsafe-inline' berarti memindahkan semuanya ke berkas
 *     terpisah beserta jalur pengiriman datanya.
 *
 * Yang tetap dijaga ketat, dan inilah nilai utama CSP di sini:
 *   - frame-ancestors 'none'  — halaman ini tidak bisa dibingkai situs lain
 *   - object-src 'none'       — tidak ada plugin/embed lawas
 *   - base-uri 'self'         — <base> palsu tidak bisa membelokkan URL relatif
 *   - form-action 'self'      — formulir tidak bisa dikirim ke server luar
 *   - script-src 'self'       — skrip dari host luar tetap diblokir
 *
 * Pertahanan utama terhadap XSS tersimpan di tempat lain: Purifier pada isi
 * berita, dan EmbedPeta yang hanya menyimpan URL peta alih-alih HTML mentah.
 */
class TambahHeaderKeamanan
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Diatur lewat bag header, bukan withHeaders(): unduhan berkas
        // mengembalikan StreamedResponse yang tidak punya metode itu.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', $this->kebijakan());

        // Hanya bermakna di atas HTTPS. Mengirimkannya lewat HTTP biasa akan
        // mengunci pengembang keluar dari `php artisan serve`.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        return $response;
    }

    private function kebijakan(): string
    {
        $sumberPengembangan = $this->sumberPengembangan();

        $arahan = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' ".$sumberPengembangan,
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com ".$sumberPengembangan,
            'font-src '.implode(' ', ["'self'", 'https://fonts.gstatic.com', 'data:']),
            'img-src '.implode(' ', array_filter(["'self'", 'data:', 'blob:', $this->asalDiskUnggahan()])),
            'frame-src '.implode(' ', $this->asalPeta()),
            "connect-src 'self' ".$sumberPengembangan,
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ];

        return implode('; ', array_map(fn (string $a) => trim($a), $arahan));
    }

    /**
     * Host peta diambil dari daftar yang sama dengan yang dipakai saat
     * memvalidasi kode embed, supaya keduanya tidak pernah berbeda.
     *
     * @return array<int, string>
     */
    private function asalPeta(): array
    {
        return array_map(
            fn (string $host) => 'https://'.$host,
            EmbedPeta::HOST_DIIZINKAN,
        );
    }

    /**
     * Asal berkas unggahan bila disimpan di luar domain ini — Cloudflare R2
     * pada pemasangan produksi (PRD 7). Tanpa ini seluruh foto akan diblokir
     * begitu UPLOAD_DISK berpindah dari `public`.
     */
    private function asalDiskUnggahan(): ?string
    {
        $url = config('filesystems.disks.'.config('unggahan.disk').'.url');

        if (! is_string($url) || $url === '') {
            return null;
        }

        $bagian = parse_url($url);

        if (! isset($bagian['scheme'], $bagian['host'])) {
            return null;
        }

        return $bagian['scheme'].'://'.$bagian['host'].(isset($bagian['port']) ? ':'.$bagian['port'] : '');
    }

    /**
     * Server pengembangan Vite. Tanpa ini `npm run dev` berhenti bekerja
     * begitu CSP aktif — dan CSP yang hanya menyala di produksi berarti
     * kesalahannya baru ketahuan setelah rilis.
     */
    private function sumberPengembangan(): string
    {
        if (! Vite::isRunningHot()) {
            return '';
        }

        $asal = trim((string) @file_get_contents(public_path('hot')));

        if ($asal === '') {
            return '';
        }

        $asal = rtrim($asal, '/');

        return $asal.' '.str_replace(['http://', 'https://'], ['ws://', 'wss://'], $asal);
    }
}
