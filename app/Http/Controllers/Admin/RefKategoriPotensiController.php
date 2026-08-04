<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefKategoriPotensi;
use Illuminate\Http\Request;

class RefKategoriPotensiController extends Controller
{
    public function index()
    {
        $daftar = RefKategoriPotensi::urut()->get();

        return view('admin.ref-kategori-potensi.index', compact('daftar'));
    }

    public function store(Request $request)
    {
        $validasi = $request->validate([
            'nama' => 'required|string|max:255|unique:ref_kategori_potensi,nama',
        ]);

        RefKategoriPotensi::create([
            'nama' => $validasi['nama'],
            'urutan' => RefKategoriPotensi::max('urutan') + 1 ?? 0,
        ]);

        return redirect(route('admin.ref-kategori-potensi.index'))
            ->with('status', 'Kategori potensi ditambahkan.');
    }

    public function update(Request $request, RefKategoriPotensi $refKategoriPotensi)
    {
        $validasi = $request->validate([
            'nama' => 'required|string|max:255|unique:ref_kategori_potensi,nama,' . $refKategoriPotensi->id,
        ]);

        $refKategoriPotensi->update($validasi);

        return redirect(route('admin.ref-kategori-potensi.index'))
            ->with('status', 'Kategori potensi diperbarui.');
    }

    public function destroy(RefKategoriPotensi $refKategoriPotensi)
    {
        if ($refKategoriPotensi->potensi()->exists()) {
            return redirect(route('admin.ref-kategori-potensi.index'))
                ->withErrors(['nama' => 'Kategori masih dipakai oleh potensi. Hapus potensi terlebih dahulu.']);
        }

        $refKategoriPotensi->delete();

        return redirect(route('admin.ref-kategori-potensi.index'))
            ->with('status', 'Kategori potensi dihapus.');
    }

    public function geser(Request $request, RefKategoriPotensi $refKategoriPotensi)
    {
        $arah = $request->input('arah');
        $lain = null;

        if ($arah === 'atas') {
            $lain = RefKategoriPotensi::where('urutan', '<', $refKategoriPotensi->urutan)
                ->orderByDesc('urutan')
                ->first();
        } else {
            $lain = RefKategoriPotensi::where('urutan', '>', $refKategoriPotensi->urutan)
                ->orderBy('urutan')
                ->first();
        }

        if ($lain) {
            [$refKategoriPotensi->urutan, $lain->urutan] = [$lain->urutan, $refKategoriPotensi->urutan];
            $refKategoriPotensi->save();
            $lain->save();
        }

        return redirect(route('admin.ref-kategori-potensi.index'))
            ->with('status', 'Urutan kategori diperbarui.');
    }
}
