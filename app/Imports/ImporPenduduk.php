<?php

namespace App\Imports;

use App\Models\Penduduk;
use Illuminate\Support\Facades\DB;

/**
 * Fitur 04 — menyimpan hasil impor yang sudah disetujui admin.
 *
 * Kelas ini tidak mengambil keputusan apa pun. Ia hanya menuliskan baris yang
 * dinyatakan siap oleh AnalisisImpor, ditambah baris ganda yang secara
 * eksplisit dicentang admin di halaman pratinjau.
 *
 * Seluruh penyimpanan berjalan dalam satu transaksi: impor yang gagal di
 * tengah jalan tidak boleh meninggalkan separuh data, karena admin akan
 * mengulang impor dan separuh yang terlanjur masuk menjadi duplikat yang
 * sulit ditelusuri.
 */
class ImporPenduduk
{
    /**
     * @param  array<int, int>  $barisGandaDisetujui  nomor baris yang dicentang admin
     */
    public function simpan(HasilAnalisisImpor $analisis, array $barisGandaDisetujui = []): HasilImpor
    {
        $disetujui = array_flip($barisGandaDisetujui);

        $akanDisimpan = collect($analisis->siap);

        $gandaDiterima = collect($analisis->ganda)
            ->filter(fn (BarisImpor $b) => isset($disetujui[$b->nomorBaris]));

        $gandaDilewati = collect($analisis->ganda)
            ->reject(fn (BarisImpor $b) => isset($disetujui[$b->nomorBaris]));

        DB::transaction(function () use ($akanDisimpan, $gandaDiterima): void {
            foreach ($akanDisimpan->merge($gandaDiterima) as $baris) {
                Penduduk::create($baris->data);
            }
        });

        return new HasilImpor(
            berhasil: $akanDisimpan->count() + $gandaDiterima->count(),
            gandaDiterima: $gandaDiterima->count(),
            gandaDilewati: $gandaDilewati->count(),
            gagal: count($analisis->gagal),
        );
    }
}
