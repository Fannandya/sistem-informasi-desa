<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Pembaca berkas apa adanya.
 *
 * Sengaja tidak memakai WithHeadingRow: baris judul harus ikut terbaca supaya
 * ImporPenduduk bisa memeriksanya sendiri terhadap kolom terlarang sebelum
 * satu baris pun diproses.
 */
class BarisMentah implements ToArray
{
    public function array(array $array): void
    {
        // Tidak melakukan apa pun. Excel::toArray() sudah mengembalikan
        // barisnya, kelas ini hanya penanda cara pembacaan.
    }
}
