<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UnggahanGagal;
use App\Http\Controllers\Admin\Concerns\MengelolaGambar;
use App\Http\Controllers\Controller;
use App\Models\PotensiDesa;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PotensiController extends Controller
{
    use MengelolaGambar;

    public function index(Request $request): View
    {
        $kategoriId = $request->input('kategori');

        return view('admin.potensi.index', [
            'daftar' => PotensiDesa::query()
                ->when($kategoriId, fn ($q) => $q->where('kategori_potensi_id', $kategoriId))
                ->urut()
                ->paginate(25)
                ->withQueryString(),
            'kategoriId' => $kategoriId,
        ]);
    }

    public function create(): View
    {
        return view('admin.potensi.form', ['potensi' => new PotensiDesa(['tampil' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->simpan($request, new PotensiDesa(), 'Potensi desa ditambahkan.');
    }

    public function edit(PotensiDesa $potensi): View
    {
        return view('admin.potensi.form', ['potensi' => $potensi]);
    }

    public function update(Request $request, PotensiDesa $potensi): RedirectResponse
    {
        return $this->simpan($request, $potensi, "Potensi \"{$potensi->nama}\" diperbarui.");
    }

    public function destroy(PotensiDesa $potensi): RedirectResponse
    {
        $nama = $potensi->nama;
        $potensi->delete();

        return redirect()->route('admin.potensi.index')->with('status', "Potensi \"{$nama}\" dihapus.");
    }

    public function ubahTampil(PotensiDesa $potensi): RedirectResponse
    {
        $potensi->update(['tampil' => ! $potensi->tampil]);

        return back()->with('status', sprintf(
            '"%s" %s di halaman publik.',
            $potensi->nama,
            $potensi->tampil ? 'ditampilkan' : 'disembunyikan',
        ));
    }

    private function simpan(Request $request, PotensiDesa $potensi, string $pesan): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'kategori_potensi_id' => ['required', 'exists:ref_kategori_potensi,id'],
            'deskripsi_singkat' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:20000'],
            'kontak' => ['nullable', 'string', 'max:120'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
            'tampil' => ['nullable', 'boolean'],
            'foto' => LayananUnggahGambar::aturanValidasi(),
            'hapus_foto' => ['nullable', 'boolean'],
            // Slug hanya berubah bila admin menyuntingnya sendiri (PRD 6.17).
            'slug' => [
                'nullable', 'string', 'max:140', 'alpha_dash',
                Rule::unique('potensi_desa', 'slug')->ignore($potensi->id),
            ],
        ], [
            'slug.alpha_dash' => 'Alamat URL hanya boleh berisi huruf, angka, dan tanda hubung.',
            'slug.unique' => 'Alamat URL itu sudah dipakai potensi lain.',
        ], [
            'deskripsi_singkat' => 'deskripsi singkat',
        ]);

        try {
            $data += $this->prosesGambar($request, $potensi, 'potensi');
        } catch (UnggahanGagal $e) {
            return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
        }

        $data['tampil'] = $request->boolean('tampil');
        $data['urutan'] = $data['urutan'] ?? 0;

        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        $potensi->fill($data)->save();

        return redirect()->route('admin.potensi.index')->with('status', $pesan);
    }
}
