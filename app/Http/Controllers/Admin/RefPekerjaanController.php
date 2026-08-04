<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefPekerjaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RefPekerjaanController extends Controller
{
    public function index(): View
    {
        return view('admin.pekerjaan.index', [
            'daftar' => RefPekerjaan::orderBy('nama')
                ->withCount(['penduduk' => fn ($q) => $q->withTrashed()])
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->simpan(new RefPekerjaan(), $request, 'Pekerjaan berhasil ditambahkan.');
    }

    public function update(Request $request, RefPekerjaan $pekerjaan): RedirectResponse
    {
        return $this->simpan($pekerjaan, $request, 'Pekerjaan berhasil diperbarui.');
    }

    public function destroy(RefPekerjaan $pekerjaan): RedirectResponse
    {
        if ($pekerjaan->is_template) {
            return redirect(route('admin.pekerjaan.index'))
                ->withErrors(['nama' => 'Pekerjaan template tidak dapat dihapus.']);
        }

        if ($pekerjaan->sedangDipakai()) {
            return redirect(route('admin.pekerjaan.index'))
                ->withErrors(['nama' => sprintf(
                    'Pekerjaan "%s" tidak dapat dihapus karena masih dipakai oleh %d data warga.',
                    $pekerjaan->nama,
                    $pekerjaan->jumlahPemakai(),
                )]);
        }

        $nama = $pekerjaan->nama;
        $pekerjaan->delete();

        return redirect(route('admin.pekerjaan.index'))
            ->with('status', "Pekerjaan \"{$nama}\" dihapus.");
    }

    private function simpan(RefPekerjaan $pekerjaan, Request $request, string $pesan): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:60'],
        ], [], ['nama' => 'nama pekerjaan']);

        $nama = trim($data['nama']);

        // Perbandingan tanpa memandang besar-kecil huruf: "Petani" dan
        // "petani" akan memecah grafik mata pencaharian jadi dua batang.
        $bentrok = RefPekerjaan::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])
            ->when($pekerjaan->exists, fn ($q) => $q->whereKeyNot($pekerjaan->getKey()))
            ->exists();

        if ($bentrok) {
            throw ValidationException::withMessages([
                'nama' => "Pekerjaan \"{$nama}\" sudah terdaftar.",
            ]);
        }

        $pekerjaan->fill(['nama' => $nama])->save();

        return back()->with('status', $pesan);
    }
}
