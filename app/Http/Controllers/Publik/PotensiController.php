<?php

namespace App\Http\Controllers\Publik;

use App\Enums\KategoriPotensi;
use App\Http\Controllers\Controller;
use App\Models\PotensiDesa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PotensiController extends Controller
{
    public function index(Request $request): View
    {
        $kategori = KategoriPotensi::tryFrom((string) $request->input('kategori'));

        return view('publik.potensi.index', [
            'daftar' => PotensiDesa::query()
                ->tampil()
                ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
                ->urut()
                ->paginate(12)
                ->withQueryString(),
            'kategori' => $kategori,
            'jumlahPerKategori' => PotensiDesa::query()
                ->tampil()
                ->selectRaw('kategori, COUNT(*) as total')
                ->groupBy('kategori')
                ->pluck('total', 'kategori'),
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
                ->where('kategori', $potensi->kategori)
                ->urut()
                ->take(3)
                ->get(),
        ]);
    }
}
