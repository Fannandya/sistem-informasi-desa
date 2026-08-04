<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\PotensiDesa;
use App\Models\RefKategoriPotensi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PotensiController extends Controller
{
    public function index(Request $request): View
    {
        $kategoriId = $request->input('kategori');

        return view('publik.potensi.index', [
            'daftar' => PotensiDesa::query()
                ->tampil()
                ->when($kategoriId, fn ($q) => $q->where('kategori_potensi_id', $kategoriId))
                ->urut()
                ->paginate(12)
                ->withQueryString(),
            'kategoriId' => $kategoriId,
            'kategoriFilter' => RefKategoriPotensi::urut()->get(),
            'jumlahPerKategori' => RefKategoriPotensi::urut()
                ->withCount(['potensi' => fn ($q) => $q->tampil()])
                ->get()
                ->pluck('potensi_count', 'nama')
                ->sortByDesc(fn ($v) => $v)
                ->take(6),
        ]);
    }

    public function show(PotensiDesa $potensi): View
    {
        // Potensi yang disembunyikan tidak boleh terbuka meski alamatnya
        // ditebak atau tersisa di riwayat peramban.
        abort_unless($potensi->tampil, 404);

        return view('publik.potensi.detail', [
            'potensi' => $potensi,
            'lainnya' => PotensiDesa::query()
                ->tampil()
                ->whereKeyNot($potensi->id)
                ->where('kategori_potensi_id', $potensi->kategori_potensi_id)
                ->urut()
                ->take(3)
                ->get(),
        ]);
    }
}
