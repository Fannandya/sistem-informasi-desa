<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Queries\StatistikPublik;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Fitur 15 — statistik publik.
 *
 * Endpoint di sini sengaja tidak menerima parameter apa pun. Menerima
 * parameter berarti membuka jalan mempersempit hasil sampai tingkat RT atau
 * kelompok kecil — persis yang dilarang PRD 2.6.
 */
class StatistikController extends Controller
{
    public function __construct(private readonly StatistikPublik $statistik) {}

    public function halaman(): View
    {
        return view('publik.statistik', [
            'ringkasan' => $this->statistik->ringkasan(),
            'sebaranDusun' => $this->statistik->sebaranDusun(),
            'sebaranUmur' => $this->statistik->sebaranUmur(),
            'pendidikan' => $this->statistik->pendidikan(),
            'jumlahDusun' => $this->statistik->jumlahDusun(),
            'tanggalPembaruan' => $this->statistik->tanggalPembaruan(),
        ]);
    }

    public function data(string $jenis): JsonResponse
    {
        abort_unless(in_array($jenis, ['umur', 'gender', 'pendidikan'], true), 404);

        return response()->json(['data' => $this->statistik->untukGrafik($jenis)]);
    }
}
