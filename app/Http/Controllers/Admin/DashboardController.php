<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penduduk;
use App\Queries\RingkasanKk;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Kartu ringkasan. Grafik demografi menyusul pada fitur 06.
     */
    public function __invoke(): View
    {
        $tahun = now()->year;
        $kk = new RingkasanKk();

        return view('admin.dashboard', [
            'totalWarga' => Penduduk::query()->aktif()->count(),
            'kk' => $kk,
            'kelahiran' => Penduduk::query()->lahirPadaTahun($tahun)->count(),
            'kematian' => Penduduk::query()->meninggalPadaTahun($tahun)->count(),
            'tahun' => $tahun,
        ]);
    }
}
