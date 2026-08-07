<?php

namespace App\Imports;

use App\Models\Penduduk;
use App\Queries\StatistikPublik;
use Carbon\CarbonInterface;
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
     * Banyaknya baris per perintah INSERT.
     *
     * Satu berkas boleh memuat 2.000 baris (config/impor.php). Menyimpannya
     * satu per satu berarti 2.000 perjalanan bolak-balik ke basis data dalam
     * satu transaksi — pada hosting bersama, itu selisih antara impor yang
     * selesai dan impor yang kehabisan waktu. Dipotong agar perintahnya tidak
     * melampaui batas ukuran paket maupun jumlah placeholder.
     */
    private const BARIS_PER_PERINTAH = 250;

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
            $sekarang = now();

            $akanDisimpan->merge($gandaDiterima)
                ->map(fn (BarisImpor $baris) => $this->barisSiapSimpan($baris, $sekarang))
                ->chunk(self::BARIS_PER_PERINTAH)
                ->each(fn ($potongan) => Penduduk::insert($potongan->values()->all()));
        });

        // insert() massal tidak memicu event model, jadi cache statistik
        // publik tidak ikut terbuang dengan sendirinya. Tanpa baris ini,
        // angka di beranda tertinggal sampai cachenya kedaluwarsa.
        StatistikPublik::lupakanCache();

        return new HasilImpor(
            berhasil: $akanDisimpan->count() + $gandaDiterima->count(),
            gandaDiterima: $gandaDiterima->count(),
            gandaDilewati: $gandaDilewati->count(),
            gagal: count($analisis->gagal),
        );
    }

    /**
     * Satu baris dalam bentuk yang siap dikirim ke insert() massal.
     *
     * Modelnya tetap dihidrasi lebih dulu, bukan dilewati: data hasil analisis
     * memuat objek enum dan tanggal, sedangkan insert() mengirim nilainya apa
     * adanya ke basis data. Hidrasi inilah yang menerapkan cast, sehingga
     * hasilnya sama persis dengan jalur create() satu per satu.
     *
     * @return array<string, mixed>
     */
    private function barisSiapSimpan(BarisImpor $baris, CarbonInterface $sekarang): array
    {
        return (new Penduduk($baris->data))->getAttributes() + [
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ];
    }
}
