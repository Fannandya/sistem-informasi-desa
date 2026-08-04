<?php

namespace Database\Seeders;

use App\Models\RefKategoriFasilitas;
use Illuminate\Database\Seeder;

class RefKategoriFasilitasSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            'Pendidikan',
            'Kesehatan',
            'Ibadah',
            'Pemerintahan',
            'Olahraga',
            'Umum',
        ];

        foreach ($kategori as $index => $nama) {
            RefKategoriFasilitas::create([
                'nama' => $nama,
                'urutan' => $index,
            ]);
        }
    }
}
