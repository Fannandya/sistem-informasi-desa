<?php

namespace Database\Seeders;

use App\Models\RefPekerjaan;
use Illuminate\Database\Seeder;

class RefPekerjaanSeeder extends Seeder
{
    private const PEKERJAAN = [
        'Petani',
        'Buruh Tani',
        'Pedagang',
        'Wiraswasta',
        'Karyawan Swasta',
        'Buruh Harian Lepas',
        'PNS',
        'Guru',
        'TNI / Polri',
        'Tenaga Kesehatan',
        'Peternak',
        'Pengrajin',
        'Sopir',
        'Nelayan',
        'Pelajar / Mahasiswa',
        'Mengurus Rumah Tangga',
        'Pensiunan',
        'Belum / Tidak Bekerja',
    ];

    public function run(): void
    {
        foreach (self::PEKERJAAN as $nama) {
            RefPekerjaan::firstOrCreate(['nama' => $nama]);
        }
    }
}
