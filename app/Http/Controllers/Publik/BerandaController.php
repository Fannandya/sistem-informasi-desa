<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\AcaraDesa;
use App\Models\BeritaDesa;
use App\Models\FasilitasUmum;
use App\Models\Pengurus;
use App\Models\PotensiDesa;
use App\Models\PrestasiDesa;
use App\Queries\StatistikPublik;
use Illuminate\View\View;

/**
 * Fitur 14 — halaman muka.
 *
 * Setiap bagian hanya memuat cuplikan, bukan seluruh data. Halaman muka desa
 * sering dibuka lewat koneksi seluler; memuat ratusan kartu di satu halaman
 * membuatnya lambat tanpa memberi manfaat apa pun.
 */
class BerandaController extends Controller
{
    public function __invoke(StatistikPublik $statistik): View
    {
        return view('publik.beranda', [
            'potensi' => PotensiDesa::query()->tampil()->urut()->take(6)->get(),

            'beritaUtama' => BeritaDesa::query()->terbit()->where('utama', true)->with('kategori')->first()
                ?? BeritaDesa::query()->terbit()->with('kategori')->terbaru()->first(),

            'berita' => BeritaDesa::query()->terbit()->with('kategori')->terbaru()->take(4)->get(),

            'acara' => AcaraDesa::query()->terbit()->withCount('foto')->with('foto')->terbaru()->take(6)->get(),

            'fasilitas' => FasilitasUmum::query()->tampil()->orderBy('kategori')->take(4)->get(),

            'prestasi' => PrestasiDesa::query()->tampil()->terbaru()->take(3)->get(),

            // Hanya pengurus inti; daftar lengkap ada di halaman khususnya.
            'pengurus' => Pengurus::query()
                ->tampil()
                ->with(['organisasi', 'wilayah'])
                ->join('ref_organisasi', 'pengurus_desa.organisasi_id', '=', 'ref_organisasi.id')
                ->orderBy('ref_organisasi.urutan')
                ->orderBy('pengurus_desa.urutan')
                ->select('pengurus_desa.*')
                ->take(4)
                ->get(),

            'statistik' => $statistik->ringkasan(),
        ]);
    }
}
