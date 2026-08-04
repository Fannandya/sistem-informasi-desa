<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UnggahanGagal;
use App\Http\Controllers\Admin\Concerns\MengelolaGambar;
use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use App\Models\Wilayah;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FasilitasController extends Controller
{
    use MengelolaGambar;

    public function index(Request $request): View
    {
        $kategoriId = $request->input('kategori');

        return view('admin.fasilitas.index', [
            'daftar' => FasilitasUmum::query()
                ->with('wilayah')
                ->when($kategoriId, fn ($q) => $q->where('kategori_fasilitas_id', $kategoriId))
                ->when($request->input('q'), fn ($q, $kata) => $q->where('nama', 'like', "%{$kata}%"))
                ->orderBy('kategori_fasilitas_id')
                ->orderBy('nama')
                ->paginate(25)
                ->withQueryString(),
            'kategoriId' => $kategoriId,
            'kata' => $request->input('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.fasilitas.form', $this->dataForm(new FasilitasUmum(['tampil' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->simpan($request, new FasilitasUmum(), 'Fasilitas ditambahkan.');
    }

    public function edit(FasilitasUmum $fasilita): View
    {
        return view('admin.fasilitas.form', $this->dataForm($fasilita));
    }

    public function update(Request $request, FasilitasUmum $fasilita): RedirectResponse
    {
        return $this->simpan($request, $fasilita, "Fasilitas \"{$fasilita->nama}\" diperbarui.");
    }

    public function destroy(FasilitasUmum $fasilita): RedirectResponse
    {
        $nama = $fasilita->nama;
        $fasilita->delete();

        return redirect()->route('admin.fasilitas.index')->with('status', "Fasilitas \"{$nama}\" dihapus.");
    }

    public function ubahTampil(FasilitasUmum $fasilita): RedirectResponse
    {
        $fasilita->update(['tampil' => ! $fasilita->tampil]);

        return back()->with('status', sprintf(
            '"%s" %s di halaman publik.',
            $fasilita->nama,
            $fasilita->tampil ? 'ditampilkan' : 'disembunyikan',
        ));
    }

    private function simpan(Request $request, FasilitasUmum $fasilitas, string $pesan): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'kategori_fasilitas_id' => ['required', 'exists:ref_kategori_fasilitas,id'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'wilayah_id' => ['nullable', 'exists:wilayah,id'],
            'tampil' => ['nullable', 'boolean'],
            'foto' => LayananUnggahGambar::aturanValidasi(),
            'hapus_foto' => ['nullable', 'boolean'],
        ], [], ['wilayah_id' => 'wilayah']);

        try {
            $data += $this->prosesGambar($request, $fasilitas, 'fasilitas');
        } catch (UnggahanGagal $e) {
            return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
        }

        $data['tampil'] = $request->boolean('tampil');

        $fasilitas->fill($data)->save();

        return redirect()->route('admin.fasilitas.index')->with('status', $pesan);
    }

    /**
     * @return array<string, mixed>
     */
    private function dataForm(FasilitasUmum $fasilitas): array
    {
        return [
            'fasilitas' => $fasilitas,
            'wilayahList' => Wilayah::urut()->get(),
        ];
    }
}
