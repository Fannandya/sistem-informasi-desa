<?php

namespace App\Imports;

/**
 * Hasil pembacaan berkas impor, sebelum satu baris pun disimpan.
 *
 * Seluruh isi objek ini ditampilkan ke admin di halaman pratinjau. Sistem
 * tidak mengambil keputusan apa pun di sini — ia hanya melaporkan apa yang
 * ditemukannya.
 */
class HasilAnalisisImpor
{
    /** @var array<int, BarisImpor> baris yang siap ditambahkan tanpa keberatan */
    public array $siap = [];

    /** @var array<int, BarisImpor> baris yang mirip data yang sudah ada */
    public array $ganda = [];

    /** @var array<int, array{baris: int, nama: string, alasan: string}> */
    public array $gagal = [];

    public int $totalBaris = 0;

    public function adaGanda(): bool
    {
        return $this->ganda !== [];
    }

    public function adaGagal(): bool
    {
        return $this->gagal !== [];
    }

    /**
     * Tidak ada yang bisa diimpor sama sekali — tombol Lanjut tidak berguna.
     */
    public function tidakAdaYangBisaDiimpor(): bool
    {
        return $this->siap === [] && $this->ganda === [];
    }

    /**
     * @return array<int, int> nomor baris seluruh data ganda
     */
    public function nomorBarisGanda(): array
    {
        return array_map(fn (BarisImpor $b) => $b->nomorBaris, $this->ganda);
    }
}
