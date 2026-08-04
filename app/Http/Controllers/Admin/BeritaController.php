<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UnggahanGagal;
use App\Http\Controllers\Admin\Concerns\MengelolaGambar;
use App\Http\Controllers\Controller;
use App\Models\BeritaDesa;
use App\Models\KategoriBerita;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;

class BeritaController extends Controller
{
    use MengelolaGambar;

    public function index(Request $request): View
    {
        return view('admin.berita.index', [
            'daftar' => BeritaDesa::query()
                ->with('kategori')
                ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
                ->when($request->input('kategori_id'), fn ($q, $k) => $q->where('kategori_id', $k))
                ->when($request->input('q'), fn ($q, $kata) => $q->where('judul', 'like', "%{$kata}%"))
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),
            'kategoriList' => KategoriBerita::orderBy('nama')->withCount('berita')->get(),
            'status' => $request->input('status'),
            'kategoriId' => $request->input('kategori_id'),
            'kata' => $request->input('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.berita.form', $this->dataForm(new BeritaDesa(['status' => 'draf'])));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->simpan($request, new BeritaDesa(), 'Berita disimpan.');
    }

    public function edit(BeritaDesa $berita): View
    {
        return view('admin.berita.form', $this->dataForm($berita));
    }

    public function update(Request $request, BeritaDesa $berita): RedirectResponse
    {
        return $this->simpan($request, $berita, "Berita \"{$berita->judul}\" diperbarui.");
    }

    public function destroy(BeritaDesa $berita): RedirectResponse
    {
        $judul = $berita->judul;
        $berita->delete();

        return redirect()->route('admin.berita.index')->with('status', "Berita \"{$judul}\" dihapus.");
    }

    public function ubahStatus(BeritaDesa $berita): RedirectResponse
    {
        $terbit = ! $berita->sudahTerbit();

        $berita->update([
            'status' => $terbit ? 'terbit' : 'draf',
            // Waktu terbit dicatat saat pertama kali diterbitkan, lalu
            // dipertahankan agar urutan daftar tidak berubah bila berita
            // sempat dikembalikan ke draf lalu diterbitkan lagi.
            'tanggal_terbit' => $terbit ? ($berita->tanggal_terbit ?? now()) : $berita->tanggal_terbit,
        ]);

        return back()->with('status', sprintf(
            '"%s" %s.',
            $berita->judul,
            $terbit ? 'diterbitkan' : 'dikembalikan ke draf',
        ));
    }

    /**
     * Menandai satu berita sebagai berita utama. Penanda lama dilepas dalam
     * transaksi yang sama, supaya tidak pernah ada dua berita utama.
     */
    public function jadikanUtama(BeritaDesa $berita): RedirectResponse
    {
        DB::transaction(function () use ($berita): void {
            BeritaDesa::where('utama', true)->whereKeyNot($berita->id)->update(['utama' => false]);
            $berita->update(['utama' => ! $berita->utama]);
        });

        return back()->with('status', $berita->utama
            ? "\"{$berita->judul}\" dijadikan berita utama."
            : 'Penanda berita utama dilepas.');
    }

    private function simpan(Request $request, BeritaDesa $berita, string $pesan): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:180'],
            'kategori_id' => ['nullable', 'exists:kategori_berita,id'],
            'ringkasan' => ['nullable', 'string', 'max:300'],
            'isi' => ['required', 'string', 'max:200000'],
            'status' => ['required', Rule::in(['draf', 'terbit'])],
            'gambar_sampul' => LayananUnggahGambar::aturanValidasi(),
            'hapus_gambar_sampul' => ['nullable', 'boolean'],
            'slug' => [
                'nullable', 'string', 'max:200', 'alpha_dash',
                Rule::unique('berita_desa', 'slug')->ignore($berita->id),
            ],
        ], [
            'isi.required' => 'Isi berita tidak boleh kosong.',
            'slug.alpha_dash' => 'Alamat URL hanya boleh berisi huruf, angka, dan tanda hubung.',
            'slug.unique' => 'Alamat URL itu sudah dipakai berita lain.',
        ], ['kategori_id' => 'kategori']);

        // Keluaran editor teks kaya dibersihkan sebelum disimpan. Tanpa ini,
        // satu akun admin yang jebol cukup untuk menanam skrip di setiap
        // halaman baca berita.
        $data['isi'] = Purifier::clean($data['isi'], 'berita');

        try {
            $data += $this->prosesGambar($request, $berita, 'berita', 'gambar_sampul');
        } catch (UnggahanGagal $e) {
            return back()->withInput()->withErrors(['gambar_sampul' => $e->getMessage()]);
        }

        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        if ($data['status'] === 'terbit' && $berita->tanggal_terbit === null) {
            $data['tanggal_terbit'] = now();
        }

        $data['admin_id'] = $berita->admin_id ?? $request->user('admin')->id;

        $berita->fill($data)->save();

        return redirect()->route('admin.berita.index')->with('status', $pesan);
    }

    /**
     * @return array<string, mixed>
     */
    private function dataForm(BeritaDesa $berita): array
    {
        return [
            'berita' => $berita,
            'kategoriList' => KategoriBerita::orderBy('nama')->get(),
        ];
    }
}
