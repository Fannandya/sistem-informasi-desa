<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TingkatPrestasi;
use App\Exceptions\UnggahanGagal;
use App\Http\Controllers\Admin\Concerns\MengelolaGambar;
use App\Http\Controllers\Controller;
use App\Models\PrestasiDesa;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PrestasiController extends Controller
{
    use MengelolaGambar;

    public function index(Request $request): View
    {
        $tingkat = TingkatPrestasi::tryFrom((string) $request->input('tingkat'));

        return view('admin.prestasi.index', [
            'daftar' => PrestasiDesa::query()
                ->when($tingkat, fn ($q) => $q->where('tingkat', $tingkat))
                ->terbaru()
                ->paginate(25)
                ->withQueryString(),
            'tingkat' => $tingkat,
        ]);
    }

    public function create(): View
    {
        return view('admin.prestasi.form', ['prestasi' => new PrestasiDesa(['tampil' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->simpan($request, new PrestasiDesa(), 'Prestasi ditambahkan.');
    }

    public function edit(PrestasiDesa $prestasi): View
    {
        return view('admin.prestasi.form', ['prestasi' => $prestasi]);
    }

    public function update(Request $request, PrestasiDesa $prestasi): RedirectResponse
    {
        return $this->simpan($request, $prestasi, "Prestasi \"{$prestasi->judul}\" diperbarui.");
    }

    public function destroy(PrestasiDesa $prestasi): RedirectResponse
    {
        $judul = $prestasi->judul;
        $prestasi->delete();

        return redirect()->route('admin.prestasi.index')->with('status', "Prestasi \"{$judul}\" dihapus.");
    }

    public function ubahTampil(PrestasiDesa $prestasi): RedirectResponse
    {
        $prestasi->update(['tampil' => ! $prestasi->tampil]);

        return back()->with('status', sprintf(
            '"%s" %s di halaman publik.',
            $prestasi->judul,
            $prestasi->tampil ? 'ditampilkan' : 'disembunyikan',
        ));
    }

    private function simpan(Request $request, PrestasiDesa $prestasi, string $pesan): RedirectResponse
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            // Prestasi yang belum diraih tidak masuk akal untuk dicatat.
            'tahun' => ['required', 'integer', 'min:1900', 'max:'.now()->year],
            'tingkat' => ['nullable', Rule::enum(TingkatPrestasi::class)],
            'penyelenggara' => ['nullable', 'string', 'max:120'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'tampil' => ['nullable', 'boolean'],
            'foto' => LayananUnggahGambar::aturanValidasi(),
            'hapus_foto' => ['nullable', 'boolean'],
        ], [
            'tahun.max' => 'Tahun tidak boleh melebihi tahun ini.',
        ]);

        try {
            $data += $this->prosesGambar($request, $prestasi, 'prestasi');
        } catch (UnggahanGagal $e) {
            return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
        }

        $data['tampil'] = $request->boolean('tampil');

        $prestasi->fill($data)->save();

        return redirect()->route('admin.prestasi.index')->with('status', $pesan);
    }
}
