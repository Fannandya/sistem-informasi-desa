<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanRekapKkRequest;
use App\Models\RekapKk;
use App\Models\Wilayah;
use App\Queries\RingkasanKk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RekapKkController extends Controller
{
    /** Berapa tahun ke belakang yang boleh dipilih dari pemilih tahun. */
    private const TAHUN_KE_BELAKANG = 5;

    public function index(Request $request): View
    {
        $tahun = $this->tahunTerpilih($request);

        $wilayah = Wilayah::urut()->get();
        $tersimpan = RekapKk::tahun($tahun)->pluck('jumlah_kk', 'wilayah_id');

        $terakhirDiperbarui = RekapKk::tahun($tahun)
            ->with('admin')
            ->latest('updated_at')
            ->first();

        return view('admin.rekap-kk.index', [
            'tahun' => $tahun,
            'tahunTersedia' => $this->tahunTersedia(),
            'kelompok' => $wilayah->groupBy('dusun'),
            'tersimpan' => $tersimpan,
            'ringkasan' => new RingkasanKk($tahun),
            'terakhirDiperbarui' => $terakhirDiperbarui,
            'adaTahunSebelumnya' => RekapKk::tahun($tahun - 1)->exists(),
        ]);
    }

    public function simpan(SimpanRekapKkRequest $request, int $tahun): RedirectResponse
    {
        abort_unless($this->tahunSah($tahun), 404);

        $isian = $request->isian();
        $wilayahSah = Wilayah::pluck('id')->all();
        $adminId = $request->user('admin')->id;

        DB::transaction(function () use ($isian, $wilayahSah, $tahun, $adminId): void {
            foreach ($isian as $wilayahId => $jumlah) {
                if (! in_array($wilayahId, $wilayahSah, true)) {
                    continue;
                }

                if ($jumlah === null) {
                    // Isian yang dikosongkan dikembalikan menjadi "belum
                    // diisi", bukan disimpan sebagai nol.
                    RekapKk::tahun($tahun)->where('wilayah_id', $wilayahId)->delete();

                    continue;
                }

                RekapKk::updateOrCreate(
                    ['wilayah_id' => $wilayahId, 'tahun' => $tahun],
                    ['jumlah_kk' => $jumlah, 'admin_id' => $adminId],
                );
            }
        });

        $ringkasan = new RingkasanKk($tahun);

        $pesan = "Rekap KK tahun {$tahun} tersimpan. ".$ringkasan->keteranganKelengkapan();

        return redirect()
            ->route('admin.rekap-kk.index', ['tahun' => $tahun])
            ->with('status', $pesan);
    }

    /**
     * Menyalin angka tahun sebelumnya sebagai titik awal pengisian.
     *
     * Angka hanya dimuat ke formulir, tidak langsung disimpan — admin masih
     * harus memeriksa dan menekan simpan. Menyimpan langsung akan membuat
     * angka tahun lalu terlanjur tercatat sebagai angka tahun ini meski
     * belum diverifikasi siapa pun.
     */
    public function salinTahunSebelumnya(int $tahun): RedirectResponse
    {
        abort_unless($this->tahunSah($tahun), 404);

        $sebelumnya = RekapKk::tahun($tahun - 1)->pluck('jumlah_kk', 'wilayah_id');

        if ($sebelumnya->isEmpty()) {
            return back()->with('galat', "Belum ada rekap KK tahun ".($tahun - 1).' yang bisa disalin.');
        }

        return redirect()
            ->route('admin.rekap-kk.index', ['tahun' => $tahun])
            ->withInput(['jumlah' => $sebelumnya->all()])
            ->with('status', sprintf(
                'Angka tahun %d dimuat ke formulir (%d RT). Periksa lalu tekan Simpan — belum tersimpan.',
                $tahun - 1,
                $sebelumnya->count(),
            ));
    }

    private function tahunTerpilih(Request $request): int
    {
        $tahun = (int) $request->input('tahun', now()->year);

        return $this->tahunSah($tahun) ? $tahun : now()->year;
    }

    private function tahunSah(int $tahun): bool
    {
        return $tahun >= now()->year - 20 && $tahun <= now()->year;
    }

    /**
     * @return array<int, int>
     */
    private function tahunTersedia(): array
    {
        $dariData = RekapKk::distinct()->pluck('tahun')->map(fn ($t) => (int) $t);

        $bawaan = collect(range(now()->year, now()->year - self::TAHUN_KE_BELAKANG));

        return $bawaan->merge($dariData)
            ->unique()
            ->filter(fn (int $t) => $this->tahunSah($t))
            ->sortDesc()
            ->values()
            ->all();
    }
}
