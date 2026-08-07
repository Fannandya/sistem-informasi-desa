<?php

namespace App\Support;

/**
 * Fitur 03 — penanganan kode embed Google Maps.
 *
 * Admin biasanya menempelkan seluruh tag <iframe> hasil salin dari Google
 * Maps. Menyimpan lalu menampilkan ulang HTML mentah dari admin adalah celah
 * XSS: siapa pun yang berhasil masuk sebagai admin bisa menanam skrip di
 * setiap halaman publik.
 *
 * Karena itu yang diambil dan disimpan hanya alamat `src`-nya, setelah
 * dipastikan berasal dari Google Maps. Peta lalu dirender ulang oleh aplikasi
 * di dalam iframe yang atributnya ditentukan sendiri.
 */
class EmbedPeta
{
    /**
     * Host yang diterima sebagai sumber peta.
     *
     * Publik karena Content-Security-Policy harus mengizinkan host yang sama
     * persis pada frame-src. Dua daftar terpisah cepat atau lambat akan
     * berbeda, dan gejalanya membingungkan: peta lolos validasi saat disimpan
     * tetapi diblokir peramban saat ditampilkan.
     */
    public const HOST_DIIZINKAN = [
        'www.google.com',
        'google.com',
        'maps.google.com',
        'www.google.co.id',
        'google.co.id',
    ];

    /**
     * Mengambil URL peta dari masukan admin.
     *
     * Menerima tag <iframe> lengkap maupun URL polos. Mengembalikan null bila
     * tidak ada URL Google Maps yang sah di dalamnya.
     */
    public static function ekstrakUrl(?string $masukan): ?string
    {
        $masukan = trim((string) $masukan);

        if ($masukan === '') {
            return null;
        }

        $url = str_contains($masukan, '<iframe')
            ? static::srcDariIframe($masukan)
            : $masukan;

        if ($url === null) {
            return null;
        }

        return static::sah($url) ? $url : null;
    }

    /**
     * Apakah sebuah URL merupakan alamat embed Google Maps yang sah.
     */
    public static function sah(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $bagian = parse_url($url);

        // https wajib — iframe http akan diblokir peramban pada situs https.
        if (($bagian['scheme'] ?? null) !== 'https') {
            return false;
        }

        $host = mb_strtolower($bagian['host'] ?? '');

        if (! in_array($host, self::HOST_DIIZINKAN, true)) {
            return false;
        }

        $path = $bagian['path'] ?? '';

        return str_starts_with($path, '/maps/embed');
    }

    private static function srcDariIframe(string $html): ?string
    {
        if (preg_match('/<iframe[^>]*\ssrc\s*=\s*["\']([^"\']+)["\']/i', $html, $cocok) !== 1) {
            return null;
        }

        return html_entity_decode($cocok[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
