<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Kerangka dashboard. Kartu ringkasan dan grafik diisi pada fitur 06
     * (Statistik Warga), setelah data penduduk dan rekap KK tersedia.
     */
    public function __invoke(): View
    {
        return view('admin.dashboard');
    }
}
