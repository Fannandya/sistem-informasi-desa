<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use App\Models\RefKategoriFasilitas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FasilitasController extends Controller
{
    public function __invoke(Request $request): View
    {
        $kategoriId = $request->input('kategori');

        $daftar = FasilitasUmum::query()
            ->tampil()
            ->with('kategori', 'wilayah')
            ->when($kategoriId, fn ($q) => $q->where('kategori_fasilitas_id', $kategoriId))
            ->orderBy('nama')
            ->get();

        return view('publik.fasilitas', [
            // Kategori yang belum punya isi tidak ikut muncul sebagai judul
            // kosong di halaman publik.
            'kelompok' => $daftar->groupBy(fn (FasilitasUmum $f) => $f->kategori?->nama ?? 'Lainnya'),
            'kategoriId' => $kategoriId,
            'kategoriFilter' => RefKategoriFasilitas::urut()->get(),
            'jumlahPerKategori' => RefKategoriFasilitas::urut()
                ->withCount(['fasilitas' => fn ($q) => $q->tampil()])
                ->get()
                ->pluck('fasilitas_count', 'nama')
                ->sortByDesc(fn ($v) => $v),
        ]);
    }
}
