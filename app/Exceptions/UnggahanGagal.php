<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Kegagalan unggah gambar yang pesannya aman ditampilkan ke admin.
 *
 * Pesan sengaja menyebut batas dan format yang diizinkan, karena admin desa
 * perlu tahu apa yang harus diperbaiki — bukan sekadar "terjadi kesalahan".
 */
class UnggahanGagal extends RuntimeException
{
    public static function formatTidakDidukung(string $format): self
    {
        $diizinkan = implode(', ', config('unggahan.ekstensi_diizinkan'));

        return new self(
            "Format berkas \"{$format}\" tidak didukung. Gunakan {$diizinkan}.",
        );
    }

    public static function terlaluBesar(): self
    {
        $maksMb = round(config('unggahan.maks_kb') / 1024, 1);

        return new self(
            "Ukuran gambar melebihi batas {$maksMb} MB. Perkecil gambar lalu coba lagi.",
        );
    }

    public static function berkasTidakValid(): self
    {
        return new self('Berkas gagal diunggah. Coba pilih ulang gambarnya.');
    }

    public static function berkasRusak(?Throwable $sebelumnya = null): self
    {
        return new self(
            'Berkas tidak dapat dibaca sebagai gambar. Pastikan berkasnya tidak rusak.',
            0,
            $sebelumnya,
        );
    }

    public static function penyimpananTidakTerjangkau(?Throwable $sebelumnya = null): self
    {
        return new self(
            'Penyimpanan gambar sedang tidak dapat dijangkau, sehingga data belum disimpan. '
            .'Coba lagi beberapa saat lagi.',
            0,
            $sebelumnya,
        );
    }

    public static function jenisTidakDikenal(string $jenis): self
    {
        return new self("Jenis unggahan \"{$jenis}\" tidak terdaftar di config/unggahan.php.");
    }
}
