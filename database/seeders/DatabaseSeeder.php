<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            PengaturanDesaSeeder::class,
            WilayahSeeder::class,
            RefPekerjaanSeeder::class,
            RefAgamaSeeder::class,
            RefKategoriPotensiSeeder::class,
            RefKategoriFasilitasSeeder::class,
            PendudukSeeder::class,
            RekapKkSeeder::class,
            PengurusSeeder::class,
            PotensiSeeder::class,
            FasilitasPrestasiSeeder::class,
            BeritaSeeder::class,
        ]);
    }
}
