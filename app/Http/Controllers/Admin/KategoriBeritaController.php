<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriBerita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KategoriBeritaController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $nama = trim($request->validate([
            'nama' => ['required', 'string', 'max:60'],
        ], [], ['nama' => 'nama kategori'])['nama']);

        if (KategoriBerita::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])->exists()) {
            throw ValidationException::withMessages(['nama' => "Kategori \"{$nama}\" sudah ada."]);
        }

        KategoriBerita::create(['nama' => $nama]);

        return back()->with('status', "Kategori \"{$nama}\" ditambahkan.");
    }

    public function destroy(KategoriBerita $kategori): RedirectResponse
    {
        if ($kategori->sedangDipakai()) {
            return back()->with('galat', sprintf(
                'Kategori "%s" tidak dapat dihapus karena masih dipakai %d berita.',
                $kategori->nama,
                $kategori->jumlahPemakai(),
            ));
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return back()->with('status', "Kategori \"{$nama}\" dihapus.");
    }
}
