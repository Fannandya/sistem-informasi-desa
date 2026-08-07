<?php

namespace Database\Seeders;

use App\Models\RefAgama;
use Illuminate\Database\Seeder;

class RefAgamaSeeder extends Seeder
{
    private const AGAMA = [
        'Islam',
        'Kristen',
        'Katolik',
        'Hindu',
        'Buddha',
        'Konghucu',
        'Tidak Ingin Menyebutkan',
    ];

    public function run(): void
    {
        foreach (self::AGAMA as $nama) {
            RefAgama::firstOrCreate(['nama' => $nama], ['is_template' => true]);
        }
    }
}
