<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UnggahanGagal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PengurusRequest;
use App\Models\Pengurus;
use App\Models\RefOrganisasi;
use App\Models\Wilayah;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengurusController extends Controller
{
    public function __construct(private readonly LayananUnggahGambar $unggahan) {}

    public function index(Request $request): View
    {
        $organisasiId = $request->input('organisasi_id');

        $daftar = Pengurus::query()
            ->with(['organisasi', 'wilayah'])
            ->when($organisasiId, fn ($q) => $q->where('organisasi_id', $organisasiId))
            ->join('ref_organisasi', 'pengurus_desa.organisasi_id', '=', 'ref_organisasi.id')
            ->orderBy('ref_organisasi.urutan')
            ->orderBy('ref_organisasi.nama')
            ->orderBy('pengurus_desa.urutan')
            ->orderBy('pengurus_desa.nama')
            ->select('pengurus_desa.*')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pengurus.index', [
            'daftar' => $daftar,
            'organisasiList' => RefOrganisasi::urut()->withCount('pengurus')->get(),
            'organisasiId' => $organisasiId,
        ]);
    }

    public function create(): View
    {
        return view('admin.pengurus.form', $this->dataForm(new Pengurus(['tampil' => true])));
    }

    public function store(PengurusRequest $request): RedirectResponse
    {
        $pengurus = new Pengurus();

        return $this->simpan($request, $pengurus, "Pengurus {$request->input('nama')} ditambahkan.");
    }

    public function edit(Pengurus $pengurus): View
    {
        return view('admin.pengurus.form', $this->dataForm($pengurus));
    }

    public function update(PengurusRequest $request, Pengurus $pengurus): RedirectResponse
    {
        return $this->simpan($request, $pengurus, "Data {$pengurus->nama} diperbarui.");
    }

    public function destroy(Pengurus $pengurus): RedirectResponse
    {
        // Hard delete sesuai PRD 6.16; foto ikut terhapus lewat trait
        // PunyaGambar pada model.
        $nama = $pengurus->nama;
        $pengurus->delete();

        return redirect()
            ->route('admin.pengurus.index')
            ->with('status', "Pengurus {$nama} dihapus.");
    }

    /**
     * Mengubah status tampil langsung dari daftar, tanpa membuka formulir.
     */
    public function ubahTampil(Pengurus $pengurus): RedirectResponse
    {
        $pengurus->update(['tampil' => ! $pengurus->tampil]);

        return back()->with('status', sprintf(
            '%s %s di halaman publik.',
            $pengurus->nama,
            $pengurus->tampil ? 'ditampilkan' : 'disembunyikan',
        ));
    }

    /**
     * Menggeser urutan satu langkah di dalam organisasinya.
     */
    public function geser(Request $request, Pengurus $pengurus): RedirectResponse
    {
        $naik = $request->input('arah') === 'naik';

        $tetangga = Pengurus::where('organisasi_id', $pengurus->organisasi_id)
            ->when($naik,
                fn ($q) => $q->where('urutan', '<', $pengurus->urutan)->orderByDesc('urutan'),
                fn ($q) => $q->where('urutan', '>', $pengurus->urutan)->orderBy('urutan'),
            )
            ->first();

        if (! $tetangga) {
            // Sudah di ujung. Bukan kesalahan, jadi tidak perlu pesan galat.
            return back();
        }

        $urutanAsal = $pengurus->urutan;
        $pengurus->update(['urutan' => $tetangga->urutan]);
        $tetangga->update(['urutan' => $urutanAsal]);

        return back();
    }

    private function simpan(PengurusRequest $request, Pengurus $pengurus, string $pesan): RedirectResponse
    {
        $data = $request->dataBersih();

        try {
            if ($request->hasFile('foto')) {
                $data['foto'] = $this->unggahan->ganti($pengurus->foto, $request->file('foto'), 'pengurus');
            } elseif ($request->boolean('hapus_foto')) {
                $this->unggahan->hapus($pengurus->foto);
                $data['foto'] = null;
            }
        } catch (UnggahanGagal $e) {
            return back()->withInput()->withErrors(['foto' => $e->getMessage()]);
        }

        $pengurus->fill($data)->save();

        return redirect()->route('admin.pengurus.index')->with('status', $pesan);
    }

    /**
     * @return array<string, mixed>
     */
    private function dataForm(Pengurus $pengurus): array
    {
        return [
            'pengurus' => $pengurus,
            'organisasiList' => RefOrganisasi::urut()->get(),
            'wilayahList' => Wilayah::urut()->get(),
        ];
    }
}
