<?php

namespace App\Support;

use App\Models\TeksTampilan;

/**
 * Static, dipanggil langsung dari Blade tanpa facade — pola yang sama
 * dengan MenuPublik dan EmbedPeta di kelas ini.
 *
 * config('teks-tampilan') WAJIB dipanggil tanpa argumen bertitik lanjutan:
 * kunci CMS ('beranda.hero.sapaan') memakai titik sebagai bagian dari
 * namanya sendiri, bukan sebagai pemisah bersarang Laravel. Ambil seluruh
 * array config lalu index dengan PHP biasa.
 */
class Teks
{
    public static function ambil(string $kunci, array $ganti = []): string
    {
        $meta = config('teks-tampilan')[$kunci] ?? null;
        $default = $meta['default'] ?? $kunci;

        $nilai = TeksTampilan::semua()[$kunci] ?? null;
        $teks = blank($nilai) ? (string) $default : $nilai;

        return $ganti === [] ? $teks : strtr($teks, $ganti);
    }

    public static function angka(string $kunci): int
    {
        return (int) self::ambil($kunci);
    }
}
