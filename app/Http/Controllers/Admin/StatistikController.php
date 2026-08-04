<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Queries\StatistikPenduduk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Fitur 06 — data grafik dashboard admin.
 *
 * Bukan API publik: rute-rute ini berada di dalam grup admin dan tunduk pada
 * middleware yang sama. Grafik memuatnya lewat fetch agar halaman dashboard
 * tampil lebih dulu, tidak menunggu seluruh agregasi selesai.
 */
class StatistikController extends Controller
{
    public function __construct(private readonly StatistikPenduduk $statistik) {}

    public function sebaranUmur(): JsonResponse
    {
        return response()->json([
            'data' => $this->statistik->sebaranUmur(),
            'total' => $this->statistik->totalWargaAktif(),
        ]);
    }

    public function rasioGender(): JsonResponse
    {
        return response()->json(['data' => $this->statistik->rasioGender()]);
    }

    public function mataPencaharian(): JsonResponse
    {
        return response()->json(['data' => $this->statistik->mataPencaharian()]);
    }

    public function pendidikan(): JsonResponse
    {
        return response()->json(['data' => $this->statistik->pendidikan()]);
    }

    public function sebaranWilayah(Request $request): JsonResponse
    {
        $tingkat = in_array($request->input('tingkat'), ['dusun', 'rw', 'rt'], true)
            ? $request->input('tingkat')
            : 'dusun';

        $urutan = $request->input('urutan') === 'asc' ? 'asc' : 'desc';

        return response()->json([
            'data' => $this->statistik->sebaranWilayah($tingkat, $urutan),
            'tingkat' => $tingkat,
            'urutan' => $urutan,
        ]);
    }

    public function trenMutasi(Request $request): JsonResponse
    {
        $jumlahTahun = (int) $request->input('tahun', 5);
        $jumlahTahun = max(2, min(15, $jumlahTahun));

        return response()->json([
            'data' => $this->statistik->trenMutasi($jumlahTahun),
            'jumlah_tahun' => $jumlahTahun,
        ]);
    }
}
