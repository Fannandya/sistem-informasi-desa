<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\BeritaDesa;
use App\Models\KategoriBerita;
use Illuminate\View\View;

class BeritaController extends Controller
{
    public function index(?KategoriBerita $kategori = null): View
    {
        $query = BeritaDesa::query()
            ->terbit()
            ->with('kategori')
            ->when($kategori?->exists, fn ($q) => $q->where('kategori_id', $kategori->id));

        // Berita utama hanya disorot pada daftar tanpa saringan; di halaman
        // kategori ia belum tentu relevan.
        $utama = $kategori?->exists
            ? null
            : BeritaDesa::query()->terbit()->where('utama', true)->with('kategori')->first();

        return view('publik.berita.index', [
            'utama' => $utama,
            'daftar' => $query
                ->when($utama, fn ($q) => $q->whereKeyNot($utama->id))
                ->terbaru()
                ->paginate(9)
                ->withQueryString(),
            'kategori' => $kategori?->exists ? $kategori : null,
            // whereHas, bukan having() atas subquery count: HAVING pada
            // kueri non-agregat ditolak SQLite, sementara pengujian berjalan
            // di SQLite dan produksi di MySQL.
            'kategoriList' => KategoriBerita::query()
                ->whereHas('berita', fn ($q) => $q->where('status', 'terbit'))
                ->withCount(['berita' => fn ($q) => $q->where('status', 'terbit')])
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function show(BeritaDesa $berita): View
    {
        // Draf tidak boleh terbuka meski alamatnya ditebak.
        abort_unless($berita->sudahTerbit(), 404);

        $berita->incrementQuietly('dilihat');

        return view('publik.berita.baca', [
            'berita' => $berita,
            'lainnya' => BeritaDesa::query()
                ->terbit()
                ->whereKeyNot($berita->id)
                ->with('kategori')
                ->terbaru()
                ->take(3)
                ->get(),
        ]);
    }
}
