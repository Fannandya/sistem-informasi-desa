<?php

namespace App\Http\Controllers\Publik;

use App\Enums\KategoriFasilitas;
use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FasilitasController extends Controller
{
    public function __invoke(Request $request): View
    {
        $kategori = KategoriFasilitas::tryFrom((string) $request->input('kategori'));

        $daftar = FasilitasUmum::query()
            ->tampil()
            ->with('wilayah')
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->orderBy('nama')
            ->get();

        return view('publik.fasilitas', [
            // Kategori yang belum punya isi tidak ikut muncul sebagai judul
            // kosong di halaman publik.
            'kelompok' => $daftar->groupBy(fn (FasilitasUmum $f) => $f->kategori->value),
            'kategori' => $kategori,
            'jumlahPerKategori' => FasilitasUmum::query()
                ->tampil()
                ->selectRaw('kategori, COUNT(*) as total')
                ->groupBy('kategori')
                ->pluck('total', 'kategori'),
        ]);
    }
}
