<?php

namespace App\Imports;

/**
 * Ringkasan apa yang benar-benar tersimpan, untuk dilaporkan kembali ke admin.
 */
class HasilImpor
{
    public function __construct(
        public readonly int $berhasil,
        public readonly int $gandaDiterima,
        public readonly int $gandaDilewati,
        public readonly int $gagal,
    ) {}

    /**
     * Kalimat laporan yang menyebut secara terbuka keputusan admin sendiri,
     * bukan keputusan yang diambil sistem.
     */
    public function ringkasan(): string
    {
        $bagian = [$this->berhasil.' warga ditambahkan'];

        if ($this->gandaDiterima > 0) {
            $bagian[] = "termasuk {$this->gandaDiterima} yang Anda setujui meski terdeteksi ganda";
        }

        if ($this->gandaDilewati > 0) {
            $bagian[] = "{$this->gandaDilewati} data ganda tidak jadi diimpor";
        }

        if ($this->gagal > 0) {
            $bagian[] = "{$this->gagal} baris bermasalah dilewati";
        }

        return implode(', ', $bagian).'.';
    }
}
