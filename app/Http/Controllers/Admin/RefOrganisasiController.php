<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefOrganisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Fitur 08 — kelola kelompok organisasi pengurus desa.
 *
 * Halaman tersendiri, sejajar dengan Wilayah dan Pekerjaan, karena ketiganya
 * sama-sama data referensi: isinya menentukan pilihan yang tersedia di
 * formulir lain, dan urutannya menentukan susunan halaman publik.
 */
class RefOrganisasiController extends Controller
{
    public function index(): View
    {
        return view('admin.organisasi.index', [
            'daftar' => RefOrganisasi::urut()->withCount('pengurus')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->simpan(new RefOrganisasi(), $request, 'Organisasi ditambahkan.');
    }

    public function update(Request $request, RefOrganisasi $organisasi): RedirectResponse
    {
        return $this->simpan($organisasi, $request, "Organisasi \"{$organisasi->nama}\" diperbarui.");
    }

    public function destroy(RefOrganisasi $organisasi): RedirectResponse
    {
        // Menghapus organisasi yang masih dipakai akan membuat pengurusnya
        // kehilangan kelompok dan hilang dari halaman publik tanpa jejak.
        if ($organisasi->sedangDipakai()) {
            return back()->with('galat', sprintf(
                'Organisasi "%s" tidak dapat dihapus karena masih dipakai %d pengurus. '
                .'Pindahkan pengurus tersebut ke organisasi lain lebih dulu.',
                $organisasi->nama,
                $organisasi->jumlahPemakai(),
            ));
        }

        $nama = $organisasi->nama;
        $organisasi->delete();

        return back()->with('status', "Organisasi \"{$nama}\" dihapus.");
    }

    /**
     * Menggeser urutan satu langkah. Urutan inilah yang menentukan kelompok
     * mana tampil lebih dulu di halaman Struktur Organisasi publik.
     */
    public function geser(Request $request, RefOrganisasi $organisasi): RedirectResponse
    {
        $naik = $request->input('arah') === 'naik';

        $tetangga = RefOrganisasi::query()
            ->when($naik,
                fn ($q) => $q->where('urutan', '<', $organisasi->urutan)->orderByDesc('urutan'),
                fn ($q) => $q->where('urutan', '>', $organisasi->urutan)->orderBy('urutan'),
            )
            ->first();

        if (! $tetangga) {
            // Sudah di ujung. Bukan kesalahan, jadi tidak perlu pesan galat.
            return back();
        }

        $urutanAsal = $organisasi->urutan;
        $organisasi->update(['urutan' => $tetangga->urutan]);
        $tetangga->update(['urutan' => $urutanAsal]);

        return back();
    }

    private function simpan(RefOrganisasi $organisasi, Request $request, string $pesan): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:60'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [], ['nama' => 'nama organisasi', 'urutan' => 'urutan tampil']);

        $nama = trim($data['nama']);

        // Perbandingan tanpa memandang besar-kecil huruf: "Karang Taruna" dan
        // "karang taruna" akan menjadi dua kelompok terpisah di halaman
        // publik untuk organisasi yang sama.
        $bentrok = RefOrganisasi::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])
            ->when($organisasi->exists, fn ($q) => $q->whereKeyNot($organisasi->getKey()))
            ->exists();

        if ($bentrok) {
            throw ValidationException::withMessages([
                'nama' => "Organisasi \"{$nama}\" sudah terdaftar.",
            ]);
        }

        // Organisasi baru diletakkan di urutan terakhir bila admin tidak
        // menentukannya sendiri, supaya tidak diam-diam menyalip kelompok
        // yang sudah tersusun rapi.
        $urutan = $data['urutan']
            ?? ($organisasi->exists ? $organisasi->urutan : (int) RefOrganisasi::max('urutan') + 1);

        $organisasi->fill(['nama' => $nama, 'urutan' => $urutan])->save();

        return back()->with('status', $pesan);
    }
}
