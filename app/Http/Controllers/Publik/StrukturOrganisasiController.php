<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\Pengurus;
use Illuminate\View\View;

class StrukturOrganisasiController extends Controller
{
    public function __invoke(): View
    {
        // Urutan kelompok mengikuti kolom `urutan` pada data referensi
        // organisasi, sehingga admin menentukannya sendiri tanpa mengubah kode.
        $pengurus = Pengurus::query()
            ->tampil()
            ->with(['organisasi', 'wilayah'])
            ->join('ref_organisasi', 'pengurus_desa.organisasi_id', '=', 'ref_organisasi.id')
            ->orderBy('ref_organisasi.urutan')
            ->orderBy('ref_organisasi.nama')
            ->orderBy('pengurus_desa.urutan')
            ->orderBy('pengurus_desa.nama')
            ->select('pengurus_desa.*')
            ->get();

        return view('publik.struktur-organisasi', [
            'kelompok' => $pengurus->groupBy(fn (Pengurus $p) => $p->organisasi->nama),
        ]);
    }
}
