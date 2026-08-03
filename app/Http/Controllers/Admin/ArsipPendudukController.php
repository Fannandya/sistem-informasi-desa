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
}
