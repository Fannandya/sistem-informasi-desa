<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Berkas impor ditolak sebelum satu baris pun diproses.
 *
 * Berbeda dari baris yang gagal: ini menolak keseluruhan berkas, karena
 * masalahnya ada pada bentuk berkas itu sendiri.
 */
class ImporDitolak extends RuntimeException
{
    public static function berkasKosong(): self
    {
        return new self('Berkas tidak berisi data. Pastikan baris pertama adalah judul kolom dan ada minimal satu baris data.');
    }

    /**
     * @param  array<int, string>  $kolom
     */
    public static function kolomWajibHilang(array $kolom): self
    {
        $daftar = implode(', ', array_map(fn ($k) => ucwords($k), $kolom));

        return new self(
            "Kolom wajib belum ada di berkas: {$daftar}. "
            .'Unduh berkas contoh untuk melihat susunan kolom yang benar.',
        );
    }

    public static function kolomTerlarang(string $kolom): self
    {
        return new self(
            "Berkas memuat kolom \"{$kolom}\" yang tidak boleh disimpan sistem ini. "
            .'Hapus kolom tersebut dari berkas, lalu unggah ulang. '
            .'Tidak ada satu baris pun yang diimpor.',
        );
    }

    public static function terlaluBanyakBaris(int $jumlah, int $maks): self
    {
        return new self(sprintf(
            'Berkas berisi %s baris, melebihi batas %s baris per impor. '
            .'Pecah berkas menjadi beberapa bagian lalu unggah bergantian.',
            number_format($jumlah, 0, ',', '.'),
            number_format($maks, 0, ',', '.'),
        ));
    }
}
