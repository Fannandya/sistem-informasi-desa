<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefKategoriFasilitas;
use Illuminate\Http\Request;

class RefKategoriFasilitasController extends Controller
{
    public function index()
    {
        $daftar = RefKategoriFasilitas::urut()->get();

        return view('admin.ref-kategori-fasilitas.index', compact('daftar'));
    }

    public function store(Request $request)
    {
        $validasi = $request->validate([
            'nama' => 'required|string|max:255|unique:ref_kategori_fasilitas,nama',
        ]);

        RefKategoriFasilitas::create([
            'nama' => $validasi['nama'],
            'urutan' => RefKategoriFasilitas::max('urutan') + 1 ?? 0,
        ]);

        return redirect(route('admin.ref-kategori-fasilitas.index'))
            ->with('status', 'Kategori fasilitas ditambahkan.');
    }

    public function update(Request $request, RefKategoriFasilitas $refKategoriFasilitas)
    {
        $validasi = $request->validate([
            'nama' => 'required|string|max:255|unique:ref_kategori_fasilitas,nama,' . $refKategoriFasilitas->id,
        ]);

        $refKategoriFasilitas->update($validasi);

        return redirect(route('admin.ref-kategori-fasilitas.index'))
            ->with('status', 'Kategori fasilitas diperbarui.');
    }

    public function destroy(RefKategoriFasilitas $refKategoriFasilitas)
    {
        if ($refKategoriFasilitas->fasilitas()->exists()) {
            return redirect(route('admin.ref-kategori-fasilitas.index'))
                ->withErrors(['nama' => 'Kategori masih dipakai oleh fasilitas. Hapus fasilitas terlebih dahulu.']);
        }

        $refKategoriFasilitas->delete();

        return redirect(route('admin.ref-kategori-fasilitas.index'))
            ->with('status', 'Kategori fasilitas dihapus.');
    }

    public function geser(Request $request, RefKategoriFasilitas $refKategoriFasilitas)
    {
        $arah = $request->input('arah');
        $lain = null;

        if ($arah === 'atas') {
            $lain = RefKategoriFasilitas::where('urutan', '<', $refKategoriFasilitas->urutan)
                ->orderByDesc('urutan')
                ->first();
        } else {
            $lain = RefKategoriFasilitas::where('urutan', '>', $refKategoriFasilitas->urutan)
                ->orderBy('urutan')
                ->first();
        }

        if ($lain) {
            [$refKategoriFasilitas->urutan, $lain->urutan] = [$lain->urutan, $refKategoriFasilitas->urutan];
            $refKategoriFasilitas->save();
            $lain->save();
        }

        return redirect(route('admin.ref-kategori-fasilitas.index'))
            ->with('status', 'Urutan kategori diperbarui.');
    }
}
