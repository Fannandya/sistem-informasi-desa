<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Navigasi halaman publik.
 *
 * Daftar tautan (rute & urutannya) tetap dikode — hanya label yang dinamis
 * lewat CMS Teks Tampilan (grup 'menu'). Tautan yang rutenya belum
 * terdaftar otomatis dilewati.
 *
 * @return array<int, array{label: string, url: string, aktif: bool}>
 */
class MenuPublik
{
    /** @var array<string, string> nama rute => kunci Teks Tampilan */
    private const TAUTAN = [
        'beranda' => 'menu.beranda',
        'publik.profil' => 'menu.profil',
        'publik.potensi.index' => 'menu.potensi',
        'publik.fasilitas' => 'menu.fasilitas',
        'publik.prestasi' => 'menu.prestasi',
        'publik.berita.index' => 'menu.berita',
        'publik.galeri.index' => 'menu.galeri',
        'publik.struktur' => 'menu.struktur',
        'publik.statistik' => 'menu.statistik',
    ];

    /**
     * @return array<int, array{label: string, url: string, aktif: bool}>
     */
    public static function tautan(): array
    {
        $hasil = [];

        foreach (self::TAUTAN as $rute => $kunci) {
            if (! Route::has($rute)) {
                continue;
            }

            $hasil[] = [
                'label' => Teks::ambil($kunci),
                'url' => route($rute),
                'aktif' => request()->routeIs($rute) || request()->routeIs(str_replace('.index', '.*', $rute)),
            ];
        }

        return $hasil;
    }
}
