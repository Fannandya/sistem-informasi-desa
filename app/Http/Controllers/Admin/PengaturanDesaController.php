<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\UnggahanGagal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePengaturanDesaRequest;
use App\Models\PengaturanDesa;
use App\Services\LayananUnggahGambar;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PengaturanDesaController extends Controller
{
    public function __construct(private readonly LayananUnggahGambar $unggahan) {}

    public function tampilkan(): View
    {
        return view('admin.pengaturan.index', [
            'pengaturan' => PengaturanDesa::ambil(),
        ]);
    }

    public function perbarui(UpdatePengaturanDesaRequest $request, string $bagian): RedirectResponse
    {
        abort_unless(in_array($bagian, UpdatePengaturanDesaRequest::BAGIAN, true), 404);

        $pengaturan = PengaturanDesa::ambil();
        $data = $request->dataBersih();

        if ($bagian === 'identitas') {
            try {
                $data += $this->prosesGambar($request, $pengaturan);
            } catch (UnggahanGagal $e) {
                // Gambar gagal disimpan berarti tidak ada yang tersimpan sama
                // sekali — data teks tidak boleh masuk setengah jalan.
                return back()
                    ->withInput()
                    ->withErrors(['logo' => $e->getMessage()]);
            }
        }

        if ($bagian === 'kontak') {
            $data['medsos'] = collect($request->input('medsos', []))
                ->map(fn ($nilai) => filled($nilai) ? trim($nilai) : null)
                ->filter()
                ->all();
        }

        $pengaturan->fill($data)->save();

        return redirect()
            ->route('admin.pengaturan', ['bagian' => $bagian])
            ->with('status', 'Pengaturan desa berhasil disimpan.');
    }

    /**
     * Menangani unggah, penggantian, dan penghapusan logo serta foto sampul.
     *
     * @return array<string, string|null>
     *
     * @throws UnggahanGagal
     */
    private function prosesGambar(UpdatePengaturanDesaRequest $request, PengaturanDesa $pengaturan): array
    {
        $hasil = [];

        foreach (['logo' => 'logo', 'foto_sampul' => 'sampul'] as $kolom => $jenis) {
            if ($request->hasFile($kolom)) {
                $hasil[$kolom] = $this->unggahan->ganti(
                    $pengaturan->{$kolom},
                    $request->file($kolom),
                    $jenis,
                );

                continue;
            }

            if ($request->boolean("hapus_{$kolom}")) {
                $this->unggahan->hapus($pengaturan->{$kolom});
                $hasil[$kolom] = null;
            }
        }

        return $hasil;
    }
}
