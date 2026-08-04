<?php

namespace App\Imports;

/**
 * Ringkasan hasil satu kali proses impor, untuk ditampilkan kembali ke admin.
 */
class HasilImporPenduduk
{
    /** @var array<int, array{baris: int, nama: string, alasan: string}> */
    public array $gagal = [];

    /** @var array<int, array{baris: int, nama: string, keterangan: string}> */
    public array $duplikat = [];

    public int $berhasil = 0;

    public int $totalBaris = 0;

    public function catatGagal(int $baris, string $nama, string $alasan): void
    {
        $this->gagal[] = ['baris' => $baris, 'nama' => $nama, 'alasan' => $alasan];
    }

    public function catatDuplikat(int $baris, string $nama, string $keterangan): void
    {
        $this->duplikat[] = ['baris' => $baris, 'nama' => $nama, 'keterangan' => $keterangan];
    }

    public function adaMasalah(): bool
    {
        return $this->gagal !== [] || $this->duplikat !== [];
    }

    public function ringkasan(): string
    {
        $bagian = ["{$this->berhasil} baris berhasil diimpor"];

        if ($this->duplikat !== []) {
            $bagian[] = count($this->duplikat).' dilewati karena terdeteksi ganda';
        }

        if ($this->gagal !== []) {
            $bagian[] = count($this->gagal).' gagal';
        }

        return implode(', ', $bagian).'.';
    }
}
