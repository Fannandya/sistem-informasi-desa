<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefAgama;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefAgamaController extends Controller
{
    public function index(): View
    {
        return view('admin.agama.index', [
            'daftar' => RefAgama::orderBy('nama')
                ->withCount(['penduduk' => fn ($q) => $q->withTrashed()])
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->simpan(new RefAgama(), $request, 'Agama berhasil ditambahkan.');
    }

    public function update(Request $request, RefAgama $agama): RedirectResponse
    {
        return $this->simpan($agama, $request, 'Agama berhasil diperbarui.');
    }

    public function destroy(RefAgama $agama): RedirectResponse
    {
        if ($agama->is_template) {
            return redirect(route('admin.agama.index'))
                ->withErrors(['nama' => 'Agama bawaan tidak dapat dihapus.']);
        }

        if ($agama->sedangDipakai()) {
            return redirect(route('admin.agama.index'))
                ->withErrors(['nama' => sprintf(
                    'Agama "%s" tidak dapat dihapus karena masih dipakai oleh %d data warga.',
                    $agama->nama,
                    $agama->jumlahPemakai(),
                )]);
        }

        $nama = $agama->nama;
        $agama->delete();

        return redirect(route('admin.agama.index'))
            ->with('status', "Agama \"{$nama}\" dihapus.");
    }

    private function simpan(RefAgama $agama, Request $request, string $pesan): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:60'],
        ], [], ['nama' => 'nama agama']);

        $nama = trim($data['nama']);

        $agama->fill(['nama' => $nama])->save();

        return back()->with('status', $pesan);
    }
}
