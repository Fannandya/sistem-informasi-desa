<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Navigasi halaman publik.
 *
 * Daftar tautan ditetapkan di satu tempat supaya kop, menu ponsel, dan footer
 * tidak pernah menampilkan menu yang berbeda. Tautan yang rutenya belum
 * terdaftar otomatis dilewati, sehingga menu tetap utuh saat fitur 09–15
 * dikerjakan bertahap.
 *
 * @return array<int, array{label: string, url: string, aktif: bool}>
 */
class MenuPublik
{
    /** @var array<string, string> nama rute => label */
    private const TAUTAN = [
        'beranda' => 'Beranda',
        'publik.profil' => 'Profil',
        'publik.potensi.index' => 'Potensi',
        'publik.fasilitas' => 'Fasilitas',
        'publik.prestasi' => 'Prestasi',
        'publik.berita.index' => 'Berita',
        'publik.galeri.index' => 'Galeri',
        'publik.struktur' => 'Struktur Organisasi',
        'publik.statistik' => 'Statistik',
    ];

    /**
     * @return array<int, array{label: string, url: string, aktif: bool}>
     */
    public static function tautan(): array
    {
        $hasil = [];

        foreach (self::TAUTAN as $rute => $label) {
            if (! Route::has($rute)) {
                continue;
            }

            $hasil[] = [
                'label' => $label,
                'url' => route($rute),
                'aktif' => request()->routeIs($rute) || request()->routeIs(str_replace('.index', '.*', $rute)),
            ];
        }

        return $hasil;
    }
}
