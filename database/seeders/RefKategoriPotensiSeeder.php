<?php

namespace Database\Seeders;

use App\Models\RefKategoriPotensi;
use Illuminate\Database\Seeder;

class RefKategoriPotensiSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            'UMKM',
            'Hasil Bumi',
            'Wisata',
            'Kerajinan',
        ];

        foreach ($kategori as $index => $nama) {
            RefKategoriPotensi::create([
                'nama' => $nama,
                'urutan' => $index,
            ]);
        }
    }
}
