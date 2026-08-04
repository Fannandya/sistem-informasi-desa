<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penduduk;
use App\Queries\PencarianPenduduk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fitur 04 — arsip warga.
 *
 * Soft delete tanpa halaman arsip sama saja dengan penghapusan permanen dari
 * sudut pandang admin: datanya ada di database tapi tidak bisa dilihat atau
 * dikembalikan. Halaman ini yang membuat "diarsipkan" benar-benar berbeda
 * dari "dihapus".
 */
class ArsipPendudukController extends Controller
{
    private const PER_HALAMAN = 50;

    public function index(Request $request): View
    {
        $pencarian = PencarianPenduduk::dariRequest($request);

        $daftar = $pencarian
            ->terapkan(Penduduk::query()->terarsip()->with(['wilayah', 'pekerjaan']))
            ->orderByDesc('tanggal_keluar')
            ->orderBy('nama_lengkap')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('admin.penduduk.arsip', [
            'daftar' => $daftar,
            'pencarian' => $pencarian,
        ]);
    }

    /**
     * Mengembalikan warga ke daftar aktif. Data mutasi keluar dikosongkan,
     * supaya warga yang dipulihkan tidak ikut terhitung pada statistik
     * kematian tahun berjalan.
     */
    public function pulihkan(int $id): RedirectResponse
    {
        $penduduk = Penduduk::onlyTrashed()->findOrFail($id);

        $penduduk->restore();
        $penduduk->forceFill([
            'alasan_keluar' => null,
            'tanggal_keluar' => null,
        ])->save();

        return redirect()
            ->route('admin.penduduk.arsip')
            ->with('status', "Data {$penduduk->nama_lengkap} dipulihkan ke daftar warga aktif.");
    }

    /**
     * Menghapus warga dari database untuk selamanya.
     *
     * Hanya bisa dari halaman arsip: warga harus diarsipkan lebih dulu,
     * sehingga tidak ada data yang lenyap tanpa pernah melewati langkah
     * "keluar dari daftar aktif" beserta alasannya.
     *
     * Admin wajib mengetik ulang nama warga, dan pengecekannya dilakukan di
     * sini — bukan hanya di peramban. Pengaman yang hanya hidup di sisi klien
     * bukan pengaman.
     */
    public function hapusPermanen(Request $request, int $id): RedirectResponse
    {
        $penduduk = Penduduk::onlyTrashed()->findOrFail($id);

        $request->validate([
            'konfirmasi_nama' => ['required', 'string'],
        ], [
            'konfirmasi_nama.required' => 'Ketik ulang nama warga untuk menghapusnya secara permanen.',
        ]);

        if ($this->normalkan($request->input('konfirmasi_nama')) !== $this->normalkan($penduduk->nama_lengkap)) {
            return back()->with('galat', sprintf(
                'Nama yang diketik tidak cocok dengan "%s". Data tidak dihapus.',
                $penduduk->nama_lengkap,
            ));
        }

        $nama = $penduduk->nama_lengkap;
        $memengaruhiStatistik = $penduduk->alasan_keluar?->value === 'meninggal';
        $tahun = $penduduk->tanggal_keluar?->year;

        $penduduk->forceDelete();

        $pesan = "Data {$nama} dihapus permanen dari database.";

        // Angka kematian tahun itu ikut berubah. Admin perlu tahu, karena
        // laporan yang sudah terlanjur dibuat tidak lagi cocok.
        if ($memengaruhiStatistik && $tahun) {
            $pesan .= " Angka kematian tahun {$tahun} ikut berkurang satu.";
        }

        return redirect()->route('admin.penduduk.arsip')->with('status', $pesan);
    }

    private function normalkan(?string $nama): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $nama)));
    }
}
