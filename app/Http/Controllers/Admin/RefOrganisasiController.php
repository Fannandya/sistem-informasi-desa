<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefOrganisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RefOrganisasiController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        return $this->simpan(new RefOrganisasi(), $request, 'Organisasi ditambahkan.');
    }

    public function update(Request $request, RefOrganisasi $organisasi): RedirectResponse
    {
        return $this->simpan($organisasi, $request, 'Organisasi diperbarui.');
    }

    public function destroy(RefOrganisasi $organisasi): RedirectResponse
    {
        if ($organisasi->sedangDipakai()) {
            return back()->with('galat', sprintf(
                'Organisasi "%s" tidak dapat dihapus karena masih dipakai %d pengurus.',
                $organisasi->nama,
                $organisasi->jumlahPemakai(),
            ));
        }

        $nama = $organisasi->nama;
        $organisasi->delete();

        return back()->with('status', "Organisasi \"{$nama}\" dihapus.");
    }

    private function simpan(RefOrganisasi $organisasi, Request $request, string $pesan): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:60'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [], ['nama' => 'nama organisasi']);

        $nama = trim($data['nama']);

        $bentrok = RefOrganisasi::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])
            ->when($organisasi->exists, fn ($q) => $q->whereKeyNot($organisasi->getKey()))
            ->exists();

        if ($bentrok) {
            throw ValidationException::withMessages([
                'nama' => "Organisasi \"{$nama}\" sudah terdaftar.",
            ]);
        }

        $organisasi->fill(['nama' => $nama, 'urutan' => $data['urutan'] ?? 0])->save();

        return back()->with('status', $pesan);
    }
}
